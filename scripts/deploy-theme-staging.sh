#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REMOTE_NAME="${DEPLOY_REMOTE:-staging-theme}"
REMOTE_BRANCH="${DEPLOY_BRANCH:-main}"

usage() {
  echo "Usage: $(basename "$0") \"commit message\""
}

if [[ $# -ne 1 ]]; then
  usage
  exit 1
fi

if ! git -C "$ROOT_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Git repo is not initialized in $ROOT_DIR."
  exit 1
fi

if ! git -C "$ROOT_DIR" remote get-url "$REMOTE_NAME" >/dev/null 2>&1; then
  echo "Git remote '$REMOTE_NAME' is not configured."
  exit 1
fi

cd "$ROOT_DIR"

git add \
  .gitattributes \
  .gitignore \
  docs/theme-staging-deploy.md \
  scripts/deploy-theme-staging.sh \
  themes/fajntabory

if git diff --cached --quiet; then
  echo "No tracked changes to deploy."
  exit 1
fi

git commit -m "$1"
git push "$REMOTE_NAME" "HEAD:${REMOTE_BRANCH}"

echo "Staging theme deploy finished."
