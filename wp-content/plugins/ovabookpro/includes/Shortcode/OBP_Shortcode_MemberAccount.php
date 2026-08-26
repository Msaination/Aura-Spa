<?php
namespace BookPro\Shortcode;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Shortcode_MemberAccount') ) {
	
	class OBP_Shortcode_MemberAccount {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_member_account', array( $this, 'add_shortcode' ) );
		}

		public function add_shortcode( $atts ){
			$atts = shortcode_atts( array(
				'class' => "",
			), $atts );
			
			$class 	= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';

			ob_start();
			obp_get_template( 'member-account/member-account.php', array( 'class' => $class ) );
			return ob_get_clean();
		}
	}
}