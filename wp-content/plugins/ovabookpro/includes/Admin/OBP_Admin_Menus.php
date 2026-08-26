<?php
namespace BookPro\Admin;


use BookPro\Traits\SingletonTrait;

use BookPro\Admin\OBP_Commission_Page;
use Bookpro\Commission\OBP_Commission;

defined( 'ABSPATH' ) || exit;

class OBP_Admin_Menus {

	use SingletonTrait;

	public function __construct(){

		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
	}


	public function add_menu_page(){
		global $menu, $submenu;

		add_menu_page( esc_html__( 'BookPro', 'ovabookpro' ), esc_html__( 'BookPro', 'ovabookpro' ), 'manage_options', 'bookpro', array( $this, 'get_homepage' ) , 'dashicons-calendar-alt', 25 );


		add_submenu_page( 'bookpro', esc_html__( 'All Businesses', 'ovabookpro' ), esc_html__( 'All Businesses', 'ovabookpro' ), 'manage_options', 'edit.php?post_type=obp_business' );

		add_submenu_page( 'bookpro', esc_html__( 'Categories', 'ovabookpro' ), esc_html__( 'Categories', 'ovabookpro' ), 'manage_options', 'edit-tags.php?taxonomy=business_cat&post_type=obp_business' );

		add_submenu_page( 'bookpro', esc_html__( 'Amenities', 'ovabookpro' ), esc_html__( 'Amenities', 'ovabookpro' ), 'manage_options', 'edit-tags.php?taxonomy=business_amenity&post_type=obp_business' );

		add_submenu_page( 'bookpro', esc_html__( 'Tags', 'ovabookpro' ), esc_html__( 'Tags', 'ovabookpro' ), 'manage_options', 'edit-tags.php?taxonomy=business_tag&post_type=obp_business' );

		add_submenu_page( 'bookpro', esc_html__( 'Bookings', 'ovabookpro' ), esc_html__( 'Bookings', 'ovabookpro' ), 'manage_options', 'edit.php?post_type=obp_order' );

		add_submenu_page(
			'bookpro', __( 'Commission', 'ovabookpro' ), __( 'Commission', 'ovabookpro' ), 'manage_options', 'obp_commission', array('BookPro\Admin\OBP_Commission_Page', 'submenu_page_callback') );


	    add_submenu_page( 'bookpro', esc_html__( 'Withdrawal', 'ovabookpro' ), esc_html__( 'Withdrawal', 'ovabookpro' ), 'manage_options', 'edit.php?post_type=obp_payout' );

	    add_submenu_page( 'bookpro', esc_html__( 'Payout', 'ovabookpro' ), esc_html__( 'Payout', 'ovabookpro' ), 'manage_options', 'edit.php?post_type=obp_payout_method' );

	    add_submenu_page( 'bookpro', esc_html__( 'Payout Info', 'ovabookpro' ), esc_html__( 'Payout Info', 'ovabookpro' ), 'manage_options', 'obp_payout_info', array( 'BookPro\Admin\OBP_Admin_Payout_Info', 'submenu_page_callback' ) );
	    

	    add_submenu_page( 'bookpro', esc_html__( 'Taxes', 'ovabookpro' ), esc_html__( 'Taxes', 'ovabookpro' ), 'manage_options', 'edit.php?post_type=obp_tax' );


	    do_action( 'ovabookpro_add_menu_page' );


	    add_submenu_page(
			'bookpro',
	        esc_html__( 'Settings', 'ovabookpro' ),
	        esc_html__( 'Settings', 'ovabookpro' ),
	        'manage_options',
	        'obp_settings',
	        array( 'BookPro\Admin\OBP_Admin_Settings', 'view_setting' ),
	    );

		if ( isset( $submenu['bookpro'][0] ) ) {
			$submenu['bookpro'][0][0] = esc_html__('Home', 'ovabookpro');
			$submenu['bookpro'][0][3] = esc_html__('Home', 'ovabookpro');
		}

		do_action( 'ovabookpro_custom_submenu' );

	}

