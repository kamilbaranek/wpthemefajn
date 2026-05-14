# Import ocisteneho obsahu + designu do ciste WP instalace

Datum: 2026-05-12

## Co tento postup resi

Mate cistou WordPress instalaci (bez pluginu), bezici theme `fajntabory` z repozitare. Z napadene produkce chcete prenest:

- **Obsah** (posty, stranky, produkty, varianty, objednavky, kategorie, komentare, attachmenty)
- **Design** (theme customizer, logo, barvy, widgety, prirazeni menu, homepage konfigurace)
- **Nastaveni pluginu** (WooCommerce, Yoast SEO, Contact Form 7, GTM Kit, Cookie Notice, Smartsupp, ACF)

Ale **NE**:

- Produkcni uzivatele (vas fresh admin musi prezit)
- `siteurl`, `home`, `admin_email`, `db_version`, `active_plugins` (fresh install si je drzi)
- Wordfence forenzni data (plugin nainstalujete cisty)
- Stale sessions, API keys, payment tokens, cron entries

## Generovani migrace

Lokalni cleanup pipeline produkuje **tri** soubory v `DB/`:

| Soubor | Velikost | Obsah |
|---|---|---|
| `wm144_wedos_net.migration.sql` | ~136 MB | Obsah (wp_posts, wp_postmeta, wp_comments, wp_terms, WC order_items, …); BEZ wp_options, wp_users, Wordfence, Yoast, ActionScheduler, sessions |
| `wm144_wedos_net.migration-design.sql` | ~100 KB | Design overlay: 350+ INSERT INTO wp_options pro theme_mods, widgety, WC nastaveni, Yoast, plugin settings |
| `wm144_wedos_net.migration-audit.log` | ~35 KB | Audit log z generovani |

Spousteni:

```bash
MIGRATION_READY=1 \
FRESH_ADMIN_ID=1 \
DUMP_INPUT=/Users/kamilbaranek/dev/fajntabory/DB/wm144_wedos_net.sql \
scripts/db-cleanup-local-restore.sh
```

`FRESH_ADMIN_ID` je user ID admin uctu v ciste instalaci (default `1`). Po importu vsechny posty/produkty/objednavky budou autoremi nove admina.

## Co `migration.sql` obsahuje (content)

**Obsahove tabulky (zachovane z produkce, ocistene od malware):**

- `wp_posts` (38 045 zaznamu): clanky, stranky, produkty (22 publish + 34 draft), varianty (525), objednavky (542 completed + 82 pending + ostatni), faktury (377), vedouci (420), galerie (568), attachmenty (35 253), shop coupons (107), ACF field groups (53), nav menu items (15)
- `wp_postmeta` — vsechna meta data
- `wp_comments` (760) + `wp_commentmeta` — vsichni `user_id = 0` (anonymizovano)
- `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`, `wp_termmeta`
- `wp_links`
- `wp_woocommerce_order_items` + `wp_woocommerce_order_itemmeta`
- `wp_woocommerce_tax_rates`, `wp_woocommerce_tax_rate_locations`
- `wp_woocommerce_shipping_zones`, `wp_woocommerce_shipping_zone_locations`, `wp_woocommerce_shipping_zone_methods`
- `wp_woocommerce_attribute_taxonomies`
- `wp_woocommerce_downloadable_product_permissions`

## Co `migration-design.sql` obsahuje (design overlay)

INSERT statementy pro nasledujici klice z `wp_options`:

- `theme_mods_*` — theme customizer (logo, barvy, prirazeni menu k locations, header image)
- `current_theme`, `template`, `stylesheet`, `custom_logo`, `site_icon`
- `widget_*` + `sidebars_widgets` — widgety v sidebars
- `nav_menu_options` — global menu options
- `show_on_front`, `page_on_front`, `page_for_posts` — **homepage / blog konfigurace**
- `permalink_structure`, `category_base`, `tag_base` — URL struktura
- `thumbnail_*`, `medium_*`, `large_*`, `image_default_*` — media velikosti
- `date_format`, `time_format`, `timezone_string`, `start_of_week`, `WPLANG`
- `default_category`, `default_post_format`, `default_ping_status`, `default_comment_status`
- `posts_per_page`, `posts_per_rss`, `comment_*`, `moderation_*`
- `woocommerce_*` — currency, country, tax, shipping, payment, checkout, email settings (~150 klicu)
- `wpseo*` — Yoast SEO global, titles, social, tools
- `cookie_notice_options`
- `gtm-kit_*`, `wpcf7*`, `cf7_*`, `cfdb7_*`
- `rocket_lazy_load_*`, `wp_super_cache_*`, `wpsc_*`
- `smartsupp_*`
- `acf_*`
- `fajntabory_*` (pokud nejake theme custom keys)
- `redirection_*`, `wp_media_categories_*`, `wp_sort_order_*`
- `facebook_for_woocommerce_*`, `fb_woocommerce_*`, `fbe_*`
- `prettyphoto_*`

