<?php
/**
 * Template Name: Výpis táborů
 */
	get_header();
	echo '<div id="content">';
	echo '<h2 class="section-title" style="background: #14b5e1; border-bottom: 3px solid #0082AE;">Nabídka našich táborů<span>U nás si vybere tábor každý!</span></h2>';
	echo '<div id="main">';

	if( have_posts() ) {
		$content = null;
		while ( have_posts() ) {
			the_post();
			$content = do_shortcode(get_the_content());
			$myid = get_the_ID();
		}
	}
?>

<div class="content"><?php echo wpautop($content); ?></div>

<!-- Filtr táborů -->
<?php /*
<div id="tabory">

<?php
if( empty( $_GET['typ_tabory']) ) { $_GET['typ_tabory'] = -1; }

	$taxarray = array();

	$taxarray[] = array(
		'taxonomy' => 'product_cat',
	    'field'    => 'slug',
	  	'terms'    => 'tabory', 
	);

	if( $_GET['typ_tabory'] < 0 ) {

		$terms = get_terms( 'typ-tabora', array(
		    'hide_empty' => false,
		) );

		echo '<ul class="buttons">';

		foreach ( $terms as $term ) {

			$barva = get_field( 'barva', 'typ-tabora_'.$term->term_id);
			if( empty( $barva ) ) {
				$barva = '#ed1087';
			}

			if( $term->term_id == $_GET['typ_tabory'] ) {
				echo '<li class="active" style="background: '.$barva.';">';
				echo '<a class="show_cat" href="#tabory-'.$term->term_id.'">';
				echo wp_get_attachment_image( get_field( 'obrazek', 'typ-tabora_'.$term->term_id)['id'], 'listing' );
				echo '<span>'.$term->name.'</span>';
				echo '</a></li>';
			} else {
				echo '<li style="background: '.$barva.';">';
				echo '<a class="show_cat" href="#tabory-'.$term->term_id.'">';
				echo wp_get_attachment_image( get_field( 'obrazek', 'typ-tabora_'.$term->term_id)['id'], 'listing' );
				echo '<span>'.$term->name.'</span>';
				echo '</a></li>';
			}
		}

		echo '</ul>';

	} else {

		$terms = get_terms( 'typ-tabora', array(
		    'hide_empty' => false,
		) );

		echo '<ul class="buttons">';

		foreach ( $terms as $term ) {
			if( $term->term_id == $_GET['typ_tabory'] ) {
				echo '<li class="active">';
				echo '<a class="show_cat" href="#tabory-'.$term->term_id.'">';
				echo wp_get_attachment_image( get_field( 'obrazek', 'typ-tabora_'.$term->term_id)['id'], 'listing' );
				echo '<span>'.$term->name.'</span>';
				echo '</a></li>';
			} else {
				echo '<li>';
				echo '<a class="show_cat" href="#tabory-'.$term->term_id.'">';
				echo wp_get_attachment_image( get_field( 'obrazek', 'typ-tabora_'.$term->term_id)['id'], 'listing' );
				echo '<span>'.$term->name.'</span>';
				echo '</a></li>';
			}
		}

		echo '</ul>';
	}
?>
</div>

*/ ?>


<div id="tabory-vypis">
<?php 

// dělení táborů podle kategorie

