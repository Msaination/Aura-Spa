<?php defined( 'ABSPATH' ) || exit;

if ( ! function_exists('obp_template_content') ) {
	function obp_template_content(){
		global $wp;
 		$current_endpoint = OBP()->endpoint->get_current_endpoint();
		if ( ! empty( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $key => $value ) {
				// Ignore pagename param.
				if ( 'pagename' === $key ) {
					continue;
				}

				if ( has_action( 'obp_account_' . $key . '_endpoint' ) ) {
					do_action( 'obp_account_' . $key . '_endpoint', $value );
					return;
				}
			}
		}

		$endpoint_key = OBP()->endpoint->get_endpoint_key( $current_endpoint );
		// Fix my business page
		$args = array(
			'current_user' => get_user_by( 'id', get_current_user_id() ),
		);

		if ( $endpoint_key == 'my-business' ) {
			$args = BookPro\Business\OBP_Business::my_business_args();
		}
		obp_get_template(
			$endpoint_key.'/'.$endpoint_key.'.php',
			$args
		);
	}
}

add_filter( 'document_title', 'obp_page_endpoint_title' );
if ( ! function_exists('obp_page_endpoint_title') ) {
	function obp_page_endpoint_title( $title ){
		global $wp_query;
		if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && is_page() && is_obp_member_account_page() ) {
			$endpoint       = OBP()->endpoint->get_current_endpoint();
			$endpoint_title = OBP()->endpoint->get_endpoint_title( $endpoint );
			$title          = $endpoint_title ? $endpoint_title : $title;

			remove_filter( 'document_title', 'obp_page_endpoint_title' );
		}

		return $title;
	}
}

add_action( 'obp_account_my-business_endpoint', 'obp_account_my_business' );
if ( ! function_exists('obp_account_my_business') ) {
	function obp_account_my_business(){
		$args = BookPro\Business\OBP_Business::my_business_args();
		obp_get_template( 'my-business/my-business.php', $args );
	}
}

add_action( 'obp_account_my-wallet_endpoint', 'obp_account_manage_wallet' );
if ( ! function_exists('obp_account_manage_wallet') ) {
	function obp_account_manage_wallet(){
		obp_get_template( 'manage-wallet/manage-wallet.php' );
	}
}

add_action( 'obp_account_manage-booking_endpoint', 'obp_account_manage_booking' );
if ( ! function_exists('obp_account_manage_booking') ) {
	function obp_account_manage_booking(){
		obp_get_template( 'manage-booking/manage-booking.php' );
	}
}

add_action( 'obp_account_manage-service_endpoint', 'obp_account_manage_service' );
if ( ! function_exists('obp_account_manage_service') ) {
	function obp_account_manage_service(){
		obp_get_template( 'manage-service/manage-service.php' );
	}
}

add_action( 'obp_account_manage-type_endpoint', 'obp_account_manage_type' );
if ( ! function_exists('obp_account_manage_type') ) {
	function obp_account_manage_type(){
		obp_get_template( 'manage-type/manage-type.php' );
	}
}

add_action( 'obp_account_manage-plan_endpoint', 'obp_account_manage_plan' );
if ( ! function_exists('obp_account_manage_plan') ) {
	function obp_account_manage_plan(){
		obp_get_template( 'manage-plan/manage-plan.php' );
	}
}

add_action( 'obp_account_manage-staff_endpoint', 'obp_account_manage_staff' );
if ( ! function_exists('obp_account_manage_staff') ) {
	function obp_account_manage_staff(){
		obp_get_template( 'manage-staff/manage-staff.php' );
	}
}

add_action( 'obp_account_manage-coupon_endpoint', 'obp_account_manage_coupon' );
if ( ! function_exists('obp_account_manage_coupon') ) {
	function obp_account_manage_coupon(){
		obp_get_template( 'manage-coupon/manage-coupon.php' );
	}
}

add_action( 'obp_account_overall-schedule_endpoint', 'obp_account_overall_schedule' );
if ( ! function_exists('obp_account_overall_schedule') ) {
	function obp_account_overall_schedule(){
		obp_get_template( 'overall-schedule/overall-schedule.php' );
	}
}

