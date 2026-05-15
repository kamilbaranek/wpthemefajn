# Fajn Tabory — project notes for Claude

## Deploy workflow

Production deploys via FTP from the working tree of the `main` branch
(see `scripts/deploy-theme.sh`, `scripts/deploy-theme-staging.sh`). There is
no `git push` step — FTP is the deploy channel.

### Standing rule: always merge to `main` after completing work

After finishing a task (or a coherent chunk of work) on this project:

1. Commit changes on the current worktree branch.
2. Switch to the main repo at `/Users/kamilbaranek/dev/fajntabory` and merge
   the worktree branch into `main` (fast-forward when possible):
   ```bash
   git -C /Users/kamilbaranek/dev/fajntabory merge <worktree-branch>
   ```
3. Confirm `main` is at the expected commit so the user can FTP-deploy
   directly from `main`'s working tree.

Do **not** `git push` unless the user explicitly asks. The user FTP-deploys.

If `main` has uncommitted changes that don't conflict with the merge, the
merge can proceed (those changes are unrelated user work — leave them alone).

## Gitignore convention

`.gitignore` is allowlist-based (`*` then `!/specific/path`). Existing
practice for adding new docs/scripts is `git add -f <path>` rather than
extending the allowlist. Follow the same pattern.

## Active runbooks (incident response 2026-05)

- `docs/production-malware-analysis-2026-05-11.md` — root cause analysis
- `docs/lftp-cleanup-production-2026-05-12.md` — filesystem cleanup over FTP
- `docs/db-cleanup-wm144-wedos-2026-05-12.sql` — DB stage 1 (users, trigger, active_plugins)
- `docs/db-cleanup-stage2-wm144-wedos-2026-05-12.sql` — DB stage 2 (hidden SEO spam divs)
- `docs/db-cleanup-stage3-wm144-wedos-2026-05-12.sql` — DB stage 3 (1226 fake spam posts cascade delete)
- `docs/db-cleanup-stage4-migration-prep-2026-05-12.sql` — DB stage 4 (fresh-install migration prep)
- `docs/db-import-into-fresh-install-2026-05-12.md` — fresh-install import runbook (CLI + phpMyAdmin)
- `docs/theme-security-audit-2026-05-15.md` — theme security audit & hardening (CSRF, XSS, CSV upload, WooCommerce template refresh)
- `scripts/db-cleanup-local-restore.sh` — ephemeral MariaDB cleanup pipeline
- `scripts/db-split-migration-by-table.sh` — per-table split for tight phpMyAdmin upload limits

## Working with this repo

- Theme code lives under `themes/fajntabory/` (the only theme deployed).
- `backup-production/` is a forensic snapshot — read-only reference.
- `DB/` holds raw SQL dumps and cleanup outputs — gitignored.
- Worktrees under `.claude/worktrees/` and `~/.codex/worktrees/` are used by
  Claude / codex agents respectively.
