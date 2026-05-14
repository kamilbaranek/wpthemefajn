#!/usr/bin/env bash
# Fallback: rozdeli `wm144_wedos_net.migration.sql` na samostatne soubory
# per-tabulka pro hostingy s velmi pristnym phpMyAdmin upload limitem
# (treba 2 MB), kde i 7.8 MB gzipped dump je moc velky.
#
# Vystup je v adresari `DB/migration-chunks/`:
#   01-schema.sql            -- SET / CREATE TABLE bloky vsech tabulek
#   02-data-wp_posts.sql     -- data wp_posts (nejvetsi)
#   03-data-wp_postmeta.sql  -- data wp_postmeta (druhe nejvetsi)
#   04-data-<small>.sql      -- data ostatnich tabulek (komentare, terms, …)
#   05-triggers-routines.sql -- triggers/routines/events
#
# Kazdy chunk je sam o sobe konzistentni a lze ho importovat zvlast.
# Doporucene poradi: 01, 02, 03, 04..., 05.
#
# Pripadne se da kazdy chunk dale gzipnout pro dalsi uspory:
#   gzip *.sql

set -euo pipefail

INPUT="${1:-${REPO_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}/DB/wm144_wedos_net.migration.sql}"
OUTDIR="${2:-$(dirname "$INPUT")/migration-chunks}"

if [[ ! -f "$INPUT" ]]; then
  echo "ERR: Migration file not found: $INPUT" >&2
  exit 1
fi

mkdir -p "$OUTDIR"
rm -f "$OUTDIR"/*.sql "$OUTDIR"/*.gz 2>/dev/null || true

echo "==> splitting $INPUT into chunks in $OUTDIR"

awk -v outdir="$OUTDIR" '
BEGIN {
  schema_file = outdir "/01-schema.sql"
  triggers_file = outdir "/05-triggers-routines.sql"
  in_table = ""
  state = "header"  # header → schema → data → triggers
  schema_buf = ""
  data_file = ""
}

# Identify section transitions
/^DROP TABLE IF EXISTS `[^`]+`/ {
  match($0, /`[^`]+`/)
  in_table = substr($0, RSTART+1, RLENGTH-2)
  state = "schema"
  next_data_target = ""
}

# Schema lines: DROP TABLE, CREATE TABLE, set blocks etc.
state == "schema" {
  print >> schema_file
  if ($0 ~ /\) ENGINE=.*;$/) {
    # End of CREATE TABLE, schema for this table done
  }
}

# Detect INSERT INTO blocks
/^(LOCK TABLES|INSERT INTO|\/\*![0-9]+ ALTER TABLE|UNLOCK TABLES)/ {
  if (state == "schema" || state == "data") {
    # Find which table the INSERT is for
    if ($0 ~ /^INSERT INTO `([^`]+)`/) {
      match($0, /`[^`]+`/)
      table = substr($0, RSTART+1, RLENGTH-2)
      # Sort big tables to dedicated files; everything else into 04-data-other.sql
      if (table == "wp_posts") {
        data_file = outdir "/02-data-wp_posts.sql"
      } else if (table == "wp_postmeta") {
        data_file = outdir "/03-data-wp_postmeta.sql"
      } else {
        data_file = outdir "/04-data-other.sql"
      }
    } else if ($0 ~ /^LOCK TABLES `([^`]+)`/) {
      match($0, /`[^`]+`/)
      table = substr($0, RSTART+1, RLENGTH-2)
      if (table == "wp_posts") {
        data_file = outdir "/02-data-wp_posts.sql"
      } else if (table == "wp_postmeta") {
        data_file = outdir "/03-data-wp_postmeta.sql"
      } else {
        data_file = outdir "/04-data-other.sql"
      }
    }
    if (data_file != "") {
      print >> data_file
    }
    state = "data"
    next
  }
}

# Trigger/routine/event section heuristic
/^DELIMITER/ {
  state = "triggers"
  print >> triggers_file
  next
}

# Data section continuation
state == "data" {
  if (data_file != "") print >> data_file
  next
}

# Header / footer / SET sections
state == "header" || state == "triggers" {
  if (state == "header") {
    print >> schema_file
  } else {
    print >> triggers_file
  }
}
' "$INPUT"

# Prepend SET / charset block to every data chunk so each file imports independently
HEADER_TMP="$(mktemp)"
cat > "$HEADER_TMP" <<'EOF'
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
/*!40101 SET NAMES utf8 */;

EOF

for f in "$OUTDIR"/02-data-wp_posts.sql "$OUTDIR"/03-data-wp_postmeta.sql "$OUTDIR"/04-data-other.sql; do
  [[ -f "$f" ]] || continue
  cat "$HEADER_TMP" "$f" > "$f.tmp" && mv "$f.tmp" "$f"
done
rm "$HEADER_TMP"

# Compress each chunk
echo "==> gzipping chunks"
for f in "$OUTDIR"/*.sql; do
  gzip -k "$f"
done

echo ""
echo "Output files (uncompressed | gzipped):"
ls -lh "$OUTDIR" | awk 'NR>1 {printf "  %s  %s\n", $5, $9}'

echo ""
echo "Recommended phpMyAdmin import order:"
echo "  1) 01-schema.sql.gz       -- creates empty tables"
echo "  2) 02-data-wp_posts.sql.gz"
echo "  3) 03-data-wp_postmeta.sql.gz"
echo "  4) 04-data-other.sql.gz"
echo "  5) 05-triggers-routines.sql.gz (if present)"
echo "  6) wm144_wedos_net.migration-design.sql.gz (design overlay)"
