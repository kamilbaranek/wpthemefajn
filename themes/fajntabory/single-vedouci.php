<?php
get_header(); ?>
	<div id="content">
		<?php while ( have_posts() ) : the_post(); ?>

			<h2 class="section-title">
				<?php the_title(); ?>
				<span><?php echo get_field( 'pozice' ) ?></span>
			</h2>

			<div id="sidebar">
				<?php the_post_thumbnail( 'listing' ); ?>
			</div>

			<div id="main">
				<?php // custom_breadcrumbs(); ?>
				<?php the_content(); ?>
			</div>

			<div class="clearfix"></div>
			
		<?php endwhile; // end of the loop. ?>
	</div>

<?php get_footer(); ?>
