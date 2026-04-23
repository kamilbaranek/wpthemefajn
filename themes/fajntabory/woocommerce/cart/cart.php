<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();

?> 

<?php $mobile_order_cta_rendered = false; ?>

<?php 
do_action( 'woocommerce_before_cart' ); ?>

<?php
	$count = 0;
	foreach( WC()->cart->get_cart() as $cart_item ) {
		$item_id = $cart_item['product_id'];
		$item_terms = get_the_terms( $item_id, 'product_cat' );
		$item_category = $item_terms[0]->term_id;
		if( $item_category == 20 ) {
			$count++;
		}
	}
	if( $count > 1 ) {
?>
<p class="sorry jako">V jedné přihlášce lze objednat pouze jeden tábor (jeden termín). Máte-li zájem o dva nebo více termínů, je zapotřebí vyplnit na každý termín přihlášku zvlášť. Pokud Váš košík obsahuje více než jeden tábor, klikněte na tlačítko Zrušit tak, aby v košíku zůstal pouze jeden, který si nyní přejete objednat. Toto neplatí u doplňků, jako jsou: Společná doprava, Táborové tričko apod.</p>
<?php
	}
?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
		<thead>
			<tr>
				<th class="product-thumbnail">&nbsp;</th>
				<th class="product-name"><?php _e( 'Product', 'woocommerce' ); ?></th>
				<th class="product-price"><?php _e( 'Price', 'woocommerce' ); ?></th>
				<!--<th class="product-quantity"><?php _e( 'Quantity', 'woocommerce' ); ?></th>-->
				<!--<th class="product-subtotal"><?php _e( 'Total', 'woocommerce' ); ?></th>-->
				<th class="product-remove">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<td class="product-thumbnail">
							<?php
								$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

								if ( ! $product_permalink ) {
									echo $thumbnail;
								} else {
									printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
								}
							?>
						</td>

						<td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
							<?php
								if ( ! $product_permalink ) {
									echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;';
								} else {
									echo apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key );
								}

								// Meta data
								echo WC()->cart->get_item_data( $cart_item );

									$attributes = $_product->attributes;
									
									if( $attributes) {
										echo '<ul class="attributes">';
										foreach ($attributes as $key => $value) {

											$key_title = null;
											$value_title = null;

											$key_title = get_taxonomy( $key );
											$key_title = $key_title->labels->singular_name;

											$value_title = get_term_by('slug', $value, $key );
											$value_title = $value_title->name;

											echo '<li>' . $key_title . ': <strong>' . $value_title . '</strong></li>';
										}
										echo '</ul>';
									}

								// Backorder notification
								if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
									echo '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>';
								}
							?>
						</td>

						<td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
							?>
						</td>

						<!--<td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
							<?php
								if ( $_product->is_sold_individually() ) {
									$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
								} else {
									$product_quantity = woocommerce_quantity_input( array(
										'input_name'  => "cart[{$cart_item_key}][qty]",
										'input_value' => $cart_item['quantity'],
										'max_value'   => $_product->backorders_allowed() ? '' : $_product->get_stock_quantity(),
										'min_value'   => '0',
									), $_product, false );
								}

								echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
							?>
						</td>-->

						<!--<td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
							<?php
								echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
							?>
						</td>-->

						<td class="product-remove">
							<?php
								$remove_link = sprintf(
									'<a href="%s" class="cart-remove-link" aria-label="%s" data-product_id="%s" data-product_sku="%s">Zrušit</a>',
									esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
									esc_attr__( 'Zrušit položku', 'fajntabory' ),
									esc_attr( $product_id ),
									esc_attr( $_product->get_sku() )
								);

								$remove_link = apply_filters( 'woocommerce_cart_item_remove_link', $remove_link, $cart_item_key );

								// Show the mobile checkout CTA only once, next to the first remove action.
								if ( ! $mobile_order_cta_rendered ) {
									$remove_link .= sprintf(
										'<a href="%s" class="checkout-button button alt wc-forward cart-mobile-order">OBJEDNAT</a>',
										esc_url( fajntabory_get_checkout_url() )
									);
									$mobile_order_cta_rendered = true;
								}

								echo $remove_link;
							?>
						</td>
					</tr>
					<?php
				}
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<tr>
				<td colspan="6" class="actions">

					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon">
							<label for="coupon_code"><?php _e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <input type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>" />
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<input type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>" />

					<?php do_action( 'woocommerce_cart_actions' ); ?>

					<?php wp_nonce_field( 'woocommerce-cart' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<div class="cart-collaterals" id="anchor-cupon">
	<?php do_action( 'woocommerce_cart_collaterals' ); ?>

</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
