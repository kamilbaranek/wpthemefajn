# Theme Staging Deploy

Tento workspace je schválně zúžený jen na deploy šablony `themes/fajntabory` pro staging:

- staging target: `/var/www/fajn.kamilbaranek.com/public/wp-content/themes/fajntabory`
- git remote pro deploy: `staging-theme`
- bare repo na serveru: `/opt/git/fajntabory-theme.git`
- deploy branch: `main`

## Jak deploy funguje

1. Lokální repo trackuje jen:
   - `.gitattributes`
   - `themes/fajntabory`
   - `scripts/deploy-theme-staging.sh`
   - `docs/theme-staging-deploy.md`
   - `.gitignore`
2. Push na remote `staging-theme` do branch `main` spustí na serveru `post-receive` hook.
3. Hook checkoutne poslední commit a přes `rsync --delete` přepíše staging theme adresář.

Deploy je úmyslně DEV rychlý:

- bez build pipeline
- bez zero-downtime řešení
- poslední push do `main` okamžitě přepisuje staging theme

## Běžné použití

Z workspace root:

```bash
scripts/deploy-theme-staging.sh "feat(theme): update checkout styling"
```

Skript udělá:

1. `git add` pro trackované deploy soubory
2. `git commit -m "..."`
3. `git push staging-theme HEAD:main`

Po pushi proběhne deploy automaticky na serveru.

## Užitečné příkazy

Zobraz remote:

```bash
git remote -v
```

Zobraz poslední commity:

```bash
git log --oneline --decorate -10
```

Ověř deploy na serveru:

```bash
ssh kamilbaranek 'tail -n 20 /opt/deploy/fajntabory-theme/deploy.log'
```

Zobraz poslední stav souborů na stagingu:

```bash
ssh kamilbaranek 'ls -la /var/www/fajn.kamilbaranek.com/public/wp-content/themes/fajntabory | sed -n "1,40p"'
```

## Poznámky

- Necommitují se dumpy databáze, `wp-config.php`, pluginy ani jiné soubory mimo theme deploy workflow.
- Pokud někdo upraví theme přímo na serveru, další deploy ty změny přepíše.
- `.DS_Store` a Apple metadata jsou ignorované a při deployi se na staging neposílají.
