<div id="main">
<?php
		$args = array(
			'wrap_before' => '',
			'wrap_after' => ''
		);
	?>
<div class="offer parallax" style="background-image: url(<?php echo the_post_thumbnail_url(); ?>);">
		<?php the_title( '<h2>', '</h2>' ); ?>
		<?php echo wpautop(do_shortcode(get_the_excerpt())); ?>
	</div>
<?php 
		$term = wp_get_post_terms( get_the_id(), 'typ-tabora');
		$term = $term[0];
		$barva = get_field( 'barva', 'typ-tabora_'.$term->term_id );

		/* if( !empty( $barva )) {
			echo '<style>';
			echo '.description h1,';
			echo '.description h2 {';
			echo '	color: '.$barva.';';
			echo '}';
			echo '</style>';
		} */

	/**
		 * Galerie
		 */
	global $product;
		$attachment_ids = $product->get_gallery_attachment_ids();
		if(!empty($attachment_ids)) {
			echo '<h2 class="entry-title" style="background-color: '.$barva.';">Fotografie<span></span></h2>';
			echo '<ul id="gallery">';
			/* echo '<li>';
			echo '<a href="http://www.youtube.com/watch?v=cH6kxtzovew" data-rel="prettyPhoto[gallery]">';
			echo '<img src="http://img.youtube.com/vi/cH6kxtzovew/default.jpg">';
			echo '</a>';
			echo '</li>'; */
			foreach( $attachment_ids as $attachment_id ) {
				echo '<li>';
				echo '<a href="'.wp_get_attachment_url($attachment_id).'" rel="prettyPhoto[gallery]">';
				echo wp_get_attachment_image( $attachment_id, 'gallery' ); 
				echo '</a>';
				echo '</li>';
			}
			echo '</ul>';
		}
	global $product;
	if ( $product->is_type( 'variable' ) ) {
			
			$available_variations = $product->get_available_variations();

			$pobytovy = array();
			$primestsky = array();
			$count_pobytove = 0;
			$count_primestske = 0;
			foreach ( $available_variations as $variation ) {

				$taxonomy = 'pa_lokalita';
				$lokalita = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
				$lokalita = get_term_by('slug', $lokalita, $taxonomy);
				$lokalita = $lokalita->name;
				
				$taxonomy = 'pa_typ-tabora';
				$typ_tabora = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
				$typ_tabora = get_term_by('slug', $typ_tabora, $taxonomy);
				$typ_tabora_slug = $typ_tabora->slug;
				$typ_tabora = $typ_tabora->name;
				
				$taxonomy = 'pa_terminy';
				$terminy = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
				$terminy = get_term_by('slug', $terminy, $taxonomy);
				$terminy = $terminy->name;

				// var_dump( get_post_meta( $variation['variation_id'] ) );
				
				if( $typ_tabora_slug == 'pobytovy-tabor' ) {
					if( empty( $variation['max_qty'] ) ) {
						$variation['max_qty'] = 0;
					}
					$sale_to = get_post_meta( $variation['variation_id'], '_sale_price_dates_to', true );
				
					$pobytovy[] = array(
						'lokalita' 				=> $lokalita,
						'typ_tabora'			=> $typ_tabora,
						'terminy'				=> $terminy,
						'variation_price'		=> $variation['display_regular_price'] . ' Kč',
						'variation_discount'	=> $variation['display_price'] . ' Kč',
						'variation_discount_to'	=> get_post_meta( $variation['variation_id'], '_sale_price_dates_to', true ),
						'variation_qty'			=> 'zbývá ' . $variation['max_qty'] . ' míst',
						'int_variation_qty'		=> $variation['max_qty'],
						'variation_id'			=> $variation['variation_id']
					);
					$count_pobytove++;
					
				} 

				if ( $typ_tabora_slug == 'primestsky-tabor' ) {
					if( empty( $variation['max_qty'] ) ) {
						$variation['max_qty'] = 0;
					}
					$sale_to = get_post_meta( $variation['variation_id'], '_sale_price_dates_to', true );
				
					$primestsky[] = array(
						'lokalita' 				=> $lokalita,
						'typ_tabora'			=> $typ_tabora,
						'terminy'				=> $terminy,
						'variation_price'		=> $variation['display_regular_price'] . ' Kč',
						'variation_discount'	=> $variation['display_price'] . ' Kč',
						'variation_discount_to'	=> get_post_meta( $variation['variation_id'], '_sale_price_dates_to', true ),
						'variation_qty'			=> 'zbývá ' . $variation['max_qty'] . ' míst',
						'int_variation_qty'		=> $variation['max_qty'],
						'variation_id'			=> $variation['variation_id']
					);
					$count_primestske++;
				}

			}
		}
	?>
