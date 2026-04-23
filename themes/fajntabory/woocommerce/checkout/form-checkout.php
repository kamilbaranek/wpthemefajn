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
	$form = function_exists( 'fajntabory_get_checkout_form_type' ) ? fajntabory_get_checkout_form_type( $reservation_order ) : null;
	$coupon_code = function_exists( 'fajntabory_get_checkout_coupon_code' ) ? fajntabory_get_checkout_coupon_code( $reservation_order ) : '';
	$recaptcha_site_key = function_exists( 'fajntabory_get_recaptcha_site_key' ) ? fajntabory_get_recaptcha_site_key() : '';

	if ( $reservation_sent ) {
		echo '<div class="woocommerce-message">Dokončete přihlášku ve vašem e-mailu.</div>';
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
		<?php get_template_part( 'woocommerce/checkout/formular', $form ); ?>
	<?php else : ?>
		<?php set_query_var( 'fajntabory_checkout_form', $form ); ?>
		<?php get_template_part( 'woocommerce/checkout/formular', 'reservation' ); ?>
	<?php endif; ?>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
