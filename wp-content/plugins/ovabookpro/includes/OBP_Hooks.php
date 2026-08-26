<?php
namespace BookPro;

defined( 'ABSPATH' ) || exit;

use BookPro\Traits\SingletonTrait;
use BookPro\MyProfile\OBP_My_Profile_Hooks;
use BookPro\LoginRegister\OBP_Forgot_Password;
use BookPro\LoginRegister\OBP_Login;
use BookPro\LoginRegister\OBP_Register_User;
use BookPro\LoginRegister\OBP_Reset_Password;
use BookPro\Plan\OBP_Plan_Hooks;
use BookPro\Order\OBP_Order_Hooks;
use BookPro\OBP_Booking;

use BookPro\Role\OBP_Role_Hooks;
use BookPro\Shortcode\OBP_Shortcode_MemberAccount;
use BookPro\Shortcode\OBP_Shortcode_Vendor;
use BookPro\Shortcode\OBP_Shortcode_Services;
use BookPro\Shortcode\OBP_Shortcode_ServiceType;
use BookPro\Shortcode\OBP_Shortcode_OrderInfo;
use BookPro\OBP_Assets;
use BookPro\OBP_Post_Types;
use BookPro\OBP_Recaptcha;
use BookPro\Admin\OBP_Admin;
use BookPro\Business\OBP_Business_Ajax;
use BookPro\Staff\OBP_Staff_Ajax;
use BookPro\Service\OBP_Service_Ajax;
use BookPro\Type\OBP_Type_Ajax;
use BookPro\Order\OBP_Order_Ajax;
use BookPro\Payout\OBP_Payout_Hooks;
use BookPro\AllSchedule\OBP_All_Schedule;
use BookPro\Business\OBP_Business;
use BookPro\Service\OBP_Service;
use BookPro\Order\OBP_Order;
use BookPro\Type\OBP_Type;
use BookPro\Staff\OBP_Staff;
use BookPro\Wishlist\OBP_Wishlist;
use BookPro\Wallet\OBP_Wallet;
use BookPro\Coupon\OBP_Coupon;
use BookPro\Calendar\OBP_Google_Calendar;

if ( ! class_exists("OBP_Hooks") ) {

	class OBP_Hooks {

		use SingletonTrait;

		public function __construct(){


			OBP_My_Profile_Hooks::instance();
			OBP_Forgot_Password::instance();
			OBP_Login::instance();
			OBP_Register_User::instance();
			OBP_Reset_Password::instance();
			OBP_Plan_Hooks::instance();
			OBP_Order_Hooks::instance();
			OBP_Booking::instance();
			
			OBP_Role_Hooks::instance();
			OBP_Shortcode_MemberAccount::instance();
			OBP_Shortcode_Vendor::instance();
			OBP_Shortcode_Services::instance();
			OBP_Shortcode_ServiceType::instance();
			OBP_Shortcode_OrderInfo::instance();
			
			OBP_Post_Types::instance();
			OBP_Recaptcha::instance();
			OBP_Business_Ajax::instance();
			OBP_Staff_Ajax::instance();
			OBP_Service_Ajax::instance();
			OBP_Type_Ajax::instance();
			OBP_Order_Ajax::instance();
			OBP_Payout_Hooks::instance();
			OBP_All_Schedule::instance();

			OBP_Google_Calendar::instance();

			OBP_Coupon::ajax_init();

			// Frontend
			if ( ! is_admin() ) {
				OBP_Business::init();
				OBP_Service::init();
				OBP_Order::init();
				OBP_Type::init();
				OBP_Staff::init();
				OBP_Wishlist::init();
				OBP_Wallet::init();
				OBP_Coupon::init();
				
				OBP_Assets::instance();
			} else {
				OBP_Admin::instance();
			}
		}

	}

}