<div class="description">
		<?php wc_print_notices(); ?>
		<div class="tab">
			<?php if( !empty( get_field( "popis_pro_pobytovy_tabor" ) ) && $count_pobytove > 0 ) { ?>
				<button class="tablinks" onclick="openCard(event, 'pobytovy')" id="defaultOpen">Pobytový tábor</button>
			<?php } ?>
		<?php if( !empty( get_field( "popis_pro_primestsky_tabor" ) ) && $count_primestske > 0 && $count_pobytove != 0 )  { ?>
				<button class="tablinks" onclick="openCard(event, 'primestsky')">Příměstský tábor</button>
			<?php } ?>
		<?php if( !empty( get_field( "popis_pro_primestsky_tabor" ) ) && $count_primestske > 0 && $count_pobytove <= 0 )  { ?>
				<button class="tablinks" onclick="openCard(event, 'primestsky')" id="defaultOpen">Příměstský tábor</button>
			<?php } ?>
		</div>
	<?php if( !empty( get_field( "popis_pro_pobytovy_tabor" ) ) && $count_pobytove > 0 )  { ?>
		<div id="pobytovy" class="tabcontent">
		<div class="variations">
				<table class="variation">
					<?php
						foreach ( $pobytovy as $tabor ) {
							echo '<tr>';
							echo '<td>'.$tabor['lokalita'].'</td>';
							echo '<td>'.$tabor['typ_tabora'].'</td>';
							echo '<td>'.$tabor['terminy'].'</td>';

							if( $tabor['variation_price'] == $tabor['variation_discount'] || $tabor['variation_price'] < $tabor['variation_discount'] ) {
								echo '<td class="variation_discount">'.wc_price( $tabor['variation_price'] ).'</td><td></td>';
							} else {
								echo '<td class="variation_price">'.wc_price( $tabor['variation_price'] ).'</td>';
								if( ! $tabor['variation_discount_to'] || $tabor['variation_discount_to'] < time() ) {
									echo '<td class="variation_discount">'.wc_price( $tabor['variation_discount'] ).'</td>';
								} else {
									echo '<td class="variation_discount">'.wc_price( $tabor['variation_discount'] ).'<br><small>platí do '.date('j.n.Y', $tabor['variation_discount_to'] ).'</small></td>';
								}
							}

							if( get_post_meta( $tabor['variation_id'], '_manage_stock', 'true' ) != 'no' ) {

								$new_qty = (int)$tabor['int_variation_qty'] - (int)count( retrieve_orders_ids_from_a_product_id($tabor['variation_id']) );

								if( $new_qty <= 0 ) {
									echo '<td><strong style="color:#c10000;">OBSAZENO</strong></td>';
									echo '<td><a href="#" class="disabled">Objednat</a></td>';
								} else if( $new_qty < 6 ) {
									echo '<td>Zbývá <strong style="color:#c10000;">'.$new_qty.'</strong> míst</td>';
									echo '<td><a href="?add_to_cart='.$tabor['variation_id'].'">Objednat</a></td>';
								} else {
									echo '<td>Zbývá '.$new_qty.' míst</td>';
									echo '<td><a href="?add_to_cart='.$tabor['variation_id'].'">Objednat</a></td>';
								}

							} else {
								echo '<td></td>';
								echo '<td><a href="?add_to_cart='.$tabor['variation_id'].'">Objednat</a></td>';
							}
							echo '<tr>';
						}
					?>
				</table>
			</div>
		<?php echo do_shortcode(get_field( "popis_pro_pobytovy_tabor" )); ?>
		</div>
		<?php } ?>
	<?php if( !empty( get_field( "popis_pro_primestsky_tabor" ) ) && $count_primestske > 0 )  { ?>
		<div id="primestsky" class="tabcontent">
		<div class="variations">
				<table class="variation">
					<?php
						foreach ( $primestsky as $tabor ) {
							echo '<tr>';
							echo '<td>'.$tabor['lokalita'].'</td>';
							echo '<td>'.$tabor['typ_tabora'].'</td>';
							echo '<td>'.$tabor['terminy'].'</td>';

							if( $tabor['variation_price'] == $tabor['variation_discount'] || $tabor['variation_price'] < $tabor['variation_discount'] ) {
								echo '<td class="variation_discount">'.wc_price( $tabor['variation_price'] ).'</td><td></td>';
							} else {
								echo '<td class="variation_price">'.wc_price( $tabor['variation_price'] ).'</td>';
								if( ! $tabor['variation_discount_to'] || $tabor['variation_discount_to'] < time() ) {
									echo '<td class="variation_discount">'.wc_price( $tabor['variation_discount'] ).'</td>';
								} else {
									echo '<td class="variation_discount">'.wc_price( $tabor['variation_discount'] ).'<br><small>platí do '.date('j.n.Y', $tabor['variation_discount_to'] ).'</small></td>';
								}
							}

							if( get_post_meta( $tabor['variation_id'], '_manage_stock', 'true' ) != 'no' ) {

								$new_qty = (int)$tabor['int_variation_qty'] - (int)count( retrieve_orders_ids_from_a_product_id($tabor['variation_id']) );

								if( $new_qty <= 0 ) {
									echo '<td><strong style="color:#c10000;">OBSAZENO</strong></td>';
									echo '<td><a href="#" class="disabled">Objednat</a></td>';
								} else if( $new_qty <= 5 && $new_qty > 0 ) {
									echo '<td><strong style="color:#c10000;">Zbývá '.$new_qty.' míst</strong></td>';
									echo '<td><a href="?add_to_cart='.$tabor['variation_id'].'">Objednat</a></td>';
								} else {
									echo '<td>Zbývá '.$new_qty.' míst</td>';
									echo '<td><a href="?add_to_cart='.$tabor['variation_id'].'">Objednat</a></td>';
								}

							} else {
								echo '<td></td>';
								echo '<td><a href="?add_to_cart='.$tabor['variation_id'].'">Objednat</a></td>';
							}
							echo '<tr>';
						}
					?>
				</table>
			</div>
		<?php echo do_shortcode(get_field( "popis_pro_primestsky_tabor" )); ?>
		</div>
		<?php } ?>
	</div>
</div>