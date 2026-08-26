<?php
namespace BookPro;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Calendar') ) {
	
	class OBP_Calendar {

		public static function range_date( $from, $to ){
			
			$data = array();
			while ( $from <= $to ) {
				$data[] = gmdate("Y-m-d", $from );
				$from = strtotime( "+1 day", $from );
			}
			return $data;
		}

		public static function get_weekday_keys(){
			return array(
				'sunday',
				'monday',
				'tuesday',
				'wednesday',
				'thursday',
				'friday',
				'saturday',
			);
		}

		public static function Hi_to_seconds( $time ){
			list($hours,$minutes) = explode(":", $time);
			$seconds = 0;
			$seconds += absint( $hours )*60*60 + absint( $minutes )*60;
			return $seconds;
		}

	}

}