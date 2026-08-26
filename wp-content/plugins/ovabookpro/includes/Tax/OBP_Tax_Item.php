<?php

namespace BookPro\Tax;

defined( 'ABSPATH' ) || exit;

class OBP_Tax_Item {

	protected $id = null;

	protected $post = null;

	public function __construct( $id = null ){
		if ( $id ) {
			$this->id = $id;
			$this->post = get_post( $id );
		}
	}

	public function get_name(){
		if ( ! is_null( $this->post ) ) {
			return $this->post->post_title;
		}
		return '';
	}

	public function get_id(){
		return $this->id;
	}

	public function get_country_code(){
		$value = OBP()->get_post_meta( $this->get_id(), 'country_code' );
		return $value;
	}

	public function get_country(){
		$country_code = $this->get_country_code();
		$country = isset( obp_get_countries()[$country_code] ) ? obp_get_countries()[$country_code] : '*';
		return $country;
	}

	public function get_state_code(){
		$value = OBP()->get_post_meta( $this->get_id(), 'state_code' );
		return $value;
	}

	public function get_postcode_zip(){
		$value = OBP()->get_post_meta( $this->get_id(), 'postcode_zip' );
		return $value;
	}

	public function get_city(){
		$value = OBP()->get_post_meta( $this->get_id(), 'city' );
		return $value;
	}

	public function get_rate(){
		$value = OBP()->get_post_meta( $this->get_id(), 'rate' );
		return $value;
	}

	public function get_priority(){
		$value = OBP()->get_post_meta( $this->get_id(), 'priority', 1 );
		return $value;
	}
}