add_action( 'obp_account_staff-schedule_endpoint', 'obp_account_staff_schedule' );
if ( ! function_exists('obp_account_staff_schedule') ) {
	function obp_account_staff_schedule(){
		obp_get_template( 'staff-schedule/staff-schedule.php' );
	}
}

add_action( 'obp_account_manage-role_endpoint', 'obp_account_manage_role' );
if ( ! function_exists('obp_account_manage_role') ) {
	function obp_account_manage_role(){
		obp_get_template( 'manage-role/manage-role.php' );
	}
}

add_action( 'obp_account_my-booking_endpoint', 'obp_account_my_booking' );
if ( ! function_exists('obp_account_my_booking') ) {
	function obp_account_my_booking(){
		obp_get_template( 'my-booking/my-booking.php' );
	}
}

add_action( 'obp_account_my-wishlist_endpoint', 'obp_account_my_wishlist' );
if ( ! function_exists('obp_account_my_wishlist') ) {
	function obp_account_my_wishlist(){
		obp_get_template( 'my-wishlist/my-wishlist.php' );
	}
}

add_action( 'obp_account_my-profile_endpoint', 'obp_account_my_profile' );
if ( ! function_exists('obp_account_my_profile') ) {
	function obp_account_my_profile(){
		obp_get_template( 'my-profile/my-profile.php' );
	}
}

add_action( 'obp_account_edit-staff_endpoint', 'obp_account_edit_staff' );
if ( ! function_exists('obp_account_edit_staff') ) { 
	function obp_account_edit_staff(){
		$args = BookPro\Staff\OBP_Staff::edit_staff_args();
		obp_get_template( 'manage-staff/edit-staff.php', $args );
	}
}


add_action( 'obp_account_edit-service_endpoint', 'obp_account_edit_service' );
if ( ! function_exists('obp_account_edit_service') ) { 
	function obp_account_edit_service(){
		$args = BookPro\Service\OBP_Service::edit_service_args();
		obp_get_template( 'manage-service/edit-service.php', $args );
	}
}

add_action( 'obp_account_edit-coupon_endpoint', 'obp_account_edit_coupon' );
if ( ! function_exists('obp_account_edit_coupon') ) {
	function obp_account_edit_coupon(){
		global $wp;
		$coupon_id = isset( $wp->query_vars['edit-coupon'] ) ? wp_unslash( $wp->query_vars['edit-coupon'] ) : '';
		$services = BookPro\Service\OBP_Service::get_category_service_groups();
		$coupon_item = obp_get_coupon( $coupon_id );
		$args = array(
			'services' 		=> $services,
			'coupon_item' 	=> $coupon_item,
			'coupon_id' 	=> $coupon_id,
		);
		obp_get_template( 'manage-coupon/edit-coupon.php', $args );
	}
}

