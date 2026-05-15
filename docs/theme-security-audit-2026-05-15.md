# Bezpečnostní audit šablony `fajntabory` — 2026-05-15

Audit a hardening WordPress šablony `themes/fajntabory/` (jediná nasazená
šablona webu fajntabory.cz). Navazuje na malware incident z května 2026
(viz `production-malware-analysis-2026-05-11.md`).

## Shrnutí

Statická analýza PHP, JS a šablon **nenašla žádný malware ani obfuskovaný
kód** — žádné `eval`, `base64_decode`, `gzinflate`, dynamické `include`.
Nalezená rizika jsou klasické chyby aplikační bezpečnosti soustředěné
v admin modulu fakturace a v importu CSV: chybějící CSRF ochrana,
neescapovaný výstup (XSS) a neověřený upload souborů. Všechna níže uvedená
zjištění byla v tomto auditu opravena.

Produkce běží WooCommerce 10.6.2; 8 WooCommerce template overrides v šabloně
bylo 5–7 let zastaralých a bylo aktualizováno.

## Nálezy a opravy

| # | Závažnost | Nález | Lokace | Stav |
|---|-----------|-------|--------|------|
| 1 | Vysoká | CSRF — smazání faktury přes GET bez nonce | `functions/fakturace.php` | Opraveno |
| 2 | Vysoká | CSRF — vytvoření/úprava faktury bez nonce; `foreach($_POST)` bez sanitizace | `functions/fakturace.php` | Opraveno |
| 3 | Vysoká | Reflected XSS — `$_GET['search']` vypsán do `value=""` bez escapování | `functions/fakturace.php` | Opraveno |
| 4 | Vysoká | Stored XSS — data objednávek a nastavení vypsaná do HTML bez escapování | `functions.php`, `front-page.php` | Opraveno |
| 5 | Střední | CSV import bez nonce, bez kontroly přípony/MIME a chyb uploadu | `functions.php` | Opraveno |
| 6 | Střední | `$_GET` parametry filtru/stránkování necastované / bez whitelistu | `functions/fakturace.php` | Opraveno |
| 7 | Nízká | Neescapovaný výstup v šabloně titulní stránky | `front-page.php` | Opraveno |
| 8 | Nízká | Rozbitá admin URL exportu dopravy (zdvojený odkaz) | `functions.php` | Opraveno |
| 9 | Info | 8 WooCommerce template overrides zastaralých (WC 2.3–3.5) | `woocommerce/` | Aktualizováno na WC 10.6 |

### 1–3, 6 — Modul fakturace (`functions/fakturace.php`)

- Mazání faktury (`action=delete`) nyní vyžaduje per-fakturu nonce
  (`wp_nonce_url` v odkazu + `wp_verify_nonce` v handleru) a `current_user_can('manage_options')`;
  `$_GET['id']` přes `absint()`.
- Formuláře vytvoření i úpravy faktury obsahují `wp_nonce_field('fajntabory_save_invoice')`;
  oba handlery ověřují přes `check_admin_referer()` + kontrolu oprávnění.
- Nová pomocná funkce `fajntabory_sanitize_invoice_post()` sanitizuje všechny
  POST hodnoty (`sanitize_text_field`, `sanitize_key`), vynechává nonce/systémové
  klíče — nahrazuje původní `foreach ($_POST) update_post_meta()`.
- Vyhledávací parametry: `search` → `sanitize_text_field`, `filter` validován
  proti whitelistu `$filtry`, `dodavatel`/`paged` → `absint`. Hodnota
  vyhledávání vypsaná zpět do pole je `esc_attr()`. Stránkovací odkazy `esc_url()`.

### 4, 7 — Escapování výstupu (XSS)

- `functions.php`: pole nastavení šablony (`get_option` pro bankovní účet,
  odkazy na sociální sítě, logo) escapována `esc_attr()`/`esc_url()`.
  Exportní tabulka účastníků (jméno, datum narození, adresa, škola, zdravotní
  poznámky, kontakty zákonného zástupce, kupón) — všechny buňky `esc_html()`.
  Pole cen variací `esc_attr()`.
- `front-page.php`: názvy termů taxonomií, datum, formát příspěvku, barvy
  ACF v `style=""` a `class=""` escapovány `esc_html()`/`esc_attr()`,
  URL (`permalink`, ACF odkazy, obrázky) `esc_url()`.

### 5 — Import CSV (`functions.php`)

