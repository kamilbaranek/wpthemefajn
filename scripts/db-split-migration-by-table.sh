#!/usr/bin/env bash
# Rozdeli `wm144_wedos_net.migration.sql` na samostatne per-tabulka soubory
# pro phpMyAdmin import po castech (kdyz 136 MB / 7.8 MB gzip najednou
# nedobehne kvuli timeoutu).
#
# Postup: naimportuje migration.sql do efemerni MariaDB a kazdou tabulku
# znovu vyexportuje samostatnym `mariadb-dump` volanim. Kazdy vystupni
# soubor je tim padem GARANTOVANE validni SQL (DROP TABLE IF EXISTS +
# CREATE TABLE + INSERT), na rozdil od textoveho sekani jednoho velkeho
# dumpu.
#
# Vystup v `DB/migration-chunks/`:
#   01-wp_posts.sql(.gz)      -- vsechny posty vcetne 35k attachmentu
#   02-wp_postmeta.sql(.gz)   -- vsechna postmeta (nejvetsi tabulka)
#   03-rest.sql(.gz)          -- komentare, terms, WC order_items, zony, dane
#
# Kazdy soubor je samostatne importovatelny v libovolnem poradi
# (WordPress nepouziva FK constraints). Doporucene poradi 01 → 02 → 03.
#
# Requirements: brew install mariadb@10.11
#
# Pouziti:
#   scripts/db-split-migration-by-table.sh [migration.sql] [outdir]

set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
INPUT="${1:-${REPO_ROOT}/DB/wm144_wedos_net.migration.sql}"
OUTDIR="${2:-$(dirname "$INPUT")/migration-chunks}"
DB_NAME="ftsplit"

red()   { printf '\033[31m%s\033[0m\n' "$*"; }
green() { printf '\033[32m%s\033[0m\n' "$*"; }
blue()  { printf '\033[34m%s\033[0m\n' "$*"; }

if [[ ! -f "$INPUT" ]]; then
  red "MISSING input: $INPUT"
  exit 1
fi
if ! brew --prefix mariadb@10.11 >/dev/null 2>&1; then
  red "mariadb@10.11 not installed. Run: brew install mariadb@10.11"
  exit 1
fi

BREW_PREFIX="$(brew --prefix mariadb@10.11)"
MARIADB_INSTALL_DB="${BREW_PREFIX}/bin/mariadb-install-db"
MARIADBD="${BREW_PREFIX}/bin/mariadbd"
MARIADB="${BREW_PREFIX}/bin/mariadb"
MARIADB_DUMP="${BREW_PREFIX}/bin/mariadb-dump"

TMPDIR_BASE="$(mktemp -d -t fajntabory-db-split-XXXXXX)"
DATA_DIR="${TMPDIR_BASE}/data"
SOCKET="${TMPDIR_BASE}/mariadb.sock"
PID_FILE="${TMPDIR_BASE}/mariadb.pid"
LOG_FILE="${TMPDIR_BASE}/mariadb.log"

cleanup() {
  local rc=$?
  if [[ -f "$PID_FILE" ]]; then
    local pid; pid="$(cat "$PID_FILE" 2>/dev/null || true)"
    if [[ -n "$pid" ]] && kill -0 "$pid" 2>/dev/null; then
      kill "$pid" 2>/dev/null || true
      for _ in $(seq 1 10); do kill -0 "$pid" 2>/dev/null || break; sleep 1; done
      kill -9 "$pid" 2>/dev/null || true
    fi
  fi
  rm -rf "$TMPDIR_BASE"
  exit "$rc"
}
trap cleanup EXIT INT TERM