Overlay zacina `DELETE FROM wp_options WHERE <whitelist>` aby pri opakovanem importu nedoslo k duplicitam, pak nasleduje `INSERT INTO wp_options` ze stejneho whitelist setu.

**Co overlay NEOBSAHUJE** (fresh install si zachova svoje):

- `siteurl`, `home`, `admin_email`
- `db_version`, `db_upgraded`, `initial_db_version`, `fresh_site`
- `blogname`, `blogdescription`
- `users_can_register`, `default_role`
- `active_plugins`, `recently_activated`
- `cron`, transients
- `wordfence_*`, `wf_*`, `wfls_*` (plugin neni nainstalovany)
- `fs_*` (Freemius)
- `mwp_*`, `_mwp_*` (ManageWP)
- `action_scheduler_*`, `schema-ActionScheduler*`
- `recently_edited`
- `acf_site_health`

## Import postup — varianty

Vyberte podle dostupnosti:

- **CLI/SSH** — `mariadb < soubor.sql` (nejrychlejsi, ~30 sekund)
- **phpMyAdmin** — viz nize, soubory jsou jiz gzipnute (`.sql.gz`)
- **Pri hostingu s velmi pristnym upload limitem** — fallback: per-table split

---

## Import varianta A — CLI / SSH

### Krok A1 — Zaloha ciste instalace

```bash
mysqldump -u <user> -p <fresh_db> > fresh-install-backup-$(date +%F).sql
```

### Krok A2 — Pripravit cistou instalaci

Cista WordPress instalace **NESMI** mit aktivovane pluginy. Pokud uz aktivovane jsou (krome theme), deaktivujte je:

```bash
wp plugin deactivate --all
```

Theme `fajntabory` muze byt aktivni — neukolibuje obsah.

### Krok A3 — Import content (`migration.sql`)

```bash
mariadb -u <user> -p <fresh_db> < DB/wm144_wedos_net.migration.sql
```

Neobsahuje `CREATE DATABASE` ani `USE` — je tedy bezpecne importovat do libovolne pojmenovane DB.

Import udela `DROP TABLE IF EXISTS` + `CREATE TABLE` + `INSERT INTO` pro obsahove tabulky. To znamena, ze **defaultni Hello World post, Sample Page, vychozi kategorie atd. budou prepsane** produkcnim obsahem. To je pozadovany stav.

Po tomto kroku **fresh `wp_options` zustava nedotcena** — tj. siteurl, home, admin, db_version, salts atd. jsou pravidla z ciste instalace.

### Krok A4 — Import design overlay (`migration-design.sql`)

```bash
mariadb -u <user> -p <fresh_db> < DB/wm144_wedos_net.migration-design.sql
```

Tento skript:

1. `DELETE FROM wp_options WHERE <design whitelist>` — odstrani fresh default hodnoty pro design klice
2. `INSERT INTO wp_options ...` — vlozi produkcni hodnoty pro tytez klice
3. `siteurl`, `home`, `admin_email`, `db_version`, `active_plugins`, salts atd. **nejsou v whitelist**, takze zustanou z ciste instalace

Po tomto kroku je homepage nastavena na produkcni `page_on_front`, widgety jsou na svych mistech, theme customizer ma logo a barvy z produkce.

---

## Import varianta B — phpMyAdmin

Pripravene soubory v `DB/`:

| Soubor | Komprimovany |
|---|---|
| `wm144_wedos_net.migration.sql` (136 MB) | **`wm144_wedos_net.migration.sql.gz` (7.8 MB)** |
| `wm144_wedos_net.migration-design.sql` (99 KB) | **`wm144_wedos_net.migration-design.sql.gz` (19 KB)** |

phpMyAdmin umi automaticky dekomprimovat `.gz` pri importu — uploadujte rovnou gzipnute.

### Krok B1 — Zaloha ciste DB

V phpMyAdmin vyberte cilovou DB v levem panelu → tab **Export** → format SQL → `Go` → ulozte soubor (`fresh-install-backup-YYYY-MM-DD.sql`).

### Krok B2 — Deaktivovat pluginy (volitelne, doporucene)

V WP admin: Plugins → vsechny → Bulk actions: Deactivate → Apply.

