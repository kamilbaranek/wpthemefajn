<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
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
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php post_class(); ?>>
	<?php 
		$id = $product->id;
		$permalink = get_permalink( $id );
		$title = get_the_title( $id );
		$excerpt = get_the_excerpt( $id );
		if( get_field( 'percent_boolean', get_the_id() ) ) {
			$price = get_field( 'percent_value' ) . '%';
		} else {
			$price = $product->get_price_html();
		}
	?>
	<a href="<?php echo $permalink; ?>">
	<span class="image"><?php echo get_the_post_thumbnail( $id ); ?></span>
	<span class="summary">
		<h2><?php echo $title; ?></h2>
		<span class="price"><?php echo $price; ?></span>
	</span>
	</a>
	<span class="excerpt"><?php echo $excerpt; ?></span>
	<span class="action">
		<?php
			if( $product->is_type( 'variable' ) ) {
				echo '<a href="'.$permalink.'" class="button">Zobrazit více</a>';
			} else {
				echo '<a href="?add_to_cart='.$id.'" class="button">Přidat do košíku</a>';
			}
		?>
	</span>

</li>
