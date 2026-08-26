<?php
namespace BookPro\Calendar;
use BookPro\Order\OBP_Order_Meta;
use BookPro\Order\OBP_Order;

defined( 'ABSPATH' ) || exit;


class OBP_Google_Calendar {

	protected static $_instance = null;

	public function __construct(){

		
		add_action( 'obp_order_info_footer', array( $this, 'obp_google_calendar_button' ) );
		add_action( 'obp_before_frontend_scripts_load', array( $this, 'load_google_calendar_script' ) );
		add_action( 'obp_frontend_scripts_loaded', array( $this, 'load_google_calendar_event_script' ) );
		add_action( 'wp_footer', array( $this, 'load_library' ), 99 );

		add_action( 'obp_order_actions', array( $this, 'obp_google_calendar_btn_popup' ) );

		
		$hooks = array(
			'obp_google_calendar_get_events',
			'obp_add_to_calendar_popup',
			'obp_order_calendar_add_events',
			'obp_ical_get_events',
			'obp_order_ical_add_events',
		);

		foreach($hooks as $val){
			add_action( 'wp_ajax_'.$val, array( $this, $val ) );
			add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
		}

	}

	public function obp_order_ical_add_events(){
		$response = [];

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
			$end_date = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';
			// convert to timestamp
			$start_date_time = strtotime( $start_date );
			$end_date_time = strtotime( $end_date );

			$order_ids = OBP_Order::get_ids_order_processing_beetween( $start_date_time, $end_date_time );

			if ( ! empty( $order_ids ) ) {
				foreach ( $order_ids as $order_id ) {
					$order = obp_get_order( $order_id );
					$order_items 	= OBP_Order_Meta::get_order_items( $order_id );
					$business_id 	= $order->get_business_id();
					$business 		= obp_get_business( $business_id );
					$full_address 	= $business->get_full_address();

					if ( ! empty( $order_items ) ) {
						foreach ($order_items as $item) {
							$order_item 	= obp_get_order_meta( $item );
							$service_id 	= $order_item->get_service_id();
							$service 		= obp_get_service( $service_id );
							$service_name 	= $service->get_title();
							$service_desc 	= $service->get_description();
							$staff_name 	= $order_item->get_staff_name();
							$_start_date 	= $order_item->get_start_date();
							$_end_date 		= $order_item->get_end_date();

							$start_date_time 	= gmdate("Y-m-d H:i:s", $_start_date);
							$end_date_time 		= gmdate("Y-m-d H:i:s", $_end_date);

							$summary = $service_name.' - '.$staff_name;

							$event = array(
								'summary' 		=> $summary,
								'location' 		=> $full_address,
								'description' 	=> $service_desc,
								'start' 		=> $start_date_time,
								'end' 			=> $end_date_time
							);

							$response[] = $event;
						}
					}
				}
			}
		}

