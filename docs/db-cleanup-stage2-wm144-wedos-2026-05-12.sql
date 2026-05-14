-- DB cleanup STAGE 2 for fajntabory production incident.
-- Target dump: /Users/kamilbaranek/dev/fajntabory/DB/wm144_wedos_net.sql
-- Date: 2026-05-12
--
-- Doplnek k `docs/db-cleanup-wm144-wedos-2026-05-12.sql`. Stage 1 cisti:
--   - trigger after_insert_comment, skodlive ucty, session_tokens,
--     active_plugins, plugin transient cache, cron entries pro fake pluginy.
-- Stage 2 cisti to, co stage 1 minul:
--   - SEO spam injection skryta v `wp_posts.post_content` (16 vyskytu / ~13 stranek);
--   - akumulovane stale reference na fake pluginy v acf_site_health JSON;
--   - audit/flag dotazy pro ucty existujici pred incidentem (admin, admin1)
--     a pro pravdepodobne kompromitovane legit ucty (silva.gloserova, fajntabory,
--     veronika.poukova) — vyhodnoti operator rucne.
--
-- Pred spustenim:
--   - mit forenzni kopii puvodni DB (dump je v /Users/kamilbaranek/dev/fajntabory/DB/);
--   - idealne nejdrive stage 1, pak stage 2 (poradi nezavisla, ale stage 1 odstrani
--     trigger ktery muze pri praci behet);
--   - vse zabaleno do transakce, ktera se na konci commitne pouze rucne
--     (vychozi je ROLLBACK aby preflight vystupy nezpusobily nahodnou zmenu).

SET @cleanup_started_at = NOW();
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

START TRANSACTION;

-- ============================================================================
-- 1) PREFLIGHT: vypsat dotcene radky pred zmenou
-- ============================================================================
-- Operator si overi, ze pocet a ID stranek sedi s ocekavanim z analyzy.

SELECT 'preflight_seo_spam_posts' AS check_name, ID, post_type, post_status, post_title, post_modified
FROM wp_posts
WHERE post_content REGEXP 'position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px'
ORDER BY ID;

SELECT 'preflight_seo_spam_count' AS check_name,
       COUNT(*) AS affected_rows,
       SUM((LENGTH(post_content) - LENGTH(
         REGEXP_REPLACE(
           post_content,
           '(?s)[\r\n]*<div style="position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px;[^"]*">.*?</div>[\r\n]*',
           ''
         )
       ))) AS bytes_to_remove
FROM wp_posts
WHERE post_content REGEXP 'position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px';

-- ============================================================================
-- 2) ODSTRANIT SKRYTE SEO SPAM DIVY z post_content
-- ============================================================================
-- Pattern: <div style="position: fixed; top: -NNNNpx; left: -NNNNpx;">...spam...</div>
-- 30+ unikatnich domen kasin/sazek (olympfrance, casinomillionz, chicken-roadcasino, ...).
-- Pouziva non-greedy `.*?` aby trefilo jen jeden konkretni spam div, ne celou stranku.
-- Pokrocila kontrola: regex vyzaduje style atribut zacinajici position:fixed,
-- coz se v legit obsahu Fajn Taborů nevyskytuje.

UPDATE wp_posts
SET post_content = REGEXP_REPLACE(
      post_content,
      '(?s)[\r\n]*<div style="position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px;[^"]*">.*?</div>[\r\n]*',
      ''
    ),
    post_modified = post_modified,
    post_modified_gmt = post_modified_gmt
WHERE post_content REGEXP 'position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px';

-- Postmeta sanity: stejne pravidlo by mohlo trefit i postmeta hodnoty, ale
-- preflight v dumpu zadny vyskyt mimo wp_posts nenasel. Nicmene ponechavam
-- ochrannou kontrolu.

SELECT 'preflight_seo_spam_postmeta' AS check_name, post_id, meta_key, LEFT(meta_value, 200) AS preview
FROM wp_postmeta
WHERE meta_value REGEXP 'position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px';

-- ============================================================================
-- 3) ACF SITE HEALTH: vyresetovat na prazdny JSON
-- ============================================================================
-- Klic `acf_site_health` cachuje seznam aktivnich pluginu z doby napadeni,
-- vcetne `worker` (ManageWP). Reseni: smazat, ACF si pri pristim runu
-- regeneruje cache jiz s ocistenym pluginovym seznamem.

DELETE FROM wp_options WHERE option_name = 'acf_site_health';

-- Defensive: smazat freemius cache (fs_active_plugins) pokud byl naplnen
-- behem incidentu — pri pristim runu se regeneruje z aktualniho stavu.

DELETE FROM wp_options
WHERE option_name IN ('fs_active_plugins', 'fs_accounts', 'fs_dynamic_init')
  AND option_value LIKE '%worker%';

-- ============================================================================
-- 4) AUDIT: ucty vytvorene PRED zname casovkou incidentu
-- ============================================================================
-- User 1 (admin / info@netbrana.eu, 2017) a user 8 (admin1 / wp@antstudio.eu, 2025-01-23)
-- vznikly pred jasnymi IOC. Mohou byt legitimni (puvodni vyvojar / agentura)
-- nebo davna persistence. Operator je musi overit s majitelem.

