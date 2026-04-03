<?php
/**
 * Template Name: Seznam táborů
 */
	get_header();
	echo '<div id="content">';
	echo '<h2 class="section-title" style="background: #14b5e1; border-bottom: 3px solid #0082AE;">Nabídka našich táborů<span>U nás si vybere tábor každý?</span></h2>';
	echo '<div id="main">';

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

	var_dump( $out_lokalita );
	var_dump( $out_termny );
	var_dump( $terminy );

	echo '</div>';
	echo '</div>';
	get_footer();
?>