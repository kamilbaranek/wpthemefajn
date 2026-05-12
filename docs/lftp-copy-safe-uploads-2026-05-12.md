# LFTP postup: presun obrazku z napadenych uploads do noveho webu

Zdroj:

```text
/domains/fajntabory.cz/wp-content/uploads
```

Cil:

```text
/domains/new.fajntabory.cz/wp-content/uploads
```

Pripraveny skript:

```bash
scripts/lftp-copy-safe-uploads.sh
```

Skript zamerne neuklada FTP heslo. Zkopiruje jen obrazkove soubory, zachova adresarovou strukturu a lokálně staging jeste jednou odfiltruje. PHP/PHTML/PHAR soubory se do noveho webu nenahravaji.

## Doporuceny postup

Nejdriv zkontrolovat plan:

```bash
MODE=plan scripts/lftp-copy-safe-uploads.sh
```

Spustit kompletni synchronizaci pres lokalni staging:

```bash
MODE=sync scripts/lftp-copy-safe-uploads.sh
```

Skript se sam zepta na FTP heslo. Pri psani hesla se na obrazovce nic nezobrazuje; je to zamerne. Po zadani hesla stisknout Enter a skript bude pokracovat.

Pokud chces heslo zadat predem pres promennou prostredi:

```bash
read -s FTP_PASS
export FTP_PASS
MODE=sync scripts/lftp-copy-safe-uploads.sh
unset FTP_PASS
```

Vychozi hodnoty ve skriptu:

```bash
FTP_HOST='160272.w72.wedos.net'
FTP_USER='w160272_new2026'
OLD_UPLOADS='/domains/fajntabory.cz/wp-content/uploads'
NEW_UPLOADS='/domains/new.fajntabory.cz/wp-content/uploads'
NEW_ROOT='/domains/new.fajntabory.cz'
NEW_UPLOADS_REL='wp-content/uploads'
LOCAL_STAGE='/private/tmp/fajntabory-safe-uploads'
```

## Dvoukrokova varianta

Stahnout ze stareho webu jen obrazky:

```bash
MODE=download scripts/lftp-copy-safe-uploads.sh
```

Zkontrolovat lokalni staging:

```bash
find /private/tmp/fajntabory-safe-uploads -type f \( -iname '*.php' -o -iname '*.phtml' -o -iname '*.phar' \)
find /private/tmp/fajntabory-safe-uploads -type f | wc -l
```

Prvni prikaz musi vratit prazdny vystup.

Nahrat do noveho webu:

```bash
MODE=upload scripts/lftp-copy-safe-uploads.sh
```

Pokud uz probehlo stazeni a chyba nastala az pri uploadu, neni nutne znovu stahovat. Staci opravit cilovou cestu nebo skript a pustit jen:

```bash
MODE=upload scripts/lftp-copy-safe-uploads.sh
```

## SVG

SVG neni ve vychozim nastaveni kopirovane, protoze muze obsahovat aktivni obsah a po incidentu je potreba ho brat jako rizikovy format. Pokud jsou SVG pro web nutne a predem zkontrolovane, lze je povolit:

```bash
ALLOW_SVG=1 MODE=sync scripts/lftp-copy-safe-uploads.sh
```

## Proc ne primy remote-to-remote presun

FTP server sice muze byt stejny, ale primy remote-to-remote presun by hure kontroloval, co se cestou kopiruje. Lokalni staging dava dve jistoty:

- lze zkontrolovat, ze v kopii nejsou PHP/PHTML/PHAR soubory;
- upload do noveho webu vychazi z uz odfiltrovaneho stromu.

Skript nepouziva `--delete`, takze v novem `/uploads` nemaze existujici soubory.
