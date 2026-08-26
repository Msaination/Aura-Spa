<?php
namespace BookPro\Shortcode;

use BookPro\Traits\SingletonTrait;
use BookPro\Order\OBP_Order;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists('OBP_Shortcode_OrderInfo') ) {
	

	class OBP_Shortcode_OrderInfo {

		use SingletonTrait;

		public function __construct(){
			add_shortcode( 'obp_booking_info', array( $this, 'add_shortcode' ) );
			add_filter( 'obp_body_class', array( &$this, 'add_class_body' ) );
		}

		public function add_class_body( $classes ){
			if ( is_obp_has_shortcode_page('obp_booking_info') ) {
				$classes[] = 'obp-page';
			}
			return $classes;
		}

		public function add_shortcode( $atts = [] ){
			$atts = shortcode_atts( array(
				'class' 	=> "",
			), $atts );

			$class 	= isset( $atts['class'] ) ? sanitize_text_field( $atts['class'] ) : '';
			$key 		= isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$under_pos 	= strpos($key, '_');
			if ( $under_pos !== false ) {
			$order_id = substr($key, 0, $under_pos );
			ob_start();
			$order = OBP_Order::get_order_by_id( $order_id );

			if ( $order && $order->get_key() == $key ) {
				?>
				<div class="obp-wrapper obp_order_info_wrapper <?php echo esc_attr( $class ); ?>">
					<?php obp_get_template( 'shortcodes/order-info/order-info.php', array( 'order' => $order ) ); ?>
				</div>
				<?php
				}
			}
			return ob_get_clean();
		}
	}
}