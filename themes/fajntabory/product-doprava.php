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
	<?php 
		wc_print_notices();
		$product = wc_get_product();

		$nastup = array();
		$vystup = array();
		$termin = array();
		$z_n_do	= array();

		if ( $product->is_type( 'variable' ) ) {

			$available_variations = $product->get_available_variations();
			foreach ( $available_variations as $available_variation ) {

				$id = $available_variation['variation_id'];
				
				$help_nastup = $available_variation['attributes']['attribute_pa_doprava'];
				$nastup[ $help_nastup ][] = $id;
				
				$help_vystup = $available_variation['attributes']['attribute_pa_lokalita'];
				$vystup[ $help_vystup ][] = $id;
				
				$help_termin = $available_variation['attributes']['attribute_pa_terminy'];
				$termin[ $help_termin ][] = $id;
				
				$help_z_n_do = $available_variation['attributes']['attribute_pa_smer'];
				$z_n_do[ $help_z_n_do ][] = $id;

			}

			ksort( $nastup );
			ksort( $vystup );
			ksort( $termin );
			ksort( $z_n_do );
		}

	?>

	<div class="variations">
		<table class="variation">
			<?php
				foreach ($vystup as $key => $value) {
					echo '<tr>';
					echo '<th>Lokalita tábora</th>';
					echo '<th>Místo nástupu / výstupu</th>';
					echo '<th>Termín</th>';
					echo '<th>Směr</th>';
					echo '<th></th>';
					echo '<th></th>';
					echo '</tr>';
					echo '<tr>';

						$ids = wp_json_encode( array_map( 'absint', $value ) );
						$title = get_term_by('slug', $key, 'pa_lokalita');
						$title = $title->name;
						echo '<td>'.esc_html( $title ).'<input class="v_chose" type="hidden" value="'.esc_attr( $ids ).'"></td>';
						
						if( !empty( $nastup ) ) {
							echo '<td><select class="n_chose d_chose">';
							echo '<option value="" disabled selected>Prosím vyberte..</option>';
							foreach ($nastup as $n_key => $n_value) {
								$ids = wp_json_encode( array_map( 'absint', $n_value ) );
								$title = get_term_by('slug', $n_key, 'pa_doprava');
								$title = $title->name;
								echo '<option value="'.esc_attr( $ids ).'">'.esc_html( $title ).'</option>';
							}
							echo '</select></td>';
						}


						if( !empty( $termin ) ) {
							echo '<td><select class="t_chose d_chose">';
							echo '<option value="" disabled selected>Prosím vyberte..</option>';
							foreach ($termin as $t_key => $t_value) {
								$ids = wp_json_encode( array_map( 'absint', $t_value ) );
								$title = get_term_by('slug', $t_key, 'pa_terminy');
								$title = $title->name;
								echo '<option value="'.esc_attr( $ids ).'">'.esc_html( $title ).'</option>';
							}
							echo '</select></td>';
						}

						if( !empty( $z_n_do ) ) {
							echo '<td><select class="z_chose d_chose">';
							echo '<option value="" disabled selected>Prosím vyberte..</option>';
							foreach ($z_n_do as $z_key => $z_value) {
								$ids = wp_json_encode( array_map( 'absint', $z_value ) );
								$title = get_term_by('slug', $z_key, 'pa_smer');
								$title = $title->name;
								echo '<option value="'.esc_attr( $ids ).'">'.esc_html( $title ).'</option>';
							}
							echo '</select></td>';
						}

						echo '<td class="variation_price"></td>';
						echo '<td class="variation_discount"></td>';
						echo '<td class="variation_qty">Není k dispozici</td>';
						echo '<td><a href="?add_to_cart=0" class="disabled">Objednat</a></td>';

					echo '</tr>';
				}

			 ?>
		</table>
	</div>
	<div class="heywait"></div>
	<?php the_content(); ?>
</div>
