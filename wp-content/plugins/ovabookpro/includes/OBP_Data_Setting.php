<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;
defined( 'ABSPATH' ) || exit;


class OBP_Data_Setting {

	use SingletonTrait;

	public $id;

	protected $data = array();

	protected $values = array();

	public function __construct(){
		$this->id = 'obp_settings';
		$this->data = get_option( $this->id , [] );
	}

	public function __get( $key ){
		$this->values = isset( $this->data[$key] ) ? $this->data[$key] : [];
		return $this;
	}

	public function get( $name, $default = '' ){
		if ( isset( $this->values[$name] ) && ! empty( $this->values[$name] ) ) {
			return $this->values[$name];
		}

		return $default;
	}

}