mkdir -p "$OUTDIR"
rm -f "$OUTDIR"/*.sql "$OUTDIR"/*.gz 2>/dev/null || true

blue "==> initialising ephemeral MariaDB"
"$MARIADB_INSTALL_DB" --datadir="$DATA_DIR" --auth-root-authentication-method=socket >/dev/null
"$MARIADBD" --no-defaults --datadir="$DATA_DIR" --socket="$SOCKET" \
  --pid-file="$PID_FILE" --log-error="$LOG_FILE" \
  --skip-networking --skip-grant-tables \
  --innodb-buffer-pool-size=512M --max-allowed-packet=512M \
  >/dev/null 2>&1 &

for i in $(seq 1 30); do
  if "$MARIADB" --socket="$SOCKET" -uroot -e "SELECT 1" >/dev/null 2>&1; then break; fi
  sleep 1
done
if ! "$MARIADB" --socket="$SOCKET" -uroot -e "SELECT 1" >/dev/null 2>&1; then
  red "MariaDB failed to start"; cat "$LOG_FILE" >&2 || true; exit 1
fi

MQ() { "$MARIADB" --socket="$SOCKET" -uroot "$@"; }
DUMP() { "$MARIADB_DUMP" --socket="$SOCKET" -uroot --default-character-set=utf8 \
           --single-transaction --skip-lock-tables "$@"; }

blue "==> importing $INPUT ($(du -h "$INPUT" | awk '{print $1}'))"
MQ -e "CREATE DATABASE \`$DB_NAME\` DEFAULT CHARACTER SET utf8"
MQ "$DB_NAME" < "$INPUT"

# All tables present after import (bash 3.2 compatible — no mapfile)
ALL_TABLES=()
while IFS= read -r t; do
  [[ -n "$t" ]] && ALL_TABLES+=("$t")
done < <(MQ "$DB_NAME" -N -e "SHOW TABLES" | sort)
blue "==> tables in migration dump: ${#ALL_TABLES[@]}"

# Other tables = everything except the two big ones
OTHER_TABLES=()
for t in "${ALL_TABLES[@]}"; do
  [[ "$t" == "wp_posts" || "$t" == "wp_postmeta" ]] && continue
  OTHER_TABLES+=("$t")
done

blue "==> exporting 01-wp_posts.sql"
DUMP "$DB_NAME" wp_posts > "$OUTDIR/01-wp_posts.sql"

blue "==> exporting 02-wp_postmeta.sql"
DUMP "$DB_NAME" wp_postmeta > "$OUTDIR/02-wp_postmeta.sql"

blue "==> exporting 03-rest.sql (${#OTHER_TABLES[@]} tables)"
DUMP "$DB_NAME" "${OTHER_TABLES[@]}" > "$OUTDIR/03-rest.sql"

blue "==> gzipping chunks"
for f in "$OUTDIR"/*.sql; do gzip -kf "$f"; done

# Verification: re-import each chunk into a clean DB and count
blue "==> verifying chunks (re-import into clean DB)"
MQ -e "DROP DATABASE IF EXISTS ftverify; CREATE DATABASE ftverify DEFAULT CHARACTER SET utf8"
MQ ftverify < "$OUTDIR/01-wp_posts.sql"
MQ ftverify < "$OUTDIR/02-wp_postmeta.sql"
MQ ftverify < "$OUTDIR/03-rest.sql"

POSTS=$(MQ ftverify -N -e "SELECT COUNT(*) FROM wp_posts")
ATTACH=$(MQ ftverify -N -e "SELECT COUNT(*) FROM wp_posts WHERE post_type='attachment'")
PMETA=$(MQ ftverify -N -e "SELECT COUNT(*) FROM wp_postmeta")
AFILE=$(MQ ftverify -N -e "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='_wp_attached_file'")

echo ""
green "=== CHUNK VERIFICATION (re-imported into clean DB) ==="
printf '  wp_posts rows         : %s\n' "$POSTS"
printf '  attachment posts      : %s\n' "$ATTACH"
printf '  wp_postmeta rows      : %s\n' "$PMETA"
printf '  _wp_attached_file meta: %s\n' "$AFILE"
echo ""
green "Output files (uncompressed | gzipped):"
ls -lh "$OUTDIR" | awk 'NR>1 {printf "  %-8s %s\n", $5, $9}'
echo ""
green "phpMyAdmin import order (kazdy soubor zvlast, tab Import):"
green "  1) 01-wp_posts.sql.gz"
green "  2) 02-wp_postmeta.sql.gz"
green "  3) 03-rest.sql.gz"
green "  4) wm144_wedos_net.migration-design.sql.gz  (design overlay, az nakonec)"
