<?php
/**
 * Template Name: Blog
 */
	get_header();
	echo '<div id="content">';
	while ( have_posts() ) : the_post();
		the_title( '<h2 class="entry-title">', '</h2>', true );
		get_sidebar( 'blog' );
		echo '<div id="main">';
		custom_breadcrumbs();
		the_content();
		echo '</div>';
	endwhile;
	echo '</div>';
	get_footer();
?>