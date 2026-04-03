<div id="sidebar">

<?php
	
	$roky = get_terms('galerie-roky');
	$lokality = get_terms('galerie-lokalita');
	$terminy = get_terms('galerie-terminy');

	if( !empty($_GET['rok']) ) {
		$filtr_rok = $_GET['rok'];
	} else {
		$filtr_rok = null;
	}

	if( !empty($_GET['lokalita']) ) {
		$filtr_lokalita = $_GET['lokalita'];
	} else {
		$filtr_lokalita = null;
	}

	if( !empty($_GET['termin']) ) {
		$filtr_termin = $_GET['termin'];
	} else {
		$filtr_termin = null;
	}

	/**
	 * Filtr pro výběr roků
	 */

	if( $roky ) {
		echo '<h3>Vyberte rok</h3>';
		echo '<ul class="select">';
		foreach( $roky as $rok ) {
			if( $filtr_rok == $rok->slug ) {
				echo '<li class="current"><a href="?rok='.$rok->slug.'">' . $rok->name . '</a></li>';
			} else {
				echo '<li><a href="?rok='.$rok->slug.'">' . $rok->name . '</a></li>';
			}
		}
		echo '</ul>';
	}

	/**
	 * Filtr pro výběr lokality
	 */
	
	if( $lokality && !empty( $filtr_rok ) ) {
		echo '<h3>Vyberte lokalitu</h3>';
		echo '<ul class="select">';
		foreach( $lokality as $lokalita ) {
			if( $filtr_lokalita == $lokalita->slug ) {
				echo '<li class="current"><a href="?rok='.$filtr_rok.'&lokalita='.$lokalita->slug.'">' . $lokalita->name . '</a></li>';
			} else {
				echo '<li><a href="?rok='.$filtr_rok.'&lokalita='.$lokalita->slug.'">' . $lokalita->name . '</a></li>';
			}
		}
		echo '</ul>';
	}

	/**
	 * Filtr pro výběr termínu
	 */
	
	if( $terminy && !empty( $filtr_rok ) && !empty( $filtr_lokalita ) ) {
		echo '<h3>Vyberte turnus</h3>';
		echo '<ul class="select">';
		foreach( $terminy as $termin ) {
			if( $filtr_termin == $termin->slug ) {
				echo '<li class="current"><a href="?rok='.$filtr_rok.'&lokalita='.$filtr_lokalita.'&termin='.$termin->slug.'">' . $termin->name . '</a></li>';
			} else {
				echo '<li><a href="?rok='.$filtr_rok.'&lokalita='.$filtr_lokalita.'&termin='.$termin->slug.'">' . $termin->name . '</a></li>';	
			}
		}
		echo '</ul>';
	}

	/**
	 * Filtr pro výběr zaměření
	 */
	

	/*
	
		
	if( !empty( $filtr_rok ) && !empty( $filtr_lokalita ) && !empty( $filtr_termin ) ) {
		
		$args = array(
			'post_type'	=> 'galerie',
			'tax_query' => array(
			'relation'  => 'AND',
				array(
					'taxonomy'         => 'galerie-roky',
					'field'            => 'slug',
					'terms'            => $filtr_rok
				),
				array(
					'taxonomy'         => 'galerie-lokalita',
					'field'            => 'slug',
					'terms'            => $filtr_lokalita
				),
				array(
					'taxonomy'         => 'galerie-terminy',
					'field'            => 'slug',
					'terms'            => $filtr_termin
				)
			)
		);
		$query = new WP_Query( $args );
		
		if( $query->have_posts() ) {
			echo '<h3>Vyberte zaměření</h3>';
			echo '<ul class="select">';
			while ( $query->have_posts() ) {
				$query->the_post();	
				echo '<li><a href="'.get_the_permalink().'">' . get_the_title() . '</a></li>';
			}
			echo '</ul>';
		}
	}

	*/
?>
</div>