		wp_send_json( $response );
	}

	public function obp_ical_get_events(){
		$response = [];

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
			$order_id 		= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			$order 			= obp_get_order( $order_id );
			$order_items 	= OBP_Order_Meta::get_order_items( $order_id );
			$business_id 	= $order->get_business_id();
			$business 		= obp_get_business( $business_id );
			$full_address 	= $business->get_full_address();

			if ( ! empty( $order_items ) ) {
				foreach ($order_items as $item) {
					$order_item 	= obp_get_order_meta( $item );
					$staff_name 	= $order_item->get_staff_name();
					$service_id 	= $order_item->get_service_id();
					$service 		= obp_get_service( $service_id );
					$service_name 	= $service->get_title();
					$service_desc 	= $service->get_description();
					$start_date 	= $order_item->get_start_date();
					$end_date 		= $order_item->get_end_date();

					$start_date_time 	= gmdate("Y-m-d H:i:s", $start_date);
					$end_date_time 		= gmdate("Y-m-d H:i:s", $end_date);

					$summary = $service_name.' - '.$staff_name;

					$event = array(
						'summary' 		=> $summary,
						'location' 		=> $full_address,
						'description' 	=> $service_desc,
						'start' 		=> $start_date_time,
						'end' 			=> $end_date_time
					);

					$response[] = $event;
				}
			}
		}


		wp_send_json( $response );
	}

	public function obp_order_calendar_add_events(){
		$response = [];

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
			$end_date 	= isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

			// convert to timestamp
			$start_date_time = strtotime( $start_date );
			$end_date_time = strtotime( $end_date );
			$current_user = wp_get_current_user();
			$my_email = $current_user->user_email;

			$order_ids = OBP_Order::get_ids_order_processing_beetween( $start_date_time, $end_date_time );

			if ( ! empty( $order_ids ) ) {
				foreach ( $order_ids as $order_id ) {
					$order = obp_get_order( $order_id );
					$order_items 	= OBP_Order_Meta::get_order_items( $order_id );
					$business_id 	= $order->get_business_id();
					$business 		= obp_get_business( $business_id );
					$full_address 	= $business->get_full_address();

					$timezone_string 	= wp_timezone_string();
					$customer_email 	= $order->get_customer_email();

					$reminders = array(
						'useDefault' => false,
						'overrides' => array(
							array(
								'method' 	=> 'email',
								'minutes' 	=> 24*60
							),
							array(
								'method' 	=> 'popup',
								'minutes' 	=> 10
							),
						)
					);

					if ( ! empty( $order_items ) ) {
						foreach ($order_items as $item) {
							$order_item 	= obp_get_order_meta( $item );
							$service_id 	= $order_item->get_service_id();
							$service 		= obp_get_service( $service_id );
							$service_name 	= $service->get_title();
							$service_desc 	= $service->get_description();
							$staff_name 	= $order_item->get_staff_name();
							$_start_date 	= $order_item->get_start_date();
							$_end_date 		= $order_item->get_end_date();
							$_start_date_time = str_replace(', ', 'T', gmdate("Y-m-d, H:i:s", $_start_date) ).$timezone_string;
							$_end_date_time = str_replace(', ', 'T', gmdate("Y-m-d, H:i:s", $_end_date) ).$timezone_string;
							$summary = $service_name.' - '.$staff_name;

							$event = array(
								'summary' => $summary,
								'location' => $full_address,
								'description' => $service_desc,
								'start' => array(
									'dateTime' => $_start_date_time,
									'timeZone' => $timezone_string,
								),
								'end' => array(
									'dateTime' => $_end_date_time,
									'timeZone' => $timezone_string,
								),
								'attendees' => array(
									array( 'email' => $my_email )
								),
								'reminders' => $reminders,
							);
							// Add event
							$response[] = $event;
						}
					}
				}
			}	
		}

		wp_send_json( $response );
	}

	public function obp_add_to_calendar_popup(){

		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
			obp_get_template( 'manage-booking/add-to-calendar.php' );
		}
		wp_die();
	}

	public function obp_google_calendar_btn_popup(){
		if ( obp_google_calendar_enabled() && obp_google_calendar_is_setup_complete() && is_obp_member_account_page() ) {
		?>
			<button type="button" class="obp_button" id="obp_add_to_calendar_popup">
				<?php esc_html_e( 'Add to calendar', 'ovabookpro' ); ?>
			</button>
		<?php
		}
	}

	public function obp_google_calendar_get_events(){
		$response = [];
		if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) , 'obp_nonce' ) ) {
			$order_id 		= isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			$order 			= obp_get_order( $order_id );
			$order_items 	= OBP_Order_Meta::get_order_items( $order_id );
			$business_id 	= $order->get_business_id();
			$business 		= obp_get_business( $business_id );
			$full_address 	= $business->get_full_address();

			$timezone_string 	= wp_timezone_string();
			$customer_email 	= $order->get_customer_email();

			$reminders = array(
				'useDefault' => false,
				'overrides' => array(
					array(
						'method' 	=> 'email',
						'minutes' 	=> 24*60
					),
					array(
						'method' 	=> 'popup',
						'minutes' 	=> 10
					),
				)
			);

			if ( ! empty( $order_items ) ) {
				foreach ($order_items as $item) {
					$order_item 	= obp_get_order_meta( $item );
					$staff_name 	= $order_item->get_staff_name();
					$service_id 	= $order_item->get_service_id();
					$service 		= obp_get_service( $service_id );
					$service_name 	= $service->get_title();
					$service_desc 	= $service->get_description();
					$start_date 	= $order_item->get_start_date();
					$end_date 		= $order_item->get_end_date();
					$start_date_time 	= str_replace(', ', 'T', gmdate("Y-m-d, H:i:s", $start_date) ).$timezone_string;
					$end_date_time 		= str_replace(', ', 'T', gmdate("Y-m-d, H:i:s", $end_date) ).$timezone_string;

					$summary = $service_name.' - '.$staff_name;

					$event = array(
						'summary' => $summary,
						'location' => $full_address,
						'description' => $service_desc,
						'start' => array(
							'dateTime' => $start_date_time,
							'timeZone' => $timezone_string,
						),
						'end' => array(
							'dateTime' => $end_date_time,
							'timeZone' => $timezone_string,
						),
						'attendees' => array(
							array( 'email' => $customer_email )
						),
						'reminders' => $reminders,
					);
					// Add event
					$response[] = $event;
				}
			}
		}

		wp_send_json( $response );
	}

	public function load_google_calendar_event_script(){
		if ( obp_google_calendar_enabled() && obp_google_calendar_is_setup_complete() && ( is_obp_thank_page() || is_obp_member_account_page() ) ) {
			wp_enqueue_script( 'google_calendar_event', OBP_PLUGIN_URI.'assets/js/frontend/google-calendar-event.js', array('jquery'), false, true );
		}
	}

	public function load_google_calendar_script(){
		if ( obp_google_calendar_enabled() && obp_google_calendar_is_setup_complete() && ( is_obp_thank_page() || is_obp_member_account_page() ) ) {

			wp_enqueue_style('zebra-dialog');
			wp_enqueue_script('zebra-dialog');
			wp_enqueue_script('google_calendar', OBP_PLUGIN_URI.'assets/js/frontend/google-calendar.js', array(), false , false );

			wp_enqueue_script( 'ics', OBP_PLUGIN_URI.'assets/libs/ics/ics.deps.min.js', array(), false, false );

			wp_localize_script( 'google_calendar', 'obp_google_calendar', array(
				'client_id' 	=> obp_get_google_client_id(),
				'api_key' 		=> obp_get_google_api_key(),
				'scopes' 		=> 'https://www.googleapis.com/auth/calendar',
				'discovery_doc' => 'https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest',
				'success_mess' 	=> esc_html__( 'Events added to Google Calendar.', 'ovabookpro' ),
				'required' 		=> esc_html__( 'Please select start and end date.', 'ovabookpro' ),
				'empty' 		=> esc_html__( 'No bookings currently in processing status.', 'ovabookpro' )
			) );
		}
	}

	public function load_library(){
		if ( obp_google_calendar_enabled() && obp_google_calendar_is_setup_complete() && ( is_obp_thank_page() || is_obp_member_account_page() ) ) {
			// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
		?>
			<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
	    	<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
		<?php
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
		}
	}

	public function obp_google_calendar_button( $order ){
		if ( obp_google_calendar_enabled() && obp_google_calendar_is_setup_complete() && ( is_obp_thank_page() || is_obp_member_account_page() ) ) {
		?>
			<a href="#" id="obp_add_google_calendar" class="obp_button" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
				<?php esc_html_e( 'Add Google Calendar', 'ovabookpro' ); ?>
			</a>

			<a href="#" id="obp_add_ical" class="obp_button" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
				<?php esc_html_e( 'Add Ical', 'ovabookpro' ); ?>
			</a>
		<?php
		}
	}

	/**
	 * Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}