<?php

namespace BookPro;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'OBP_Assets', false ) ) {
	/**
	 * OBP_Assets class.
	 */
	class OBP_Assets {

		use SingletonTrait;
		/**
		 * Constructor.
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		}

		/**
		 * enqueue scripts
		 */
		public function load_scripts(){

			$settings = OBP()->settings->general;

			//date time settings
			$date_format = obp_get_date_format();
			$time_format = obp_get_time_format();
			$language 	 = obp_get_calendar_language();
			$weekend 	 = $settings->get('weekend',array( 'saturday', 'sunday' ));
			$first_day   = obp_get_first_day();

			do_action( 'obp_before_frontend_scripts_load', $this );

			// Load Libraries
			$this->load_libraries();

			// Load style theme
			$this->load_style_for_current_theme();
			
			// Frontend
			wp_enqueue_style( 'obp-frontend', OBP_PLUGIN_URI.'assets/css/frontend/style.css', 'all' );
			wp_enqueue_script( 'obp-frontend', OBP_PLUGIN_URI.'assets/js/frontend/script.js', array('jquery'), false, true );

			wp_localize_script( 'obp-frontend', 'ajax_object', array(
				'ajax_url' 		=> admin_url('admin-ajax.php?lang='.obp_get_current_language() ),
				'nonce' 		=> wp_create_nonce( 'obp_nonce' ),
			) );

			wp_localize_script( 'obp-frontend',
				'calendar_object',
				array(
					'date_format' => $date_format,
					'time_format' => $time_format,
					'language' 	  => $language,
					'weekend'     => $weekend,
					'first_day'   => $first_day,
				) 
			);

			// Member Account Scripts
			if ( is_obp_member_account_page() ) {
				$this->load_member_account_scripts();
			}

			if ( function_exists( 'is_checkout' ) && is_checkout() ) {
				// Countdown
				wp_enqueue_style( 'jquery-countdown' );
				wp_enqueue_script( 'jquery-countdown-plugin' );
				wp_enqueue_script( 'jquery-countdown' );

				wp_enqueue_script('order-countdown', OBP_PLUGIN_URI.'assets/js/frontend/countdown.js', array('jquery'), false, true );
			}

			do_action( 'obp_frontend_scripts_loaded', $this );
		}

		public function load_style_for_current_theme(){
			$current_theme = wp_get_theme();

			switch ( $current_theme->get_template() ) {
				case 'astra':
					wp_enqueue_style( 'obp-astra', OBP_PLUGIN_URI.'assets/css/frontend/themes/astra.css', 'all' );
					break;

				case 'storefront':
					wp_enqueue_style( 'obp-storefront', OBP_PLUGIN_URI.'assets/css/frontend/themes/storefront.css', 'all' );
					break;
				case 'twentytwentytwo':
					wp_enqueue_style( 'obp-twentytwentytwo', OBP_PLUGIN_URI.'assets/css/frontend/themes/twentytwentytwo.css', 'all' );
					break;
				case 'twentytwentythree':
					wp_enqueue_style( 'obp-twentytwentythree', OBP_PLUGIN_URI.'assets/css/frontend/themes/twentytwentythree.css', 'all' );
					break;
				case 'twentytwentyfour':
					wp_enqueue_style( 'obp-twentytwentyfour', OBP_PLUGIN_URI.'assets/css/frontend/themes/twentytwentyfour.css', 'all' );
					break;

				case 'twentytwentyfive':
					wp_enqueue_style( 'obp-twentytwentyfive', OBP_PLUGIN_URI.'assets/css/frontend/themes/twentytwentyfive.css', 'all' );
					break;
				case 'oceanwp':
					wp_enqueue_style( 'obp-oceanwp', OBP_PLUGIN_URI.'assets/css/frontend/themes/oceanwp.css', 'all' );
				break;
					
				default:
					break;
			}
		}