- Oba importní formuláře (`csv` = tábory, `tcsv` = doprava) mají `wp_nonce_field`;
  handlery ověřují nonce **před** `move_uploaded_file`.
- Nová pomocná funkce `fajntabory_is_valid_csv_upload()` ověří existenci
  `$_FILES`, `UPLOAD_ERR_OK`, `is_uploaded_file()` a příponu `.csv`
  (`wp_check_filetype`). Neplatný upload skončí přesměrováním `uploaded=false`.

### 8 — Drobnost

- Opravena rozbitá admin URL exportu společné dopravy
  (`functions.php` — obsahovala zdvojený absolutní odkaz, pravděpodobně
  chyba kopírování, nikoli malware).

## Ověřené false-positives (ponecháno beze změny)

- **AJAX `pchose` / `dchose`** (`functions.php`) — `wp_verify_nonce` je
  přítomen; `wp_send_json()` interně volá `wp_die()`, takže běh po chybné
  nonce korektně končí.
- **`fajntabory_decode_choice_ids()`** (`functions.php`) — `unserialize`
  s `allowed_classes => false`, primárně se používá `json_decode`. Bezpečné.
- **Rezervační / checkout flow** (`fajntabory_create_reservation` aj.) —
  nonce, reCAPTCHA i honeypot již zavedené.
- **Sledovací kódy** (GA, Facebook Pixel v `header.php`/`footer.php`) —
  obaleny `if(false)`, tj. záměrně vypnuté; ID jsou veřejná.

## WooCommerce template overrides — aktualizace na WC 10.6

| Soubor | Z verze | Na verzi | Způsob |
|--------|---------|----------|--------|
| `woocommerce/content-product.php` | 3.0.0 | 9.4.0 | plná náhrada (verbatim) |
| `woocommerce/checkout/review-order.php` | 2.3.0 | 5.2.0 | plná náhrada (verbatim) |
| `woocommerce/emails/email-order-items.php` | 3.5.0 | 10.7.0 | plná náhrada (verbatim) |
| `woocommerce/cart/cross-sells.php` | 3.0.0 | 9.6.0 | rebase + český nadpis + `content-crossell` |
| `woocommerce/cart/proceed-to-checkout-button.php` | 2.4.0 | 7.0.1 | rebase + tlačítko OBJEDNAT + kupón |
| `woocommerce/cart/cart.php` | 3.0.3 | 10.1.0 | verze sladěna; escapování vlastního výpisu atributů; vlastní struktura zachována |
| `woocommerce/emails/customer-completed-order.php` | 2.5.0 | 10.4.0 | rebase; `$order->id` → `$order->get_order_number()` |
| `woocommerce/emails/customer-on-hold-order.php` | 2.5.0 | 10.4.0 | rebase; zachovány platební pokyny |

Bespoke soubory (`form-checkout.php`, `formular-*.php`, `single-product.php`,
`content-crossell.php`) nejsou standardní WC overrides — neaktualizují se.

## Reziduální / odložené položky

- **Front-end knihovny** (jQuery UI 1.11.3, Font Awesome 4.7.0, bxSlider 4.2.7,
  jquery.mask 1.14.8) jsou zastaralé. Upgrade vyžaduje plný browser retest
  sliderů/datepickerů/masek — odloženo jako samostatný úkol.
- **Hodnoty z importovaného CSV** (ceny) se ukládají bez normalizace
  `wc_format_decimal`. Riziko je nízké (admin-only vstup, výstupní místa jsou
  escapována); změna měnové logiky odložena, vyžaduje test s reálnými CSV.
- ACF textová pole na titulní stránce (titulky/popisky) se vypisují bez
  escapování — záměrně, jde o editorský obsah; riziko vyžaduje admin přístup.

## Ověření

1. `php -l` na všech upravených souborech — bez chyb.
2. WooCommerce → Status → System status → sekce „Templates" nesmí hlásit
   outdated overrides.
3. Fakturace: vytvořit / upravit / smazat fakturu; smazání bez platné nonce
   musí skončit chybou; vyhledávání s `<script>` se vypíše escapované.
4. Import CSV: validní `.csv` projde, jiná přípona je odmítnuta, upload bez
   nonce selže.
5. Košík / checkout / e-maily objednávky (completed, on-hold) — vizuální
   i funkční kontrola.
6. Testovat na staging (`scripts/deploy-theme-staging.sh`), až poté produkce.
