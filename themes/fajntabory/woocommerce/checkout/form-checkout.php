<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
?>
	<?php wc_print_notices(); 


	// do_action( 'woocommerce_before_checkout_form', $checkout );

	if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
		echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
		return;
	}

?>

<form name="checkout" method="post" class="objednavka" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php

	global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $form = null;

    echo '<input type="hidden" name="coupon_code" value="'.$woocommerce->cart->applied_coupons[0].'">';
	
	foreach($items as $item => $values) { 
		$id = $values['product_id'];
		$typ_form = get_field( 'typ_form', $id );

		if( $typ_form == 'A' ) {
			$form = 'A';
		} else if( $typ_form == 'B' && $form != 'A' ) {
			$form = 'B';
		} else if( $typ_form == 'C' && $form != 'A' && $form != 'B' ) {
			$form = 'C';
		} else if( $typ_form == 'D' && $form != 'A' && $form != 'B' && $form != 'C' ) {
			$form = 'D';
		}
	}

	if( $form == 'A' ) {
		get_template_part( 'woocommerce/checkout/formular', 'A' );
	} else if( $form == 'B' ) {
		get_template_part( 'woocommerce/checkout/formular', 'B' );
	} else if( $form == 'C' ) {
		get_template_part( 'woocommerce/checkout/formular', 'C' );
	} else if( $form == 'D' ) {
		get_template_part( 'woocommerce/checkout/formular', 'D' );
	}

	?>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>