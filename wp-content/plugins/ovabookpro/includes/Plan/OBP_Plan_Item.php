<?php

namespace BookPro\Plan;

use BookPro\Service\OBP_Service;


defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Plan_Item") ) {
	

	class OBP_Plan_Item {

		protected $id = null;

		public function __construct( $id ){

			$this->id = $id;
		}

		public function get_id(){
			return $this->id;
		}

		public function get_start_date(){
			$id = $this->get_id();
			$start_date = get_post_meta( $id, OBP_METABOX.'start_date', true );
			return $start_date;
		}

		public function get_end_date(){
			$id = $this->get_id();
			$end_date = get_post_meta( $id, OBP_METABOX.'end_date', true );
			return $end_date;
		}

		public function get_vendor_id(){
			$id = $this->get_id();
			$vendor_id = get_post_meta( $id, OBP_METABOX.'vendor_id', true );
			return $vendor_id;
		}

		public function get_status(){
			$id = $this->get_id();
			$status = get_post_meta( $id, OBP_METABOX.'status', true );
			return $status;
		}

		public function get_times(){
			$id = $this->get_id();
			$times = get_post_meta( $id, OBP_METABOX.'times', true );
			return $times;
		}

		public function get_time_from_service( $service_id ){
			$special_services = $this->get_special_services();
			$service_ids = array_column( $special_services, 'id' );
			$key = array_search( $service_id, $service_ids );
			$the_time = [];
			if ( $key !== false && isset( $special_services[$key]['time'] ) ) {
				$the_time = $special_services[$key]['time'];
			}

			return $the_time;
		}

		public function get_date_str(){
			$date_format = OBP()->settings->general->get('date_format', 'Y-m-d');
			$start_date = $this->get_start_date();
			$end_date = $this->get_end_date();
			$date_str = date_i18n($date_format, $start_date )._x( ' to ', 'date to date', 'ovabookpro' ).date_i18n( $date_format, $end_date );
			return $date_str;
		}

		public function get_working_hours(){
			$working_hour = '';
			$time_type = $this->get_time_type();
			if ( $time_type == 'full_time' ) {
				$working_hour = esc_html__( 'Full Time', 'ovabookpro' );
			} else {
				$times = $this->get_times();
				$arr_times = [];
				if ( ! empty( $times ) ) {
					foreach ( $times as $time_value ) {
						$start_hour = isset( $time_value['start_hour'] ) ? $time_value['start_hour'] : '';
						$end_hour = isset( $time_value['end_hour'] ) ? $time_value['end_hour'] : '';
						if ( $start_hour && $end_hour ) {
							$arr_times[] = $start_hour._x( ' to ', 'time to time', 'ovabookpro' ).$end_hour;
						}
					}
				}

				if ( ! empty( $arr_times ) ) {
					$working_hour = implode(', ', $arr_times);
				}
			}

			return $working_hour;
		}

		public function get_service_str(){
			$status 		= $this->get_status();
			$service_type 	= $this->get_service_type();

			$service_str = '';
			$key = '';
			if ( $status == 'open' ) {
				$key = 'all';
				if ( $service_type != 'all_services' ) {
					$key = 'some';
				}
			} else {
				$key = 'closed';
				if ( $service_type != 'all_services' ) {
					$key = 'some_closed';
				}
			}

			switch ( $key ) {
				case 'all':
					$service_str = esc_html__( 'Open All Services', 'ovabookpro' );
					break;
				case 'some':
					$some_sv = [];
					if ( count( $this->get_service_ids() ) > 0 ) {
						foreach ( $this->get_service_ids() as $sv_id ) {
							$service = obp_get_service( $sv_id );
							$some_sv[] = $service->get_title();
						}
					}

					if ( count( $some_sv ) > 0 ) {
						$service_str.= implode(', ', $some_sv);
					}

					break;

				case 'closed':
					$service_str = esc_html__( 'Closed All Services', 'ovabookpro' );
					break;

				case 'some_closed':
					$some_sv = [];
					if ( count( $this->get_service_ids() ) > 0 ) {
						foreach ( $this->get_service_ids() as $sv_id ) {
							$service = obp_get_service( $sv_id );
							$some_sv[] = $service->get_title();
						}
					}

					if ( count( $some_sv ) > 0 ) {
						$service_str.= implode(', ', $some_sv);
					}
					break;
				default:
					break;
			}

			return $service_str;
		}

		public function get_class_color(){
			$status 		= $this->get_status();
			$service_type 	= $this->get_service_type();

			$class_color = '';
			if ( $status == 'open' ) {
				$class_color = 'all';
				if ( $service_type != 'all_services' ) {
					$class_color = 'some';
				}
			} else {
				$class_color = 'closed';
				if ( $service_type != 'all_services' ) {
					$class_color = 'some_closed';
				}
			}

			return $class_color;
		}

		public function get_service_ids(){
			$id = $this->get_id();
			$service_type = $this->get_service_type();

			if ( $service_type != 'all_services' ) {
				$service_ids = get_post_meta( $id, OBP_METABOX.'service_ids', true );
				if ( ! empty( $service_ids ) ) {
					$service_ids = explode("|", $service_ids );
				} else {
					$service_ids = array();
				}
			} else {
				$vendor_id = $this->get_vendor_id();
				$service_ids = OBP_Service::get_service_ids_by_vendor_id( $vendor_id );
			}

			return $service_ids;
		}

		public function get_custom_service_ids(){
			$id = $this->get_id();
			$service_ids = get_post_meta( $id, OBP_METABOX.'service_ids', true );
			if ( ! empty( $service_ids ) ) {
				$service_ids = explode("|", $service_ids );
			} else {
				$service_ids = array();
			}
			return $service_ids;
		}

		public function get_special_services(){
			$id = $this->get_id();
			$special_services = get_post_meta( $id, OBP_METABOX.'data_special_services', true );
			return $special_services;
		}

		public function get_service_type(){
			$id = $this->get_id();
			$service_type = get_post_meta( $id, OBP_METABOX.'service_type', true );
			return $service_type;
		}

		public function get_time_type(){
			$id = $this->get_id();
			$time_type = get_post_meta( $id, OBP_METABOX.'time_type', true );
			return $time_type;
		}

		public function has_special_service(){
			$id = $this->get_id();
			$has_special_service = get_post_meta( $id, OBP_METABOX.'special_service', true );
			return $has_special_service;
		}
	}
}