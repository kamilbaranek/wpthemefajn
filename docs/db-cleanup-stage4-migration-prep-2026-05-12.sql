-- DB cleanup STAGE 4 — migration prep for fresh WordPress install.
-- Target: po stage 1+2+3 jsou data ocistena. Stage 4 pripravi DB
-- pro import do CISTE WordPress instalace tak, aby:
--   - cisty install si zachoval svoje wp_users (novy admin),
--   - cisty install si zachoval svoje wp_options (URL, salts, db_version),
--   - WooCommerce / Yoast / Wordfence / ActionScheduler tabulky se znovu
--     vytvori az pri instalaci prislusneho pluginu (vse zacne s prazdnym
--     stavem, ne se starymi datele z napadene produkce),
--   - obsah (posty, produkty, objednavky, kategorie, komentare) prejde
--     na novou DB.
--
-- Spousti se LOKALNE v ramci cleanup pipeline (skript db-cleanup-local-restore.sh
-- s MIGRATION_READY=1). Vystupem je `*.migration.sql` ktery se po dokonceni
-- importuje do CISTE WP DB pomoci `mariadb cisty_db < migration.sql`.

SET @cleanup_started_at = NOW();
SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET @fresh_admin_id = COALESCE(@fresh_admin_id, 1);

START TRANSACTION;

-- ============================================================================
-- 1) Normalizace odkazu na uzivatele
-- ============================================================================
-- Vsechny posty/produkty/objednavky v produkcnim dumpu maji post_author
-- ukazujici na ID 1, 3, 8, 9, 11, 12, 14, 15 (production users), ktere
-- v cistem installu nebudou. Prepise se na fresh admin ID (defaultne 1).
-- Stejne tak komentare s user_id → 0 (anonymous).

UPDATE wp_posts
SET post_author = @fresh_admin_id
WHERE post_author > 0;

UPDATE wp_comments
SET user_id = 0
WHERE user_id > 0;

-- ============================================================================
-- 2) DROP tabulek ktere se NEMAJI importovat
-- ============================================================================
-- DROP = export nebude obsahovat CREATE TABLE pro tyto tabulky, takze
-- fresh install si svoje verze zachova (wp_users, wp_options) nebo
-- se vytvori cisty stav od pluginu (Wordfence, Yoast, atd.).

-- 2a) AUTH / USERS — fresh admin musi prezit
DROP TABLE IF EXISTS wp_users;
DROP TABLE IF EXISTS wp_usermeta;

-- 2b) wp_options ZUSTAVA v DB pro design-overlay export.
-- Stage 4 si ulozi prazdny placeholder, hlavni migration.sql vyradi celou
-- tabulku pres --ignore-table, samostatny migration-design.sql vybere
-- jen design-relevantni klice. Tim si fresh install zachova svoji
-- siteurl/home/admin_email/db_version, ale prevezme z produkce theme_mods,
-- widgety, WC nastaveni, Yoast atd.
--
-- Aby design overlay neobsahoval zbytecne / nebezpecne entries, smazeme
-- z wp_options veci ktere produkce nesmi prepsat:
DELETE FROM wp_options
WHERE option_name IN (
  -- Identifikatory cele instalace
  'siteurl', 'home', 'admin_email', 'blogname', 'blogdescription',
  -- WP schema management
  'db_version', 'db_upgraded', 'initial_db_version', 'fresh_site',
  -- Auth / regsitrace policy (fresh install si je drzi)
  'users_can_register', 'default_role',
  -- Active plugin set — uzivatel aktivuje plugin po pluginu rucne
  'active_plugins', 'recently_activated',
  -- Cron — regeneruje se z hooks
  'cron',
  -- ACF site health snapshot (regeneruje se)
  'acf_site_health',
  -- Editor (neimportovat upraveny soubor)
  'recently_edited'
)
-- Wordfence se cely zahazuje (plugin neni nainstalovany)
OR option_name LIKE 'wordfence%'
OR option_name LIKE 'wf_%'
OR option_name LIKE 'wfls_%'
OR option_name LIKE '_wordfence%'
-- Freemius (paid-plugin tracking) — neimportovat
OR option_name LIKE 'fs_%'
-- ManageWP (worker) — zlikvidovan v stage 1
OR option_name LIKE 'mwp_%'
OR option_name LIKE '_mwp_%'
-- ActionScheduler internal (regeneruje)
OR option_name LIKE 'action_scheduler_%'
OR option_name LIKE 'schema-ActionScheduler%'
-- WooCommerce DB migrations (plugin to spravuje samo)
OR option_name LIKE 'woocommerce_schema_%'
OR option_name LIKE 'woocommerce_db_%'
OR option_name LIKE 'woocommerce_version'
-- Yoast migration tracking (plugin to spravuje)
OR option_name LIKE 'wpseo_db_version%'
OR option_name LIKE 'wpseo_indexables_indexing%'
-- Transients (caches)
OR option_name LIKE '_transient_%'
OR option_name LIKE '_site_transient_%';

