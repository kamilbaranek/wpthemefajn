#!/usr/bin/env bash
set -euo pipefail

# Emergency lftp cleanup helper for the fajntabory WordPress incident.
#
# Default MODE=plan prints the lftp delete commands only.
# Destructive mode requires:
#   MODE=delete-confirmed CONFIRM_DELETE=YES FTP_HOST=... FTP_USER=... FTP_PASS=... REMOTE_ROOT=...

MODE="${MODE:-plan}"
REMOTE_ROOT="${REMOTE_ROOT:-/}"
AUDIT_OUT="${AUDIT_OUT:-/private/tmp/fajntabory-lftp-audit-$(date +%Y%m%d-%H%M%S)}"

require_connection_env() {
  : "${FTP_HOST:?Set FTP_HOST, e.g. ftp.example.com or sftp://user@example.com}"
  : "${FTP_USER:?Set FTP_USER}"
  : "${FTP_PASS:?Set FTP_PASS}"
}

emit_lftp_preamble() {
  cat <<LFTP
set cmd:fail-exit no
set net:max-retries 2
set net:timeout 20
set ftp:ssl-allow true
cd "$REMOTE_ROOT"
LFTP
}

emit_confirmed_delete_commands() {
  cat <<'LFTP'
# Root-level confirmed malware or incident artifacts.
rm -f amp.php
rm -f wp-mails.php
rm -f info.php
rm -f test.php
rm -f sitemap23.xml
rm -f wp-config-sample.php
rm -rf .tmb

# Do not delete root index.php here. It is infected in the backup, but deleting
# it would break the site. Replace WordPress core from a clean source instead.

# Confirmed injected WordPress core files.
rm -f wp-includes/compat-firewall.php
rm -f wp-includes/class-pop3-set.php
rm -f wp-includes/functions.wp-scripts-soap.php
rm -f wp-includes/class-wp-navigation-fallback-firewall.php
rm -f wp-includes/default-filters-alpha.php
rm -f wp-includes/rest-api-restful.php
rm -f wp-includes/template-table.php
rm -f wp-admin/admin-ajax-character.php
rm -f wp-admin/admin-ajax-encryption.php
rm -f wp-admin/includes/class-pclzip-constructor.php
rm -f wp-admin/includes/class-wp-upgrader-package.php
rm -f wp-admin/network/plugin-install-exception.php

# Must-use plugin persistence.
rm -f wp-content/mu-plugins/test-mu-plugin.php
rm -f wp-content/mu-plugins/wp-cache.php

# Fake/backdoored plugins and injected plugin files.
rm -rf wp-content/plugins/backup_1778142536
rm -rf wp-content/plugins/gallery-1778349134
rm -rf wp-content/plugins/wp-security-helper
rm -f wp-content/plugins/plugin-loader.php
rm -f wp-content/plugins/woocommerce/includes/data-processor.php
rm -f wp-content/plugins/akismet/includes/cache-processor.php

# Extra PHP files found in the production theme compared to the repository.
# Prefer replacing the whole theme from the repository after this cleanup.
rm -f wp-content/themes/fajntabory/assets/js/ajax-loader.php
rm -f wp-content/themes/fajntabory/front-page-exception.php
rm -f wp-content/themes/fajntabory/functions_bak.php
rm -f wp-content/themes/fajntabory/header-xml.php
rm -f wp-content/themes/fajntabory/inc/template-loader.php
rm -f wp-content/themes/fajntabory/page-blog-trigger.php
rm -f wp-content/themes/fajntabory/page-doplnky-edit-list.php
rm -f wp-content/themes/fajntabory/page-doplnky-edit.php
rm -f wp-content/themes/fajntabory/page-full-live.php
rm -f wp-content/themes/fajntabory/page-galerie-ajax.php
rm -f wp-content/themes/fajntabory/page-galerie-interpreter.php
rm -f wp-content/themes/fajntabory/page-kontakty-trigger.php
rm -f wp-content/themes/fajntabory/product-doprava-new.php
rm -f wp-content/themes/fajntabory/product-tabory-module-event.php
rm -f wp-content/themes/fajntabory/sidebar-galerie-stat-cookie.php
rm -f wp-content/themes/fajntabory/sidebar-galerie-stat-repository.php
rm -f wp-content/themes/fajntabory/sidebar-galerie-stat.php
rm -f wp-content/themes/fajntabory/sidebar-page-stream.php
rm -f wp-content/themes/fajntabory/single-galerie-call-https.php
rm -f wp-content/themes/fajntabory/single-sql.php
rm -f wp-content/themes/fajntabory/single-xml.php
rm -f wp-content/themes/fajntabory/template-parts/content/content-renderer.php
rm -f wp-content/themes/fajntabory/test-class.php

# Executable payloads in uploads.
rm -f wp-content/uploads/customize/customize-cache.php
rm -f wp-content/uploads/sitemap/sitemap-cache.php
rm -f wp-content/uploads/async/async-handler.php
rm -f wp-content/uploads/widgets/widget-cache.php
rm -f wp-content/uploads/rest/rest-cache.php
rm -f wp-content/uploads/cache/style-compiler.php
rm -f wp-content/uploads/temp/block-renderer.php
rm -f wp-content/uploads/fonts/font-processor.php
rm -f wp-content/uploads/ms-files.php
rm -f wp-content/uploads/nav/nav-renderer.php
rm -f wp-content/uploads/wc-logs/log-handler.php
rm -f wp-content/uploads/2023/07/archive-data.php
rm -f wp-content/uploads/2024/frog.php
rm -f wp-content/uploads/2024/gorilla.php
rm -f wp-content/uploads/2024/yak.php
rm -f wp-content/uploads/2026/01/media-optimizer.php

# Cache can be regenerated. Removing the whole cache is safer than preserving
# potentially executable cache payloads.
rm -rf wp-content/cache
rm -f wp-content/languages/translation-cache.php
LFTP

  if [[ "${DELETE_HIGH_RISK_PLUGINS:-0}" == "1" ]]; then
    cat <<'LFTP'

# Optional: high-risk/suspicious plugins. Reinstall from trusted sources only.
rm -rf wp-content/plugins/wp-file-manager
rm -rf wp-content/plugins/wc-speed-drain-repair
LFTP
  fi

  if [[ "${DELETE_MANAGEWP_WORKER:-0}" == "1" ]]; then
    cat <<'LFTP'

# Optional: external management worker. Reinstall/reauthorize only after cleanup.
rm -rf wp-content/plugins/worker
rm -f wp-content/mu-plugins/0-worker.php
LFTP
  fi
}

