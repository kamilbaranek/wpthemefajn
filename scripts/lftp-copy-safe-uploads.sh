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
NEW_ROOT="${NEW_ROOT:-/domains/new.fajntabory.cz}"
NEW_UPLOADS_REL="${NEW_UPLOADS_REL:-wp-content/uploads}"
NEW_UPLOADS="${NEW_UPLOADS:-$NEW_ROOT/$NEW_UPLOADS_REL}"
LOCAL_STAGE="${LOCAL_STAGE:-/private/tmp/fajntabory-safe-uploads}"
ALLOW_SVG="${ALLOW_SVG:-0}"

require_password() {
  if [[ -z "${FTP_PASS:-}" ]]; then
    printf 'FTP password for %s@%s: ' "$FTP_USER" "$FTP_HOST" >&2
    read -r -s FTP_PASS
    printf '\n' >&2
  fi

  if [[ -z "$FTP_PASS" ]]; then
    printf 'FTP password is empty; aborting.\n' >&2
    exit 2
  fi
}

print_plan() {
  cat <<PLAN
FTP host:      $FTP_HOST
FTP user:      $FTP_USER
Old uploads:   $OLD_UPLOADS
New uploads:   $NEW_UPLOADS
New root:      $NEW_ROOT
New rel path:  $NEW_UPLOADS_REL
Local stage:   $LOCAL_STAGE
Mode:          $MODE
SVG allowed:   $ALLOW_SVG

Recommended run:
  MODE=sync scripts/lftp-copy-safe-uploads.sh

The script will ask for the FTP password if FTP_PASS is not already set.
This copies image files only, preserves directory structure, filters the local
stage again, and uploads to the new uploads directory without deleting remote
files that are already there.
PLAN
}

download_images() {
  require_password
  mkdir -p "$LOCAL_STAGE"
  printf 'Downloading image uploads from %s to %s\n' "$OLD_UPLOADS" "$LOCAL_STAGE" >&2

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
  printf 'Download/filter finished. Local stage: %s\n' "$LOCAL_STAGE" >&2
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
  printf 'Uploading filtered images from %s to %s\n' "$LOCAL_STAGE" "$NEW_UPLOADS" >&2

  lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP
set cmd:fail-exit yes
set net:max-retries 2
set net:timeout 30
set ftp:ssl-allow true
cd "$NEW_ROOT"
mkdir -fp wp-content
mkdir -fp "$NEW_UPLOADS_REL"
mirror -R --verbose --parallel=4 --continue "$LOCAL_STAGE" "$NEW_UPLOADS_REL"
bye
LFTP
  printf 'Upload finished.\n' >&2
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
