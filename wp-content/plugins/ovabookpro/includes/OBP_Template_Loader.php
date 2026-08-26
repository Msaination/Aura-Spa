<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;
use BookPro\Business\OBP_Business;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Template_Loader') ) {
	

	class OBP_Template_Loader {

		use SingletonTrait;

		public function __construct(){

			add_filter( 'template_include', array( $this, 'template_loader' ) );
		}

		public function template_loader( $template ){
			global $wp;

			if ( is_singular( 'obp_business' ) ) {
				$args = OBP_Business::single_business_args();
				$template = obp_get_template( 'my-business/single-business.php', $args );
			}

			return $template;
		}
	}
}