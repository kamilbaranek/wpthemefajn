<?php
/**
 * Template Name: Doplňky
 */
	get_header();
	while ( have_posts() ) : the_post();
	echo '<div id="content">';
		the_title( '<h2 class="entry-title">', '</h2>', true );
	echo '<div id="main">';

		$taxarray = array();

		$taxarray[] = array(
			'taxonomy' => 'product_cat',
	        'field'    => 'slug',
	        'terms'    => 'doplnky', 
		);

		$wooargs = array(
			'post_type' => 'product',
			'tax_query' => $taxarray,
			'posts_per_page' => -1
		);

		$query = new WP_Query( $wooargs );
		echo '<ul class="product-listing">';
		while ( $query->have_posts() ) : $query->the_post();
			echo '<li>';
			$_product = wc_get_product();
			echo '<a href="'.get_the_permalink().'">';
			the_title( '<h2 class="product-title">', '</h2>', true );
			the_post_thumbnail( 'listing' );
			echo '</a>';
			if( get_field( 'percent_boolean', get_the_id() ) ) {
				$value = get_field( 'percent_value' );
				echo '<p class="product-price">'.$value.'%</p>';
			} else {
				// echo '<p class="product-price">'.wc_price($_product->get_price()).'</p>';
				if( $_product->is_type( 'variable' ) ) {
					if( $_product->get_variation_price('min') == $_product->get_variation_price('max') ) {
						echo '<p class="product-price">'.wc_price($_product->get_variation_price('min')).'</p>';
					} else {
						echo '<p class="product-price">'.$_product->get_variation_price('min') . ' - ' . wc_price($_product->get_variation_price('max')).'</p>';
					}
				} else {
					echo '<p class="product-price">'.wc_price($_product->get_price()).'</p>';
				}
			}
			echo '<p><a class="detail" href="'.get_the_permalink().'">Detail doplňku</a></p>';
			echo '</li>';
		endwhile;
		echo '</ul>';

	echo '</div>';
	echo '<br style="clear: both;">';
	echo '</div>';
	endwhile;
	get_footer();
?>