<?php
/**
 * Template Name: Seznam táborů
 */
	get_header();
	while ( have_posts() ) : the_post();
	echo '<div id="content">';
		the_title( '<h2 class="entry-title">', '</h2>', true );
	echo '<div id="main">';
		the_content();
	echo '</div>';
	echo '</div>';
	endwhile;
	get_footer();
?>