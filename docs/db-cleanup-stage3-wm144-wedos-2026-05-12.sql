-- DB cleanup STAGE 3 for fajntabory production incident.
-- Target dump: /Users/kamilbaranek/dev/fajntabory/DB/wm144_wedos_net.sql
-- Date: 2026-05-12
--
-- Stage 2 cistil skryte SEO injekce v wp_posts (16 div blocks v 13 strankach).
-- Behem audit fáze stage 2 vyplulo, ze utocnik krome injekce vytvoril i
-- KOMPLETNI fake postiky (315+ ks) publikovane v defaultni kategorii
-- "nezarazene" / "Uncategorized": CZ/EN/FR clanky o online kasinech,
-- bonusech bez vkladu, free spinech, atd. Plus nekolik test/cloak stranek
-- s nahodnymi hash slugy.
--
-- Tato faze:
--   1) najde vsechny postiky obsahujici typove znaky kasino spamu;
--   2) vypise je k recenzi pred mazanim;
--   3) provede kaskadovy delete ze vsech navazujicich tabulek:
--      wp_posts, wp_postmeta, wp_term_relationships, wp_comments,
--      wp_yoast_indexable, wp_yoast_indexable_hierarchy, wp_yoast_seo_links,
--      wp_yoast_seo_meta;
--   4) uklidi orphan rows v navazujicich tabulkach;
--   5) vypise post-cleanup verifikaci.
--
-- Detekce je konzervativni: post se oznaci za spam, pokud post_content
-- nebo post_title obsahuje vice gambling/kasino keywordu. Pure slug-based
-- detekce by mohla zachytit i legit "uncategorized" prispevky.
--
-- Pred spustenim:
--   - mit forenzni kopii puvodni DB;
--   - skript je transakcni (defaultne nezacommitnuty) — operator po revizi
--     pust COMMIT; pokud vystupy preflightu sedi.

SET @cleanup_started_at = NOW();
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

START TRANSACTION;

-- ============================================================================
-- 1) Identifikovat spam posty
-- ============================================================================

DROP TEMPORARY TABLE IF EXISTS tmp_ft_spam_posts;
CREATE TEMPORARY TABLE tmp_ft_spam_posts (
  ID BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
  detection_reason VARCHAR(64) NOT NULL
);

-- 1a) Posty s explicitnimi domenami / odkazy na kasina v obsahu.
INSERT IGNORE INTO tmp_ft_spam_posts (ID, detection_reason)
SELECT ID, 'casino_domain_in_content'
FROM wp_posts
WHERE post_content REGEXP
  '(casinomillionz|olympfrance|casinovegashero|chicken-roadcasino|amon-casino-fr|boomerangcasinoo|casino-roman|casinohappyhugo|casinomonsterwin|casinosdragonia|casinospistolo|casinovascasino|lunubet-casino|montecryptocasino|mystakes-casino|novajackpotcasino|olympcasino|spinsy-casino|tortugacasinos|twincasino-online|vegashero-casino|verdescasino|win-bet-casino|banerpanel\\.live|betgitguncel)';

-- 1b) Posty s gambling terminology v titulku.
-- Titulky fake postu jsou priznacne: "lunubet casino bonus...",
-- "Les conditions de mise des bonus...", "free spiny bez vkladu", ...
INSERT IGNORE INTO tmp_ft_spam_posts (ID, detection_reason)
SELECT ID, 'gambling_terms_in_title'
FROM wp_posts
WHERE post_title REGEXP
  '(?i)(casino|kasino|free[[:space:]]+spin|free[[:space:]]+spiny|bonus[[:space:]]+bez[[:space:]]+vkladu|bonus[[:space:]]+za[[:space:]]+registraci|no[[:space:]]+deposit|cashback[[:space:]]+bonus|sazkov|gambling|pokies|blackjack|jackpot|sloty[[:space:]]+online|roulette|ruleta[[:space:]]+online|baccarat|megaways|wager|automaty[[:space:]]+online|hazard)'
  AND post_status IN ('publish', 'draft', 'pending', 'private')
  AND post_type IN ('post', 'page');

-- 1c) Cizojazycne posty (FR/EN) v ramci CZ webu pro deti — nedavaji smysl
-- a v dumpu jsou vyhradne kasino spam. Pouzijeme jen ty s konkretnimi
-- FR/EN gambling termy + dalsi heuristika (slug v /nezarazene/).
INSERT IGNORE INTO tmp_ft_spam_posts (ID, detection_reason)
SELECT ID, 'foreign_lang_gambling_post'
FROM wp_posts
WHERE post_content REGEXP
  '(?i)(les[[:space:]]+(bonus|conditions|amateurs|paris|joueurs|jeux)|d''expérience.*SEO|free[[:space:]]+spins[[:space:]]+no[[:space:]]+deposit|deposit[[:space:]]+bonus[[:space:]]+code|spins?[[:space:]]+at[[:space:]]+best)'
  AND post_status IN ('publish', 'draft', 'pending', 'private')
  AND post_type IN ('post', 'page');

