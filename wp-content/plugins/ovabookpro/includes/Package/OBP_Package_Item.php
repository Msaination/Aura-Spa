<?php
namespace BookPro\Package;

defined( 'ABSPATH' ) || exit;

class OBP_Package_Item {

	public $id = null;

	public $post = null;

	public function __construct( $id = null ){
		$this->id = $id;
		if ( $id ) {
			$this->post = get_post( $id );
		}
	}

	public function get_id(){
		$id = $this->id;
		return $id;
	}

	public function get_name(){
		$name = '';
		if ( $this->post ) {
			$name = $this->post->post_title;
		}
		return apply_filters( 'obp_get_package_name', $name, $this );
	}

	public function get_belong_label(){
		$id = $this->get_id();
		$label = OBP()->get_post_meta( $id, 'label' );
		return $label;
	}

	public function get_label(){
		$name 		= $this->get_name();
		$seconds 	= absint( $this->get_seconds() );
		$time 		= obp_timestamp_to_hour_minute( $seconds );
		$service_id = $this->get_service_id();
		$service 	= obp_get_service( $service_id );
		$price 		= obp_get_price_html( obp_show_price_cart( $this->get_price(), $service->get_rates() ) );
		$label_arr 	= array( $name, $time, $price );
		return implode(" - ", $label_arr);
	}

	public function get_seconds(){
		$seconds = OBP()->get_post_meta( $this->get_id(), 'seconds' );
		return absint( $seconds );
	}

	public function get_hours(){
		$hours = OBP()->get_post_meta( $this->get_id(), 'hours' );
		return absint( $hours );
	}

	public function get_minutes(){
		$minutes = OBP()->get_post_meta( $this->get_id(), 'minutes' );
		return $minutes;
	}

	public function get_price(){
		$price = OBP()->get_post_meta( $this->get_id(), 'price', 0 );
		return $price;
	}

	public function get_service_id(){
		$service_id = OBP()->get_post_meta( $this->get_id(), 'service_id' );
		return $service_id;
	}
}