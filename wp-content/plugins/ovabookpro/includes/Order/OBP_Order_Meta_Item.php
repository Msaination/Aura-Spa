<?php

namespace BookPro\Order;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Order_Meta_Item") ) {
	

	class OBP_Order_Meta_Item {

		protected $data;

		public function __construct( $item ){
			$this->data = $item;
		}

		public function get_service_id(){
			$item = $this->data;
			$service_id = $item->service_id;

			return $service_id;
		}

		public function get_service_name(){
			$item = $this->data;

			$service_id = $this->get_service_id();
			$service = obp_get_service( $service_id );
			$service_name = $service->get_title();

			return $service_name;
		}

		public function get_price(){
			$item = $this->data;
			$price = $item->price;
			return $price;
		}

		public function get_staff_name(){
			$item = $this->data;
			$user = obp_get_user( $item->staff_id );
			$staff_name = $user->get_nickname();
			return $staff_name;
		}

		public function get_staff_fullname(){
			$item = $this->data;
			$user = obp_get_user( $item->staff_id );
			$staff_fullname = $user->get_fullname();
			return $staff_fullname;
		}

		public function get_package_ids(){
			$item = $this->data;
			$package_ids = maybe_unserialize( $item->package_ids );
			return $package_ids;
		}

		public function get_package_names(){
			$package_ids = $this->get_package_ids();
			$package_name_arr = array();
			$package_label_arr = array();
			$package_group_arr = array();

			if ( ! empty( $package_ids ) ) {
				foreach ( $package_ids as $key => $value ) {
					$package = obp_get_package( $value );
					if ( in_array( $package->get_belong_label(), $package_label_arr) ) {
						$find_key = array_search( $package->get_belong_label(), $package_label_arr );
						if ( $find_key !== false ) {
							$package_group_arr[$find_key]['package_ids'][] = $value;
						}
					} else {
						$package_label_arr[] = $package->get_belong_label();
						$package_group_arr[] = array(
							'label' 		=> $package->get_belong_label(),
							'package_ids' 	=> array($value)
						);
					}
				}
			}

			if ( ! empty( $package_group_arr ) ) {
				foreach ($package_group_arr as $key => $value) {
					$p_label = $value['label'];
					$p_package_ids = $value['package_ids'];
					$name_arr = [];
					if ( ! empty( $p_package_ids ) ) {
						foreach ($p_package_ids as $_key => $_value) {
							$package = obp_get_package( $_value );
							$name_arr[] = $package->get_name();
						}
					}
					$package_name_arr[] = '<strong>'.$p_label.': </strong>'.implode(', ', $name_arr);
				}
			}

			return implode(". ", $package_name_arr);
		}

		public function get_time(){
			$item = $this->data;

			$start_date 	= $item->start_date;
			$end_date 		= $item->end_date;
			$date_format 	= OBP()->settings->general->get('date_format','Y-m-d');
			$time_format 	= OBP()->settings->general->get( 'time_format', 'H:i' );

			$time = date_i18n( $date_format , $start_date).' '.date_i18n( $time_format, $start_date ).' - '.date_i18n( $time_format, $end_date );
			
			return $time;
		}

		public function get_start_date(){
			$item = $this->data;
			$start_date = $item->start_date;
			return $start_date;
		}

		public function get_end_date(){
			$item = $this->data;
			$end_date = $item->end_date;
			return $end_date;
		}

		public function get_taxes_line(){
			$item = $this->data;
			$taxes = maybe_unserialize( $item->taxes );
			$results = [];
			if ( $taxes ) {
				foreach ( $taxes as $tax_id => $tax_amount ) {
					$tax = obp_get_tax( $tax_id );
					$results[] = $tax->get_name().': '.obp_get_price_html( $tax_amount );
				}
			}

			return implode(', ', $results);
		}
	}
}