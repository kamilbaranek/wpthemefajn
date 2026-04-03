<?php
	class calendar {

		public $dates = array();

		public function __construct( $begin, $end ) {

			$this->header( $begin, $end );

		}

		public function header( $begin, $end ) {

			$begin 	= strtotime( $begin );
			$end 	= strtotime( $end );
			$loop 	= $begin;
			$dates 	= array();

			while( $loop <= $end ) {

			   $dates[$loop] = 0;
			   $this->dates[$loop] = 0;
			   $loop = strtotime( '+1 day', $loop );

			}

			foreach ( $this->dates as $key => $value ) {

				$year 	= intval(date( 'Y', $key ));
			   	$month 	= intval(date( 'n', $key ));
			   	$week 	= intval(date( 'W', $key ));
			   	$day 	= intval(date( 'j', $key ));

			}

		}

	}

/*
	$begin = new DateTime( $date_from );
	$end = new DateTime( $date_to );

	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod( $begin, $interval, $end );

	$year 	= null;
	$month 	= null;
	$week 	= null;
	$day 	= null;

	$calendar = new calendar( $date_from, $date_to );

	foreach ( $period as $dt ) {

		if( $year != intval($dt->format( "Y" )) ) {
			$year = intval($dt->format( "Y" ));
		}

		if( $month != intval($dt->format( "n" )) ) {
			$month = intval($dt->format( "n" ));
		}

		if( $week != intval($dt->format( "W" )) ) {
			$week = intval($dt->format( "W" ));
		}

		if( $day != intval($dt->format( "j" )) ) {
			$day = intval($dt->format( "j" ));
		}

	}

	$count = intval( abs( strtotime($date_from) - strtotime($date_to) ) / 86400 ) * 60;
	$width = $count . 'px';


	$date_from = '2017-01-01';
	$date_to = '2017-02-29';

	$begin = new DateTime( $date_from );
	$end = new DateTime( $date_to );

	$count = intval( abs( strtotime($date_from) - strtotime($date_to) ) / 86400 ) * 60;
	$width = $count . 'px';

	$year = null;
	$month = null;
	$week = null;
	$day = null;
	$c = 0;

	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod( $begin, $interval, $end );

	echo '<ul style="width: '.$width.'">';

	foreach ( $period as $dt ) {

		$c++;

		if( $year != intval($dt->format( "Y" )) ) {
			$year = intval($dt->format( "Y" ));
		}

		if( $month != intval($dt->format( "n" )) ) {
			$month = intval($dt->format( "n" ));
			echo '<li class="month" style="width:'.($c*60).'px"><span>' . $month . '</span></li>';
			$c = 0;
		}

	}

	echo '</ul>';
	echo '<ul style="width: '.$width.'">';

	foreach ( $period as $dt ) {
		$c++;
		if( $week != intval($dt->format( "W" )) ) {
			$week = intval($dt->format( "W" ));
			echo '<li class="week" style="width:'.($c*60).'px"><span>' . $week . '</span></li>';
			$c = 0;
		}

	}

	echo '</ul>';
	echo '<ul style="width: '.$width.'">';

	foreach ( $period as $dt ) {

		if( $day != intval($dt->format( "j" )) ) {
			$day = intval($dt->format( "j" ));
			echo '<li class="day"><span>' . $day . '</span></li>';
		}

	}

	echo '</ul>';

	*/

?>