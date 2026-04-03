# Theme Deploy

Tento workspace je schválně zúžený jen na deploy šablony `themes/fajntabory`.

Default je vždy staging. Produkce je explicitní a jde přes FTP.

- staging target: `/var/www/fajn.kamilbaranek.com/public/wp-content/themes/fajntabory`
- git remote pro deploy: `staging-theme`
- bare repo na serveru: `/opt/git/fajntabory-theme.git`
- deploy branch: `main`
- production target: `/domains/fajntabory.cz/wp-content/themes/fajntabory`
- production transport: FTP
- production local config: `.deploy.production.env` (necommitovaný soubor)

## Jak deploy funguje

1. Lokální repo trackuje jen:
   - `.deploy.production.env.example`
   - `.gitattributes`
   - `themes/fajntabory`
   - `scripts/deploy-theme.sh`
   - `scripts/deploy-theme-staging.sh`
   - `docs/theme-staging-deploy.md`
   - `.gitignore`
2. Push na remote `staging-theme` do branch `main` spustí na serveru `post-receive` hook.
3. Hook checkoutne poslední commit a přes `rsync --delete` přepíše staging theme adresář.
4. Hook po deployi čistí staré `.DS_Store` a `._*` soubory v targetu.
5. Produkční deploy používá lokální FTP upload přes `curl`, nahrává jen `themes/fajntabory` a drží si remote manifest pro mazání smazaných souborů.

Deploy je úmyslně DEV rychlý:

- bez build pipeline
- bez zero-downtime řešení
- staging je default
- produkce se deployuje jen explicitně

## Běžné použití

Z workspace root:

```bash
scripts/deploy-theme.sh "feat(theme): update checkout styling"
```

Skript udělá:

1. `git add` pro trackované deploy soubory
2. `git commit -m "..."`
3. podle targetu:
   - `staging`: `git push staging-theme HEAD:main`
   - `production`: FTP upload na produkční theme path

Staging je default:

```bash
scripts/deploy-theme.sh "feat(theme): update checkout styling"
```

Explicitní staging:

```bash
scripts/deploy-theme.sh --staging "fix(theme): adjust cart totals"
```

Produkce jen explicitně:

```bash
scripts/deploy-theme.sh --production "release(theme): publish verified checkout changes"
```

Původní wrapper pro staging zůstává funkční:

```bash
scripts/deploy-theme-staging.sh "feat(theme): quick staging push"
```

## Produkční konfigurace

Produkční FTP credentials jsou načítané z lokálního, necommitovaného souboru:

```bash
.deploy.production.env
```

Formát:

```bash
PRODUCTION_FTP_SCHEME=ftp
PRODUCTION_FTP_HOST=example.wedos.net
PRODUCTION_FTP_USER=example_user
PRODUCTION_FTP_PASSWORD=example_password
PRODUCTION_FTP_THEME_PATH=/domains/fajntabory.cz/wp-content/themes/fajntabory
```

Pro repo je commitovaný jen example soubor:

```bash
.deploy.production.env.example
```

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

Ověř produkční FTP login bez deploye:

```bash
source .deploy.production.env
curl --user "$PRODUCTION_FTP_USER:$PRODUCTION_FTP_PASSWORD" "${PRODUCTION_FTP_SCHEME}://$PRODUCTION_FTP_HOST/domains/fajntabory.cz/" --list-only
```

## Poznámky

- Necommitují se dumpy databáze, `wp-config.php`, pluginy ani jiné soubory mimo theme deploy workflow.
- Pokud někdo upraví theme přímo na serveru, další deploy ty změny přepíše.
- `.DS_Store` a Apple metadata jsou ignorované a při deployi se na staging ani produkci neposílají.
- Produkční deploy je záměrně ne-defaultní, aby staging zůstal rychlá a bezpečnější DEV cesta.
