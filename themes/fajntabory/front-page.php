<?php
/**
 * Template Name: Úvodní stránka
 */
	get_header();

	if( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			$image = get_the_post_thumbnail_url( $post, 'slider' );
			$pid = get_the_ID();

?>

<?php
if( !empty($_GET['oid']) ) { $oid = absint( wp_unslash( $_GET['oid'] ) ); } else { $oid = 0; }
if( !empty($_GET['email']) ) { $email = sanitize_email( wp_unslash( $_GET['email'] ) ); } else { $email = ''; }
if( empty( $email ) ) { $email = 'svém e-mailu'; }
?>

<div id="promo" style="background-image: url(<?php echo esc_url( $image ); ?>);">
	<?php if( !isset($_GET['oid']) && !isset($_GET['email']) ) { ?>
		<div class="flyer">Fajn Tábory<br/>jsou prostě boží!<img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/images/fajn_tabory.png"></div>
	<?php } else { ?>
		<div class="confirm">
			<p>
				Vaše objednávka č. <?php echo esc_html( $oid ); ?> byla úspěšně odeslána!</br>
				Potvrzení naleznete na <?php echo esc_html( $email ); ?>.</br>
			</p>
			<h2>DĚKUJEME <span>:)</span></h2>
		</div>
	<?php } ?>
</div>

<div id="about">

<?php
			echo '<div class="about">';
			the_title( '<h2>', '</h2>' );
			the_content();
			echo '</div>';
		}
	}
?>

<div class="news">

<?php 

	$args = array(
		'post-type' => 'post'
	); 

	$query = new WP_Query( $args ); 
	if($query->have_posts()){
		while ( $query->have_posts() ) {
			$query->the_post();
			echo '<div class="news-post">';
			echo '<div class="news-type '.get_post_format().'"></div>';
			echo '<div class="news-date">' . get_the_date('j.n.Y') . '</div>';
			if( get_post_format() != 'status' ) {
				echo '<div class="news-title"><a href="'.get_the_permalink().'">' . get_the_title() . '</a></div>';
			} else {
				echo '<div class="news-title">' . get_the_title() . '</div>';
			}
			echo '<div class="news-excerpt">' . get_the_excerpt() . '</div>';
			echo '</div>';
		}
	}
?>

</div>

<br style="clear: both;">
</div>



<!-- Filtr táborů -->

<?php /* ?>


<div id="tabory">

<h2 class="hp-section-title"><span><?php echo get_field( "hp_tabory_titulek", $pid ); ?></span></h2>
<p><?php echo get_field( "hp_tabory_popisek", $pid ); ?></p>

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

<?php */ ?>

<div id="tabory-vypis">

