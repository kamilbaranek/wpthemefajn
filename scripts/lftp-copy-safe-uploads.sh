#!/usr/bin/env bash
set -euo pipefail

# Copy safe image uploads from the compromised WordPress tree to the new tree.
# The FTP password is intentionally not stored in this file.
#
# Defaults:
#   FTP_HOST=160272.w72.wedos.net
#   FTP_USER=w160272_new2026
#   OLD_UPLOADS=/domains/fajntabory.cz/wp-content/uploads
#   NEW_UPLOADS=/domains/new.fajntabory.cz/wp-content/uploads
#
# Modes:
#   MODE=plan      print the commands and paths
#   MODE=download  download image files to LOCAL_STAGE and filter locally
#   MODE=upload    upload already prepared LOCAL_STAGE to NEW_UPLOADS
#   MODE=sync      download, filter, then upload

MODE="${MODE:-plan}"
FTP_HOST="${FTP_HOST:-160272.w72.wedos.net}"
FTP_USER="${FTP_USER:-w160272_new2026}"
OLD_UPLOADS="${OLD_UPLOADS:-/domains/fajntabory.cz/wp-content/uploads}"
NEW_UPLOADS="${NEW_UPLOADS:-/domains/new.fajntabory.cz/wp-content/uploads}"
LOCAL_STAGE="${LOCAL_STAGE:-/private/tmp/fajntabory-safe-uploads}"
ALLOW_SVG="${ALLOW_SVG:-0}"

require_password() {
  : "${FTP_PASS:?Set FTP_PASS. Do not store it in this script.}"
}

print_plan() {
  cat <<PLAN
FTP host:      $FTP_HOST
FTP user:      $FTP_USER
Old uploads:   $OLD_UPLOADS
New uploads:   $NEW_UPLOADS
Local stage:   $LOCAL_STAGE
Mode:          $MODE
SVG allowed:   $ALLOW_SVG

Recommended run:
  read -s FTP_PASS
  export FTP_PASS
  MODE=sync scripts/lftp-copy-safe-uploads.sh

This copies image files only, preserves directory structure, filters the local
stage again, and uploads to the new uploads directory without deleting remote
files that are already there.
PLAN
}

download_images() {
  require_password
  mkdir -p "$LOCAL_STAGE"

  lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP
set cmd:fail-exit yes
set net:max-retries 2
set net:timeout 30
set ftp:ssl-allow true
mirror --verbose --parallel=4 --continue --no-empty-dirs \
  --include-glob */ \
  --include-glob *.jpg --include-glob *.JPG \
  --include-glob *.jpeg --include-glob *.JPEG \
  --include-glob *.jpe --include-glob *.JPE \
  --include-glob *.png --include-glob *.PNG \
  --include-glob *.gif --include-glob *.GIF \
  --include-glob *.webp --include-glob *.WEBP \
  --include-glob *.avif --include-glob *.AVIF \
  --include-glob *.ico --include-glob *.ICO \
  --include-glob *.bmp --include-glob *.BMP \
  --include-glob *.tif --include-glob *.TIF \
  --include-glob *.tiff --include-glob *.TIFF \
  --include-glob *.heic --include-glob *.HEIC \
  --include-glob *.heif --include-glob *.HEIF \
  --exclude-glob *.php --exclude-glob *.PHP \
  --exclude-glob *.phtml --exclude-glob *.PHTML \
  --exclude-glob *.phar --exclude-glob *.PHAR \
  --exclude-glob * \
  "$OLD_UPLOADS" "$LOCAL_STAGE"
bye
LFTP

  filter_local_stage
}

filter_local_stage() {
  # Defense in depth: remove executable PHP-like files even if the remote
  # include/exclude rules behave differently on a specific FTP server.
  find "$LOCAL_STAGE" -type f \( \
    -iname '*.php' -o \
    -iname '*.php[0-9]' -o \
    -iname '*.phtml' -o \
    -iname '*.phar' \
  \) -print -delete

  if [[ "$ALLOW_SVG" == "1" ]]; then
    find "$LOCAL_STAGE" -type f ! \( \
      -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.jpe' -o \
      -iname '*.png' -o -iname '*.gif' -o -iname '*.webp' -o \
      -iname '*.avif' -o -iname '*.ico' -o -iname '*.bmp' -o \
      -iname '*.tif' -o -iname '*.tiff' -o -iname '*.heic' -o \
      -iname '*.heif' -o -iname '*.svg' \
    \) -print -delete
  else
    find "$LOCAL_STAGE" -type f ! \( \
      -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.jpe' -o \
      -iname '*.png' -o -iname '*.gif' -o -iname '*.webp' -o \
      -iname '*.avif' -o -iname '*.ico' -o -iname '*.bmp' -o \
      -iname '*.tif' -o -iname '*.tiff' -o -iname '*.heic' -o \
      -iname '*.heif' \
    \) -print -delete
  fi

  find "$LOCAL_STAGE" -mindepth 1 -type d -empty -delete
}

upload_images() {
  require_password

  if [[ ! -d "$LOCAL_STAGE" ]]; then
    printf 'Local stage does not exist: %s\n' "$LOCAL_STAGE" >&2
    exit 2
  fi

  filter_local_stage

  lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP
set cmd:fail-exit yes
set net:max-retries 2
set net:timeout 30
set ftp:ssl-allow true
mkdir -p "$NEW_UPLOADS"
mirror -R --verbose --parallel=4 --continue "$LOCAL_STAGE" "$NEW_UPLOADS"
bye
LFTP
}

case "$MODE" in
  plan)
    print_plan
    ;;
  download)
    download_images
    ;;
  upload)
    upload_images
    ;;
  sync)
    download_images
    upload_images
    ;;
  *)
    printf 'Unknown MODE=%s. Use plan, download, upload, or sync.\n' "$MODE" >&2
    exit 2
    ;;
esac