/*

	$terms = get_terms( 'typ-tabora', array(
		'hide_empty' => false,
	) );

	foreach ($terms as $term ) {
		echo '<div id="tabory-'.$term->term_id.'" class="tabory_cat">';


		$args = array(
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order'	=> 'ASC',
			'tax_query' => array(
			'relation'  => 'AND',
				array(
					'taxonomy'         => 'typ-tabora',
					'field'            => 'id',
					'terms'            => $term->term_id
				)
			)
			
		);

*/

		$args = array(
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'order'	=> 'ASC',
			'tax_query' => array(
				'relation'  => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => 'tabory'
				)
			)
		);

		/* Upraveny argumenty výše */
		
		$query = new WP_Query( $args );
		
		if( $query->have_posts() ) {
			echo '<ul class="buttons">';
			while ( $query->have_posts() ) {
				
				$query->the_post();
				$term = wp_get_post_terms( get_the_id(), 'typ-tabora');
				$term = $term[0];
				$barva = get_field( 'barva', 'typ-tabora_'.$term->term_id );

				global $product;
				if ($product->is_type( 'variable' )) {

					$available_variations = $product->get_available_variations();
					$out_lokalita = array();
					$out_typ_tabora = array();
					$out_terminy = array();

					foreach ($available_variations as $variation) {

						$taxonomy = 'pa_lokalita';
						$lokalita = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
						$lokalita = get_term_by('slug', $lokalita, $taxonomy);
						$out_lokalita[] = $lokalita->name;
						
						$taxonomy = 'pa_typ-tabora';
						$typ_tabora = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
						$typ_tabora = get_term_by('slug', $typ_tabora, $taxonomy);
						$typ_tabora_slug = $typ_tabora->slug;
						$out_typ_tabora[] = $typ_tabora->name;

						$taxonomy = 'pa_terminy';
						$terminy = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
						$terminy = get_term_by('slug', $terminy, $taxonomy);
						$out_terminy[] = $terminy->name;

					}
				}

				$out_lokalita = array_unique( $out_lokalita );
				asort( $out_lokalita );

				$out_typ_tabora = array_unique( $out_typ_tabora );
				asort( $out_typ_tabora );

				$out_terminy = array_unique( $out_terminy );
				// asort( $out_terminy );

				echo '<li>';
				echo '<a href="'.get_the_permalink().'">';
				
				$title = get_field('upraveny_titulek');
				if( empty($title) ) {
					$title = get_the_title();
				}

				echo '<h2 class="entry-title" style="background: '.$barva.';">' .$title. '</h2>';
				

				echo '<div class="typy_taboru" style="background: '.$barva.';">';

				echo '<div class="lokalita">';
				echo '<ul>';
				$count = count($out_lokalita);

				foreach ( $out_lokalita as $lokalita ) {
					if( !empty( $lokalita ) ) {
						echo '<li><i class="fa fa-map-marker" aria-hidden="true"></i>'.$lokalita.'</li>';
					}
				}
				echo '</ul>';
				echo '</div>';


				the_post_thumbnail( 'listing' );

				echo '<ul class="f_typ">';
				foreach ( $out_typ_tabora as $typ_tabora ) {
					if( !empty( $typ_tabora ) ) {
						echo '<li>'.$typ_tabora.'</li>';
					}
				}
				echo '</ul>';
				echo '<br class="clear">';
				echo '</div>';

				echo '<div class="terminy" style="background: '.$barva.';">';
				echo '<ul>';

				$count = count($out_terminy);
				if( $count > 2 ) {
					$out_terminy = array_slice($out_terminy, 0, 2);
				}
				foreach ( $out_terminy as $termin ) {
					if( !empty( $termin ) ) {
						echo '<li>'.$termin.'</li>';
					}
				}
				if( $count ) {
					$ncount = $count - 2;
					if( $ncount <= 0 ) {
						echo '<li style="font-weight: bold;">žádné další termíny</li>';
					} else if( $ncount == 1 ) {
						echo '<li style="font-weight: bold;">' . $ncount . ' další termín</li>';
					} else if ( $ncount > 1 && $ncount < 5 ) {
						echo '<li style="font-weight: bold;">' . $ncount . ' další termíny</li>';
					} else {
						echo '<li style="font-weight: bold;">' . $ncount . ' dalších termínů</li>';
					}
				}

				echo '</ul>';
				echo '</div>';
				echo '</a>';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo '<p class="sorry">Je nám líto, ale v současné době nenabízíme žádný tábor zařazený do této kategorie.</p>';
		}
		

		echo '</div>';
	/* } */


?>
</div>

<div id="tabory-obsah">
	<?php echo get_field( 'obsah_pod_taborama', $myid ); ?>
</div>

<?php

	echo '</div>';
	echo '</div>';
	get_footer();
?>