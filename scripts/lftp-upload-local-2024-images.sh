#!/usr/bin/env bash
set -euo pipefail

# Upload image files from the local production dump uploads/2024 to the new
# WordPress installation over lftp. The FTP password is intentionally not stored.
#
# Defaults:
#   LOCAL_SOURCE=/Users/kamilbaranek/dev/fajntabory/uploads/2024
#   LOCAL_STAGE=/private/tmp/fajntabory-uploads-2024-images
#   FTP_HOST=160272.w72.wedos.net
#   FTP_USER=w160272_new2026
#   NEW_ROOT=/domains/new.fajntabory.cz
#   REMOTE_TARGET_REL=wp-content/uploads/2024
#
# Modes:
#   MODE=plan        print configuration only
#   MODE=stage       copy allowed local images into LOCAL_STAGE
#   MODE=upload      upload existing LOCAL_STAGE to FTP
#   MODE=sync        stage, then upload
#   MODE=clean-stage remove LOCAL_STAGE

MODE="${MODE:-plan}"
LOCAL_SOURCE="${LOCAL_SOURCE:-/Users/kamilbaranek/dev/fajntabory/uploads/2024}"
LOCAL_STAGE="${LOCAL_STAGE:-/private/tmp/fajntabory-uploads-2024-images}"
FTP_HOST="${FTP_HOST:-160272.w72.wedos.net}"
FTP_USER="${FTP_USER:-w160272_new2026}"
NEW_ROOT="${NEW_ROOT:-/domains/new.fajntabory.cz}"
REMOTE_TARGET_REL="${REMOTE_TARGET_REL:-wp-content/uploads/2024}"
ALLOW_SVG="${ALLOW_SVG:-0}"
RESET_STAGE="${RESET_STAGE:-1}"

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

assert_stage_is_safe_to_remove() {
  case "$LOCAL_STAGE" in
    /private/tmp/*|/tmp/*)
      ;;
    *)
      printf 'Refusing to remove LOCAL_STAGE outside /private/tmp or /tmp: %s\n' "$LOCAL_STAGE" >&2
      exit 2
      ;;
  esac
}

print_plan() {
  cat <<PLAN
Local source:       $LOCAL_SOURCE
Local stage:        $LOCAL_STAGE
FTP host:           $FTP_HOST
FTP user:           $FTP_USER
New root:           $NEW_ROOT
Remote target rel:  $REMOTE_TARGET_REL
SVG allowed:        $ALLOW_SVG
Reset stage:        $RESET_STAGE
Mode:               $MODE

Recommended run:
  MODE=sync scripts/lftp-upload-local-2024-images.sh

The script copies image files only, skips .cache directories, filters the local
stage, asks for the FTP password if FTP_PASS is not set, and uploads to:
  $NEW_ROOT/$REMOTE_TARGET_REL
PLAN
}

stage_images() {
  if [[ ! -d "$LOCAL_SOURCE" ]]; then
    printf 'Local source does not exist: %s\n' "$LOCAL_SOURCE" >&2
    exit 2
  fi

  if [[ "$RESET_STAGE" == "1" ]]; then
    assert_stage_is_safe_to_remove
    rm -rf "$LOCAL_STAGE"
  fi

  mkdir -p "$LOCAL_STAGE"
  printf 'Staging images from %s to %s\n' "$LOCAL_SOURCE" "$LOCAL_STAGE" >&2

  if [[ "$ALLOW_SVG" == "1" ]]; then
    (
      cd "$LOCAL_SOURCE"
      find . -type d -name '.cache' -prune -o -type f \( \
        -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.jpe' -o \
        -iname '*.png' -o -iname '*.gif' -o -iname '*.webp' -o \
        -iname '*.avif' -o -iname '*.ico' -o -iname '*.bmp' -o \
        -iname '*.tif' -o -iname '*.tiff' -o -iname '*.heic' -o \
        -iname '*.heif' -o -iname '*.svg' \
      \) -print0
    ) | copy_find_results
  else
    (
      cd "$LOCAL_SOURCE"
      find . -type d -name '.cache' -prune -o -type f \( \
        -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.jpe' -o \
        -iname '*.png' -o -iname '*.gif' -o -iname '*.webp' -o \
        -iname '*.avif' -o -iname '*.ico' -o -iname '*.bmp' -o \
        -iname '*.tif' -o -iname '*.tiff' -o -iname '*.heic' -o \
        -iname '*.heif' \
      \) -print0
    ) | copy_find_results
  fi

  filter_stage
  printf 'Staged files: %s\n' "$(find "$LOCAL_STAGE" -type f | wc -l | tr -d ' ')" >&2
}

copy_find_results() {
  while IFS= read -r -d '' rel_path; do
    rel_path="${rel_path#./}"
    mkdir -p "$LOCAL_STAGE/$(dirname "$rel_path")"
    cp -p "$LOCAL_SOURCE/$rel_path" "$LOCAL_STAGE/$rel_path"
  done
}

filter_stage() {
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
    printf 'Run MODE=stage first, or use MODE=sync.\n' >&2
    exit 2
  fi

  filter_stage
  printf 'Uploading %s files to %s/%s\n' \
    "$(find "$LOCAL_STAGE" -type f | wc -l | tr -d ' ')" \
    "$NEW_ROOT" \
    "$REMOTE_TARGET_REL" >&2

  lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP
set cmd:fail-exit yes
set net:max-retries 2
set net:timeout 30
set ftp:ssl-allow true
cd "$NEW_ROOT"
mkdir -fp wp-content
mkdir -fp wp-content/uploads
mkdir -fp "$REMOTE_TARGET_REL"
mirror -R --verbose --parallel=4 --continue "$LOCAL_STAGE" "$REMOTE_TARGET_REL"
bye
LFTP

  printf 'Upload finished.\n' >&2
}

clean_stage() {
  assert_stage_is_safe_to_remove
  rm -rf "$LOCAL_STAGE"
  printf 'Removed local stage: %s\n' "$LOCAL_STAGE" >&2
}

case "$MODE" in
  plan)
    print_plan
    ;;
  stage)
    stage_images
    ;;
  upload)
    upload_images
    ;;
  sync)
    stage_images
    upload_images
    ;;
  clean-stage)
    clean_stage
    ;;
  *)
    printf 'Unknown MODE=%s. Use plan, stage, upload, sync, or clean-stage.\n' "$MODE" >&2
    exit 2
    ;;
esac
