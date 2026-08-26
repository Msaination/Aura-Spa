<?php

namespace BookPro\Tax;

defined( 'ABSPATH' ) || exit;


class OBP_Tax {

	public static function get_matched_taxes( $service_id ){

		$service 		= obp_get_service( $service_id );
		$business_id 	= $service->get_business_id();
		$tax_class 		= $service->get_tax_class();
		$business 		= obp_get_business( $business_id );
		$country 		= $business->get_country_code();
		$state 			= $business->get_state();
		$postcode 		= $business->get_postcode();
		$city 			= $business->get_city();

		$results 		= array();

		$args = array(
			'post_type' 		=> 'obp_tax',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'meta_key' 			=> OBP_METABOX.'priority',
			'order' 			=> 'ASC',
			'orderby' 			=> 'meta_value_num',
			'tax_query' 		=> array(
				'taxonomy' 		=> 'obp_tax_classes',
				'fields' 		=> 'term_id',
				'terms' 		=> $tax_class,
			),
			'fields' 			=> 'ids',
		);

		$first_query = $agrs['meta_query'] = array(
			'relation' => 'OR',
			array(
				'relation' => 'AND',
				array(
					'key' 	=> OBP_METABOX.'country_code',
					'value' => strtoupper($country)
				),
				array(
					'key' 	=> OBP_METABOX.'state_code',
					'value' => strtoupper($state)
				),
				array(
					'key' 	=> OBP_METABOX.'postcode',
					'value' => $postcode
				),
				array(
					'key' 	=> OBP_METABOX.'city',
					'value' => $city
				),
			),
			array(
				'relation' => 'AND',
				array(
					'key' 	=> OBP_METABOX.'country_code',
					'value' => strtoupper($country)
				),
				array(
					'key' 	=> OBP_METABOX.'state_code',
					'value' => strtoupper($state)
				),
				array(
					'key' 	=> OBP_METABOX.'postcode',
					'value' => $postcode
				),
			),
		);

		$second_query = array(
			'relation' => 'OR',
			array(
				'relation' => 'AND',
				array(
					'key' 	=> OBP_METABOX.'country_code',
					'value' => strtoupper($country)
				),
				array(
					'key' 	=> OBP_METABOX.'state_code',
					'value' => strtoupper($state)
				),
			),
			array(
				'key' 	=> OBP_METABOX.'country_code',
				'value' => strtoupper( $country )
			),
		);

			

		$first_query 	= get_posts( $args );
		$second_query 	= get_posts( $args );

		$priorities = [];

		if ( ! empty( $first_query ) ) {
			foreach ( $first_query as $post_id ) {
				$tax_obj = obp_get_tax( $post_id );
				if ( ! in_array( $tax_obj->get_priority() , $priorities ) ) {
					$results[] = $tax_obj;
					$priorities[] = $tax_obj->get_priority();
				}
				
			}
		}

		if ( ! empty( $second_query ) ) {
			foreach ( $second_query as $post_id ) {
				$tax_obj = obp_get_tax( $post_id );
				if ( ! in_array( $tax_obj->get_priority() , $priorities ) ) {
					$results[] = $tax_obj;
					$priorities[] = $tax_obj->get_priority();
				}
			}
		}

		return $results;
	}

	public static function calc_inclusive_tax( $price, $rates ){
		$taxes = array();
		$regular_rates = [];

		if ( $rates ) {
			foreach ( $rates as $key => $obj ) {
				$tax_rate = $obj->get_rate();
				if ( ! isset( $regular_rates[$key] ) ) {
					$regular_rates[$key] = (float)$tax_rate;
				} else {
					$regular_rates[$key] += (float)$tax_rate;
				}
				
			}
		}
		$regular_tax_rate = 1 + ( array_sum( $regular_rates ) / 100 );
		foreach ( $regular_rates as $key => $regular_rate ) {
			$the_rate       = ( $regular_rate / 100 ) / $regular_tax_rate;
			$net_price      = $price - ( $the_rate * $price );
			$tax_amount     = apply_filters( 'obp_price_inc_tax_amount', $price - $net_price, $key, $rates[$key], $price );
			if ( ! isset( $taxes[$key] ) ) {
				$taxes[$key] = $tax_amount;
			} else {
				$taxes[$key] += $tax_amount;
			}
			
		}

		/**
		 * Round all taxes to precision (4DP) before passing them back. Note, this is not the same rounding
		 * as in the cart calculation class which, depending on settings, will round to 2DP when calculating
		 * final totals. Also unlike that class, this rounds .5 up for all cases.
		 */
		$taxes = array_map( array( __CLASS__, 'round' ), $taxes );

		return $taxes;
	}

	public static function calc_exclusive_tax( $price, $rates ){
		$taxes = array();
		$price = (float) $price;

		if ( ! empty( $rates ) ) {
			foreach ( $rates as $key => $rate ) {
				$tax_rate = $rate->get_rate();
				$tax_amount = $price * ( floatval( $tax_rate ) / 100 );
				$tax_amount = apply_filters( 'obp_price_ex_tax_amount', $tax_amount, $key, $rate, $price ); // ADVANCED: Allow third parties to modify this rate.

				if ( ! isset( $taxes[ $key ] ) ) {
					$taxes[ $key ] = (float) $tax_amount;
				} else {
					$taxes[ $key ] += (float) $tax_amount;
				}
			}
		}

		/**
		 * Round all taxes to precision (4DP) before passing them back. Note, this is not the same rounding
		 * as in the cart calculation class which, depending on settings, will round to 2DP when calculating
		 * final totals. Also unlike that class, this rounds .5 up for all cases.
		 */
		$taxes = array_map( array( __CLASS__, 'round' ), $taxes );

		return $taxes;
	}

	public static function get_total_taxes( $taxes = array() ){
		return array_sum( $taxes );
	}

	public static function calc_tax( $price, $rates, $price_includes_tax  = 'no', $deprecated = false ){
		if ( $price_includes_tax == 'yes' ) {
			$taxes = self::calc_inclusive_tax( $price, $rates );
		} else {
			$taxes = self::calc_exclusive_tax( $price, $rates );
		}
		return apply_filters( 'obp_calc_tax', $taxes, $price, $rates, $price_includes_tax, $deprecated );
	}

	public static function round( $in ) {
		$decimals = OBP()->settings->general->get('price_num_decimals', 2);
		return apply_filters( 'obp_tax_round', round( $in, absint( $decimals ) ) );
	}
}