-- 1d) Posty s nahodnymi hash slugy (32-hex znaku) — typicky test/cloak stranky.
INSERT IGNORE INTO tmp_ft_spam_posts (ID, detection_reason)
SELECT ID, 'random_hash_slug'
FROM wp_posts
WHERE post_name REGEXP '^[a-f0-9]{32}$'
  AND post_type IN ('post', 'page');

-- 1e) Cista numerika ve slugu typu "/nezarazene/56309/" — automaticky
-- generovane placeholder stranky utocnikem.
INSERT IGNORE INTO tmp_ft_spam_posts (ID, detection_reason)
SELECT ID, 'numeric_only_slug'
FROM wp_posts
WHERE post_name REGEXP '^[0-9]+$'
  AND post_type = 'post'
  AND post_status IN ('publish', 'draft', 'pending', 'private')
  AND (post_title = '' OR post_title REGEXP '^[0-9]+$');

-- ============================================================================
-- 2) PREFLIGHT: ukazat co se chysta smazat
-- ============================================================================

SELECT 'spam_posts_total' AS check_name, COUNT(*) AS total FROM tmp_ft_spam_posts;

SELECT 'spam_posts_by_reason' AS check_name, detection_reason, COUNT(*) AS count
FROM tmp_ft_spam_posts
GROUP BY detection_reason
ORDER BY count DESC;

-- Ukazka prvnich 30 zachycenych postu pro vizualni overeni.
SELECT 'spam_posts_sample' AS check_name,
       p.ID, p.post_status, p.post_date,
       SUBSTRING(p.post_name, 1, 60) AS slug,
       SUBSTRING(p.post_title, 1, 80) AS title,
       t.detection_reason
FROM tmp_ft_spam_posts t
JOIN wp_posts p ON p.ID = t.ID
ORDER BY p.ID
LIMIT 30;

-- Defensivni kontrola: vypis SPAM postu, ktere maji slug podobny legit
-- prispevkum o letnich taborech, abychom je nahodou nesmazali.
-- Pokud tato kontrola vrati radky, je nutne je rucne prozkoumat
-- a pripadne zruzit z tmp_ft_spam_posts pred DELETE krokem.
SELECT 'POSSIBLE_FALSE_POSITIVES_REVIEW_REQUIRED' AS check_name,
       p.ID, p.post_name, SUBSTRING(p.post_title, 1, 80) AS title, t.detection_reason
FROM tmp_ft_spam_posts t
JOIN wp_posts p ON p.ID = t.ID
WHERE p.post_name REGEXP '(tabor|tabory|fajn|vedouci|deti|rodice|letni-tabor|seznam-vedoucich|hledame-nove)'
   OR p.post_title REGEXP '(?i)(tabor|fajn|deti|rodice|letni|vedouci)';

-- Pocet dotcenych radku v navazujicich tabulkach.
SELECT 'cascade_impact_postmeta' AS check_name, COUNT(*) AS rows_to_delete
FROM wp_postmeta WHERE post_id IN (SELECT ID FROM tmp_ft_spam_posts);

SELECT 'cascade_impact_term_rel' AS check_name, COUNT(*) AS rows_to_delete
FROM wp_term_relationships WHERE object_id IN (SELECT ID FROM tmp_ft_spam_posts);

SELECT 'cascade_impact_comments' AS check_name, COUNT(*) AS rows_to_delete
FROM wp_comments WHERE comment_post_ID IN (SELECT ID FROM tmp_ft_spam_posts);

SELECT 'cascade_impact_yoast_indexable' AS check_name, COUNT(*) AS rows_to_delete
FROM wp_yoast_indexable
WHERE object_type = 'post' AND object_id IN (SELECT ID FROM tmp_ft_spam_posts);

SELECT 'cascade_impact_yoast_seo_links' AS check_name, COUNT(*) AS rows_to_delete
FROM wp_yoast_seo_links
WHERE post_id IN (SELECT ID FROM tmp_ft_spam_posts)
   OR target_post_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- ============================================================================
-- 3) CASCADE DELETE
-- ============================================================================

-- 3a) Yoast indexable hierarchy (musi byt nejdrive, vazane na indexable.id)
DELETE h FROM wp_yoast_indexable_hierarchy h
JOIN wp_yoast_indexable i ON i.id = h.indexable_id
WHERE i.object_type = 'post' AND i.object_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3b) Yoast seo links (post_id i target_post_id)
DELETE FROM wp_yoast_seo_links
WHERE post_id IN (SELECT ID FROM tmp_ft_spam_posts)
   OR target_post_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3c) Yoast primary term
DELETE FROM wp_yoast_primary_term
WHERE post_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3d) Yoast seo meta
DELETE FROM wp_yoast_seo_meta
WHERE object_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3e) Yoast indexable (zaznam typu 'post' s object_id ze spam postu)
DELETE FROM wp_yoast_indexable
WHERE object_type = 'post' AND object_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3f) Comments + commentmeta
DELETE cm FROM wp_commentmeta cm
JOIN wp_comments c ON c.comment_ID = cm.comment_id
WHERE c.comment_post_ID IN (SELECT ID FROM tmp_ft_spam_posts);

