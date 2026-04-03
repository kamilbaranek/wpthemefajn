<?php

	global $post;
	$current = $post->ID;

	if ( is_page() && $post->post_parent ) {
		$id = $post->post_parent;
	} else {
		$id = get_the_id();
	}

	$args = array(
	    'post_type'      => 'page',
	    'posts_per_page' => -1,
	    'post_parent'    => $id,
	    'order'          => 'ASC',
	    'orderby'        => 'menu_order'
	);


	$parent = new WP_Query( $args );

	if ( $parent->have_posts() ) : ?>
	<div id="sidebar">
		<h3>Další informace</h3>
		<ul>
	    <?php while ( $parent->have_posts() ) : $parent->the_post(); ?>
	    	<?php $item = get_the_id(); ?>
	    	<?php
	    		if( $current == $item ) {
	    			$class = 'current';
	    		} else {
	    			$class = null;
	    		}
	    	?>
	        <li class="<?php echo $class; ?>">
				<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
	        </li>
	    <?php endwhile; ?>
	<?php endif; wp_reset_query(); ?>
		</ul>
	</div>