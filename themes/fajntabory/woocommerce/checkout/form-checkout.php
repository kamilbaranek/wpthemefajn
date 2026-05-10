<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
?>
<?php
	wc_print_notices();

	if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
		echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
		return;
	}

	$reservation_token = function_exists( 'fajntabory_get_reservation_token' ) ? fajntabory_get_reservation_token() : '';
	$reservation_order = function_exists( 'fajntabory_get_reservation_order' ) ? fajntabory_get_reservation_order( $reservation_token ) : false;
	$reservation_sent = ! empty( $_GET['reservation'] ) && 'sent' === $_GET['reservation'];
	$reservation_choice = ! empty( $_GET['reservation'] ) && 'choose' === $_GET['reservation'];
	$form = function_exists( 'fajntabory_get_checkout_form_type' ) ? fajntabory_get_checkout_form_type( $reservation_order ) : null;
	$coupon_code = function_exists( 'fajntabory_get_checkout_coupon_code' ) ? fajntabory_get_checkout_coupon_code( $reservation_order ) : '';
	$recaptcha_site_key = function_exists( 'fajntabory_get_recaptcha_site_key' ) ? fajntabory_get_recaptcha_site_key() : '';

	if ( $reservation_sent ) {
		?>
		<section class="reservation-sent" role="status" aria-live="polite">
			<div class="reservation-sent__icon" aria-hidden="true">&#10003;</div>
			<div class="reservation-sent__content">
				<p class="reservation-sent__eyebrow">Rezervace je vytvořená</p>
				<h2>Teď zkontrolujte svůj e-mail</h2>
				<p>Poslali jsme vám odkaz pro dokončení přihlášky. Otevřete e-mail a klikněte na tlačítko v něm.</p>
				<p class="reservation-sent__hint">Pokud zprávu nevidíte během pár minut, zkontrolujte také spam nebo hromadnou poštu.</p>
			</div>
		</section>
		<?php
		return;
	}

	if ( $reservation_choice && ( empty( $reservation_token ) || ! $reservation_order ) ) {
		echo '<div class="woocommerce-error">Rezervaci se nepodařilo najít. Zkuste prosím vytvořit objednávku znovu.</div>';
		return;
	}

	if ( ! empty( $reservation_token ) && ! $reservation_order ) {
		echo '<div class="woocommerce-error">Odkaz pro dokončení přihlášky je neplatný nebo už neexistuje.</div>';
		return;
	}

	if ( $reservation_order && function_exists( 'fajntabory_reservation_is_completed' ) && fajntabory_reservation_is_completed( $reservation_order ) ) {
		echo '<div class="woocommerce-message">Tato přihláška už byla dokončena.</div>';
		return;
	}

	if ( $reservation_choice ) {
		?>
		<section class="reservation-choice">
			<p class="reservation-choice__eyebrow">Předběžná objednávka č. <?php echo esc_html( fajntabory_get_order_id( $reservation_order ) ); ?> je vytvořená</p>
			<h2>Jak chcete přihlášku dokončit?</h2>
			<p class="reservation-choice__lead">Místo držíme. Můžete si nechat poslat odkaz do e-mailu a vrátit se k přihlášce později, nebo zbývající údaje doplnit hned.</p>

			<div class="reservation-choice__options">
				<div class="reservation-choice__option">
					<h3>Dokončit později z e-mailu</h3>
					<p>Pošleme vám odkaz pro doplnění přihlášky. Po odeslání e-mailu uvidíte potvrzení jako doposud.</p>
					<form method="post" action="<?php echo esc_url( fajntabory_get_checkout_url() ); ?>">
						<input type="hidden" name="reservation_step" value="send_link">
						<input type="hidden" name="reservation_token" value="<?php echo esc_attr( $reservation_token ); ?>">
						<?php wp_nonce_field( 'fajntabory_send_reservation_link', 'reservation_action_nonce' ); ?>
						<button type="submit" class="reservation-choice__button reservation-choice__button--secondary">Poslat odkaz e-mailem</button>
					</form>
				</div>

				<div class="reservation-choice__option reservation-choice__option--primary">
					<h3>Doplnit údaje hned</h3>
					<p>Přejdete rovnou na formulář s údaji o účastnících, kontaktech a platbě. Pokud ho zavřete, předběžná objednávka zůstane uložená jako nedokončená.</p>
					<a class="reservation-choice__button reservation-choice__button--primary" href="<?php echo esc_url( fajntabory_get_reservation_complete_url( $reservation_token ) ); ?>">Pokračovat na formulář</a>
				</div>
			</div>
		</section>
		<?php
		return;
	}

	if ( $reservation_order && empty( $reservation_order->get_meta( '_reservation_email_sent_at' ) ) && empty( $reservation_order->get_meta( '_reservation_completion_form_opened_at' ) ) ) {
		update_post_meta( fajntabory_get_order_id( $reservation_order ), '_reservation_completion_form_opened_at', current_time( 'mysql' ) );
		$reservation_order->add_order_note( 'Zákazník otevřel formulář pro okamžité doplnění údajů.' );
	}

	if ( empty( $form ) ) {
		return;
	}

	$form_class = 'objednavka';
	$step = $reservation_order ? 'complete' : 'reserve';

	if ( ! $reservation_order ) {
		$form_class .= ' wpcf7-form';
	}
?>

<form
	name="checkout"
	method="post"
	class="<?php echo esc_attr( $form_class ); ?>"
	action="<?php echo esc_url( fajntabory_get_checkout_url() ); ?>"
	enctype="multipart/form-data"
	data-checkout-step="<?php echo esc_attr( $step ); ?>"
	<?php if ( ! empty( $recaptcha_site_key ) ) : ?>
	data-recaptcha-sitekey="<?php echo esc_attr( $recaptcha_site_key ); ?>"
	<?php endif; ?>
>
	<input type="hidden" name="coupon_code" value="<?php echo esc_attr( $coupon_code ); ?>">

	<?php if ( $reservation_order ) : ?>
		<input type="hidden" name="reservation_step" value="complete">
		<input type="hidden" name="reservation_token" value="<?php echo esc_attr( $reservation_token ); ?>">
		<?php wp_nonce_field( 'fajntabory_complete_reservation', 'reservation_complete_nonce' ); ?>
		<?php get_template_part( 'woocommerce/checkout/formular', $form ); ?>
	<?php else : ?>
		<?php set_query_var( 'fajntabory_checkout_form', $form ); ?>
		<?php get_template_part( 'woocommerce/checkout/formular', 'reservation' ); ?>
	<?php endif; ?>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