(Nepotreba aktivni Worker/wc-speed-drain-repair/wp-file-manager — ty se nainstaluji uplne novy v Kroku 5.)

### Krok B3 — Overit upload limit v phpMyAdmin

phpMyAdmin → tab **Import** → vis "Max: X MiB". Potreba aby > 7.8 MB.

Pokud limit < 8 MB:
- Volba 1: pres SFTP/FTP nahrat `.sql.gz` do `phpmyadmin/upload/` (nebo `phpmyadmin/save/` podle konfigurace) — pak je vybiratelne z dropdownu **"Or select from the web server upload directory"** v Import tabu.
- Volba 2: fallback na variant C nize (per-table split).

### Krok B4 — Import content (`wm144_wedos_net.migration.sql.gz`)

1. phpMyAdmin → leva navigace → klik na cilovou DB
2. Pred prvnim importem **smazat default tabulky** (volitelne): tab **Structure** → vybrat vsechny tabulky checkboxem → Bulk actions: **Drop** → Yes
   - Tento krok neni nutny — migration.sql obsahuje `DROP TABLE IF EXISTS` pro vsechny content tabulky.
3. Tab **Import**
4. **Choose file**: `wm144_wedos_net.migration.sql.gz`
5. **Character set of the file**: `utf-8`
6. **Format**: `SQL` (auto-detect)
7. **Partial import**: zaskrtnout *Allow the interruption of an import in case the script detects it is close to the PHP timeout limit*
8. **Other options**: nechte default (SQL compatibility = NONE, do not use AUTO_INCREMENT = unchecked)
9. Klik na **Go**
10. Pockejte — pro 38 045 postu + ~250 000 postmeta to muze trvat 2-10 minut
11. Pokud "Reached the script execution time limit" hlaska — kliknete na **Continue** (phpMyAdmin si pamatuje offset)

Po dokonceni: phpMyAdmin zobrazi `Import has been successfully finished, N queries executed.`

### Krok B5 — Import design overlay (`wm144_wedos_net.migration-design.sql.gz`)

Stejny proces jako Krok B4, ale soubor `wm144_wedos_net.migration-design.sql.gz`. Soubor je male (19 KB), import zabere par sekund.

**Co se stane**:
1. `DELETE FROM wp_options WHERE <design whitelist>` — odstrani fresh defaultni hodnoty pro design klice
2. `INSERT INTO wp_options ...` — vlozi produkcni hodnoty
3. `siteurl`, `home`, `admin_email`, `db_version`, `active_plugins` zustanou nedotcene

### Krok B6 — Verifikace v phpMyAdmin

- DB → tabulka `wp_options` → tab **Browse** → vyhledat `option_name = 'siteurl'` → musi byt **vasa nova URL**, ne produkcni
- DB → tabulka `wp_options` → vyhledat `option_name = 'theme_mods_fajntabory'` → musi mit dlouhy serialized PHP string s nastavenim customizeru
- DB → tabulka `wp_posts` → tab **Browse** → ocekavany pocet rows ~38000
- DB → tabulka `wp_postmeta` → ocekavany pocet rows ~250000

---

## Import varianta C — phpMyAdmin s pristnym upload limitem (< 8 MB)

Pokud i 7.8 MB gzip je moc, pouzite per-table split:

```bash
scripts/db-split-migration-by-table.sh \
  /Users/kamilbaranek/dev/fajntabory/DB/wm144_wedos_net.migration.sql
```

Vystup v `DB/migration-chunks/`:

```
01-schema.sql.gz                  ~30 KB    -- CREATE TABLE pro vsechny tabulky
02-data-wp_posts.sql.gz           ~3 MB     -- INSERT INTO wp_posts
03-data-wp_postmeta.sql.gz        ~3 MB     -- INSERT INTO wp_postmeta
04-data-other.sql.gz              ~1 MB     -- ostatni tabulky
05-triggers-routines.sql.gz       ~1 KB     -- (pokud nejake)
```

Kazdy chunk je samostatne importovatelny. Doporucene poradi:

1. `01-schema.sql.gz` — vytvori prazdne tabulky (DROP IF EXISTS + CREATE TABLE)
2. `02-data-wp_posts.sql.gz` — naplni posty
3. `03-data-wp_postmeta.sql.gz` — postmeta
4. `04-data-other.sql.gz` — komentare, kategorie, WC order_items atd.
5. `05-triggers-routines.sql.gz` — pokud existuje (default neexistuji)
6. `wm144_wedos_net.migration-design.sql.gz` — design overlay

Krok pro phpMyAdmin shodny s variantou B, jen postupne pro kazdy chunk.

---

## Post-import kroky (po vsech variantach A/B/C)

