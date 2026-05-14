#!/usr/bin/env bash
# Local DB cleanup for fajntabory wm144_wedos_net production incident.
#
# Spins up an ephemeral MariaDB instance in a temp datadir, imports the dump,
# runs stage 1 + stage 2 + stage 3 cleanup scripts, exports a cleaned dump and
# audit log, then tears everything down.
#
# Two output modes:
#   default            -> wm144_wedos_net.cleaned.sql  (full DB, replaces prod)
#   MIGRATION_READY=1  -> wm144_wedos_net.migration.sql (data-only, fresh install safe)
#                         runs stage 4 (DROP wp_users/wp_options/Wordfence/Yoast/...)
#                         + normalizes post_author to FRESH_ADMIN_ID
#
# The original input dump is NEVER modified.
#
# Requirements:
#   - brew install mariadb@10.11
#
# Env overrides:
#   DUMP_INPUT          (default: REPO_ROOT/DB/wm144_wedos_net.sql)
#   DUMP_OUTPUT         (default: cleaned.sql OR migration.sql depending on mode)
#   AUDIT_LOG           (default: REPO_ROOT/DB/wm144_wedos_net.cleanup-audit.log)
#   MIGRATION_READY=1   (produce a migration-ready dump for fresh WP install)
#   FRESH_ADMIN_ID=1    (post_author target when MIGRATION_READY=1)
#   KEEP_DATADIR=1      (preserve the temp datadir after run, useful for debug)

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DUMP_INPUT="${DUMP_INPUT:-${REPO_ROOT}/DB/wm144_wedos_net.sql}"
MIGRATION_READY="${MIGRATION_READY:-}"
FRESH_ADMIN_ID="${FRESH_ADMIN_ID:-1}"
if [[ -n "$MIGRATION_READY" ]]; then
  DUMP_OUTPUT="${DUMP_OUTPUT:-${REPO_ROOT}/DB/wm144_wedos_net.migration.sql}"
else
  DUMP_OUTPUT="${DUMP_OUTPUT:-${REPO_ROOT}/DB/wm144_wedos_net.cleaned.sql}"
fi
AUDIT_LOG="${AUDIT_LOG:-${REPO_ROOT}/DB/wm144_wedos_net.cleanup-audit.log}"
STAGE1_SQL="${REPO_ROOT}/docs/db-cleanup-wm144-wedos-2026-05-12.sql"
STAGE2_SQL="${REPO_ROOT}/docs/db-cleanup-stage2-wm144-wedos-2026-05-12.sql"
STAGE3_SQL="${REPO_ROOT}/docs/db-cleanup-stage3-wm144-wedos-2026-05-12.sql"
STAGE4_SQL="${REPO_ROOT}/docs/db-cleanup-stage4-migration-prep-2026-05-12.sql"
DB_NAME="d160272_tabory"

red()   { printf '\033[31m%s\033[0m\n' "$*"; }
green() { printf '\033[32m%s\033[0m\n' "$*"; }
blue()  { printf '\033[34m%s\033[0m\n' "$*"; }

STAGE_FILES=("$STAGE1_SQL" "$STAGE2_SQL" "$STAGE3_SQL")
if [[ -n "$MIGRATION_READY" ]]; then
  STAGE_FILES+=("$STAGE4_SQL")
fi
for f in "$DUMP_INPUT" "${STAGE_FILES[@]}"; do
  if [[ ! -f "$f" ]]; then
    red "MISSING input: $f"
    exit 1
  fi
done

if ! brew --prefix mariadb@10.11 >/dev/null 2>&1; then
  red "mariadb@10.11 not installed. Run: brew install mariadb@10.11"
  exit 1
fi

BREW_PREFIX="$(brew --prefix mariadb@10.11)"
MARIADB_INSTALL_DB="${BREW_PREFIX}/bin/mariadb-install-db"
MARIADBD="${BREW_PREFIX}/bin/mariadbd"
MARIADB="${BREW_PREFIX}/bin/mariadb"
MARIADB_DUMP="${BREW_PREFIX}/bin/mariadb-dump"

