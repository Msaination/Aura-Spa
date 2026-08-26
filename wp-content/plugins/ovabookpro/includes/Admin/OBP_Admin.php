<?php
namespace BookPro\Admin;

use BookPro\Traits\SingletonTrait;
use BookPro\Admin\OBP_Metaboxs;
use BookPro\Admin\OBP_Admin_Hooks;
use BookPro\Admin\OBP_Admin_Assets;
use BookPro\Admin\OBP_Admin_Business;

use BookPro\Admin\Settings\OBP_Tax_Setting;
use BookPro\Admin\Settings\OBP_Recaptcha_Setting;
use BookPro\Admin\Settings\OBP_Payment_Setting;
use BookPro\Admin\Settings\OBP_Mail_Setting;
use BookPro\Admin\Settings\OBP_General_Setting;
use BookPro\Admin\Settings\OBP_Endpoint_Setting;
use BookPro\Admin\Settings\OBP_Earning_Setting;
use BookPro\Admin\Settings\OBP_Cancel_Setting;
use BookPro\Admin\Settings\OBP_Cart_Order_Setting;
use BookPro\Admin\OBP_Admin_Settings;
use BookPro\Admin\OBP_Commission_Page;
use BookPro\Admin\OBP_Admin_Menus;

defined( 'ABSPATH' ) || exit;

/**
 * OBP_Admin class.
 */
class OBP_Admin {

	use SingletonTrait;
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
		$this->includes();
	}

	public function includes(){
	}

	public function init_hooks(){

		// Settings
		new OBP_Tax_Setting();
		new OBP_Recaptcha_Setting();
		new OBP_Payment_Setting();
		new OBP_Mail_Setting();
		new OBP_General_Setting();
		new OBP_Endpoint_Setting();
		new OBP_Earning_Setting();
		new OBP_Cancel_Setting();
		new OBP_Cart_Order_Setting();

		OBP_Admin_Hooks::instance();
		OBP_Metaboxs::instance();
		OBP_Admin_Assets::instance();
		OBP_Admin_Pages::instance();

		OBP_Admin_Settings::instance();
		OBP_Commission_Page::instance();
		OBP_Admin_Menus::instance();
	}

}