### Krok 5 — Aktivovat pluginy

**WP admin UI**: Plugins → Add New → vyhledat → Install → Activate. Postupne tyto:

| Plugin | Slug | Poznamka |
|---|---|---|
| WooCommerce | `woocommerce` | Nactena WC nastaveni z design overlay |
| WooCommerce Legacy REST API | `woocommerce-legacy-rest-api` | Pokud potrebujete |
| Yoast SEO | `wordpress-seo` | Po aktivaci viz Krok 6C |
| ACF Content Analysis for Yoast SEO | `acf-content-analysis-for-yoast-seo` | |
| Advanced Custom Fields | `advanced-custom-fields` | ACF field groups jsou v `wp_posts` |
| Contact Form 7 | `contact-form-7` | |
| Contact Form CFDB7 | `contact-form-cfdb7` | |
| Cookie Notice | `cookie-notice` | |
| GTM Kit | `gtm-kit` | |
| Smartsupp Live Chat | `smartsupp-live-chat` | |
| Rocket Lazy Load | `rocket-lazy-load` | |
| WP Super Cache | `wp-super-cache` | |
| Pretty Photo | `prettyphoto` | |
| WP Media Categories | `wp-media-categories` | |
| WP Sort Order | `wp-sort-order` | |
| Facebook for WooCommerce | `facebook-for-woocommerce` | |
| Wordfence | `wordfence` | Cista instalace |

**NEINSTALUJTE:**

- `worker` (ManageWP) — externi sprava, musi byt nove autorizovano
- `wc-speed-drain-repair` — neoverena utilita
- `wp-file-manager` — vysoke riziko, ne nutny

Pri prvni aktivaci kazdy plugin vytvori svoje vlastni DB tabulky (wp_actionscheduler_*, wp_wf*, wp_yoast_*, wp_woocommerce_sessions, atd.) prazdne. WooCommerce + Yoast nactou nastaveni z wp_options ktere jsme jiz importovali v design overlay.

### Krok 6 — Normalizace

#### A) Permalinks regenerate

- **wp-cli**: `wp rewrite flush`
- **WP admin UI**: Settings → Permalinks → kliknout **Save Changes** (rewrite rules se regeneruji)

#### B) WooCommerce lookup tabulky

- **wp-cli**: `wp wc tool run regenerate_product_lookup_table_data --user=admin`
- **WP admin UI**: WooCommerce → Status → Tools → najit **"Regenerate the product lookup tables"** + **"Verify base database tables"** → Run

#### C) Yoast SEO reindex

- **wp-cli**: `wp yoast index --reindex --batch-size=500`
- **WP admin UI**: SEO → Tools → najit **"SEO data optimization"** → Start (regeneruje wp_yoast_indexable, wp_yoast_seo_meta z wp_postmeta)

#### D) Thumbnail regenerate (volitelne, pokud uploads jsou na disku)

- **wp-cli**: `wp media regenerate --yes`
- **WP admin UI**: nutny plugin "Regenerate Thumbnails" (`regenerate-thumbnails`)

#### E) Pridat zpet tymove uzivatele s NOVYMI hesly

**phpMyAdmin volba**: nelze rozumne udelat (heslovy hash). Pouzijte WP admin.

**WP admin UI**: Users → Add New → vytvořit nove ucty pro tymove cleny s **novymi hesly** (puvodni byla pravdepodobne odcizena pri napadeni):

- `fajntabory` (info@fajntabory.cz) — role: Administrator
- `silva.gloserova` (silva@fajntabory.cz) — role: Editor
- `veronika.poukova` (veronika.poukova.ftlw@gmail.com) — role: Editor
- `Sandra.Korsusova` (korsusova.sandra@seznam.cz) — role: Editor
- atd.

Hesla generujte nahodne a sdelte uzivatelum out-of-band (Signal, telefon, ne email).

#### F) Volitelne — autorstvi konkretnich postu (napr. Silva ma blogove clanky)

**phpMyAdmin**: tab SQL → vyplnit a Go:
```sql
UPDATE wp_posts SET post_author = <silva_id_nova> WHERE ID IN (123, 456, ...);
```

**wp-cli**: `wp post update <ID1> <ID2> --post_author=<silva_id>`

### Krok 7 — Search-replace pri zmene domeny

Pokud cista instalace bezi na jine domene nez produkcni `https://www.fajntabory.cz`:

**wp-cli**:
```bash
wp search-replace 'https://www.fajntabory.cz' 'https://novy.domain.cz' --dry-run
wp search-replace 'http://www.fajntabory.cz' 'http://novy.domain.cz' --dry-run
# pak bez --dry-run
```