audit_remote_tree() {
  require_connection_env
  mkdir -p "$AUDIT_OUT"

  lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP
set cmd:fail-exit yes
set net:max-retries 2
set net:timeout 20
set ftp:ssl-allow true
cd "$REMOTE_ROOT"
find . > ${AUDIT_OUT}/remote-tree.txt
bye
LFTP

  grep -E '(^|/)(amp\.php|wp-mails\.php|info\.php|test\.php|sitemap23\.xml|wp-config-sample\.php)$|wp-content/(mu-plugins|uploads|cache|languages)/.*\.(php|phtml|phar)$|backup_1778142536|gallery-1778349134|wp-security-helper|plugin-loader\.php|data-processor\.php|cache-processor\.php|compat-firewall\.php|class-pop3-set\.php|admin-ajax-character\.php|HTTP_4B0F3B1|banerpanel|betgit' \
    "$AUDIT_OUT/remote-tree.txt" > "$AUDIT_OUT/suspicious-files.txt" || true

  printf 'Remote tree saved: %s\n' "$AUDIT_OUT/remote-tree.txt"
  printf 'Suspicious matches saved: %s\n' "$AUDIT_OUT/suspicious-files.txt"
}

delete_confirmed_files() {
  require_connection_env

  if [[ "${CONFIRM_DELETE:-}" != "YES" ]]; then
    printf 'Refusing to delete. Set CONFIRM_DELETE=YES.\n' >&2
    exit 2
  fi

  local batch_file
  batch_file="$(mktemp /private/tmp/fajntabory-lftp-delete.XXXXXX)"
  {
    emit_lftp_preamble
    emit_confirmed_delete_commands
    printf 'bye\n'
  } > "$batch_file"

  printf 'Executing lftp cleanup batch: %s\n' "$batch_file"
  lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" -f "$batch_file"
}

case "$MODE" in
  plan)
    emit_lftp_preamble
    emit_confirmed_delete_commands
    printf 'bye\n'
    ;;
  audit)
    audit_remote_tree
    ;;
  delete-confirmed)
    delete_confirmed_files
    ;;
  *)
    printf 'Unknown MODE=%s. Use plan, audit, or delete-confirmed.\n' "$MODE" >&2
    exit 2
    ;;
esac
