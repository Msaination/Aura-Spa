<?php
namespace BookPro\Admin;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'OBP_Admin_Assets', false ) ) {
	/**
	 * OBP_Admin_Assets class.
	 */
	class OBP_Admin_Assets {

		use SingletonTrait;
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}

		/**
		 * enqueue styles
		 */
		public function admin_styles() {
			// Select2
			wp_enqueue_style( 'obp-select2', OBP_PLUGIN_URI.'assets/libs/select2/css/select2.min.css', 'all' );
			// Jquery ui
			wp_enqueue_style( 'jquery-ui', OBP_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.min.css', 'all' );
			// Dialog
			wp_enqueue_style( 'zebra-dialog', OBP_PLUGIN_URI.'assets/libs/Zebra_Dialog/css/materialize/zebra_dialog.min.css', 'all' );

			// Admin
			wp_enqueue_style( 'obp-admin', OBP_PLUGIN_URI.'assets/css/admin/style.css', 'all' );
		}

		/**
		 * enqueue scripts
		 */
		public function admin_scripts() {

			global $pagenow;

			$currency_symbol 	= obp_get_currency_symbol();
			$currency_position 	= OBP()->settings->general->get('currency_position','left');
			$thousand_separator = OBP()->settings->general->get('thousand_separator',',');
			$decimal_separator 	= OBP()->settings->general->get('decimal_separator','.');
			$number_decimal 	= OBP()->settings->general->get('price_num_decimals','2');
			$datepicker_lang 	= obp_get_datepicker_language();

			$settings = OBP()->settings->general;

			//date time settings
			$date_format = obp_get_date_format();
			$time_format = obp_get_time_format();
			$language 	 = obp_get_calendar_language();
			$weekend 	 = $settings->get('weekend',array( 'saturday', 'sunday' ));
			$first_day   = obp_get_first_day();

			$currency_object = array(
				'currency_symbol' 		=> $currency_symbol,
				'currency_position' 	=> $currency_position,
				'thousand_separator' 	=> $thousand_separator,
				'decimal_separator' 	=> $decimal_separator,
				'number_decimal' 		=> $number_decimal,
			);

			wp_enqueue_media();

			wp_enqueue_code_editor(array(
                'type'       => 'text/css',
            ));

			wp_enqueue_script( 'jquery-ui-accordion' );

			wp_enqueue_script('jquery-blockUI', OBP_PLUGIN_URI.'assets/libs/jquery-blockUI/jquery.blockUI.js', array('jquery'), false, true );
			// Select2
			wp_enqueue_script( 'obp-select2', OBP_PLUGIN_URI.'assets/libs/select2/js/select2.min.js', array('jquery'), '4.1.0', true );
			// Dialog
			wp_enqueue_script('zebra-dialog', OBP_PLUGIN_URI.'assets/libs/Zebra_Dialog/zebra_dialog.min.js', array('jquery'), false, true );

			wp_enqueue_style( 'flatpickr', OBP_PLUGIN_URI.'assets/libs/flatpickr/flatpickr.min.css');
			wp_enqueue_script('flatpickr', OBP_PLUGIN_URI.'assets/libs/flatpickr/flatpickr.min.js', array('jquery'), false, true );
			wp_localize_script( 'flatpickr', 'obp_flatpickr_obj', array( 'lang' => $datepicker_lang ) );
			wp_enqueue_script( 'flatpickr-localize', OBP_PLUGIN_URI.'assets/libs/flatpickr/l10n/'.$datepicker_lang.'.js', array('jquery'), false, true );

			wp_enqueue_script('flatpickr-rangePlugin', OBP_PLUGIN_URI.'assets/libs/flatpickr/rangePlugin.js', array('jquery'), false, true );

			wp_enqueue_style( 'obp-flaticon', OBP_PLUGIN_URI.'assets/libs/bookproicon/font/flaticon_bookpro.css' );

			// Admin
			wp_enqueue_script( 'obp-admin', OBP_PLUGIN_URI.'assets/js/admin/script.min.js', array('jquery'), false, true );
			wp_localize_script( 'obp-admin', 'currency_object', $currency_object );
			wp_localize_script( 'obp-admin', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'obp_nonce' ) ) );
			wp_localize_script( 'obp-admin',
				'calendar_object',
				array(
					'date_format' => $date_format,
					'time_format' => $time_format,
					'language' 	  => $language,
					'weekend'     => $weekend,
					'first_day'   => $first_day,
				) 
			);

			wp_localize_script( 'obp-admin', 'obp_message', array(
				'tax_classes' 		=> esc_html__( 'Tax Classes', 'ovabookpro' ),
				'payout_accounts' 	=> esc_html__( 'Payout Accounts', 'ovabookpro' ),
				'payout_methods' 	=> esc_html__( 'Payout Methods', 'ovabookpro' ),
			) );

		

			if ( ( $pagenow == 'edit.php' ) && isset( $_GET['post_type'] ) && ( sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) == 'obp_business') ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$my_business = OBP()->settings->endpoint->get( 'my_business', 'my-business' );
				$my_business_url = OBP()->endpoint->get_endpoint_url( $my_business, '', obp_member_account_url() );

				wp_enqueue_script( 'obp_business_admin', OBP_PLUGIN_URI.'assets/js/admin/business.js', array('jquery'), false, true );
				wp_localize_script( 'obp_business_admin', 'business_admin_obj', array(
					'add_new' 		=> esc_html__( 'Add new business', 'ovabookpro' ),
					'add_new_url' 	=> esc_url( $my_business_url )
				) );
			}
		}
	}
}