**phpMyAdmin** (nelze rozumne na serializovanych datech) → doporucujem instalovat plugin **"Better Search Replace"** v WP admin:

1. Plugins → Add New → Better Search Replace → Install + Activate
2. Tools → Better Search Replace
3. Search for: `https://www.fajntabory.cz`
4. Replace with: `https://novy.domain.cz`
5. Select tables: vse vybrat
6. **"Run as dry run?"** zaskrtnout → Run Search/Replace → zkontrolovat odhad
7. Odznacit dry run → Run znova
8. Opakovat pro `http://www.fajntabory.cz` (i bez https)
9. Po dokonceni: deactivate + delete Better Search Replace plugin

Plugin zvlada serializovane PHP (theme_mods, widgets) bezpecne — to je dulezite, protoze prosty `UPDATE ... SET ... = REPLACE(...)` v SQL by serializaci rozbil.

## Co po importu zkontrolovat

1. **Frontend** — homepage, blog, produktove stranky, kontakty (page `/kontakty/`), galerie, vedouci
2. **Theme customizer** (admin → Vzhled → Pristava) — logo, barvy, sekce z produkce
3. **Widgety** (admin → Vzhled → Widgety) — produkcni widgety v sidebars
4. **Menu** (admin → Vzhled → Menu) — menu items prirazene k theme locations
5. **WooCommerce → Objednavky** — 542 dokoncenych + 82 pending
6. **WooCommerce → Produkty** — 22 publish + 34 draft
7. **WooCommerce → Settings** — currency CZK, danove sazby, dopravni zony
8. **Yoast SEO** (po reindexu) — meta titles, descriptions
9. **Posts → Categories / Tags**
10. **Settings → Reading** — homepage je static page, page_on_front a page_for_posts spravne
11. **Settings → Permalinks** — custom struktura zachovana
12. **Comments** — vsechny `user_id = 0` ale author name/email zachovany

## Caveaty

- **post_author = 1**: vsechny posty maji autora ID 1 (vas fresh admin). Pro zachovani autorstvi konkretnich autoru (Silva pro blogove clanky) provedete updatech v Kroku 6F.

- **180 postu ma `post_author = 0`** — attachmenty bez puvodniho autora, auto-drafty, custom_css, oembed_cache atd. Bez problemu.

- **Komentare anonymizovane** (`user_id = 0`). Author name / email zustavaji v `comment_author` a `comment_author_email`.

- **WC Analytika** se musi regenerovat (Krok 6B). Historicka data zustanou.

- **Yoast SEO** se znovu indexuje (Krok 6C). Focus keyphrase a obsahove SEO meta ulozena v `wp_yoast_seo_meta` jsme dropli — pokud byly dulezite, lze je pred dropem zalohovat samostatne z `wm144_wedos_net.cleaned.sql`.

- **WordFence forensic data** se neimportuje. Pokud potrebujete dukazni materialy z napadeni, mate je v `wm144_wedos_net.cleaned.sql` (plny dump).

- **Theme customizer (`theme_mods_fajntabory`)** muze mit reference na konkretni IDs (logo attachment, header image, menu items). Tyto ID musi v `wp_posts` existovat — `migration.sql` je tam dava.

- **Widget Reference IDs**: widgety jako "Recent Posts" nebo "Custom Menu" muzou odkazovat na konkretni post / menu IDs. Pokud nektery konkretni post byl smazany behem cleanup (stage 3 spam posts), widget zobrazi prazdny vystup — WordPress to zvlada gracefully.

## Rollback

Pokud import skonci spatne:

```bash
# Restore fresh install ze zalohy Kroku 1:
mariadb -u <user> -p <fresh_db> < fresh-install-backup-2026-05-12.sql
```

Migration soubory jsou idempotentni — lze opakovane importovat:
- `migration.sql` udela DROP+CREATE+INSERT (cisty stav obsahovych tabulek).
- `migration-design.sql` zacina DELETE pres design whitelist a pak INSERT — vzdy synchronizuje na produkcni stav design klicu.

## Generovani prazdneho dumpu pro alternativni server / domain

Pokud chcete migration dump pro jine cilove DB nazev nebo jiny pripad:

```bash
# Vlastni nazev cilove DB / cesta vystupu:
DUMP_INPUT=DB/wm144_wedos_net.sql \
DUMP_OUTPUT=DB/staging.migration.sql \
MIGRATION_READY=1 \
FRESH_ADMIN_ID=2 \
scripts/db-cleanup-local-restore.sh
```

Vystup pak `DB/staging.migration.sql` + `DB/staging.migration-design.sql`.
