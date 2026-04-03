<?php
get_header(); ?>
	<div id="content">
		<?php while ( have_posts() ) : the_post(); ?>

			<h2 class="section-title">
				<?php the_title(); ?>
			</h2>

			<div id="main">
				<a href="javascript: window.history.back();">Zpět</a>
				<?php
					if( get_post_gallery() ) {
						$gallery = get_post_gallery( get_the_id(), false );
						$gallery = $gallery['ids'];
						$gallery = explode( ",", $gallery );
						echo '<ul class="listing">';
						$i = 0;
						foreach ( $gallery as $item ) {
							$fullsrc = wp_get_attachment_image_src( $item, 'full' );
							$src = wp_get_attachment_image_src( $item, 'listing' );

							echo '<li>';
							echo '<a rel="prettyPhoto[gallery]" href="'.$fullsrc[0].'">';
							echo '<img src="'.$src[0].'">';
							echo '</a>';
							echo '</li>';
							$i++;
						}
						echo '</ul>';
					}
				?>
			</div>

			<div id="sidebar">
				<?php // the_post_thumbnail( 'listing' ); ?>
			</div>

			<div class="clearfix"></div>
			
		<?php endwhile; // end of the loop. ?>
	</div>

<?php get_footer(); ?>
