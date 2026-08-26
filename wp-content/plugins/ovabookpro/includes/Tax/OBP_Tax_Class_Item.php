<?php

namespace BookPro\Tax;

defined( 'ABSPATH' ) || exit;

class OBP_Tax_Class_Item {

	protected $id = null;

	protected $term_obj = null;

	public function __construct( $term_id = null ){

		if ( $term_id ) {
			$this->id = $term_id;
			$this->term_obj = get_term( $term_id, 'obp_tax_classes' );
		}
	}

	public function get_id(){
		$id = $this->id;
		return $id;
	}

	public function get_default(){
		$default = get_term_meta( $this->get_id(), OBP_METABOX.'default', true );
		return $default;
	}

	public function get_name(){
		if ( $this->term_obj ) {
			return $this->term_obj->name;
		}
		return '';
	}

	public function get_slug(){
		if ( $this->term_obj ) {
			return $this->term_obj->slug;
		}
		return '';
	}

	public function get_term_group(){
		if ( $this->term_obj ) {
			return $this->term_obj->term_group;
		}

		return '';
	}

	public function get_term_taxonomy_id(){
		if ( $this->term_obj ) {
			return $this->term_obj->term_taxonomy_id;
		}
		return '';
	}

	public function get_taxonomy(){
		if ( $this->term_obj ) {
			return $this->term_obj->taxonomy;
		}
		return '';
	}

	public function get_description(){
		if ( $this->term_obj ) {
			return $this->term_obj->description;
		}
		return '';
	}

	public function get_parent(){
		if ( $this->term_obj ) {
			return $this->term_obj->parent;
		}
		return '';
	}

	public function get_count(){
		if ( $this->term_obj ) {
			return $this->term_obj->count;
		}
		return '';
	}

	public function get_filter(){
		if ( $this->term_obj ) {
			return $this->term_obj->filter;
		}
		return '';
	}

	public function get_meta(){
		if ( $this->term_obj ) {
			return $this->term_obj->meta;
		}
		return [];
	}

}