<h2 class="hp-section-title"><span><?php echo get_field( "hp_tabory_titulek", $pid ); ?></span></h2>
<p><?php echo get_field( "hp_tabory_popisek", $pid ); ?></p>

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
			'posts_per_page' => 10,
			'orderby' => 'rand',
			'tax_query' => array(
				'relation'  => 'AND',
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => 'tabory'
				)
			)
		);
		
		$query = new WP_Query( $args );
		
		if( $query->have_posts() ) {
			echo '<ul class="buttons">';

			while ( $query->have_posts() ) {
				
				$query->the_post();
				$term = wp_get_post_terms( get_the_id(), 'typ-tabora');
				$term = $term[0];
				$barva = get_field( 'barva', 'typ-tabora_'.$term->term_id );
				
				$out_lokalita = array();
				$out_typ_tabora = array();
				$out_terminy = array();

				global $product;

				if ($product->is_type( 'variable' )) {

					$available_variations = $product->get_available_variations();

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

<!-- Vedoucí -->
<div id="hp_vedouci">
<h2 class="hp-section-title"><span><?php echo get_field( "hp_vedouci_titulek", $pid ); ?></span></h2>
<p><?php echo get_field( "hp_vedouci_popisek", $pid ); ?></p>
<?php

		$args = array(
			'posts_per_page' => 9,
			'orderby' => 'rand',
			'post_type' => 'vedouci'
			
		);
		
		$query = new WP_Query( $args );
		
		if( $query->have_posts() ) {
			echo '<ul class="buttons">';
			while ( $query->have_posts() ) {
				$query->the_post();

				$barva = get_field( 'barva', get_the_id() );
				if( empty( $barva ) ) {
					$barva = '#ed1087';
				}

				echo '<li>';
				echo '<a href="'.get_the_permalink().'">';
				// the_post_thumbnail( 'listing' );
				$image = get_the_post_thumbnail_url( get_the_id(), 'listing' );
				echo '<img width="320" height="320" src="'.$image.'" style="border-color: '.$barva.'">';
				the_title( '<h2 class="entry-title">', '</h2>', true );
				echo '</a>';
				echo '</li>';
			}
				echo '<li class="ostatni">';
				echo '<a href="'.get_field( "hp_vedouci_odkaz", $pid ).'">';
				echo '<img width="320" height="320" src="'.get_stylesheet_directory_uri().'/assets/images/arrow.png">';
				echo '<h2 class="entry-title">Náš kompletní tým</h2>';
				echo '</a>';
				echo '</li>';
			echo '</ul>';
		}

?>

</div>
<div id="hp_bannery">
<h2 class="hp-section-title"><span><?php echo get_field( "hp_bannery_titulek", $pid ); ?></span></h2>
	<div>
		<?php $barva = get_field( 'barva_zeleneho_banneru', $pid ); ?>
		<div class="banner" style="background-color: <?php echo $barva; ?>;">
			<span><?php echo get_field( "hp_bannery_titulek_zeleny", $pid ); ?></span>
			<p><?php echo get_field( "hp_bannery_popisek_zeleny", $pid ); ?></p>
		</div>
		<?php $barva = get_field( 'barva_ruzoveho_banneru', $pid ); ?>
		<div class="banner" style="background-color: <?php echo $barva; ?>;">
			<span><?php echo get_field( "hp_bannery_titulek_ruzovy", $pid ); ?></span>
			<p><?php echo get_field( "hp_bannery_popisek_ruzovy", $pid ); ?></p>
		</div>
		<?php $barva = get_field( 'barva_modreho_banneru', $pid ); ?>
		<div class="banner" style="background-color: <?php echo $barva; ?>;">
			<span><?php echo get_field( "hp_bannery_titulek_modry", $pid ); ?></span>
			<p><?php echo get_field( "hp_bannery_popisek_modry", $pid ); ?></p>
		</div>
		<?php $barva = get_field( 'barva_zluteho_banneru', $pid ); ?>
		<div class="banner" style="background-color: <?php echo $barva; ?>;">
			<span><?php echo get_field( "hp_bannery_titulek_zluty", $pid ); ?></span>
			<p><?php echo get_field( "hp_bannery_popisek_zluty", $pid ); ?></p>
		</div>
	</div>
</div>
<br class="clear" />

<!-- Galerie -->
<div id="hp_galerie">
<h2 class="hp-section-title"><span><?php echo get_field( "hp_galerie_titulek", $pid ); ?></span></h2>
<p><?php echo get_field( "hp_galerie_popisek", $pid ); ?></p>

<?php

		$args = array(
			'posts_per_page' => 9,
			'orderby' => 'rand',
			'post_type' => 'galerie'
			
		);
		
		$query = new WP_Query( $args );
		
		if( $query->have_posts() ) {
			echo '<ul class="buttons">';
			$i = 0;
			while ( $query->have_posts() ) {
				$query->the_post();
				$term = wp_get_post_terms( get_the_id(), 'typ-tabora');
				$term = $term[0];
				$barva = get_field( 'barva', 'typ-tabora_'.$term->term_id );
				echo '<li>';
				echo '<a href="'.get_the_permalink().'">';
				echo '<div>';
				if( $i == 999 ) {
					the_post_thumbnail( 'double-listing' );
				} else {
					the_post_thumbnail( 'listing' );
				}
				echo '</div>';
				the_title( '<h2 class="entry-title">', '</h2>', true );
				echo '</a>';
				echo '</li>';
				$i++;
			}

			echo '<li class="ostatni">';
			echo '<a href="'.get_field( "hp_galerie_odkaz", $pid ).'">';
			echo '<img src="'.get_stylesheet_directory_uri().'/assets/images/arrow.png">';
			echo '<h2 class="entry-title title-dark">Ostatní</h2>';
			echo '</a>';
			echo '</li>';

			echo '</ul>';
			echo '<br style="clear:both;" />';
		}

?>
</div>

<?php
	get_footer();
?>
