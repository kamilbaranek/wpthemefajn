-- DB cleanup for fajntabory production incident.
-- Target dump: /Users/kamilbaranek/dev/fajntabory/DB/wm144_wedos_net.sql
-- Date: 2026-05-12
--
-- Run only after preserving a forensic copy of the original DB.
-- This script removes confirmed DB persistence and disables high-risk plugins.

SET @cleanup_started_at = NOW();

-- 1) Remove database backdoor that recreates the cmsrss administrator.
DROP TRIGGER IF EXISTS after_insert_comment;

-- 2) Collect confirmed malicious/suspicious users by login.
CREATE TEMPORARY TABLE IF NOT EXISTS tmp_ft_malicious_users (
  ID BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY
);

INSERT IGNORE INTO tmp_ft_malicious_users (ID)
SELECT ID
FROM wp_users
WHERE user_login IN (
  'admbec45q',
  'adm4i0vfm',
  'cmsrss',
  'wnadmin',
  'lutehefi',
  'ehibriqw',
  'upazekge',
  'sarah_j0hnson',
  'mike_br0wn',
  'emily_davi3s',
  'james_wils0n',
  'olivia_tayl0r',
  'dan_m00re',
  's0phie_martin'
);

-- 3) Remove WooCommerce customer lookup rows for those fake users.
DELETE cl
FROM wp_wc_customer_lookup cl
JOIN tmp_ft_malicious_users bad ON bad.ID = cl.user_id;

-- 4) Remove user metadata and users.
DELETE um
FROM wp_usermeta um
JOIN tmp_ft_malicious_users bad ON bad.ID = um.user_id;

DELETE u
FROM wp_users u
JOIN tmp_ft_malicious_users bad ON bad.ID = u.ID;

-- 5) Invalidate all existing WordPress sessions and application passwords.
-- This forces every legitimate admin to log in again after password/salt rotation.
DELETE FROM wp_usermeta
WHERE meta_key IN ('session_tokens', 'application_passwords', '_application_passwords');

-- 6) Remove trigger phrase comments if present.
DELETE FROM wp_comments
WHERE comment_content LIKE '%are you struggling to get comments on your blog?%';

-- 7) Deactivate high-risk plugins in active_plugins.
-- Removed from the active list:
-- - worker/init.php: external management must be reauthorized after incident
-- - wc-speed-drain-repair/wcsdr.php: suspicious plugin, must be verified before reuse
-- - wp-file-manager/file_folder_manager.php: high-risk file manager, should not be active
UPDATE wp_options
SET option_value = 'a:16:{i:0;s:57:"acf-content-analysis-for-yoast-seo/yoast-acf-analysis.php";i:1;s:30:"advanced-custom-fields/acf.php";i:2;s:36:"contact-form-7/wp-contact-form-7.php";i:3;s:42:"contact-form-cfdb7/contact-form-cfdb-7.php";i:4;s:31:"cookie-notice/cookie-notice.php";i:5;s:53:"facebook-for-woocommerce/facebook-for-woocommerce.php";i:6;s:19:"gtm-kit/gtm-kit.php";i:7;s:27:"prettyphoto/prettyphoto.php";i:8;s:37:"rocket-lazy-load/rocket-lazy-load.php";i:9;s:33:"smartsupp-live-chat/smartsupp.php";i:10;s:59:"woocommerce-legacy-rest-api/woocommerce-legacy-rest-api.php";i:11;s:27:"woocommerce/woocommerce.php";i:12;s:24:"wordpress-seo/wp-seo.php";i:13;s:43:"wp-media-categories/wp-media-categories.php";i:14;s:23:"wp-sort-order/index.php";i:15;s:27:"wp-super-cache/wp-cache.php";}'
WHERE option_name = 'active_plugins';

-- 8) Remove plugin update/cache rows that remember hidden or fake plugins.
DELETE FROM wp_options
WHERE option_name IN (
  '_site_transient_update_plugins',
  '_site_transient_timeout_update_plugins',
  '_transient_update_plugins',
  '_transient_timeout_update_plugins',
  'recently_activated'
)
OR option_value LIKE '%backup_1778142536%'
OR option_value LIKE '%gallery-1778349134%'
OR option_value LIKE '%wp-security-helper%'
OR option_value LIKE '%googlespeed-xml-sitemaps%'
OR option_value LIKE '%HTTP_4B0F3B1%'
OR option_value LIKE '%betgitguncelgiris%'
OR option_value LIKE '%banerpanel.live%';

-- 9) Remove scheduled events mentioning known malicious plugin names, if any are present.
DELETE FROM wp_options
WHERE option_name = 'cron'
  AND (
    option_value LIKE '%backup_1778142536%'
    OR option_value LIKE '%gallery-1778349134%'
    OR option_value LIKE '%wp-security-helper%'
    OR option_value LIKE '%googlespeed-xml-sitemaps%'
  );

-- 10) Leave forensic Wordfence tables intact. They can be dropped later after archiving.
DROP TEMPORARY TABLE IF EXISTS tmp_ft_malicious_users;

-- 11) Post-cleanup checks. These SELECTs should return zero suspicious rows,
-- except the capability audit, which should contain only legitimate admins.
SELECT 'remaining_malicious_users' AS check_name, COUNT(*) AS count
FROM wp_users
WHERE user_login IN (
  'admbec45q',
  'adm4i0vfm',
  'cmsrss',
  'wnadmin',
  'lutehefi',
  'ehibriqw',
  'upazekge',
  'sarah_j0hnson',
  'mike_br0wn',
  'emily_davi3s',
  'james_wils0n',
  'olivia_tayl0r',
  'dan_m00re',
  's0phie_martin'
);

SELECT 'remaining_bad_options' AS check_name, COUNT(*) AS count
FROM wp_options
WHERE option_value LIKE '%backup_1778142536%'
   OR option_value LIKE '%gallery-1778349134%'
   OR option_value LIKE '%wp-security-helper%'
   OR option_value LIKE '%googlespeed-xml-sitemaps%'
   OR option_value LIKE '%HTTP_4B0F3B1%'
   OR option_value LIKE '%betgitguncelgiris%'
   OR option_value LIKE '%banerpanel.live%';

SELECT 'privileged_users_audit' AS check_name, u.ID, u.user_login, u.user_email, u.user_registered, m.meta_value
FROM wp_users u
JOIN wp_usermeta m ON m.user_id = u.ID
WHERE m.meta_key = 'wp_capabilities'
  AND (
    m.meta_value LIKE '%administrator%'
    OR m.meta_value LIKE '%install_plugins%'
    OR m.meta_value LIKE '%edit_plugins%'
    OR m.meta_value LIKE '%edit_files%'
    OR m.meta_value LIKE '%manage_options%'
  )
ORDER BY u.ID;

SELECT 'cleanup_finished_at' AS check_name, @cleanup_started_at AS started_at, NOW() AS finished_at;
