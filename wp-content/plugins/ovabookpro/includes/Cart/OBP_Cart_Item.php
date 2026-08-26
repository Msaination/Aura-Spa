<?php
namespace BookPro\Cart;

use BookPro\Tax\OBP_Tax;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Cart_Item") ) {
	

	class OBP_Cart_Item {

		protected $data = array();

		public function __construct( $data = array() ){
			$this->data = $data;
		}

		public function get_service_id(){
			$data = $this->data;
			$service_id = isset( $data['service_id'] ) ? $data['service_id'] : '';
			return $service_id;
		}

		public function get_vendor_id(){
			$data = $this->data;
			$vendor_id = isset( $data['vendor_id'] ) ? $data['vendor_id'] : '';
			return $vendor_id;
		}

		public function get_service_name(){
			$data = $this->data;
			$service_id = isset( $data['service_id'] ) ? $data['service_id'] : '';

			$service = obp_get_service( $service_id );
			$service_name = $service->get_title();
			return $service_name;
		}

		public function get_package_ids(){
			$data = $this->data;
			$package_ids = isset( $data['package_ids'] ) ? $data['package_ids'] : [];
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

		public function get_price(){
			$data = $this->data;
			$price = isset( $data['price'] ) ? $data['price'] : '';
			return $price;
		}

		public function get_rates(){
			$service_id = $this->get_service_id();
			$rates = OBP_Tax::get_matched_taxes( $service_id );
			return $rates;
		}

		public function get_total_tax(){
			$price 		= $this->get_price();
			$rates 		= $this->get_rates();
			$price_includes_tax = OBP()->settings->tax->get( 'prices_include_tax', 'no' );
			$total_tax 	= array_sum( OBP_Tax::calc_tax( $price, $rates, $price_includes_tax ) );
			return apply_filters( 'obp_cart_item_total_tax', $total_tax );
		}

		public function get_data_taxes(){
			$price_includes_tax = OBP()->settings->tax->get( 'prices_include_tax', 'no' );
			$rates = $this->get_rates();
			$taxes = array();
			$regular_rates = [];
			$price = $this->get_price();
			$decimals = OBP()->settings->general->get('price_num_decimals', 2);
			if ( $rates ) {
				foreach ( $rates as $key => $obj ) {
					$tax_rate = $obj->get_rate();
					$regular_rates[$obj->get_id()] = (float)$tax_rate;
				}
			}

			if ( $price_includes_tax == 'yes' ) {
				$regular_tax_rate = 1 + ( array_sum( $regular_rates ) / 100 );
				foreach ( $regular_rates as $key => $regular_rate ) {
					$the_rate       = ( $regular_rate / 100 ) / $regular_tax_rate;
					$net_price      = $price - ( $the_rate * $price );
					$tax_amount     = $price - $net_price;
					if ( ! isset( $taxes[$key] ) ) {
						$taxes[$key] = $tax_amount;
					} else {
						$taxes[$key] += $tax_amount;
					}
					
				}
			} else {
				foreach ( $regular_rates as $key => $regular_rate ) {
					$tax_amount = $price * ( $regular_rate / 100 );
					if ( ! isset( $taxes[$key] ) ) {
						$taxes[$key] = $tax_amount;
					} else {
						$taxes[$key] += $tax_amount;
					}
				}
			}

			$taxes = array_map( function( $value ) use ( $decimals ) {
				return round($value, $decimals );
			}, $taxes );
			
			return apply_filters( 'obp_cart_item_taxes' ,$taxes, $price );
		}

		public function get_duration(){
			$data = $this->data;
			$duration = isset( $data['duration'] ) ? $data['duration'] : '';
			return $duration;
		}

		public function get_start_date(){
			$data = $this->data;
			$start_date = isset( $data['start_date'] ) ? $data['start_date'] : '';
			return $start_date;
		}

		public function get_time(){
			$start_date 	= $this->get_start_date();
			$end_date 		= $this->get_end_date();
			$time_format 	= OBP()->settings->general->get( 'time_format', 'H:i' );
			$time 			= date_i18n( $time_format, $start_date ).' - '.date_i18n( $time_format, $end_date );
			return $time;
		}

		public function get_date(){
			$start_date 	= $this->get_start_date();
			$date_format 	= OBP()->settings->general->get( 'date_format', 'Y-m-d' );
			$date = date_i18n( $date_format, $start_date );
			return $date;
		}

		public function get_end_date(){
			$data 		= $this->data;
			$end_date 	= isset( $data['end_date'] ) ? $data['end_date'] : '';
			return $end_date;
		}

		public function get_staff_id(){
			$data 		= $this->data;
			$staff_id 	= isset( $data['staff_id'] ) ? $data['staff_id'] : '';
			return $staff_id;
		}

		public function get_business_id(){
			$data = $this->data;
			$business_id = isset( $data['business_id'] ) ? $data['business_id'] : '';
			return $business_id;
		}

		public function get_plan_id(){
			$data = $this->data;
			$plan_id = isset( $data['plan_id'] ) ? $data['plan_id'] : '';
			return $plan_id;
		}

	}
}