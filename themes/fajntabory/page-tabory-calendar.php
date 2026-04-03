<?php

/**
 * Template Name: Kalendář táborů
 */

get_header(); ?>
<div id="content">
<h2 class="section-title" style="background: #14b5e1; border-bottom: 3px solid #0082AE;">Nabídka našich táborů<span>U nás si vybere tábor každý?</span></h2>
<div id="main">
	
<?php
	$date_from = '2017-01-01';
	$date_to = '2017-02-29';
	$calendar = new calendar( $date_from, $date_to );
?>

</div>
</div>

<?php
get_footer();

function monthToName( $month ) {
	switch ( $month ) {
		case 1: return 'Leden'; break;
		case 2: return 'Únor'; break;
		case 3: return 'Březen'; break;
		case 4: return 'Duben'; break;
		case 5: return 'Květen'; break;
		case 6: return 'Červen'; break;
		case 7: return 'Červenec'; break;
		case 8: return 'Srpen'; break;
		case 9: return 'Září'; break;
		case 10: return 'Říjen'; break;
		case 11: return 'Listopad'; break;
		case 12: return 'Prosinec'; break;
	}
}
?>