for bin in "$MARIADB_INSTALL_DB" "$MARIADBD" "$MARIADB" "$MARIADB_DUMP"; do
  if [[ ! -x "$bin" ]]; then
    red "Required binary not found or not executable: $bin"
    exit 1
  fi
done

TMPDIR_BASE="$(mktemp -d -t fajntabory-db-cleanup-XXXXXX)"
DATA_DIR="${TMPDIR_BASE}/data"
SOCKET="${TMPDIR_BASE}/mariadb.sock"
PID_FILE="${TMPDIR_BASE}/mariadb.pid"
LOG_FILE="${TMPDIR_BASE}/mariadb.log"

cleanup() {
  local rc=$?
  blue "==> shutting down local MariaDB"
  if [[ -f "$PID_FILE" ]]; then
    local pid
    pid="$(cat "$PID_FILE" 2>/dev/null || true)"
    if [[ -n "$pid" ]] && kill -0 "$pid" 2>/dev/null; then
      kill "$pid" 2>/dev/null || true
      for _ in 1 2 3 4 5 6 7 8 9 10; do
        kill -0 "$pid" 2>/dev/null || break
        sleep 1
      done
      kill -9 "$pid" 2>/dev/null || true
    fi
  fi
  if [[ -n "${KEEP_DATADIR:-}" ]]; then
    blue "    KEEP_DATADIR set, preserving $TMPDIR_BASE"
  else
    rm -rf "$TMPDIR_BASE"
  fi
  exit "$rc"
}
trap cleanup EXIT INT TERM

blue "==> input  dump: $DUMP_INPUT ($(du -h "$DUMP_INPUT" | awk '{print $1}'))"
blue "==> output dump: $DUMP_OUTPUT"
blue "==> audit log:   $AUDIT_LOG"
blue "==> tempdir:     $TMPDIR_BASE"

blue "==> initialising ephemeral datadir"
"$MARIADB_INSTALL_DB" \
  --datadir="$DATA_DIR" \
  --auth-root-authentication-method=socket \
  >/dev/null

blue "==> starting MariaDB (socket-only, no networking)"
"$MARIADBD" \
  --no-defaults \
  --datadir="$DATA_DIR" \
  --socket="$SOCKET" \
  --pid-file="$PID_FILE" \
  --log-error="$LOG_FILE" \
  --skip-networking \
  --skip-grant-tables \
  --innodb-buffer-pool-size=512M \
  --max-allowed-packet=512M \
  >/dev/null 2>&1 &

for i in $(seq 1 30); do
  if "$MARIADB" --socket="$SOCKET" -uroot -e "SELECT 1" >/dev/null 2>&1; then
    green "    MariaDB up after ${i}s"
    break
  fi
  sleep 1
done
if ! "$MARIADB" --socket="$SOCKET" -uroot -e "SELECT 1" >/dev/null 2>&1; then
  red "MariaDB failed to start. Log:"
  cat "$LOG_FILE" >&2 || true
  exit 1
fi

MQ() { "$MARIADB" --socket="$SOCKET" -uroot "$@"; }

blue "==> importing dump (this may take 1-3 minutes)"
# Strip the trailing `information_schema` dump that phpMyAdmin appends — it
# tries to CREATE DATABASE information_schema which is a system schema that
# already exists and would fail. Only keep statements up to the first
# subsequent CREATE DATABASE that is NOT for our target db.
time awk -v target="$DB_NAME" '
  /^CREATE DATABASE / {
    if ($0 !~ "`" target "`") { stop=1 }
  }
  !stop { print }
' "$DUMP_INPUT" | MQ

# Confirm db is present and switch session default
MQ -e "SHOW DATABASES" | grep -q "$DB_NAME" || {
  red "Expected database '$DB_NAME' not created by dump"
  exit 1
}

: > "$AUDIT_LOG"
{
  echo "========================================================================"
  echo " fajntabory DB cleanup audit log"
  echo " started: $(date -u +%FT%TZ)"
  echo " input:   $DUMP_INPUT"
  echo "========================================================================"
} | tee -a "$AUDIT_LOG"

blue "==> running stage 1 cleanup"
{
  echo ""
  echo "========== STAGE 1 OUTPUT =========="
  MQ "$DB_NAME" --table < "$STAGE1_SQL" 2>&1
} | tee -a "$AUDIT_LOG"

