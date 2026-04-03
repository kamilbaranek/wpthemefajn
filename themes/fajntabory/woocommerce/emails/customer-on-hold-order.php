<?php
/**
 * Customer on-hold order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-on-hold-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>potvrzujeme přijetí objednávky. Pokud jste vybrali možnost platby převodem, řiďte se prosím pokyny níže. Jakmile platbu zaevidujeme, zašleme Vám potvrzení o jejím přijetí. V případě možnosti proplacení Vaším zaměstnavatelem Vám zašleme fakturu na základě vyplněných fakturačních údajů.</p>


<p><strong>Platební pokyny</strong><br/>
Číslo účtu:	2700868985/2010<br/>
Variabilní symbol:	číslo objednávky<br/>
Datum splatnosti:	týden od odeslání objednávky</p>




<p>Po neobdržení platby do výše uvedeného data splatnosti, bude Vaše objednávka stornována v domnění, že jste již o ni ztratili zájem.<br/>
V případě opakovaného zájmu bude zapotřebí vyplnit objednávku znovu. Upozorňujeme, že cena již nemusí být stejná.</p>

<p><strong>Těšíme se na Vaši účast! :)</strong></p>

<p>Fajn Tábory, z.s.</p>

<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
