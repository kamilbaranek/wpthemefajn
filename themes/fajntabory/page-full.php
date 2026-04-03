<?php
/**
 * Template Name: Bez postraního panelu
 */
	get_header();
	echo '<div id="content" class="full">';
	while ( have_posts() ) : the_post();
		the_title( '<h2 class="entry-title">', '</h2>', true );
		echo '<div id="main">';
		custom_breadcrumbs();
		the_content();
		echo '</div>';
	endwhile;
	get_sidebar( 'page' );
	echo '</div>';
	get_footer();
?>