		public function register_scripts(){

			$settings = OBP()->settings->general;

			$datepicker_lang = obp_get_datepicker_language();

			$weekdays = apply_filters( 'obp_calendar_weekdays_name', array(
				__( 'Sun', 'ovabookpro' ),
				__( 'Mon', 'ovabookpro' ),
				__( 'Tue', 'ovabookpro' ),
				__( 'Wed', 'ovabookpro' ),
				__( 'Thu', 'ovabookpro' ),
				__( 'Fri', 'ovabookpro' ),
				__( 'Sat', 'ovabookpro' ),
			));

			$months_name = apply_filters( 'obp_calendar_months_name', array(
				__( 'January', 'ovabookpro' ),
				__( 'February', 'ovabookpro' ),
				__( 'March', 'ovabookpro' ),
				__( 'April', 'ovabookpro' ),
				__( 'May', 'ovabookpro' ),
				__( 'June', 'ovabookpro' ),
				__( 'July', 'ovabookpro' ),
				__( 'August', 'ovabookpro' ),
				__( 'September', 'ovabookpro' ),
				__( 'October', 'ovabookpro' ),
				__( 'November', 'ovabookpro' ),
				__( 'December', 'ovabookpro' ),
			) );

			wp_register_style( 'jquery-ui', OBP_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.min.css', 'all' );
			wp_register_script('jquery-blockUI', OBP_PLUGIN_URI.'assets/libs/jquery-blockUI/jquery.blockUI.js', array('jquery'), false, true );

			wp_register_style( 'zebra-dialog', OBP_PLUGIN_URI.'assets/libs/Zebra_Dialog/css/materialize/zebra_dialog.min.css', 'all' );
			wp_register_script('zebra-dialog', OBP_PLUGIN_URI.'assets/libs/Zebra_Dialog/zebra_dialog.min.js', array('jquery'), false, true );

			wp_register_style( 'obp-select2', OBP_PLUGIN_URI.'assets/libs/select2/css/select2.min.css', [], '4.1.0' );
			wp_register_script( 'obp-select2', OBP_PLUGIN_URI.'assets/libs/select2/js/select2.min.js', array('jquery'), '4.1.0', true );

			wp_register_style( 'obp-timepicker', OBP_PLUGIN_URI.'assets/libs/jquery-timepicker/jquery.timepicker.css', [], '1.3.5' );
			wp_register_script( 'obp-timepicker', OBP_PLUGIN_URI.'assets/libs/jquery-timepicker/jquery.timepicker.min.js', array('jquery'), '1.3.5', true );

			wp_register_style( 'flatpickr', OBP_PLUGIN_URI.'assets/libs/flatpickr/flatpickr.min.css');
			wp_register_script('flatpickr', OBP_PLUGIN_URI.'assets/libs/flatpickr/flatpickr.min.js', array('jquery'), false, true );
			wp_localize_script( 'flatpickr', 'obp_flatpickr_obj', array( 'lang' => $datepicker_lang ) );
			wp_register_script( 'flatpickr-localize', OBP_PLUGIN_URI.'assets/libs/flatpickr/l10n/'.$datepicker_lang.'.js', array('jquery'), false, true );

			wp_register_script('flatpickr-rangePlugin', OBP_PLUGIN_URI.'assets/libs/flatpickr/rangePlugin.js', array('jquery'), false, true );

			wp_register_style( 'tippy', OBP_PLUGIN_URI.'assets/libs/tippy/tippy.css' );
			wp_register_script('popper', OBP_PLUGIN_URI.'assets/libs/tippy/popper.min.js', array('jquery'), false, true );
			wp_register_script('tippy', OBP_PLUGIN_URI.'assets/libs/tippy/tippy-bundle.umd.min.js', array('jquery'), false, true );

			wp_register_style( 'jquery-countdown', OBP_PLUGIN_URI.'assets/libs/jquery-countdown/css/jquery.countdown.css', 'all' );
			wp_register_script('jquery-countdown-plugin', OBP_PLUGIN_URI.'assets/libs/jquery-countdown/js/jquery.plugin.min.js', array('jquery'), false, true );
			wp_register_script('jquery-countdown', OBP_PLUGIN_URI.'assets/libs/jquery-countdown/js/jquery.countdown.min.js', array('jquery'), false, true );
			wp_localize_script( 'jquery-countdown', 'obp_countdown_obj', array(
				'title' => esc_html__( 'Time left to pay', 'ovabookpro' )
			) );

			wp_register_style( 'owl-carousel', OBP_PLUGIN_URI.'assets/libs/owl-carousel/assets/owl.carousel.min.css', 'all' );
			wp_register_script( 'owl-carousel', OBP_PLUGIN_URI.'assets/libs/owl-carousel/owl.carousel.min.js', array('jquery'), false, true );

			wp_register_script('xregexp', OBP_PLUGIN_URI.'assets/libs/xregexp/xregexp-all.js', array('jquery'), false, true );

			wp_register_style( 'fancybox', OBP_PLUGIN_URI.'assets/libs/fancybox/fancybox.css', 'all' );
			wp_register_script( 'fancybox', OBP_PLUGIN_URI.'assets/libs/fancybox/fancybox.umd.js', array('jquery'), false, true );

			wp_register_script( 'jquery-collapse', OBP_PLUGIN_URI.'assets/libs/jquery-collapse/jquery.collapse.js', array('jquery'), false, true );

			wp_register_script('fullcalendar', OBP_PLUGIN_URI.'assets/libs/fullcalendar/index.global.min.js', array('jquery'), false, true);
			wp_register_script('fullcalendar-daygrid', OBP_PLUGIN_URI.'assets/libs/fullcalendar/daygrid/index.global.min.js', array('jquery'), false, true);
			wp_register_script('fullcalendar-timegrid', OBP_PLUGIN_URI.'assets/libs/fullcalendar/timegrid/index.global.min.js', array('jquery'), false, true);
			wp_register_script('fullcalendar-list', OBP_PLUGIN_URI.'assets/libs/fullcalendar/list/index.global.min.js', array('jquery'), false, true);


			wp_localize_script( 'fullcalendar', 'obp_fullcalendar', array(
				'weekdays' 		=> $weekdays,
				'months_name'	=> $months_name,
				'button_text' => array(
					'today' 	=> __( 'Today', 'ovabookpro' ),
					'year' 		=> __( 'Year', 'ovabookpro' ),
					'month' 	=> __( 'Month', 'ovabookpro' ),
					'week' 		=> __( 'Week', 'ovabookpro' ),
					'day' 		=> __( 'Day', 'ovabookpro' ),
					'list' 		=> __( 'List', 'ovabookpro' ),
				),
				'week' 		=> __( 'Week', 'ovabookpro' ),
				'all_day' 	=> __( 'All day', 'ovabookpro' ),
				'more' 		=> __( 'more', 'ovabookpro' ),
				'no_events' => __( 'No events to display', 'ovabookpro' ),
			) );

			wp_register_script( 'obp-booking', OBP_PLUGIN_URI.'assets/js/frontend/booking.js' , array('jquery'), false, true );
			wp_register_script( 'obp-checkout', OBP_PLUGIN_URI.'assets/js/frontend/checkout.js', array( 'jquery' ) );
			wp_localize_script( 'obp-checkout', 'obp_checkout_object', array(
					'full_name_req' 	=> esc_html__( 'Full Name is required.', 'ovabookpro' ),
					'phone_req' 		=> esc_html__( 'Phone number is required.', 'ovabookpro' ),
					'phone_invalid' 	=> esc_html__( 'Phone is not valid', 'ovabookpro' ),
					'email_req' 		=> esc_html__( 'Email is required.', 'ovabookpro' ),
					'email_invalid' 	=> esc_html__( 'Email is not valid', 'ovabookpro' ),
					'booking_success' 	=> esc_html__( 'Booking Success', 'ovabookpro' ),
					'booking_error' 	=> esc_html__( 'Booking Error', 'ovabookpro' ),
					'recaptcha_invalid' => esc_html__( 'Incorrect ReCaptcha validation', 'ovabookpro' ),
					'cancel_booking' 	=> esc_html__( 'Cancel Booking', 'ovabookpro' ),
					'discard_title' 	=> esc_html__( 'Discard booking?', 'ovabookpro' ),
					'discard_message' 	=> esc_html__( 'Are you sure to want to abort the booking process? Unsaved Changes will be lost', 'ovabookpro' ),
					'continue_booking'  => esc_html__( 'Continue booking', 'ovabookpro' ),
					'yes_discard' 		=> esc_html__( 'Yes, discard', 'ovabookpro' ),
					'confirm_cancel_booking' => esc_html__( 'Are you sure you want to cancel your booking? This cannot be undone.', 'ovabookpro' ),
				) );
			wp_register_script( 'obp-service-section', OBP_PLUGIN_URI.'assets/js/frontend/service-section.js', array('jquery'), false, true );
		}

		public function load_member_account_scripts(){
			$endpoint 		= OBP()->endpoint->get_current_endpoint();
			$endpoint_key 	= OBP()->endpoint->get_endpoint_key( $endpoint );

			// WP Media
			wp_enqueue_media();

			// Jquery UI
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			// Tooltipster
			wp_enqueue_style( 'tippy' );
			wp_enqueue_script( 'popper' );
			wp_enqueue_script( 'tippy' );


			do_action( 'obp_load_member_account_'.$endpoint_key.'_scripts', $this );
			
		}

		public function load_libraries(){

			$this->register_scripts();
			
			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_script('jquery-blockUI');

			// Flaticon: flaticon -> bookproicon - Avoid conflicts with themes that use flaticon
			wp_enqueue_style( 'obp-flaticon', OBP_PLUGIN_URI.'assets/libs/bookproicon/font/flaticon_bookpro.css' );

			wp_enqueue_style( 'tippy' );
			wp_enqueue_script( 'popper' );
			wp_enqueue_script( 'tippy' );

			// Select2
			wp_enqueue_style( 'obp-select2' );
			wp_enqueue_script( 'obp-select2' );

			// Owl Carousel
			wp_enqueue_style( 'owl-carousel' );
			wp_enqueue_script( 'owl-carousel' );

			// Xregexp
			wp_enqueue_script( 'xregexp' );

			// Fancybox
			wp_enqueue_style( 'fancybox' );
			wp_enqueue_script( 'fancybox' );

			wp_enqueue_script( 'jquery-collapse' );

		}

		public function load_map_js() {
			$enable_map 		= OBP()->settings->general->get('enable_map','yes');
			$map_platform       = OBP()->settings->general->get('map_platform', 'google_map');
			$google_api_key 	= obp_get_google_api_key();
			$bounds 			= OBP()->settings->general->get('bounds', '1');
			$lat 				= OBP()->settings->general->get( 'lat', 40.730610 );
			$lng 				= OBP()->settings->general->get( 'lng', -73.935242 );
			$radius 			= absint( OBP()->settings->general->get( 'radius', 100 ) );
			$restrictions 		= OBP()->settings->general->get('restrictions', [] );
			$google_map_lang 	= obp_get_google_map_lang();

			$map_object = array(
				'enable_map' 	=> $enable_map,
				'map_platform' 	=> $map_platform,
				'bounds' 		=> $bounds,
				'lat' 			=> $lat,
				'lng' 			=> $lng,
				'radius' 		=> $radius,
				'restrictions' 	=> $restrictions
			);

			// Maps
			if ( $enable_map == 'yes' ){
			
				if( $map_platform == 'google_map' ) { // load google map api 
					wp_enqueue_script( 'google','//maps.googleapis.com/maps/api/js?key='.$google_api_key.'&libraries=places,marker&v=weekly&v=beta&callback=Function.prototype&language='.$google_map_lang, array('jquery'), false, true);

				} else { // Leaflet libs for interactive maps
	    			wp_enqueue_style('leaflet', OBP_PLUGIN_URI.'assets/libs/leaflet/leaflet.css');
	    			wp_enqueue_script('leaflet', OBP_PLUGIN_URI.'assets/libs/leaflet/leaflet.js', array(), false, true);

	    			wp_enqueue_style( 'leaflet-autocomplete', OBP_PLUGIN_URI.'assets/libs/leaflet/dist/css/autocomplete.min.css', 'all' );
	    			wp_enqueue_script( 'leaflet-autocomplete', OBP_PLUGIN_URI.'assets/libs/leaflet/dist/js/autocomplete.min.js', false, true );
				}

			}

			wp_enqueue_script( 'obp-frontend-map', OBP_PLUGIN_URI.'assets/js/frontend/map.js', array('jquery'), false, true );
			wp_localize_script( 'obp-frontend-map', 'map_object', $map_object );

			
		}
	}

}