blue "==> running stage 2 cleanup (transactional; auto-commit appended)"
{
  echo ""
  echo "========== STAGE 2 OUTPUT =========="
  {
    cat "$STAGE2_SQL"
    echo ""
    echo "COMMIT;"
  } | MQ "$DB_NAME" --table 2>&1
} | tee -a "$AUDIT_LOG"

blue "==> running stage 3 cleanup (fake spam posts cascade delete)"
{
  echo ""
  echo "========== STAGE 3 OUTPUT =========="
  {
    cat "$STAGE3_SQL"
    echo ""
    echo "COMMIT;"
  } | MQ "$DB_NAME" --table 2>&1
} | tee -a "$AUDIT_LOG"

if [[ -n "$MIGRATION_READY" ]]; then
  blue "==> running stage 4 migration prep (drop config tables, normalize post_author=$FRESH_ADMIN_ID)"
  {
    echo ""
    echo "========== STAGE 4 OUTPUT =========="
    {
      printf 'SET @fresh_admin_id = %d;\n' "$FRESH_ADMIN_ID"
      cat "$STAGE4_SQL"
      echo ""
      echo "COMMIT;"
    } | MQ "$DB_NAME" --table 2>&1
  } | tee -a "$AUDIT_LOG"
fi

blue "==> final IOC sweep"
{
  echo ""
  echo "========== FINAL SWEEP =========="
  echo "-- Remaining triggers (expect 0)"
  MQ "$DB_NAME" --table -e "
    SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION
    FROM information_schema.TRIGGERS
    WHERE TRIGGER_SCHEMA = DATABASE();" || true

  echo "-- Remaining hidden offscreen spam divs in posts (expect 0)"
  MQ "$DB_NAME" --table -e "
    SELECT COUNT(*) AS remaining_seo_spam_posts
    FROM wp_posts
    WHERE post_content REGEXP 'position:[[:space:]]*fixed;[[:space:]]*top:[[:space:]]*-[0-9]+px;[[:space:]]*left:[[:space:]]*-[0-9]+px';" || true

  echo "-- Remaining casino spam domains in posts (expect 0)"
  MQ "$DB_NAME" --table -e "
    SELECT COUNT(*) AS remaining_casino_links
    FROM wp_posts
    WHERE post_content REGEXP
      '(casinomillionz|olympfrance|casinovegashero|chicken-roadcasino|amon-casino-fr|boomerangcasinoo|casino-roman|casinohappyhugo|casinomonsterwin|casinosdragonia|casinospistolo|casinovascasino|lunubet-casino|montecryptocasino|mystakes-casino|novajackpotcasino|olympcasino|spinsy-casino|tortugacasinos|twincasino-online|vegashero-casino|verdescasino|win-bet-casino|banerpanel\\\\.live|betgitguncel)';" || true

  if [[ -z "$MIGRATION_READY" ]]; then
    # These checks make sense only for the cleaned (full) dump variant.
    # Migration mode drops wp_users / wp_options entirely.
    echo "-- Remaining bad users (expect 0)"
    MQ "$DB_NAME" --table -e "
      SELECT ID, user_login, user_email
      FROM wp_users
      WHERE user_login IN ('admbec45q','adm4i0vfm','cmsrss','wnadmin','lutehefi',
                           'ehibriqw','upazekge','sarah_j0hnson','mike_br0wn',
                           'emily_davi3s','james_wils0n','olivia_tayl0r',
                           'dan_m00re','s0phie_martin');" || true

    echo "-- Remaining session_tokens (expect 0)"
    MQ "$DB_NAME" --table -e "
      SELECT COUNT(*) AS leftover_sessions
      FROM wp_usermeta
      WHERE meta_key IN ('session_tokens','application_passwords','_application_passwords');" || true

    echo "-- Remaining IOC plugin references in autoloaded options (expect 0)"
    MQ "$DB_NAME" --table -e "
      SELECT option_id, option_name
      FROM wp_options
      WHERE autoload IN ('on','yes','auto')
        AND ( option_value LIKE '%backup_1778142536%'
           OR option_value LIKE '%gallery-1778349134%'
           OR option_value LIKE '%wp-security-helper%'
           OR option_value LIKE '%banerpanel.live%'
           OR option_value LIKE '%betgitguncel%');" || true

    echo "-- Pre-incident admin accounts (manual review required)"
    MQ "$DB_NAME" --table -e "
      SELECT ID, user_login, user_email, user_registered
      FROM wp_users
      WHERE ID IN (1, 8);" || true

    echo "-- Active plugins after cleanup"
    MQ "$DB_NAME" --table -e "
      SELECT option_value FROM wp_options WHERE option_name='active_plugins';" || true
  fi
} | tee -a "$AUDIT_LOG"

