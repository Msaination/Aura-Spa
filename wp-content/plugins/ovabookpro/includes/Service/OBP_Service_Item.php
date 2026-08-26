<?php 
namespace BookPro\Service;
defined( 'ABSPATH' ) || exit;

use BookPro\Business\OBP_Business;
use BookPro\Tax\OBP_Tax_Class;
use BookPro\Tax\OBP_Tax;

if ( ! class_exists("OBP_Service_Item") ) {
	

	class OBP_Service_Item {

		protected $id = null;

		public $service = null;

		public function __construct( $service_id ){
			$this->id = $service_id;
			if ( get_post_type( get_post( $service_id ) ) == 'obp_service' ) {
				$this->service = get_post( $service_id );
			}
		}

		public function get_title(){
			$service_name = '';
			if ( $this->service ){
				$service_name = $this->service->post_title;
			}
			return apply_filters( 'obp_get_service_name', $service_name, $this );
		}

		public function get_id(){
			return $this->id;
		}

		public function get_staff_ids(){
			$id = $this->get_id();
			$staff_ids = OBP()->get_post_meta( $id, 'staff_ids', [] );
			return $staff_ids;
		}

		public function get_vendor_id(){
			$id = $this->get_id();
			$vendor_id = OBP()->get_post_meta( $id, 'vendor_id' );
			return $vendor_id;
		}

		public function get_business_id(){
			$vendor_id 		= $this->get_vendor_id();
			$business_id 	= OBP_Business::get_id( $vendor_id );
			return $business_id;
		}

		public function get_note_price(){
			$id = $this->get_id();
			$note_price = OBP()->get_post_meta( $id, 'note_price' );
			return $note_price;
		}

		public function get_type(){
			$id = $this->get_id();
			$type_id = OBP()->get_post_meta( $id, 'type' );
			return $type_id;
		}

		public function get_type_name(){
			$type_id = $this->get_type();
			$type = obp_get_type( $type_id );
			$type_name = '';
			if ( $type_id ) {
				$type_name = $type->get_name();
			}
			return $type_name;
		}

		public function get_price(){
			$id = $this->get_id();
			$price = OBP()->get_post_meta( $id, 'price', 0 );
			return $price;
		}

		public function get_price_type(){
			$id = $this->get_id();
			$price_type = OBP()->get_post_meta( $id, 'price_type', 'fixed' );
			return $price_type;
		}

		public function get_hours(){
			$id = $this->get_id();
			$hours = OBP()->get_post_meta( $id, 'hour', '0' );
			return $hours;
		}

		public function get_minutes(){
			$id = $this->get_id();
			$minutes = OBP()->get_post_meta( $id, 'minute', '0' );
			return $minutes;
		}

		public function get_description(){
			$description = '';
			if ( $this->service ) {
				$description = $this->service->post_content;
			}
			
			return apply_filters( 'obp_get_service_description', $description, $this );
		}

		public function get_tax_class(){
			$tax_class = OBP()->get_post_meta( $this->get_id(), 'tax_class', OBP_Tax_Class::get_tax_class_default() );
			return $tax_class;
		}

		public function get_rates(){
			$service_id = $this->get_id();
			$rates = OBP_Tax::get_matched_taxes( $service_id );
			return $rates;
		}

		public function get_color(){
			$id = $this->get_id();
			$color = OBP()->get_post_meta( $id, 'color', '#84C815' );
			return $color;
		}

		public function get_duration_text(){
			$hours 		  = $this->get_hours();
			$minutes 	  = $this->get_minutes();

			$duration 		= array();
			if ( $hours ) {
				// translators: %s: number of hours.
				$duration[] = sprintf( _n( '%s Hour', '%s Hours', $hours , 'ovabookpro' ), number_format_i18n( $hours ) );
			}
			if ( $minutes ) {
				// translators: %s: number of minutes.
				$duration[] = sprintf( _n( '%s Min', '%s Min', $minutes , 'ovabookpro' ), number_format_i18n( $minutes ) );
			}

			if ( empty( $duration ) ) {
				return '-';
			}

			return implode(" ", $duration);
		}

		public function get_short_duration_text(){
			$hours 		  = $this->get_hours();
			$minutes 	  = $this->get_minutes();

			$duration = $hours . esc_html__('h','ovabookpro') . ' ' . $minutes . esc_html__('min','ovabookpro');
							
			if( $hours == '0' ) {
				$duration = $minutes . esc_html__('min','ovabookpro');
			}
			if( $minutes == '0' ) {
				$duration = $hours . esc_html__('h','ovabookpro');
			}
			if( $hours == '0' && $minutes == '0' ) {
				$duration = esc_html__('-','ovabookpro');
			}

			return $duration;
		}

		public function get_use_on(){
			$id 	= $this->get_id();
			$val 	= OBP()->get_post_meta( $id, 'use_on', 'booking_date' );
			return $val;
		}

		public function get_duration(){
			$duration = 0;
			$duration_hours 	= absint( $this->get_hours() );
			$duration_minutes 	= absint( $this->get_minutes() );

			if ( $duration_hours ) {
				$duration += $duration_hours*60*60;
			}
			if ( $duration_minutes ) {
				$duration += $duration_minutes*60;
			}

			return $duration;
		}

		public function get_sale_price(){
			$id = $this->get_id();
			$decimal_separator 	= OBP()->settings->general->get('decimal_separator','.');
			$sale_price = OBP()->get_post_meta( $id, 'sale_price' );
			$sale_price = str_replace('.', $decimal_separator, $sale_price );
			return $sale_price;
		}

		public function get_sale_off_start_date(){
			$id = $this->get_id();
			$sale_off_start_date = OBP()->get_post_meta( $id, 'sale_off_start_date' );
			return $sale_off_start_date;
		}

		public function get_sale_off_end_date(){
			$id = $this->get_id();
			$sale_off_end_date = OBP()->get_post_meta( $id, 'sale_off_end_date' );
			return $sale_off_end_date;
		}

		public function get_sale_off_from(){
			$id = $this->get_id();
			$sale_off_from = OBP()->get_post_meta( $id, 'sale_off_from' );
			return $sale_off_from;
		}

		public function get_sale_off_to(){
			$id = $this->get_id();
			$sale_off_to = OBP()->get_post_meta( $id, 'sale_off_to' );
			return $sale_off_to;
		}

		public function get_packages(){
			$id = $this->get_id();
			$packages = OBP()->get_post_meta( $id, 'packages', [] );
			return $packages;
		}

		public function get_price_on_sale_off_date( $date_timestamp = null ){
			if ( empty( $date_timestamp ) ) {
				$date_timestamp = strtotime( gmdate("Y-m-d", current_time( 'timestamp' ) ) );
			}
			
			$sale_price 				= $this->get_sale_price();
			$sale_off_start_date 		= absint( $this->get_sale_off_start_date() );
			$sale_off_end_date 			= absint( $this->get_sale_off_end_date() );
			$use_on 					= $this->get_use_on();
			$regular_price 				= $this->get_price();
			$current_time 				= absint( current_time( 'timestamp' ) );
			$price = 0;

			if ( $sale_off_start_date && $sale_off_end_date ) {

				if ( $use_on == 'booking_date' ) {
					if ( $sale_off_start_date <= $current_time &&
					$current_time < absint( strtotime("+1 day", absint( $sale_off_end_date ) ) ) &&
					$sale_price && $sale_price < $regular_price ) {
						$price = $sale_price;
					}
				} else {
					if ( $sale_off_start_date <= absint( $date_timestamp ) &&
					absint( $date_timestamp ) < absint( strtotime("+1 day", absint( $sale_off_end_date ) ) ) &&
					$sale_price && $sale_price < $regular_price ) {
						$price = $sale_price;
					}
				}
			}

			return $price;
		}

		public function get_percent_sale_off(){
			$price_sale_off = $this->get_sale_price();
			$price = $this->get_price();
			$percent = 0;
			if ( $price_sale_off ) {
				$discount 	= (float)$price - (float)$price_sale_off;
				$percent 	= ceil( $discount / (float)$price * 100 );
			}
			return $percent;
		}

		public function get_price_specified_date( $date_timestamp = null ){
			$price_on_sale_off = $this->get_price_on_sale_off_date( $date_timestamp );
			$price = $this->get_price();
			if ( $price_on_sale_off ) {
				$price = $price_on_sale_off;
			}
			return $price;
		}

		public function get_price_specified_time( $timestamp = null ){
			$sale_off_start_date 	= absint( $this->get_sale_off_start_date() );
			$sale_off_end_date 		= absint( $this->get_sale_off_end_date() );
			$sale_off_from 			= $this->get_sale_off_from();
			$sale_off_to 			= $this->get_sale_off_to();
			$sale_off_start_time 	= 0;
			$sale_off_end_time 		= 0;
			$price 			= $this->get_price();
			$sale_price 	= $this->get_sale_price();
			$current_time 	= absint( current_time( 'timestamp' ) );
			$use_on 		= $this->get_use_on();
			if ( $sale_price ) {
				if ( $sale_off_start_date && $sale_off_end_date ) {
					$sale_off_start_time 	= $sale_off_start_date;
					$sale_off_end_time 		= strtotime("+1 day", absint( $sale_off_end_date ) );

					if ( $sale_off_from && $sale_off_to ) {
						$sale_off_start_time 	= strtotime( $sale_off_from, $sale_off_start_date );
						$sale_off_end_time 		= strtotime( $sale_off_to, $sale_off_end_date );
					}
				}
				if ( $use_on == 'booking_date' ) {
					if ( absint( $sale_off_start_time ) <= $current_time && $current_time < absint( $sale_off_end_time ) ) {
						$price = $sale_price;
					}
				} else {
					if ( absint( $sale_off_start_time ) <= absint( $timestamp ) && absint( $timestamp ) < absint( $sale_off_end_time ) ) {
						$price = $sale_price;
					}
				}
				
			}

			return $price;
		}

	}
}