if ( ! function_exists('obp_template_redirect') ) {
	function obp_template_redirect(){
		global $wp_query;

		if ( OBP()->endpoint->is_endpoint_url() && ! is_obp_member_account_page() ) {
			$wp_query->set_404();
			status_header( 404 );
			include get_query_template( '404' );
			exit;
		}

		if ( is_obp_member_account_page() ) {
			$endpoint 		= OBP()->endpoint->get_current_endpoint();
			$my_profile_ep 	= OBP()->endpoint->get_endpoint('my-profile');
			$capabilities  	= OBP()->endpoint->get_capabilities();
			$capability 	= $capabilities[$endpoint];


			if ( ! is_user_logged_in() ) {
				wp_safe_redirect( obp_login_url() );
				exit();
			}

			if ( isset( $wp_query->query_vars['logout'] ) ) {
				wp_logout();
				wp_safe_redirect( obp_login_url() );
				exit;
			}

			if ( ! empty( $capability ) && ! BookPro\OBP_Permission::user_can( $capability ) ) {

				$args = array(
					'response' 	=> 403,
					'link_text' => esc_html__( 'Go to My Profile page', 'ovabookpro' ),
					'link_url' 	=> OBP()->endpoint->get_endpoint_url( $my_profile_ep ),
				);

				wp_die( esc_html__( 'You do not have permission to access this page.', 'ovabookpro' ), esc_html__( 'Forbidden', 'ovabookpro' ), $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( get_query_var( $endpoint ) && ! is_numeric( get_query_var( $endpoint ) ) ) {
				$wp_query->set_404();
				status_header( 404 );
				include get_query_template( '404' );
				exit;
			}
			
		}
		
	}
}
add_action( 'template_redirect', 'obp_template_redirect' );


if ( ! function_exists('obp_show_current_user_attachments') ) {
	function obp_show_current_user_attachments( $query = array() ) {
		$user    = wp_get_current_user();
	    $user_id = $user->ID;
	    $roles   = $user->roles;

	    if ( in_array('vendor', $roles ) || in_array( 'staff', $roles ) ) {
	    	$query['author'] = $user_id;
	    }

	    return $query;
	}
}
add_filter( 'ajax_query_attachments_args', 'obp_show_current_user_attachments', 10, 1 );

if ( ! function_exists('obp_is_current_account_menu_item') ) {
	function obp_is_current_account_menu_item( $endpoint ){
		global $wp;
		$current = isset( $wp->query_vars[$endpoint] );
		if ( 'my-profile' === $endpoint && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
			$current = true; // Dashboard is not an endpoint, so needs a custom check.
		}

		return $current;
	}
}


// Not show title member account page
add_filter( 'the_title', 'obp_member_account_title', 10, 2 );
if ( ! function_exists('obp_member_account_title') ) {
	function obp_member_account_title( $post_title, $post_id  ){
		if ( ! is_admin() ) {
			$member_account_page_id = obp_member_account_page_id();
			if ( $member_account_page_id == $post_id ) {
				$post_title = '';
			}
		}
		return apply_filters( 'obp_member_account_title', $post_title, $post_id );
	}
}
// Fix member account page on nav menu item
add_filter( 'nav_menu_item_title', 'obp_member_account_menu_item_title', 10, 2 );
if ( ! function_exists('obp_member_account_menu_item_title') ) {
	function obp_member_account_menu_item_title( $title, $menu_item ){
		$member_account_page_id = obp_member_account_page_id();
		$post_id = get_post_meta( $menu_item->ID, '_menu_item_object_id', true );
		if ( $member_account_page_id == $post_id ) {
			$page = get_post( $post_id );
			$title 	= $page->post_title;
		}
		return apply_filters( 'obp_member_account_menu_item_title', $title, $menu_item );
	}
}

// Disable robots member account page
add_filter( 'wp_robots', 'obp_wp_robots' );
if ( ! function_exists('obp_wp_robots') ) {
	function obp_wp_robots( $array ){
		global $post;
		if ( is_page() ) {
			$member_account_page_id = obp_member_account_page_id();
			if ( $post->ID == $member_account_page_id ) {
				$array = array(
					'noindex' 	=> 1,
					'nofollow' 	=> 1,
				);
			}
		}
		
		return apply_filters( 'obp_wp_robots', $array );
	}
}


if ( ! function_exists('obp_show_dashboard_nav_menu_item') ) {
	function obp_show_dashboard_nav_menu_item(){
		$navigation_items 	= OBP()->endpoint->get_navigation_items();
		$capabilities  		= OBP()->endpoint->get_capabilities();
		$current_endpoint 	= OBP()->endpoint->get_current_endpoint();
		if ( ! empty( $navigation_items ) ) {
			foreach ($navigation_items as $key => $item ) {
				$child_endpoint = isset( $item['child_endpoint'] ) ? $item['child_endpoint'] : [];
				$class_active 	= $current_endpoint == $item['endpoint'] || in_array( $current_endpoint, $child_endpoint ) ? 'nav-active' : '';
				$class_item   = 'obp-nav-item';
				$attributes   = array( $class_item ,$item['class'], $class_active );
				$attributes   = array_filter( $attributes );
				$capability   = $capabilities[$item['endpoint']];

				if ( $item['endpoint'] == 'logout' || BookPro\OBP_Permission::user_can( $capability ) ) { ?>
					<li class="<?php echo esc_attr( implode(' ', $attributes ) ); ?>">
						<a href="<?php echo esc_url( $item['url'] ); ?>" class="nav-link">
							<i class="<?php echo esc_attr( $item['icon'] ); ?>"></i>
							<span class="text">
								<?php echo esc_html( $item['title'] ); ?>	
							</span>
						</a>
					</li>
				<?php }
			}
		}
	}
}


// add class to body: obp-page
if ( ! function_exists('obp_body_class') ) {
	function obp_body_class( $classes ){
		$classes = (array) $classes;

		if ( is_obp_member_account_page() || is_obp_login_page() || is_obp_register_user_page() ||
		is_obp_forgot_password_page() || is_obp_reset_password_page() ||
		is_singular( 'obp_business' ) || is_obp_has_shortcode_page('obp_vendor') ) {
			$classes[] = 'obp-page';
		}


		if ( is_obp_member_account_page() ) {
			$classes[] = 'obp-member-account';
		}

		if ( is_obp_login_page() ) {
			$classes[] = 'obp-login';
		}

		if ( is_obp_register_user_page() ) {
			$classes[] = 'obp-register-user';
		}

		if ( is_obp_forgot_password_page() ) {
			$classes[] = 'obp-forgot-password';
		}

		if ( is_obp_reset_password_page() ) {
			$classes[] = 'obp-reset-password';
		}

		if ( is_obp_member_account_page() ) {
		$endpoint = OBP()->endpoint->get_current_endpoint();

			foreach ( OBP()->endpoint->get_query_vars() as $key => $value ) {

				if ( OBP()->endpoint->is_endpoint_url( $key ) || $endpoint ==  $value ) {
					$classes[] = 'obp-' . sanitize_html_class( $key );
				}
			}
		}

		return apply_filters( 'obp_body_class', $classes );
	}
}
add_filter( 'body_class', 'obp_body_class' );


if ( ! function_exists('obp_single_business_gallery') ) {
	function obp_single_business_gallery( $args ){
		obp_get_template('my-business/single-business/gallery.php',$args);
	}
}

if ( ! function_exists('obp_single_business_info') ) {
	function obp_single_business_info( $args ){
		obp_get_template('my-business/single-business/info.php',$args);
	}
}

if ( ! function_exists('obp_single_business_services') ) {
	function obp_single_business_services( $args ){
		obp_get_template('my-business/single-business/services.php',$args);
	}
}

if ( ! function_exists('obp_single_business_our_work') ) {
	function obp_single_business_our_work( $args ){
		obp_get_template('my-business/single-business/our-work.php',$args);
	}
}

if ( ! function_exists('obp_single_business_amenities') ) {
	function obp_single_business_amenities( $args ){
		obp_get_template('my-business/single-business/amenities.php',$args);
	}
}

if ( ! function_exists('obp_single_business_tags') ) {
	function obp_single_business_tags( $args ){
		obp_get_template( 'my-business/single-business/tags.php', $args );
	}
}

if ( ! function_exists('obp_single_business_sidebar') ) {
	function obp_single_business_sidebar( $args ){
		obp_get_template('my-business/single-business/sidebar.php',$args);
	}
}

if ( ! function_exists('obp_single_business_portfolio') ) {
	function obp_single_business_portfolio( $args ){
		obp_get_template('my-business/single-business/all-our-works.php',$args);
	}
}

if ( ! function_exists('obp_my_business_infomation') ) {
	function obp_my_business_infomation( $args ){
		obp_get_template('my-business/parts/infomation.php', $args );
	}
}

if ( ! function_exists('obp_my_business_work_hours') ) {
	function obp_my_business_work_hours( $args ){
		obp_get_template('my-business/parts/work-hours.php', $args);
	}
}

if ( ! function_exists('obp_my_business_business_hours') ) {
	function obp_my_business_business_hours( $args ){
		obp_get_template('my-business/parts/business-hours.php', $args);
	}
}

if ( ! function_exists('obp_my_business_media') ) {
	function obp_my_business_media( $args ){
		obp_get_template('my-business/parts/media.php', $args );
	}
}

if ( ! function_exists('obp_my_business_tags') ) {
	function obp_my_business_tags( $args ){
		obp_get_template('my-business/parts/tags.php', $args );
	}
}

if ( ! function_exists('obp_my_profile_update_profile') ) {
	function obp_my_profile_update_profile( $args = array() ){
		obp_get_template('my-profile/update-profile.php', $args);
	}
}

if ( ! function_exists("obp_update_profile_content") ) {
	function obp_update_profile_content( $args = array() ){
		$args = BookPro\MyProfile\OBP_My_Profile::obp_update_profile_args( $args );
		obp_get_template('my-profile/update-profile-content.php', $args);
	}
}

if ( ! function_exists('obp_my_profile_change_password') ) {
	function obp_my_profile_change_password( $args = array() ){
		obp_get_template('my-profile/change-password.php', $args );
	}
}

if ( ! function_exists("obp_change_password_content") ) {
	function obp_change_password_content( $args = array() ){
		$args = BookPro\MyProfile\OBP_My_Profile::obp_change_password_args( $args );
		obp_get_template('my-profile/change-password-content.php', $args );
	}
}

if ( ! function_exists('obp_my_profile_delete_account') ) {
	function obp_my_profile_delete_account( $args = array() ){
		obp_get_template('my-profile/delete-account.php', $args);
	}
}

if ( ! function_exists("obp_delete_account_content") ) {
	function obp_delete_account_content( $args = array() ){
		$args = BookPro\MyProfile\OBP_My_Profile::obp_delete_account_args( $args );
		obp_get_template('my-profile/delete-account-content.php', $args);
	}
}

if ( ! function_exists('obp_manage_plan_calendar') ) {
	function obp_manage_plan_calendar( $args = array() ){
		$args = BookPro\Plan\OBP_Plan::obp_manage_plan_calendar_args( $args );
		obp_get_template('manage-plan/calendar.php', $args);
	}
}

if ( ! function_exists("obp_manage_plan_content") ) {
	function obp_manage_plan_content( $args = array() ){
		obp_get_template('manage-plan/manage-plan-content.php', $args );
	}
}

if ( ! function_exists('obp_manage_plan_list_table') ) {
	function obp_manage_plan_list_table( $args = array() ){
		$args = BookPro\Plan\OBP_Plan::obp_manage_plan_list_table_args( $args );
		obp_get_template('manage-plan/list-plan.php', $args);
	}
}

if ( ! function_exists('obp_staff_list_staff') ) {
	function obp_staff_list_staff( $args = array() ){
		obp_get_template('manage-staff/list-staff.php', $args);
	}
}

if ( ! function_exists('obp_staff_day_off_content') ) {
	function obp_staff_day_off_content($user_id = ''){
		obp_get_template('manage-staff/day-off-staff.php', array('user_id' => $user_id ));
	}
}

if ( ! function_exists('obp_manage_role_listing') ) {
	function obp_manage_role_listing( $args = array() ){
		$args = BookPro\Role\OBP_Role::obp_manage_role_listing_args( $args );
		obp_get_template( 'manage-role/listing-roles.php', $args );
	}
}

if ( ! function_exists('obp_manage_role_add_new') ) {
	function obp_manage_role_add_new( $args = array() ){
		$args = BookPro\Role\OBP_Role::obp_manage_role_add_new_args( $args );
		obp_get_template( 'manage-role/add-new.php', $args );
	}
}

if ( ! function_exists("obp_manage_role_edit") ) {
	function obp_manage_role_edit( $args = array() ){
		$args = BookPro\Role\OBP_Role::obp_edit_role_args( $args );
		obp_get_template( 'manage-role/edit.php', $args );
	}
}


if ( ! function_exists('obp_overall_schedule_calendar') ) {
	function obp_overall_schedule_calendar( $args = array() ){
		obp_get_template( 'overall-schedule/calendar.php', $args );
	}
}

if ( ! function_exists("obp_overall_schedule_calendar_content") ) {
	function obp_overall_schedule_calendar_content( $args = array() ){

		$current_time 		= current_time( 'timestamp' );
		$first_day_of_month = gmdate('Y-m-d', $current_time);
		$timestep 			= OBP()->settings->general->get('time_step', '00:30:00');
		$data_calendar 		= BookPro\Order\OBP_Order_Meta_Queue::get_data_calendar_all_schedule( $args );

		$args = array(
			'data_calendar' 		=> $data_calendar,
			'first_day_of_month' 	=> $first_day_of_month,
			'timestep' 				=> $timestep,
		);

		$args = apply_filters( 'obp_overall_schedule_calendar_args', $args );

		obp_get_template( 'overall-schedule/calendar-content.php', $args );
	}
}

if ( ! function_exists("obp_overall_schedule_filter") ) {
	function obp_overall_schedule_filter(){
		$staff_list = BookPro\Staff\OBP_Staff::get_list_staff();

		$args =  array(
			'staff_list' => $staff_list,
		);

		$args = apply_filters( 'obp_overall_schedule_filter_args', $args );

		obp_get_template( 'overall-schedule/filter.php', $args );
	}
}


if ( ! function_exists("obp_staff_schedule_filter") ) {
	function obp_staff_schedule_filter(){
		obp_get_template( 'staff-schedule/filter.php');
	}
}

if ( ! function_exists('obp_staff_schedule_calendar') ) {
	function obp_staff_schedule_calendar(){
		obp_get_template( 'staff-schedule/calendar.php');
	}
}

if ( ! function_exists("obp_staff_schedule_calendar_content") ) {
	function obp_staff_schedule_calendar_content(){

		$current_time 		= current_time( 'timestamp' );
		$first_day_of_month = gmdate('Y-m-d', $current_time);
		$data_calendar 		= BookPro\Order\OBP_Order_Meta_Queue::get_data_calendar_staff_schedule();
		$timestep 			= OBP()->settings->general->get('time_step', '00:30:00');

		$args =  array(
			'data_calendar' 		=> $data_calendar,
			'first_day_of_month' 	=> $first_day_of_month,
			'timestep' 				=> $timestep,
		);

		$args = apply_filters( 'obp_staff_schedule_calendar_args', $args );

		obp_get_template( 'staff-schedule/calendar-content.php', $args );
	}
}

if ( ! function_exists("obp_manage_plan_add_new") ) {
	function obp_manage_plan_add_new( $args = array() ){
		$args = BookPro\Plan\OBP_Plan::obp_manage_plan_add_new_args( $args );
		obp_get_template('manage-plan/add-new.php', $args );
	}
}

if ( ! function_exists("obp_manage_plan_edit") ) {
	function obp_manage_plan_edit( $args = array() ){
		$args = BookPro\Plan\OBP_Plan::obp_manage_plan_edit_args( $args );
		obp_get_template('manage-plan/edit.php', $args );
	}
}

if ( ! function_exists("obp_booking_form_order") ) {
	function obp_booking_form_order( $args = array() ){
		$args = BookPro\OBP_Booking::obp_booking_form_order_args( $args );
		obp_get_template( 'my-business/single-business/popup-form/order.php', $args );
	}
}

if ( ! function_exists("obp_booking_popup_service") ) {
	function obp_booking_popup_service( $args = array() ){
		$args = apply_filters( 'obp_booking_popup_service', array_merge( $args, array() ) );
		obp_get_template("my-business/single-business/popup-form/popup-service.php", $args );
	}
}

if ( ! function_exists("obp_booking_popup_staff") ) {
	function obp_booking_popup_staff( $args = array() ){
		$args = BookPro\OBP_Booking::obp_booking_popup_staff_args( $args );
		obp_get_template("my-business/single-business/popup-form/popup-staff.php", $args );
	}
}

if ( ! function_exists("obp_booking_form_calendar_content") ) {
	function obp_booking_form_calendar_content( $args = array() ){
		obp_get_template( 'my-business/single-business/popup-form/calendar-content.php', $args );
	}
}

if ( ! function_exists("obp_booking_form_footer") ) {
	function obp_booking_form_footer( $args = array() ){
		$args = BookPro\OBP_Booking::obp_booking_form_footer_args( $args );
		obp_get_template( 'my-business/single-business/popup-form/footer.php', $args );
	}
}

if ( ! function_exists("obp_booking_form_calendar") ) {
	function obp_booking_form_calendar( $args = array() ){
		$args = BookPro\OBP_Booking::obp_booking_form_calendar_args( $args );
		obp_get_template( 'my-business/single-business/popup-form/calendar.php', $args );
	}
}

if ( ! function_exists("obp_booking_form_time_slider") ) {
	function obp_booking_form_time_slider( $args = array() ){
		$args = BookPro\OBP_Booking::obp_booking_form_time_slider_args( $args );
		obp_get_template( 'my-business/single-business/popup-form/time-slider.php', $args );
	}
}

if ( ! function_exists("obp_booking_form_order_item") ) {
	function obp_booking_form_order_item( $args = array() ){
		$args = BookPro\OBP_Booking::obp_booking_form_order_item_args( $args );
		obp_get_template("my-business/single-business/popup-form/order-item.php", $args );
	}
}

if ( ! function_exists("obp_booking_form_payment_method") ) {
	function obp_booking_form_payment_method( $args = array() ){
		obp_get_template("my-business/single-business/popup-form/payment-method.php", $args );
	}
}

if ( ! function_exists("obp_manage_wallet_content") ) {
	function obp_manage_wallet_content( $args = array() ){
		obp_get_template( "manage-wallet/content.php", $args );
	}
}

if ( ! function_exists("obp_manage_wallet_cards") ) {
	function obp_manage_wallet_cards( $args = array() ){
		$args = BookPro\Wallet\OBP_Wallet::obp_manage_wallet_cards_args( $args );
		obp_get_template( "manage-wallet/cards.php", $args );
	}
}

if ( ! function_exists("obp_manage_wallet_transaction_history") ) {
	function obp_manage_wallet_transaction_history( $args = array() ){
		$args = BookPro\Wallet\OBP_Wallet::obp_transaction_history_args( $args );
		obp_get_template( "manage-wallet/transaction-history.php", $args );
	}
}

if ( ! function_exists("obp_manage_wallet_payout_method") ) {
	function obp_manage_wallet_payout_method( $args = array() ){
		$args = BookPro\Wallet\OBP_Wallet::obp_manage_wallet_payout_method_args( $args );
		obp_get_template( "manage-wallet/payout-method.php", $args );
	}
}

if ( ! function_exists("obp_payout_method_field") ) {
	function obp_payout_method_field( $args = array() ){
		$args = BookPro\Wallet\OBP_Wallet::obp_payout_method_field_args( $args );
		obp_get_template("manage-wallet/payout-method-field.php", $args);
	}
}

if ( ! function_exists("obp_payout_method_popup") ) {
	function obp_payout_method_popup( $args = array() ){
		$args = BookPro\Wallet\OBP_Wallet::obp_payout_method_popup_args( $args );
		obp_get_template( "manage-wallet/payout-method-popup.php", $args );
	}
}


if ( ! function_exists( "obp_booking_form_popup_content" ) ) {
	function obp_booking_form_popup_content( $args = array() ){
		obp_get_template( "my-business/single-business/popup-form/booking-content.php", $args );
	}
}

if ( ! function_exists("obp_withdraw_popup_content") ) {
	function obp_withdraw_popup_content( $args = array() ){
		$args = BookPro\Wallet\OBP_Wallet::obp_withdraw_popup_content_args( $args );
		obp_get_template( "manage-wallet/withdraw-popup-content.php", $args );
	}
}

if ( ! function_exists("obp_change_order_content") ) {
	function obp_change_order_content( $args = array() ){
		obp_get_template( "my-booking/popup-form/change-order-content.php", $args );
	}
}

if ( ! function_exists("obp_change_order_form_calendar") ) {
	function obp_change_order_form_calendar( $args = array() ){
		$args = BookPro\Order\OBP_Order::obp_change_order_form_calendar_args( $args );
		obp_get_template( "my-booking/popup-form/calendar.php", $args );
	}
}

if ( ! function_exists("obp_change_order_calendar_content") ) {
	function obp_change_order_calendar_content( $args = array() ){
		obp_get_template( "my-booking/popup-form/calendar-content.php", $args );
	}
}

if ( ! function_exists("obp_change_order_time_slider") ) {
	function obp_change_order_time_slider( $args = array() ){
		$args = BookPro\Order\OBP_Order::obp_change_order_time_slider_args( $args );
		obp_get_template( "my-booking/popup-form/time-slider.php", $args );
	}
}

if ( ! function_exists("obp_change_order_form_order") ) {
	function obp_change_order_form_order( $args = array() ){
		obp_get_template( "my-booking/popup-form/order.php", $args );
	}
}

if ( ! function_exists("obp_change_order_popup_staff") ) {
	function obp_change_order_popup_staff( $args = array() ){
		$args = BookPro\Order\OBP_Order::obp_change_order_popup_staff_args( $args );
		obp_get_template("my-booking/popup-form/popup-staff.php", $args );
	}
}

if ( ! function_exists("obp_change_order_form_order_item") ) {
	function obp_change_order_form_order_item( $args = array() ){
		$args = BookPro\Order\OBP_Order::obp_change_order_form_order_item_args( $args );
		obp_get_template( "my-booking/popup-form/order-item.php", $args );
	}
}

if ( ! function_exists("obp_change_order_form_footer") ) {
	function obp_change_order_form_footer( $args = array() ){
		$args = BookPro\Order\OBP_Order::obp_change_order_form_footer_args( $args );
		obp_get_template( "my-booking/popup-form/footer.php", $args );
	}
}

if ( ! function_exists("obp_member_account_sidebar") ) {
	function obp_member_account_sidebar(){
		$user_id 		= get_current_user_id();
		$user 			= obp_get_user( $user_id );

		$business_id = $user->get_business_id();

		$business = obp_get_business( $business_id );
		$args = array(
			'user' 			=> $user,
			'business' 		=> $business
		);

		obp_get_template("layouts/sidebar.php", $args );
	}
}

if ( ! function_exists("obp_email_order_schedule") ) {
	function obp_email_order_schedule( $args = array() ){
		$order 			= isset( $args['order'] ) ? $args['order'] : '';
		$order_id 		= $order->get_id();
		$order_items 	= BookPro\Order\OBP_Order_Meta::get_order_items( $order_id );

		$args = array_merge( $args, array(
			'order_items' 	=> $order_items,
			'order' 		=> $order,
		) );

		$args = apply_filters( 'obp_email_order_schedule_args', $args );

		obp_get_template("emails/order-schedule.php", $args );
	}
}

if ( ! function_exists("obp_email_order_info") ) {
	function obp_email_order_info( $args = array() ){
		$order 			= isset( $args['order'] ) ? $args['order'] : '';
		$order_id 		= $order->get_id();
		$order_items 	= BookPro\Order\OBP_Order_Meta::get_order_items( $order_id );

		$args = array_merge( $args, array(
			'order_items' => $order_items,
		) );

		$args = apply_filters( 'obp_email_order_info_args', $args );

		obp_get_template("emails/order-info.php", $args );
	}
}

if ( ! function_exists("obp_booking_services_content") ) {
	function obp_booking_services_content( $args = array() ){

		$args = apply_filters( 'obp_booking_services_content_args', $args );

		obp_get_template( "my-business/single-business/popup-form/service-items.php", $args );
	}
}

if ( ! function_exists('obp_my_booking_filter_content') ) {
	function obp_my_booking_filter_content(){
		obp_get_template( "my-booking/order-filter.php" );
	}
}


if ( ! function_exists('obp_my_booking_pagination') ) {
	function obp_my_booking_pagination(){
		obp_get_template("my-booking/order-pagination.php");
	}
}

if ( ! function_exists('obp_manager_orders_filter_content') ) {
	function obp_manager_orders_filter_content(){
		obp_get_template( "manage-booking/order-filter.php" );
	}
}

if ( ! function_exists('obp_message_content') ) {
	function obp_message_content(){
		obp_get_template( "message/message-wrapper.php" );
	}
}

if ( ! function_exists("obp_message_content_inner") ) {
	function obp_message_content_inner(){
		OBP()->message->print();
	}
}

if ( ! function_exists('obp_single_business_coupon') ) {
	function obp_single_business_coupon(){
		obp_get_template('my-business/single-business/coupon.php');
	}
}