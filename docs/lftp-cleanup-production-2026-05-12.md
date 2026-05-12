# LFTP cleanup postup pro incident Fajn tabory

Tento postup je nouzova pomucka pro smazani potvrzenych skodlivych souboru pres FTP/SFTP. Neni to plna nahrada za cisty rebuild. U teto kompromitace je bezpecnejsi vysledny stav:

1. zachovat forenzni kopii;
2. vycistit databazi offline;
3. nasadit cisty WordPress core, ciste pluginy a theme z repozitare;
4. uploady prenaset jen jako data;
5. lftp pouzit jen jako nouzovy cleanup nebo jako transport pro cisty deploy.

## Pripraveny skript

Skript:

```bash
scripts/lftp-clean-infected-files.sh
```

Vychozi rezim nic nemaze. Jen vypise lftp batch:

```bash
MODE=plan REMOTE_ROOT='/www/domains/fajntabory.cz' scripts/lftp-clean-infected-files.sh
```

Audit remote stromu:

```bash
FTP_HOST='ftp.example.com' \
FTP_USER='user' \
FTP_PASS='password' \
REMOTE_ROOT='/www/domains/fajntabory.cz' \
MODE=audit \
scripts/lftp-clean-infected-files.sh
```

Vystupy auditu:

- `/private/tmp/fajntabory-lftp-audit-*/remote-tree.txt`;
- `/private/tmp/fajntabory-lftp-audit-*/suspicious-files.txt`.

Skutecne mazani potvrzenych souboru:

```bash
FTP_HOST='ftp.example.com' \
FTP_USER='user' \
FTP_PASS='password' \
REMOTE_ROOT='/www/domains/fajntabory.cz' \
MODE=delete-confirmed \
CONFIRM_DELETE=YES \
scripts/lftp-clean-infected-files.sh
```

Volitelne lze pridat:

```bash
DELETE_HIGH_RISK_PLUGINS=1
```

To smaze i `wp-file-manager` a `wc-speed-drain-repair`. Tyto pluginy je lepsi po incidentu preinstalovat z duveryhodneho zdroje nebo je vubec nevracet.

Volitelne lze pridat:

```bash
DELETE_MANAGEWP_WORKER=1
```

To smaze `worker` plugin a `wp-content/mu-plugins/0-worker.php`. Pouzit jen pokud bude externi sprava znovu instalovana a autorizovana az po vycisteni pristupu.

## Co skript maze

Skript maze potvrzene nalezy z offline kopie:

- root malware: `amp.php`, `wp-mails.php`, `info.php`, `test.php`, `sitemap23.xml`;
- modifikovany/nepotrebny `wp-config-sample.php`;
- injektovane soubory ve `wp-admin` a `wp-includes`;
- skodlive mu-pluginy `test-mu-plugin.php` a `wp-cache.php`;
- falešne pluginy `backup_1778142536`, `gallery-1778349134`, `wp-security-helper`;
- pluginove backdoory `plugin-loader.php`, `woocommerce/includes/data-processor.php`, `akismet/includes/cache-processor.php`;
- extra PHP soubory v theme `fajntabory`, ktere nejsou v repozitari;
- potvrzene PHP payloady v `wp-content/uploads`;
- cele `wp-content/cache`;
- `wp-content/languages/translation-cache.php`.

Skript zamerne nemaze root `index.php`, i kdyz byl v zaloze napadeny. Smazani by web rozbilo. Ten se ma nahradit cistym WordPress core souborem pri redeployi.

## Lepsi varianta nez selektivni mazani

Pokud je k dispozici cisty lokalni WordPress root, je bezpecnejsi nepokouset se dohackovany web "opravit" soubor po souboru, ale prepsat neduveryhodne casti:

```bash
lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST"
cd /www/domains/fajntabory.cz
mirror -R --delete --verbose --parallel=4 /path/to/clean-wordpress-root .
bye
```

Tento prikaz je destruktivni. Pouzit jen pokud `/path/to/clean-wordpress-root` obsahuje kompletni a overenou instalaci, ktera ma na produkci zustat. Pred tim je nutne vyresit vyjimky:

- neprepsat produkcni `wp-config.php` naslepo;
- neprenaset zpet kompromitovane plugin adresare;
- `wp-content/uploads` resit oddelene a nikdy tam nenechat PHP/PHTML/PHAR;
- pred `mirror --delete` mit forenzni kopii celeho webrootu.

Pragmaticky nejbezpecnejsi postup je:

1. na hostingu prejmenovat stary webroot mimo verejny provoz;
2. nahrat cisty webroot;
3. nahrat ciste theme z repozitare;
4. pluginy instalovat z cistych zdroju;
5. uploady prenest po skenu;
6. importovat ocistenou DB kopii;
7. zmenit hesla, salts a DB heslo;
8. az potom web vratit do provozu.