DELETE FROM wp_comments
WHERE comment_post_ID IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3g) Term relationships (kategorie, tagy)
DELETE FROM wp_term_relationships
WHERE object_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3h) Postmeta
DELETE FROM wp_postmeta
WHERE post_id IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3i) Posts samotne (vcetne attached revisions a auto-drafts)
DELETE FROM wp_posts
WHERE ID IN (SELECT ID FROM tmp_ft_spam_posts)
   OR post_parent IN (SELECT ID FROM tmp_ft_spam_posts);

-- 3j) Stale Yoast SEO links na kasino domeny ze stage-2 ocistenych legit
-- postu (ID 2,10,11,12,13,65,67,69,71,73,76,1925,3153,13902,13908,28391).
-- Yoast si tyto external links zapamatoval z doby pred odstranenim spam divu;
-- po pristim crawl pasu se regeneruje, ale v dumpu by zustaly zname casino
-- domeny.
DELETE FROM wp_yoast_seo_links
WHERE url REGEXP
  '(?i)(casinomillionz|olympfrance|casinovegashero|chicken-roadcasino|amon-casino-fr|boomerangcasinoo|casino-roman|casinohappyhugo|casinomonsterwin|casinosdragonia|casinospistolo|casinovascasino|lunubet-casino|montecryptocasino|mystakes-casino|novajackpotcasino|olympcasino|spinsy-casino|tortugacasinos|twincasino-online|vegashero-casino|verdescasino|win-bet-casino|banerpanel\\.live|betgitguncel)';

-- ============================================================================
-- 4) Vyresit orphan rows a counter cache
-- ============================================================================

-- Recalculate term counts (kategorie pocty po smazani)
UPDATE wp_term_taxonomy tt
SET count = (
  SELECT COUNT(*) FROM wp_term_relationships tr
  WHERE tr.term_taxonomy_id = tt.term_taxonomy_id
);

-- Smazat tagy / kategorie ktere zustaly bez prirazenych prispevku
-- (krome defaultni kategorie a typu nezarazene)
DELETE tt FROM wp_term_taxonomy tt
LEFT JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
WHERE tt.count = 0
  AND tt.taxonomy IN ('post_tag', 'category')
  AND tt.term_id NOT IN (
    SELECT term_id FROM wp_terms WHERE slug IN ('nezarazene', 'uncategorized')
  )
  AND tr.object_id IS NULL;

-- Tag/kategorie radky (wp_terms) ktere zustaly bez taxonomy
DELETE t FROM wp_terms t
LEFT JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
WHERE tt.term_id IS NULL;

-- ============================================================================
-- 5) POST-CLEANUP VERIFIKACE
-- ============================================================================

SELECT 'verify_remaining_spam_in_posts' AS check_name, COUNT(*) AS count
FROM wp_posts
WHERE post_content REGEXP
  '(casinomillionz|olympfrance|casinovegashero|chicken-roadcasino|amon-casino-fr|boomerangcasinoo|casino-roman|casinohappyhugo|casinomonsterwin|casinosdragonia|casinospistolo|casinovascasino|lunubet-casino|montecryptocasino|mystakes-casino|novajackpotcasino|olympcasino|spinsy-casino|tortugacasinos|twincasino-online|vegashero-casino|verdescasino|win-bet-casino|banerpanel\\.live|betgitguncel)';

SELECT 'verify_remaining_gambling_titles' AS check_name, COUNT(*) AS count
FROM wp_posts
WHERE post_title REGEXP
  '(?i)(casino|kasino|free[[:space:]]+spin|bonus[[:space:]]+bez[[:space:]]+vkladu)'
  AND post_type IN ('post', 'page');

SELECT 'verify_total_posts_remaining' AS check_name,
       post_type, post_status, COUNT(*) AS count
FROM wp_posts
GROUP BY post_type, post_status
ORDER BY count DESC;

SELECT 'verify_yoast_indexable_for_deleted' AS check_name, COUNT(*) AS count
FROM wp_yoast_indexable yi
LEFT JOIN wp_posts p ON p.ID = yi.object_id AND yi.object_type = 'post'
WHERE yi.object_type = 'post' AND p.ID IS NULL;

SELECT 'verify_orphan_postmeta' AS check_name, COUNT(*) AS count
FROM wp_postmeta pm
LEFT JOIN wp_posts p ON p.ID = pm.post_id
WHERE p.ID IS NULL;

SELECT 'cleanup_stage3_finished_at' AS check_name,
       @cleanup_started_at AS started_at, NOW() AS finished_at;

DROP TEMPORARY TABLE IF EXISTS tmp_ft_spam_posts;

-- BEZPECNOSTNI POJISTKA:
-- Pokud vystupy NEsedi:  ROLLBACK;
-- Pokud sedi:            COMMIT;
