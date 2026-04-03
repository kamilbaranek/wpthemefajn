#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="themes/fajntabory"
STAGING_REMOTE_NAME="${DEPLOY_REMOTE:-staging-theme}"
STAGING_REMOTE_BRANCH="${DEPLOY_BRANCH:-main}"
PRODUCTION_ENV_FILE="${PRODUCTION_ENV_FILE:-$ROOT_DIR/.deploy.production.env}"
PRODUCTION_MANIFEST_NAME=".theme-deploy-manifest"
TARGET="staging"

usage() {
  cat <<'EOF'
Usage:
  deploy-theme.sh [--staging|--production] "commit message"

Notes:
  - Default target is staging.
  - Production deploy is explicit and uses FTP credentials from .deploy.production.env.
EOF
}

require_git_repo() {
  if ! git -C "$ROOT_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    echo "Git repo is not initialized in $ROOT_DIR."
    exit 1
  fi
}

require_staging_remote() {
  if ! git -C "$ROOT_DIR" remote get-url "$STAGING_REMOTE_NAME" >/dev/null 2>&1; then
    echo "Git remote '$STAGING_REMOTE_NAME' is not configured."
    exit 1
  fi
}

require_production_env() {
  if [[ ! -f "$PRODUCTION_ENV_FILE" ]]; then
    echo "Missing production config: $PRODUCTION_ENV_FILE"
    exit 1
  fi

  # shellcheck disable=SC1090
  source "$PRODUCTION_ENV_FILE"

  : "${PRODUCTION_FTP_SCHEME:?Missing PRODUCTION_FTP_SCHEME}"
  : "${PRODUCTION_FTP_HOST:?Missing PRODUCTION_FTP_HOST}"
  : "${PRODUCTION_FTP_USER:?Missing PRODUCTION_FTP_USER}"
  : "${PRODUCTION_FTP_PASSWORD:?Missing PRODUCTION_FTP_PASSWORD}"
  : "${PRODUCTION_FTP_THEME_PATH:?Missing PRODUCTION_FTP_THEME_PATH}"

  if ! command -v curl >/dev/null 2>&1; then
    echo "curl is required for production FTP deploy."
    exit 1
  fi
}

stage_tracked_files() {
  git add \
    .deploy.production.env.example \
    .gitattributes \
    .gitignore \
    docs/theme-staging-deploy.md \
    scripts/deploy-theme.sh \
    scripts/deploy-theme-staging.sh \
    "$THEME_DIR"
}

commit_changes() {
  local message="$1"

  stage_tracked_files

  if git diff --cached --quiet; then
    if ! git rev-parse --verify HEAD >/dev/null 2>&1; then
      echo "No tracked changes and no existing commit to deploy."
      exit 1
    fi

    echo "No tracked changes. Deploying current HEAD."
    return
  fi

  git commit -m "$message"
}

production_deploy() {
  local current_manifest previous_manifest deleted_manifest
  current_manifest="$(mktemp)"
  previous_manifest="$(mktemp)"
  deleted_manifest="$(mktemp)"

  trap 'rm -f "$current_manifest" "$previous_manifest" "$deleted_manifest"' RETURN

  git ls-files "$THEME_DIR" | while IFS= read -r path; do
    local rel="${path#$THEME_DIR/}"
    case "$rel" in
      .DS_Store|*/.DS_Store|._*|*/._*)
        ;;
      *)
        printf '%s\n' "$rel"
        ;;
    esac
  done | sort > "$current_manifest"

  curl -fsS \
    --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" \
    "${PRODUCTION_FTP_SCHEME}://$PRODUCTION_FTP_HOST${PRODUCTION_FTP_THEME_PATH}/${PRODUCTION_MANIFEST_NAME}" \
    -o "$previous_manifest" || true

  while IFS= read -r rel; do
    [[ -z "$rel" ]] && continue
    echo "Uploading $rel"
    curl -fsS \
      --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" \
      --ftp-create-dirs \
      -T "$ROOT_DIR/$THEME_DIR/$rel" \
      "${PRODUCTION_FTP_SCHEME}://$PRODUCTION_FTP_HOST${PRODUCTION_FTP_THEME_PATH}/$rel" \
      >/dev/null
  done < "$current_manifest"

  if [[ -s "$previous_manifest" ]]; then
    comm -23 "$previous_manifest" "$current_manifest" > "$deleted_manifest" || true

    while IFS= read -r rel; do
      [[ -z "$rel" ]] && continue
      echo "Deleting stale file $rel"
      curl -fsS \
        --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" \
        --quote "DELE ${PRODUCTION_FTP_THEME_PATH}/$rel" \
        "${PRODUCTION_FTP_SCHEME}://$PRODUCTION_FTP_HOST/" \
        >/dev/null || true
    done < "$deleted_manifest"

    awk -F/ 'NF > 1 { NF--; print }' "$deleted_manifest" | sort -r -u | while IFS= read -r dir; do
      [[ -z "$dir" ]] && continue
      curl -fsS \
        --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" \
        --quote "RMD ${PRODUCTION_FTP_THEME_PATH}/$dir" \
        "${PRODUCTION_FTP_SCHEME}://$PRODUCTION_FTP_HOST/" \
        >/dev/null || true
    done
  fi

  curl -fsS \
    --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" \
    --ftp-create-dirs \
    -T "$current_manifest" \
    "${PRODUCTION_FTP_SCHEME}://$PRODUCTION_FTP_HOST${PRODUCTION_FTP_THEME_PATH}/${PRODUCTION_MANIFEST_NAME}" \
    >/dev/null

  echo "Production theme deploy finished."
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --staging)
      TARGET="staging"
      shift
      ;;
    --production)
      TARGET="production"
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    --)
      shift
      break
      ;;
    -*)
      echo "Unknown option: $1"
      usage
      exit 1
      ;;
    *)
      break
      ;;
  esac
done

if [[ $# -ne 1 ]]; then
  usage
  exit 1
fi

COMMIT_MESSAGE="$1"

require_git_repo

case "$TARGET" in
  staging)
    require_staging_remote
    ;;
  production)
    require_production_env
    ;;
esac

cd "$ROOT_DIR"

commit_changes "$COMMIT_MESSAGE"

case "$TARGET" in
  staging)
    git push "$STAGING_REMOTE_NAME" "HEAD:${STAGING_REMOTE_BRANCH}"
    echo "Staging theme deploy finished."
    ;;
  production)
    production_deploy
    ;;
esac
