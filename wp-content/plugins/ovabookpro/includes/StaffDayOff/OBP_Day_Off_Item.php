<?php 
namespace BookPro\StaffDayOff;

use BookPro\OBP_Calendar;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Day_Off_Item") ) {

	class OBP_Day_Off_Item {

		protected $item = array();

		public function __construct( $row ){
			$this->item = $row;
		}

		public function get_item(){
			return $this->item;
		}

		public function get_id(){
			$item = $this->item;
			return $item['id'];
		}

		public function get_time(){
			$item = $this->get_item();
			$time = isset( $item['time'] ) ? $item['time'] : 'full_time';
			return $time;
		}

		public function get_time_translate(){
			$time = $this->get_time();
			return $time == 'full_time' ? esc_html__( 'Full Time', 'ovabookpro' ) : esc_html__( 'Custom Time', 'ovabookpro' );
		}

		public function get_start_date(){
			$item = $this->get_item();
			$start_date = isset( $item['start_date'] ) ? $item['start_date'] : '';
			return $start_date;
		}

		public function get_end_date(){
			$item = $this->get_item();
			$end_date = isset( $item['end_date'] ) ? $item['end_date'] : '';
			return $end_date;
		}

		public function get_vendor_id(){
			$item = $this->get_item();
			$vendor_id = isset( $item['vendor_id'] ) ? $item['vendor_id'] : '';
			return $vendor_id;
		}

		public function get_staff_id(){
			$item = $this->get_item();
			$staff_id = isset( $item['vendor_id'] ) ? $item['vendor_id'] : '';
			return $staff_id;
		}

		public function get_hour_off(){
			$item = $this->get_item();
			$hour_off = isset( $item['hour_off'] ) ? maybe_unserialize( $item['hour_off'] ) : array();
			return $hour_off;
		}

		public function check_timeslots( $start_date, $end_date ){
			$hour_off 	= $this->get_hour_off();
			$flag 		= true;
			$start_hours 		= gmdate( "H:i", $start_date );
			$end_hours 			= gmdate( "H:i", $end_date );
			$start_seconds 		= OBP_Calendar::Hi_to_seconds( $start_hours );
			$end_seconds 		= OBP_Calendar::Hi_to_seconds( $end_hours );

			if ( ! empty( $hour_off ) ) {
				foreach ( $hour_off as $item ) {

					$from_time 	= OBP_Calendar::Hi_to_seconds( $item['start_hour'] );
					$to_time 	= OBP_Calendar::Hi_to_seconds( $item['end_hour'] );

					if ( ( $start_seconds >= $from_time && $start_seconds < $to_time ) || ( $end_seconds > $from_time && $end_seconds <= $to_time ) ) {
						$flag = false;
					}
				}
			}
			return $flag;

		}

	}

}