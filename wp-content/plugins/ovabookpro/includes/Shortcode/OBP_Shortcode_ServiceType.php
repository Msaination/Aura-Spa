<?php
namespace BookPro\Shortcode;

use BookPro\Traits\SingletonTrait;
use BookPro\Service\OBP_Service;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Shortcode_ServiceType') ) {
	
	class OBP_Shortcode_ServiceType {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_types', array( &$this, 'add_shortcode' ) );
			add_filter( 'obp_body_class', array( &$this, 'add_class_body' ) );
			add_action( 'obp_frontend_scripts_loaded', array( &$this, 'load_scripts' ) );
		}

		public function add_class_body( $classes ){
			if ( is_obp_has_shortcode_page('obp_types') ) {
				$classes[] = 'obp-page';
			}
			return $classes;
		}

		public function load_scripts( $assets ){
			// Shortcode Search Ajax
			if ( is_obp_has_shortcode_page('obp_types') ) {
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

		public function add_shortcode( $atts ){
			$atts = shortcode_atts( array(
				'type_ids' 	=> "",
				'class' 	=> "",
			), $atts );

			$type_ids = ! empty( $atts['type_ids'] ) ? sanitize_text_field( $atts['type_ids'] ) : [];
			$class  = isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';

			if ( ! is_array( $type_ids ) ) {
				$type_ids = array_map('trim', explode(",", $type_ids) );
			}
			$services = OBP_Service::get_service_by_type( $type_ids );

			ob_start();
			?>
			<div class="obp-wrapper <?php echo esc_attr( $class ); ?>">
				<div class="service-wrap">
				<?php
				obp_get_template( 'shortcodes/service-type/service-section.php', array(
					'services' 	=> $services,
				) );
				?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}
	}
}