-- 2c) WORDFENCE — plugin neni nainstalovany, forenzni data byla
-- v puvodnim cleaned.sql archivovana
DROP TABLE IF EXISTS wp_wfauditevents;
DROP TABLE IF EXISTS wp_wfblockediplog;
DROP TABLE IF EXISTS wp_wfblocks7;
DROP TABLE IF EXISTS wp_wfconfig;
DROP TABLE IF EXISTS wp_wfcrawlers;
DROP TABLE IF EXISTS wp_wffilechanges;
DROP TABLE IF EXISTS wp_wffilemods;
DROP TABLE IF EXISTS wp_wfhits;
DROP TABLE IF EXISTS wp_wfhoover;
DROP TABLE IF EXISTS wp_wfissues;
DROP TABLE IF EXISTS wp_wfknownfilelist;
DROP TABLE IF EXISTS wp_wflivetraffichuman;
DROP TABLE IF EXISTS wp_wflocs;
DROP TABLE IF EXISTS wp_wflogins;
DROP TABLE IF EXISTS wp_wfls_2fa_secrets;
DROP TABLE IF EXISTS wp_wfls_role_counts;
DROP TABLE IF EXISTS wp_wfls_settings;
DROP TABLE IF EXISTS wp_wfnotifications;
DROP TABLE IF EXISTS wp_wfpendingissues;
DROP TABLE IF EXISTS wp_wfreversecache;
DROP TABLE IF EXISTS wp_wfsecurityevents;
DROP TABLE IF EXISTS wp_wfsnipcache;
DROP TABLE IF EXISTS wp_wfstatus;
DROP TABLE IF EXISTS wp_wftrafficrates;
DROP TABLE IF EXISTS wp_wfwaffailures;

-- 2d) YOAST — plugin regeneruje pri prvni indexaci
DROP TABLE IF EXISTS wp_yoast_indexable;
DROP TABLE IF EXISTS wp_yoast_indexable_hierarchy;
DROP TABLE IF EXISTS wp_yoast_migrations;
DROP TABLE IF EXISTS wp_yoast_primary_term;
DROP TABLE IF EXISTS wp_yoast_seo_links;
DROP TABLE IF EXISTS wp_yoast_seo_meta;

-- 2e) ACTIONSCHEDULER — fresh install generuje z cron
DROP TABLE IF EXISTS wp_actionscheduler_actions;
DROP TABLE IF EXISTS wp_actionscheduler_claims;
DROP TABLE IF EXISTS wp_actionscheduler_groups;
DROP TABLE IF EXISTS wp_actionscheduler_logs;

-- 2f) WOOCOMMERCE ephemeralni / auth / cache tabulky
DROP TABLE IF EXISTS wp_woocommerce_sessions;
DROP TABLE IF EXISTS wp_woocommerce_api_keys;
DROP TABLE IF EXISTS wp_woocommerce_payment_tokens;
DROP TABLE IF EXISTS wp_woocommerce_payment_tokenmeta;
DROP TABLE IF EXISTS wp_woocommerce_webhooks;
DROP TABLE IF EXISTS wp_woocommerce_log;
DROP TABLE IF EXISTS wp_wc_webhooks;
DROP TABLE IF EXISTS wp_wc_rate_limits;
DROP TABLE IF EXISTS wp_wc_reserved_stock;
DROP TABLE IF EXISTS wp_wc_admin_notes;
DROP TABLE IF EXISTS wp_wc_admin_note_actions;
DROP TABLE IF EXISTS wp_wc_download_log;
-- 2g) WC ANALYTIKA — lookup tabulky lze regenerovat:
--     wp-cli: wp wc tool run regenerate_product_lookup_table_data
DROP TABLE IF EXISTS wp_wc_product_attributes_lookup;
DROP TABLE IF EXISTS wp_wc_product_meta_lookup;
DROP TABLE IF EXISTS wp_wc_category_lookup;
DROP TABLE IF EXISTS wp_wc_customer_lookup;
DROP TABLE IF EXISTS wp_wc_order_coupon_lookup;
DROP TABLE IF EXISTS wp_wc_order_product_lookup;
DROP TABLE IF EXISTS wp_wc_order_stats;
DROP TABLE IF EXISTS wp_wc_order_tax_lookup;
DROP TABLE IF EXISTS wp_wc_product_download_directories;
DROP TABLE IF EXISTS wp_wc_tax_rate_classes;

-- 2h) PLUGIN-SPECIFIC tabulky — pokud plugin nebudete znovu instalovat,
-- nemajipridanou hodnotu. Pokud nainstalujete, plugin si je vytvori cisty.
DROP TABLE IF EXISTS wp_db7_forms;
DROP TABLE IF EXISTS wp_redirection_404;
DROP TABLE IF EXISTS wp_redirection_groups;
DROP TABLE IF EXISTS wp_redirection_items;
DROP TABLE IF EXISTS wp_redirection_logs;
DROP TABLE IF EXISTS wp_wpfm_backup;

-- ============================================================================
-- 3) Verifikace pred exportem
-- ============================================================================

SELECT 'tables_remaining_for_export' AS check_name, TABLE_NAME, TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
ORDER BY TABLE_NAME;

SELECT 'post_count_by_type_for_export' AS check_name, post_type, post_status, COUNT(*) AS count
FROM wp_posts
GROUP BY post_type, post_status
ORDER BY count DESC;

SELECT 'orders_by_status_for_export' AS check_name, post_status, COUNT(*) AS count
FROM wp_posts WHERE post_type = 'shop_order' GROUP BY post_status;

SELECT 'products_by_status_for_export' AS check_name, post_status, COUNT(*) AS count
FROM wp_posts WHERE post_type = 'product' GROUP BY post_status;

SELECT 'comment_authors_remaining' AS check_name, user_id, COUNT(*) AS count
FROM wp_comments GROUP BY user_id ORDER BY count DESC;

SELECT 'post_authors_after_remap' AS check_name, post_author, COUNT(*) AS count
FROM wp_posts GROUP BY post_author ORDER BY count DESC;

SELECT 'cleanup_stage4_finished_at' AS check_name,
       @cleanup_started_at AS started_at, NOW() AS finished_at,
       @fresh_admin_id AS fresh_admin_id;

-- BEZPECNOSTNI POJISTKA:
-- ROLLBACK pokud vystup nesedi.
-- COMMIT  pokud sedi a chcete export pokracovat.
