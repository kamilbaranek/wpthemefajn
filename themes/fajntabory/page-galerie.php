<?php
/**
 * Template Name: Seznam galerií
 */

	get_header();
	echo '<div id="content">';
	
	echo '<h2 class="section-title">'.get_the_title().'<span></span></h2>';
	get_sidebar( 'galerie' );
	echo '<div id="main">';

	$relation = array();

	if( !empty($_GET['rok']) ) {
		$filtr_rok = $_GET['rok'];
		$relation[] = array(
			'taxonomy'         => 'galerie-roky',
			'field'            => 'slug',
			'terms'            => $filtr_rok
		);
	}

	if( !empty($_GET['lokalita']) ) {
		$filtr_lokalita = $_GET['lokalita'];
		$relation[] = array(
			'taxonomy'         => 'galerie-lokalita',
			'field'            => 'slug',
			'terms'            => $filtr_lokalita
		);
	}

	if( !empty($_GET['termin']) ) {
		$filtr_termin = $_GET['termin'];
		$relation[] = array(
			'taxonomy'         => 'galerie-terminy',
			'field'            => 'slug',
			'terms'            => $filtr_termin
		);
	}

	if( !empty( $relation ) ) {
		$wooargs = array(
			'post_type' => 'galerie',
			'posts_per_page' => -1,
			'tax_query' => array(
				'relation'  => 'AND', $relation
			)
		);
	} else {
		$wooargs = array(
			'post_type' => 'galerie',
			'posts_per_page' => -1
		);
	}

	$query = new WP_Query( $wooargs );
	
	echo '<ul class="buttons">';
	$i = 0;
	while ( $query->have_posts() ) : $query->the_post();
			echo '<li>';
			echo '<a href="'.get_the_permalink().'">';
			if( $i == 9999 ) {
				the_post_thumbnail( 'double-listing' );
			} else {
				the_post_thumbnail( 'listing' );
			}
			the_title( '<h2 class="entry-title">', '</h2>', true );
			echo '</a>';
			echo '</li>';
			$i++;
	endwhile;
	echo '</ul>';
	echo '<div class="clearfix"></div>';

	echo '</div><!-- #main -->';
	echo '</div><!-- #content -->';
	get_footer();
?>