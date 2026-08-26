<?php

namespace BookPro\Tax;

defined( 'ABSPATH' ) || exit;

class OBP_Tax_Class {

	public static function get_tax_classes(){
		$args = array(
			'taxonomy'   => 'obp_tax_classes',
			'hide_empty' => false,
		);

		$tax_classes = get_terms( $args );
		return $tax_classes;
	}

	public static function get_tax_class_default(){
		$args = array(
			'taxonomy'   => 'obp_tax_classes',
			'hide_empty' => false,
			'meta_query' => array(
				'key' 	=> OBP_METABOX.'default',
				'value' => 1,
			),
			'fields' => 'ids',
			'number' => 1,
		);
		$tax_classes = get_terms( $args );
		if ( isset( $tax_classes[0] ) ) {
			return $tax_classes[0];
		}
		return '';
	}
}