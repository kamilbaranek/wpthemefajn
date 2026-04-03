<div id="main">
<?php
		$args = array(
			'wrap_before' => '',
			'wrap_after' => ''
		);
	?>
<div class="offer parallax" style="background-image: url(<?php echo the_post_thumbnail_url(); ?>);">
	<?php the_title( '<h2>', '</h2>' ); ?>
	<?php the_excerpt(); ?>
</div>
<?php
	global $product;
	$attachment_ids = $product->get_gallery_attachment_ids();
	if(!empty($attachment_ids)) {
		echo '<h2 class="entry-title" style="background-color: '.$barva.';">Fotografie<span></span></h2>';
		echo '<ul id="gallery">';
		foreach( $attachment_ids as $attachment_id ) {
			echo '<li>';
			echo '<a href="'.wp_get_attachment_url($attachment_id).'" data-rel="prettyPhoto[gallery]">';
			echo wp_get_attachment_image( $attachment_id, 'gallery' ); 
			echo '</a>';
			echo '</li>';
		}
		echo '</ul>';
	}
?>
</div>
<div class="description">
	<?php wc_print_notices(); ?>
	<?php
		$_product = wc_get_product();
		/* 
		echo wc_price($_product->get_regular_price());
		echo wc_price($_product->get_sale_price());
		echo wc_price($_product->get_price());
		*/
	?>

	<div class="variations">
		<table class="variation">
			<?php

				if ( $_product->is_type( 'variable' ) ) {

					// Má-li produkt varianty, pokračuj zde..
					
					echo '<tr>';
					$variation_attributes = $_product->get_variation_attributes();
					foreach ( $variation_attributes as $key => $available_variation ) {
						echo '<th>' . wc_attribute_label( $key ) . '</th>';
					}
					echo '<th colspan="3"></th></tr><tr>';

					foreach ( $variation_attributes as $key => $value ) {
						echo '<td>';
						echo '<input type="hidden" value="'.$_product->id.'" name="id">';
						echo '<select class="p_chose" name="attribute_'.$key.'">';
						echo '<option value="" disabled selected>Prosím vyberte..</option>';
						foreach ($value as $vkey => $vvalue) {

							$taxonomy = 'attribute_' . $key;
							$title = get_term_by( 'slug', $vvalue, $key );
							$title = $title->name;
							echo '<option value="'.$vvalue.'">'.$title.'</option>';
						}
						echo '</select></td>';
					}

					echo '<td class="variation_price"></td>';
					echo '<td class="variation_discount"></td>';
					// echo '<td class="variation_qty">Není k dispozici</td>';
					echo '<td><a href="?add_to_cart=0" class="disabled">Objednat</a></td>';
					echo '</tr>';

				} else {

					// Nemá-li produkt varianty, pokračuj zde ..

					echo '<tr>';

					if( get_field( 'percent_boolean', get_the_id() ) ) {
						$value = get_field( 'percent_value' );
						echo '<td class="variation_discount">'.$value.'%<br><small>Z hodnoty objednávky</small></td>';
					} else {

						if( $_product->get_regular_price() == $_product->get_sale_price() || $_product->get_sale_price() > $_product->get_regular_price() || $_product->get_sale_price() == 0 ) {

							echo '<td class="variation_discount">'.wc_price($_product->get_regular_price()).'</td>';
							if( $_product->get_name = 'zapujceni-airsoftove-vybavy' ) {
								$qty = (int)$_product->get_stock_quantity() - (int)count( retrieve_orders_ids_from_a_product_id( $_product->ID ) );
								echo '<td>Zbývá '.$qty.' ks</td>';
							}

						} else {

							if( ! $_product->date_on_sale_to || strtotime( $_product->date_on_sale_to ) < time() ) {
								echo '<td class="variation_price">'.wc_price($_product->get_regular_price()).'</td>';
								echo '<td class="variation_discount">'.wc_price($_product->get_sale_price()).'</td>';
								if( $_product->get_name = 'zapujceni-airsoftove-vybavy' ) {
									$qty = (int)$_product->get_stock_quantity() - (int)count( retrieve_orders_ids_from_a_product_id( $_product->ID ) );
									echo '<td>Zbývá '.$qty.' ks</td>';
								}
							} else {
								echo '<td class="variation_price">'.wc_price($_product->get_regular_price()).'</td>';
								echo '<td class="variation_discount">'.wc_price($_product->get_sale_price()).'<br><small>platí do '.date('j.n.Y', strtotime($_product->date_on_sale_to)).'</small></td>';
								if( $_product->get_name = 'zapujceni-airsoftove-vybavy' ) {
									$qty = (int)$_product->get_stock_quantity() - (int)count( retrieve_orders_ids_from_a_product_id( $_product->ID ) );
									echo '<td>Zbývá '.$qty.' ks</td>';
								}
							}

						}

					}
				
					if( $qty <= 0 ) {
						echo '<td><a href="#" class="disabled">Objednat</a></td>';
					} else {
						echo '<td><a href="?add_to_cart='.get_the_id().'">Objednat</a></td>';
					}
					echo '</tr>';
				}
			?>
		</table>
	</div>
	<div class="heywait"></div>
	<?php the_content(); ?>
</div>