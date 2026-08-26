<?php defined( 'ABSPATH' ) || exit;

// Member Account
add_action( 'obp_main_content', 'obp_template_content', 10 , 1 );

add_action( 'obp_member_account_sidebar', 'obp_member_account_sidebar', 10, 1 );
add_action( 'obp_dashboard_nav_content', 'obp_show_dashboard_nav_menu_item', 10 , 1 );

// Messages
add_action( 'obp_before_main_content', 'obp_message_content', 10 );
add_action( 'obp_message_inner', 'obp_message_content_inner', 10 );

// Single Business


add_action( 'obp_single_business_main_content', 'obp_single_business_gallery', 10 );
add_action( 'obp_single_business_main_content', 'obp_single_business_info', 20 );
add_action( 'obp_single_business_main_content', 'obp_single_business_coupon', 30 );
add_action( 'obp_single_business_main_content', 'obp_single_business_services', 40 );
add_action( 'obp_single_business_main_content', 'obp_single_business_our_work', 50 );
add_action( 'obp_single_business_main_content', 'obp_single_business_amenities', 60 );
add_action( 'obp_single_business_main_content', 'obp_single_business_tags', 70 );

add_action( 'obp_single_business_sidebar', 'obp_single_business_sidebar', 10 );
add_action( 'obp_single_business_portfolio', 'obp_single_business_portfolio', 10 );

// My Business
add_action( 'obp_my_business_main_content', 'obp_my_business_infomation', 10 );
add_action( 'obp_my_business_main_content', 'obp_my_business_business_hours', 20 );
add_action( 'obp_my_business_main_content', 'obp_my_business_work_hours', 30 );
add_action( 'obp_my_business_main_content', 'obp_my_business_media', 40 );

// My Profile
add_action( 'obp_my_profile_main_content', 'obp_my_profile_update_profile', 10 );
add_action( 'obp_my_profile_main_content', 'obp_my_profile_change_password', 20 );
add_action( 'obp_my_profile_main_content', 'obp_my_profile_delete_account', 30 );

add_action( 'obp_update_profile_content', 'obp_update_profile_content', 10 );
add_action( 'obp_change_password_content', 'obp_change_password_content', 10 );
add_action( 'obp_delete_account_content', 'obp_delete_account_content', 10 );

// Staff
add_action( 'obp_staff_list_staff', 'obp_staff_list_staff', 10, 1 );
add_action( 'obp_staff_schedule_content', 'obp_staff_schedule_content', 10, 1 );
add_action( 'obp_staff_day_off_content', 'obp_staff_day_off_content', 10, 1 );

// Plan
add_action( 'obp_manage_plan_main_content', 'obp_manage_plan_content', 10 );
add_action( 'obp_manage_plan_content', 'obp_manage_plan_calendar', 10 );
add_action( 'obp_manage_plan_content', 'obp_manage_plan_list_table', 20 );

// Roles
add_action( 'obp_manage_role_main_content', 'obp_manage_role_listing', 10 );
add_action( 'obp_manage_role_main_content', 'obp_manage_role_add_new', 20 );

// All Schedules
add_action( 'obp_overall_schedule_main_content', 'obp_overall_schedule_filter', 10 );
add_action( 'obp_overall_schedule_main_content', 'obp_overall_schedule_calendar', 20 );
add_action( 'obp_overall_schedule_calendar_content', 'obp_overall_schedule_calendar_content', 10 );

// Staff Schedule
add_action( 'obp_staff_schedule_main_content', 'obp_staff_schedule_filter', 10 );
add_action( 'obp_staff_schedule_main_content', 'obp_staff_schedule_calendar', 20 );
add_action( 'obp_staff_schedule_calendar_content', 'obp_staff_schedule_calendar_content', 10 );

// Booking Form
add_action( 'obp_booking_form_popup', 'obp_booking_form_popup_content', 10 );
add_action( 'obp_booking_form_content', 'obp_booking_form_calendar', 10 );
add_action( 'obp_booking_form_content', 'obp_booking_form_payment_method', 20 );
add_action( 'obp_booking_form_calendar', 'obp_booking_form_calendar_content', 10 );
add_action( 'obp_booking_form_calendar_content', 'obp_booking_form_time_slider', 10 );
add_action( 'obp_booking_form_calendar_content', 'obp_booking_form_order', 20 );
add_action( 'obp_booking_form_calendar_content', 'obp_booking_form_footer', 30 );
add_action( 'obp_booking_form_order_content', 'obp_booking_form_order_item', 10 );
add_action( 'obp_booking_services_content', 'obp_booking_services_content', 10 );

// Manage Wallet
add_action( 'obp_manage_wallet_main_content', 'obp_manage_wallet_content', 10 );
add_action( 'obp_withdraw_popup_main_content', 'obp_withdraw_popup_content', 10 );

// My Orders
add_action( 'obp_change_order_form_popup', 'obp_change_order_content', 10 );
add_action( 'obp_change_order_form_content', 'obp_change_order_form_calendar', 10 );
add_action( 'obp_change_order_form_calendar', 'obp_change_order_calendar_content', 10 );
add_action( 'obp_change_order_calendar_content', 'obp_change_order_time_slider', 10 );
add_action( 'obp_change_order_calendar_content', 'obp_change_order_form_order', 20 );
add_action( 'obp_change_order_calendar_content', 'obp_change_order_form_footer', 30 );
add_action( 'obp_change_order_form_order_content', 'obp_change_order_form_order_item', 10 );

add_action( 'obp_my_booking_before_main_content', 'obp_my_booking_filter_content', 10 );
add_action( 'obp_manager_orders_before_main_content', 'obp_manager_orders_filter_content', 10 );