SELECT 'pre_incident_user_audit' AS check_name,
       u.ID, u.user_login, u.user_email, u.user_registered, u.display_name,
       (SELECT meta_value FROM wp_usermeta WHERE user_id = u.ID AND meta_key = 'wp_capabilities' LIMIT 1) AS capabilities,
       (SELECT meta_value FROM wp_usermeta WHERE user_id = u.ID AND meta_key = 'session_tokens' LIMIT 1) AS session_tokens
FROM wp_users u
WHERE u.ID IN (1, 8)
ORDER BY u.ID;

-- ============================================================================
-- 5) AUDIT: legit ucty s podezrelymi session vzorci
-- ============================================================================
-- silva.gloserova (user 11) mela v dumpu 25 aktivnich sessions z desitek
-- nesouvisejicich IP vcetne curl/Wget user-agenta — kompromitovany legit ucet.
-- fajntabory (user 3) a veronika.poukova (user 9) mely sessions z IP 188.119.97.162.
-- Stage 1 sice smazala session_tokens, ale heslo techto uctu mohlo byt odcizene
-- a musi byt rotovane MIMO SQL (ucet vlastnika provede password reset).

SELECT 'compromised_legit_users_to_rotate' AS check_name,
       u.ID, u.user_login, u.user_email, u.user_registered,
       'PASSWORD MUST BE ROTATED BY USER OUT-OF-BAND' AS action_required
FROM wp_users u
WHERE u.user_login IN ('silva.gloserova', 'fajntabory', 'veronika.poukova')
ORDER BY u.ID;

-- ============================================================================
-- 6) AUDIT: spousteci fraze v komentarich (nadbytek nad stage 1)
-- ============================================================================
-- Stage 1 smaze konkretni frazi pro trigger. Stage 2 hleda potencialni varianty
-- (case-insensitive, ruzne formulace), abychom meli jistotu ze nezbyl zarodek.

SELECT 'trigger_phrase_residue_audit' AS check_name,
       comment_ID, comment_post_ID, comment_author, comment_date, LEFT(comment_content, 200) AS preview
FROM wp_comments
WHERE comment_content REGEXP '(?i)(struggling[[:space:]]+to[[:space:]]+get[[:space:]]+comments|are[[:space:]]+you[[:space:]]+struggling)'
   OR comment_content LIKE '%cmsrss%'
   OR comment_content LIKE '%wnadmin%';

-- ============================================================================
-- 7) AUDIT: zbytkove IOC v post_content / postmeta
-- ============================================================================
-- Verifikace, ze v dumpu nezustava jiny vektor SEO spam injection.

SELECT 'residual_spam_domains_in_posts' AS check_name, ID, post_title, post_modified
FROM wp_posts
WHERE post_content REGEXP '(?i)(casinomillionz|olympfrance|casinovegashero|chicken-roadcasino|amon-casino-fr|boomerangcasinoo|casino-roman|casinohappyhugo|casinomonsterwin|casinosdragonia|casinospistolo|casinovascasino|lunubet-casino|montecryptocasino|mystakes-casino|novajackpotcasino|olympcasino|spinsy-casino|tortugacasinos|twincasino-online|vegashero-casino|verdescasino|win-bet-casino|banerpanel\\.live|betgitguncel)';

SELECT 'residual_spam_in_postmeta' AS check_name, post_id, meta_key, LEFT(meta_value, 200) AS preview
FROM wp_postmeta
WHERE meta_value REGEXP '(?i)(casinomillionz|olympfrance|casinovegashero|chicken-roadcasino|banerpanel\\.live|betgitguncel)';

-- ============================================================================
-- 8) AUDIT: kontrola po-cleanup stavu klicovych voleb
-- ============================================================================

SELECT 'active_plugins_after_cleanup' AS check_name, option_value
FROM wp_options
WHERE option_name = 'active_plugins';

SELECT 'autoload_options_with_known_iocs' AS check_name, option_id, option_name, LEFT(option_value, 200) AS preview
FROM wp_options
WHERE autoload IN ('on', 'yes', 'auto')
  AND (
       option_value LIKE '%backup_1778142536%'
    OR option_value LIKE '%gallery-1778349134%'
    OR option_value LIKE '%wp-security-helper%'
    OR option_value LIKE '%banerpanel.live%'
    OR option_value LIKE '%betgitguncel%'
  );

-- ============================================================================
-- 9) AUDIT: jakekoli zbyle triggery / procedury / funkce / udalosti
-- ============================================================================
SELECT 'remaining_triggers' AS check_name, TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE();

SELECT 'remaining_routines' AS check_name, ROUTINE_NAME, ROUTINE_TYPE, CREATED, LAST_ALTERED
FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = DATABASE();

SELECT 'remaining_events' AS check_name, EVENT_NAME, STATUS, CREATED, LAST_ALTERED
FROM information_schema.EVENTS
WHERE EVENT_SCHEMA = DATABASE();

-- ============================================================================
-- 10) ZAVER
-- ============================================================================
SELECT 'cleanup_stage2_finished_at' AS check_name, @cleanup_started_at AS started_at, NOW() AS finished_at;

-- BEZPECNOSTNI POJISTKA:
-- Pokud preflight a audit vystupy NEvypadaji spravne, behem prohlizeni
-- v transakci spust:
--
--   ROLLBACK;
--
-- Pokud vystupy sedi a chces zmeny ulozit:
--
--   COMMIT;
--
-- Defaultne necham otevreny stav — operator MUSI explicitne potvrdit.
