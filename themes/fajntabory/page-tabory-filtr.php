<?php
/**
 * Template Name: Filtr táborů
 */
	get_header();
	echo '<div id="content">';
	echo '<h2 class="section-title" style="background: #14b5e1; border-bottom: 3px solid #0082AE;">Nabídka našich táborů<span>U nás si vybere tábor každý?</span></h2>';
	echo '<div id="main">';
	?>

	<?php 
		$tabory = array();
		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'product',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => 'tabory',
				),
			),
		);

		$query = new WP_Query( $args );
		if( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				global $product;
				if ( $product->is_type( 'variable' ) ) {
					$available_variations = $product->get_available_variations();
					foreach( $available_variations as $variation ) {
						$attributes = $variation['attributes'];
						$attributes = $attributes['attribute_pa_terminy'];
						$term = get_term_by( 'slug', $attributes, 'pa_terminy' );
						$options = get_option( 'taxonomy_' . $term->term_id );
						$tabory[] = array(
							'product_id' => get_the_id(),
							'term_id' => $term->term_id,
							'datum_od' => $options['datum_od'],
							'datum_do' => $options['datum_do'],
						);
					}
				}
			}
		}

		$min = '9999-99-99';
		$max = NULL;

		foreach ($tabory as $tabor) {
			// var_dump( $tabor );
			if( $tabor['datum_od'] != NULL && $tabor['datum_od'] < $min ) {
				$min = $tabor['datum_od'];
			}

			if( $tabor['datum_do'] != NULL && $tabor['datum_do'] > $max ) {
				$max = $tabor['datum_do'];
			}
		}

		$calendar = array();
		$calendar['min'] = $min;
		$calendar['max'] = $max;

	?>

	<div class="calendar">
		
	<?php
		$date_from = $calendar['min'];
		$date_to = $calendar['max'];

		$begin = new DateTime( $date_from );
		$end = new DateTime( $date_to );

		$count = intval( abs( strtotime($date_from) - strtotime($date_to) ) / 86400 ) * 60;
		$width = $count . 'px';

		$year = null;
		$month = null;
		$week = null;
		$day = null;

		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod( $begin, $interval, $end );

		echo '<ul style="width: '.$width.'">';

		foreach ( $period as $dt ) {

			// Uzavírání měsíce
			if( !empty($month) && $month != intval($dt->format( "n" )) ) {
				echo '</ul></li><!--month-->';
			}

			if( $year != intval($dt->format( "Y" )) ) {
				$year = intval($dt->format( "Y" ));
			}

			// Otevírání měsíce
			if( $month != intval($dt->format( "n" )) ) {
				$month = intval($dt->format( "n" ));

				/*
				switch( $month ) {
					case 1: $month = 'Leden'; break;
					case 2: $month = 'Únor'; break;
					case 3: $month = 'Březen'; break;
					case 4: $month = 'Duben'; break;
					case 5: $month = 'Květen'; break;
					case 6: $month = 'Červen'; break;
					case 7: $month = 'Červenec'; break;
					case 8: $month = 'Srpen'; break;
					case 9: $month = 'Září'; break;
					case 10: $month = 'Říjen'; break;
					case 11: $month = 'Listopad'; break;
					case 12: $month = 'Prosinec'; break;
				}
				*/

				echo '<li class="month"><span>' . $month . '</span><ul>';
			}

			// Vypisování dnů
			if( $day != intval($dt->format( "j" )) ) {
				$day = intval($dt->format( "j" ));
				echo '<li class="day"><span>' . $day . '</span></li>';
			}

		}
			echo '</ul></li>'; // Last Week
		echo '</ul>';
?>


		<div class="events">
			<?php

				$args = array(
					'post_type' => 'product',
					'posts_per_page' => -1,
					'tax_query' => array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => 'tabory',
						),
					),
				);

				$query = new WP_Query( $args );
				if( $query->have_posts() ) {
					$events = array();
					while ( $query->have_posts() ) {
						
						$query->the_post();
						global $product;
											
						$term = wp_get_post_terms( get_the_id(), 'typ-tabora');
						$term = $term[0];
						$barva = get_field( 'barva', 'typ-tabora_'.$term->term_id );

						if ($product->is_type( 'variable' )) {

							$available_variations = $product->get_available_variations();
							foreach ($available_variations as $variation) {
								
								$taxonomy = 'pa_terminy';
								$terminy = get_post_meta( $variation['variation_id'], 'attribute_'.$taxonomy, true );
								$terminy = get_term_by('slug', $terminy, $taxonomy);

								if( $terminy ) {
									$options = get_option( 'taxonomy_' . $terminy->term_id );
									$datum_od = $options['datum_od'];
									$datum_do = $options['datum_do'];
								}

								$datum_start = strtotime( $calendar['min'] );
								$datum_end = strtotime( $calendar['max'] );

								$datum_od = strtotime( $datum_od );
								$datum_do = strtotime( $datum_do );

								if( $datum_od != 0 && $datum_do != 0 ) {

									$start_position = $datum_od - $datum_start;
									$start_position = floor($start_position / (60 * 60 * 24));
									$start_position = $start_position + 1;
									$end_position = $datum_do - $datum_start;
									$end_position = floor($end_position / (60 * 60 * 24));
									$end_position = $end_position + 1;

									$events[] = array(
										'barva'				=> $barva,
										'start-position'	=> $start_position,
										'end-position'		=> $end_position,
										'title'				=> get_the_title()
									);

								}

							}
						}

					}

					function compareByStart($a, $b) {
  						return strcmp($a["start-position"], $b["start-position"]);
					}
					usort($events, 'compareByStart');

					$top_position = 0;
					$walker = 0;

					foreach ( $events as $event ) {
						if( $event['start-position'] == 0 ) {
							$walker = $event['start-position'];
						} else if ( $event['start-position'] == $walker ) {
							$top_position = $top_position + 110;
						} else if ( $event['start-position'] != $valker ) {
							$walker = $event['start-position'];
							$top_position = 0;
						}

						// var_dump( $top_position );

						echo '<div class="event" style="background-color: '.$event['barva'].'; margin-top: 10px;" data-start-position="'.$event['start-position'].'" data-end-position="'.$event['end-position'].'" data-top-position="'.$top_position.'">';
									
						echo '<div class="event-title">'.$event['title'].'</div>';
						echo '<div class="event-row">';
						echo '<div class="event-col">';
						echo '<i class="fa fa-map-marker" aria-hidden="true"></i> Lanškroun<br/>';
						echo 'Cena: 9999 Kč';
						echo '</div>';
						echo '<div class="event-col">';
						echo 'Pobytový tábor<br/>';
						echo '<strong>zbývá 20 míst</strong>';
						echo '</div>';
						echo '</div>';

						echo '</div>';

					}

					// var_dump( $events );
				}
			?>
		</div>
	</div>


	<?php

	echo '</div>';
	echo '</div>';
	get_footer();
?>