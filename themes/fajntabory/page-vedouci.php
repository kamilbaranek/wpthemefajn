<?php
/**
 * Template Name: Seznam vedoucích
 */

	get_header();
	echo '<div id="content">';
	
	echo '<h2 class="section-title">Náš táborový personál<span></span></h2>';
	echo '<div id="main">';
	
	if( have_posts() ) {
		while( have_posts() ) {
			the_post();
			the_content();
		}
	}

	$terms = get_terms( array(
	    'taxonomy' => 'pozice-vedoucich',
	    'hide_empty' => true,
	) );

	if( $terms ) {

		foreach ($terms as $term) {
			echo '<h2 class="hp-section-title"><span>'.$term->name.'</span></h2>';

			$wooargs = array(
				'post_type' => 'vedouci',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
						'taxonomy' => 'pozice-vedoucich',
						'field'    => 'slug',
						'terms'    => $term->slug,
					),
				),
			);

			$query = new WP_Query( $wooargs );
			
			echo '<ul class="listing">';
			while ( $query->have_posts() ) : $query->the_post();

					$barva = get_field( 'barva', get_the_id() );
					if( empty( $barva ) ) {
						$barva = '#ed1087';
					}

					echo '<li>';
					echo '<a href="'.get_the_permalink().'">';
					$image = get_the_post_thumbnail_url( get_the_id(), 'listing' );
					echo '<img src="'.$image.'" style="border-color: '.$barva.'">';
					the_title( '<h2 class="entry-title">', '</h2>', true );
					echo '<p style="color: '.$barva.'">'.get_field('pozice').'</p>';
					if( get_field('zamereni') ) {
						echo '<p class="zamereni">'.get_field('zamereni').'</p>';
					}
					echo '</a>';
					echo '</li>';
			endwhile;
			echo '</ul>';
			echo '<div class="clearfix"></div>';

		}

	}

	echo '</div><!-- #main -->';
	echo '</div><!-- #content -->';
	get_footer();
?>