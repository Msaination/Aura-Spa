<?php

namespace BookPro\Payout;

defined( 'ABSPATH' ) || exit;



if ( ! class_exists("OBP_Payout_Method_Item") ) {
	

	class OBP_Payout_Method_Item {

		protected $id = null;

		public function __construct( $id ){
			$this->id = $id;
		}

		public function get_id(){
			$id = $this->id;
			return $id;
		}

		public function get_name(){
			$id = $this->get_id();
			$name = get_the_title( $id );
			return $name;
		}

		public function get_slug(){
			$id = $this->get_id();
			$slug = get_post_field('post_name', $id);
			return $slug;
		}

		public function get_setting_fields(){
			$id 			= $this->get_id();
			$labels 		= get_post_meta( $id, OBP_METABOX.'label', true );
			$placeholders 	= get_post_meta( $id, OBP_METABOX.'placeholder', true );
			$keys 			= get_post_meta( $id, OBP_METABOX.'key', true );
			$requireds 		= get_post_meta( $id, OBP_METABOX.'required', true );

			$setting_fields = array();

			if ( ! empty( $labels ) ) {
				foreach ( $labels as $_key => $label ) {
					$placeholder 	= isset( $placeholders[$_key] ) ? $placeholders[$_key] : '';
					$key 			= isset( $keys[$_key] ) ? $keys[$_key] : '';
					$required 		= isset( $requireds[$_key] ) ? $requireds[$_key] : '';
					$item = array(
						'label' 		=> $label,
						'placeholder' 	=> $placeholder,
						'key' 			=> $key,
						'required' 		=> $required,
					);


					$setting_fields[] = $item;
				}
			}

			return $setting_fields;
		}
	}
}