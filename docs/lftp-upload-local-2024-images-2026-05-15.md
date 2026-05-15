# LFTP postup: upload obrazku z lokalniho dumpu uploads/2024

Zdroj:

```text
/Users/kamilbaranek/dev/fajntabory/uploads/2024
```

Cil na FTP:

```text
/domains/new.fajntabory.cz/wp-content/uploads/2024
```

Pripraveny skript:

```bash
scripts/lftp-upload-local-2024-images.sh
```

Skript bere lokalni dump produkcnich souboru z roku 2024, vytvori filtrovanou
kopii jen s obrazky v `/private/tmp/fajntabory-uploads-2024-images` a potom ji
pres `lftp mirror -R` nahraje do nove instalace. FTP heslo se neuklada do
souboru.

## Dulezite zjisteni

V lokalnim dumpu je 53 928 souboru. Z toho 53 912 odpovida povolenym obrazkovym
priponam. Zbytek obsahuje mimo jine PDF, `.DS_Store`, PHP soubory v koreni roku
2024 a PHP soubory pod `.cache`. Proto se nesmi delat prime nahrani celeho
adresare.

Povolene formaty ve vychozim nastaveni:

```text
jpg, jpeg, jpe, png, gif, webp, avif, ico, bmp, tif, tiff, heic, heif
```

SVG je vypnute, protoze po incidentu je bez dalsi kontroly rizikove.

## Doporuceny postup

Spoustet z korene projektu:

```bash
cd /Users/kamilbaranek/dev/fajntabory
```

Nejdriv zkontrolovat plan:

```bash
MODE=plan scripts/lftp-upload-local-2024-images.sh
```

Pripravit lokalni filtrovanou kopii:

```bash
MODE=stage scripts/lftp-upload-local-2024-images.sh
```

Overit, ze staging neobsahuje spustitelne PHP/PHTML/PHAR soubory:

```bash
find /private/tmp/fajntabory-uploads-2024-images -type f \( -iname '*.php' -o -iname '*.php[0-9]' -o -iname '*.phtml' -o -iname '*.phar' \)
find /private/tmp/fajntabory-uploads-2024-images -type f | wc -l
```

Prvni prikaz musi vratit prazdny vystup. Druhy by mel ukazat pocet obrazku,
typicky 53 912.

Nahrat filtrovanou kopii na FTP:

```bash
MODE=upload scripts/lftp-upload-local-2024-images.sh
```

Jednokrokova varianta:

```bash
MODE=sync scripts/lftp-upload-local-2024-images.sh
```

Skript se sam zepta na FTP heslo. Pri psani hesla se nic nezobrazuje; je to
normalni chovani `read -s`. Po napsani hesla stisknout Enter.

## Heslo pres promennou prostredi

Pokud nechces cekat na dotaz skriptu, lze heslo predat jen pro aktualni shell:

```bash
read -s FTP_PASS
export FTP_PASS
MODE=sync scripts/lftp-upload-local-2024-images.sh
unset FTP_PASS
```

Heslo se v tomto postupu nezapise do historie shellu ani do skriptu.

## Kam se co uklada

Lokalni staging:

```text
/private/tmp/fajntabory-uploads-2024-images
```

Vzdaleny cil:

```text
/domains/new.fajntabory.cz/wp-content/uploads/2024
```

Skript nepouziva `--delete`, takze v nove instalaci nemaze existujici soubory.
Pouze nahraje chybejici nebo rozpracovane obrazky z lokalniho stagingu.

Po dokonceni lze lokalni staging smazat:

```bash
MODE=clean-stage scripts/lftp-upload-local-2024-images.sh
```

## Pokud by uploads/2024 na FTP jeste neexistovalo

Skript se po prihlaseni prepne do `/domains/new.fajntabory.cz` a pouziva
relativni vytvareni adresaru:

```text
wp-content
wp-content/uploads
wp-content/uploads/2024
```

To obchazi problem s absolutnim `mkdir /domains/new.fajntabory.cz/...`, ktery
na Wedosu vratil chybu `550 Create directory operation failed`.