	public function get_homepage(){
	?>
	<div class="wrap ovabookpro-home-page">
		<div class="ovabookpro-container">
			<div class="ovabookpro-head">
				<div class="ovabookpro-header">
					<div class="logo">
						<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/logo.png' ); ?>" alt="<?php esc_attr_e( 'BRW', 'ovabookpro' ); ?>">
					</div>
				</div>
			</div>
			<div class="ovabookpro-body">
				<h2 class="ovabookpro-heading">
					<?php esc_html_e( 'Home', 'ovabookpro' ); ?>
				</h2>
				<div class="boxes">
					<a href="https://ova-themes.gitbook.io/bookpro" rel="nofollow" target="_blank">
						<div class="item-box document">
							<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/document.svg' ); ?>" alt="<?php esc_attr_e( 'Documentation', 'ovabookpro' ); ?>">
							<h3 class="item-title">
								<?php esc_html_e( 'Documentation', 'ovabookpro' ); ?>
							</h3>
						</div>
					</a>
					<a href="https://ovatheme.ticksy.com/" rel="nofollow" target="_blank">
						<div class="item-box supports">
							<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/support.svg' ); ?>" alt="<?php esc_attr_e( 'Supports', 'ovabookpro' ); ?>">
							<h3 class="item-title">
								<?php esc_html_e( 'Supports', 'ovabookpro' ); ?>
							</h3>
						</div>
					</a>
					<a href="https://www.youtube.com/watch?v=KAEjBy1EDjI" rel="nofollow" target="_blank">
						<div class="item-box videos">
							<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/youtube.svg' ); ?>" alt="<?php esc_attr_e( 'Videos', 'ovabookpro' ); ?>">
							<h3 class="item-title">
								<?php esc_html_e( 'Videos', 'ovabookpro' ); ?>
							</h3>
						</div>
					</a>
				</div>
				<div class="overview">
					<h2 class="ovabookpro-heading">
						<?php esc_html_e( 'Overview', 'ovabookpro' ); ?>
					</h2>
					<p class="description">
						<?php esc_html_e( 'BookPro is an “All-in-One” WordPress Appointment Booking plugin designed for a wide range of businesses, including barber shops, hair salons, spa, nail studios, tuition classes, sports facility rentals, rental services, repair workshops, therapists, medical clinics, lifestyle and care services, and any business that requires appointment scheduling and booking.', 'ovabookpro' ); ?>
					</p>
				</div>
				<div class="related-items">
					<h2 class="ovabookpro-heading">
						<?php esc_html_e( 'Our Themes and Add-ons', 'ovabookpro' ); ?>
					</h2>
					<ul class="items">
						<li class="item">
							<a href="https://codecanyon.net/item/multi-language-addon-for-bookpro-plugin/58307234" class="name" target="_blank">
								<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/multi-language.png' ); ?>" alt="<?php esc_attr_e( 'Multi Language', 'ovabookpro' ); ?>">
								<p class="name">
									<?php esc_html_e( 'Multi Language Add-on for BookPro Plugin', 'ovabookpro' ); ?>
								</p>
							</a>
						</li>
						<li class="item">
							<a href="https://codecanyon.net/item/vendor-addon-for-bookpro-plugin-wordpress/57724868" class="name" target="_blank">
								<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/vendor.png' ); ?>" alt="<?php esc_attr_e( 'Vendor', 'ovabookpro' ); ?>">
								<p class="name">
									<?php esc_html_e( 'Vendor Add-on for BookPro Plugin WordPress', 'ovabookpro' ); ?>
								</p>
							</a>
						</li>
						<li class="item">
							<a href="https://codecanyon.net/item/paypal-payments-standard-addon-for-bookpro-plugin/58018310" class="name" target="_blank">
								<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/paypal.png' ); ?>" alt="<?php esc_attr_e( 'PayPal', 'ovabookpro' ); ?>">
								<p class="name">
									<?php esc_html_e( 'PayPal Payments Standard Add-on for BookPro Plugin', 'ovabookpro' ); ?>
								</p>
							</a>
						</li>
						<li class="item">
							<a href="https://codecanyon.net/item/stripe-payment-addon-for-bookpro-plugin/58088808" class="name" target="_blank">
								<img src="<?php echo esc_url( OBP_PLUGIN_URI . 'assets/img/preview/stripe.png' ); ?>" alt="<?php esc_attr_e( 'Stripe', 'ovabookpro' ); ?>">
								<p class="name">
									<?php esc_html_e( 'Stripe Payment Add-on for BookPro Plugin', 'ovabookpro' ); ?>
								</p>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<?php
	}
}