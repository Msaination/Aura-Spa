<?php
namespace BookPro\Shortcode;

use BookPro\Traits\SingletonTrait;
use BookPro\Service\OBP_Service;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Shortcode_Vendor') ) {
	
	class OBP_Shortcode_Vendor {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_vendor', array( $this, 'add_shortcode' ) );

			add_action( 'obp_frontend_scripts_loaded', array( $this, 'load_scripts' ) );
		}

		public function load_scripts( $assets ){
			// Shortcode Services: show services to booking of each vendor
			if ( is_obp_has_shortcode_page('obp_vendor') ) {

				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-accordion' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style('zebra-dialog');
				wp_enqueue_script('zebra-dialog');

				// Countdown
				wp_enqueue_style( 'jquery-countdown' );
				wp_enqueue_script( 'jquery-countdown-plugin' );
				wp_enqueue_script( 'jquery-countdown' );

				wp_enqueue_script('order-countdown', OBP_PLUGIN_URI.'assets/js/frontend/countdown.js', array('jquery'), false, true );
				wp_enqueue_script( 'obp-checkout' );
				
				// Booking
				wp_enqueue_script( 'obp-booking' );
				wp_enqueue_script( 'obp-service-section' );
			}
		}

		public function add_shortcode( $atts = [] ){
			$atts = shortcode_atts( array(
				'vendor_id' => "",
				'class' 	=> "",
			), $atts );

			$vendor_id 	= isset( $atts['vendor_id'] ) ? sanitize_text_field( $atts['vendor_id'] ) : '';
			$user 		= obp_get_user( $vendor_id );
			$class 		= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';
			$services 	= OBP_Service::get_list_service_ajax( $vendor_id );

			$args = apply_filters( 'obp_shortcode_vendor_args', array(
				'vendor_id' => $vendor_id,
				'user' 		=> $user,
				'services' 	=> $services,
			) );
			
			ob_start();
			?>
			
			<div class="obp-wrapper shortcode-wrapper <?php echo esc_attr( $class ); ?>">
				<?php obp_get_template( 'shortcodes/vendor/services.php', $args ); ?>
			</div>

			<?php 
			return ob_get_clean();
		}
	}
}