if [[ -n "$MIGRATION_READY" ]]; then
  blue "==> exporting MIGRATION dump (content only, no wp_options/wp_users)"
  # No --databases means no CREATE DATABASE / USE statements — caller picks
  # target DB. Stage 4 already dropped tables that fresh install must own.
  # wp_options is excluded here and exported separately as a design overlay.
  "$MARIADB_DUMP" \
    --socket="$SOCKET" -uroot \
    --default-character-set=utf8 \
    --routines --triggers --events \
    --single-transaction \
    --skip-lock-tables \
    --ignore-table="${DB_NAME}.wp_options" \
    "$DB_NAME" \
    > "$DUMP_OUTPUT"

  # Design overlay: selective wp_options entries (theme_mods, widgets,
  # WC settings, plugin display settings, site preferences) as DELETE-then-INSERT.
  # Fresh install's siteurl/home/admin_email/db_version are NOT in the
  # whitelist, so they stay untouched.
  DESIGN_OUTPUT="${DUMP_OUTPUT%.sql}-design.sql"
  blue "==> exporting DESIGN overlay to ${DESIGN_OUTPUT##*/}"
  DESIGN_WHERE="(
       option_name LIKE 'theme_mods_%'
    OR option_name IN ('current_theme', 'template', 'stylesheet',
                       'custom_logo', 'site_icon', 'site_logo')
    OR option_name LIKE 'header_image%'
    OR option_name IN ('background_color', 'background_image')
    OR option_name LIKE 'widget_%'
    OR option_name = 'sidebars_widgets'
    OR option_name = 'nav_menu_options'
    OR option_name IN ('show_on_front', 'page_on_front', 'page_for_posts',
                       'page_comments', 'default_category', 'default_post_format')
    OR option_name IN ('posts_per_page', 'posts_per_rss', 'rss_use_excerpt',
                       'default_ping_status', 'default_comment_status',
                       'comment_moderation', 'comment_registration',
                       'comments_notify', 'comment_max_links',
                       'comment_order', 'default_comments_page',
                       'comment_whitelist', 'moderation_notify',
                       'moderation_keys', 'disallowed_keys')
    OR option_name LIKE 'comments_%'
    OR option_name IN ('permalink_structure', 'category_base', 'tag_base')
    OR option_name LIKE 'thumbnail_%'
    OR option_name LIKE 'medium_%'
    OR option_name LIKE 'large_%'
    OR option_name LIKE 'image_default_%'
    OR option_name IN ('uploads_use_yearmonth_folders', 'upload_path', 'upload_url_path')
    OR option_name IN ('date_format', 'time_format', 'start_of_week',
                       'WPLANG', 'timezone_string', 'gmt_offset', 'blog_charset')
    OR option_name LIKE 'woocommerce_%'
    OR option_name LIKE 'wc_%'
    OR option_name LIKE 'wpseo%'
    OR option_name = 'cookie_notice_options'
    OR option_name LIKE 'gtm-kit_%'
    OR option_name LIKE 'wpcf7%'
    OR option_name LIKE 'cf7_%'
    OR option_name LIKE 'cfdb7_%'
    OR option_name LIKE 'rocket_lazy_load_%'
    OR option_name LIKE 'wp_super_cache_%'
    OR option_name LIKE 'wpsc_%'
    OR option_name LIKE 'smartsupp_%'
    OR option_name LIKE 'acf_%'
    OR option_name LIKE 'fajntabory_%'
    OR option_name LIKE 'redirection_%'
    OR option_name LIKE 'wp_media_categories_%'
    OR option_name LIKE 'wp_sort_order_%'
    OR option_name LIKE 'facebook_for_woocommerce_%'
    OR option_name LIKE 'fb_woocommerce_%'
    OR option_name LIKE 'fbe_%'
    OR option_name LIKE 'prettyphoto_%'
  )"

  # Build a DELETE statement using the same WHERE so we don't double-insert
  # if user re-runs. INSERT IGNORE alone would silently skip updates.
  {
    echo "-- Design overlay: cleanly replaces design / settings options"
    echo "-- in fresh wp_options without touching siteurl/home/admin_email/db_version."
    echo "-- Run AFTER importing the matching migration.sql."
    echo ""
    echo "SET autocommit=0;"
    echo "START TRANSACTION;"
    echo ""
    echo "DELETE FROM wp_options WHERE $DESIGN_WHERE;"
    echo ""
  } > "$DESIGN_OUTPUT"

  # Strip `option_id` (PK) from INSERTs so fresh install's auto-increment
  # generates a new ID. Without this, production option_id values collide
  # with rows already in fresh wp_options (e.g. siteurl might be id=1 in
  # both, but they're different rows by option_name).
  "$MARIADB_DUMP" \
    --socket="$SOCKET" -uroot \
    --default-character-set=utf8 \
    --no-create-info \
    --skip-triggers \
    --single-transaction \
    --skip-lock-tables \
    --skip-extended-insert \
    --where="$DESIGN_WHERE" \
    "$DB_NAME" wp_options \
    | awk '
        /^INSERT INTO `wp_options` VALUES \([0-9]+,/ {
          sub(/VALUES \([0-9]+,/, "(`option_name`,`option_value`,`autoload`) VALUES (")
          print
          next
        }
        /^INSERT INTO/ { print }
      ' \
    >> "$DESIGN_OUTPUT"

  {
    echo ""
    echo "COMMIT;"
  } >> "$DESIGN_OUTPUT"
else
  blue "==> exporting CLEANED dump (full DB replacement)"
  "$MARIADB_DUMP" \
    --socket="$SOCKET" -uroot \
    --default-character-set=utf8 \
    --add-drop-database --databases "$DB_NAME" \
    --routines --triggers --events \
    --single-transaction \
    --skip-lock-tables \
    > "$DUMP_OUTPUT"
fi

{
  echo ""
  echo "========== SUMMARY =========="
  echo "input  size : $(du -h "$DUMP_INPUT"  | awk '{print $1}')"
  echo "output size : $(du -h "$DUMP_OUTPUT" | awk '{print $1}')"
  echo "finished    : $(date -u +%FT%TZ)"
} | tee -a "$AUDIT_LOG"

green ""
green "DONE."
if [[ -n "$MIGRATION_READY" ]]; then
  # Gzip both migration artefacts for phpMyAdmin / large-upload workflows.
  blue "==> compressing migration artefacts (.gz)"
  gzip -kf "$DUMP_OUTPUT"
  gzip -kf "${DUMP_OUTPUT%.sql}-design.sql"
  green "    migration dump  : $DUMP_OUTPUT ($(du -h "$DUMP_OUTPUT" | awk '{print $1}')) + .gz ($(du -h "${DUMP_OUTPUT}.gz" | awk '{print $1}'))"
  green "    design overlay  : ${DUMP_OUTPUT%.sql}-design.sql ($(du -h "${DUMP_OUTPUT%.sql}-design.sql" 2>/dev/null | awk '{print $1}')) + .gz ($(du -h "${DUMP_OUTPUT%.sql}-design.sql.gz" 2>/dev/null | awk '{print $1}'))"
else
  green "    cleaned dump : $DUMP_OUTPUT"
fi
green "    audit log    : $AUDIT_LOG"
green ""
green "Doporuceny dalsi krok: zkontroluj audit log a porovnej cleaned dump:"
green "    less '$AUDIT_LOG'"
green "    grep -c 'position: fixed; top: -' '$DUMP_OUTPUT'  # mel by vratit 0"
green "    grep -cE 'cmsrss|wnadmin|admbec45q' '$DUMP_OUTPUT'  # mel by vratit 0"
