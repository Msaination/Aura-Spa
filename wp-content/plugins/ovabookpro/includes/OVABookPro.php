<?php
namespace BookPro;

defined( 'ABSPATH' ) || exit;

use BookPro\Traits\SingletonTrait;
use BookPro\Payments\Woocommerce\OBP_Woocommerce_Hooks;
use BookPro\Cart\OBP_Cart;
use BookPro\Database\OBP_Create_Tables;
use BookPro\OBP_Endpoint;
use BookPro\OBP_Hooks;
use BookPro\OBP_Install;
use BookPro\OBP_Data_Setting;
use BookPro\OBP_Template_Loader;
use BookPro\OBP_Cron_Jobs;
use BookPro\OBP_Session_Handler;
use BookPro\OBP_Message;
use BookPro\Payments\OBP_Payment_Gateways;

final class OVABookPro {

	use SingletonTrait;
	/**
	 * BookPro version.
	 *
	 * @var string
	 */
	public $version = '1.0.1';

	/**
	 * BookPro Settings
	 * @var object
	 */
	public $settings = null;

	public $endpoint = null;

	public $cart = null;

	public $session = null;

	public $message = null;

	/**
	 * BookPro Constructor.
	 */
	public function __construct() {

		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files.
	 */
	public function includes() {

		// Core Functions
		include_once OBP_PLUGIN_INC . 'obp-core-functions.php';
		include_once OBP_PLUGIN_INC . 'obp-business-functions.php';
		include_once OBP_PLUGIN_INC .'Admin/obp-admin-functions.php';
		
		// Template Hooks
		if ( $this->is_request( 'frontend' ) ) {
			require_once OBP_PLUGIN_INC . 'obp-template-hooks.php';
		}

	}

	public function include_template_functions() {
		include_once OBP_PLUGIN_INC . 'obp-template-functions.php';
	}


	public function payment_gateways() {
		return OBP_Payment_Gateways::instance();
	}

	public function get_post_meta( $post_id, $key , $default = '' ){
		$key 				= OBP_METABOX.$key;
		$meta_value 		= get_post_meta( $post_id, $key, true );

		if ( $meta_value ) {
			return apply_filters( 'obp_get_post_meta', $meta_value, $post_id, $key );
		}
		return $default;
	}

	public function get_user_meta( $user_id, $key, $default = '' ){
		$value = get_user_meta( $user_id, OBP_METABOX.$key, true );
		if ( $value ) {
			return $value;
		}
		return $default;
	}

	public function woocommerce_install(){

		if ( ! function_exists( 'is_plugin_active' ) ) {
		    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'before_woocommerce_init', array( $this, 'woocommerce_init' ) );
		}

		if ( $this->is_request('admin') ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_plugin_notices' ) );
		}
		
	}

	public function woocommerce_plugin_notices(){

		$all_plugins = get_plugins();
		if ( ! array_key_exists( "woocommerce/woocommerce.php", $all_plugins ) ) {
			$action = 'install-plugin';
			$slug = 'woocommerce';
			$link = wp_nonce_url(
			    add_query_arg(
			        array(
			            'action' => $action,
			            'plugin' => $slug
			        ),
			        admin_url( 'update.php' )
			    ),
			    $action.'_'.$slug
			);
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
				<?php
				$text = '<a href="'.$link.'">Woocommerce</a>';
				// translators: %s: plugin name.
				echo sprintf( esc_html__( 'You need to install and activate the %s plugin to be able to make payments.',  'ovabookpro' ), wp_kses_post( $text ) ); ?>
				</p>
			</div>
			<?php
		} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
				<?php
				$path = 'woocommerce/woocommerce.php';
				$link = wp_nonce_url(admin_url('plugins.php?action=activate&plugin='.$path), 'activate-plugin_'.$path);
				$text = '<a href="'.$link.'">Woocommerce</a>';
				// translators: %s: plugin name.
				echo sprintf( esc_html__( 'You need to activate the %s plugin to be able to make payments.',  'ovabookpro' ), wp_kses_post( $text ) ); ?>
				</p>
			</div>
			<?php
		}
	}


	public function woocommerce_init(){
		\BookPro\Payments\Woocommerce\OBP_Woocommerce_Hooks::instance();
	}

	public function load_notices(){

		OBP_Create_Tables::instance()->check_tables_exists();

		// Check member account page id
		$member_account_page_id = obp_member_account_page_id();

		if ( empty( $member_account_page_id ) ) {
			$setting_url = add_query_arg( array(
				'post_type' => 'obp_business',
				'page' 		=> 'obp_settings',
				'tab' 		=> 'general',
				'group' 	=> 'general_setting',
			), get_admin_url().'edit.php' );
			?>
			<div class="notice notice-warning">
				<p>
					<?php // translators: %s: setting url.
					printf( esc_html__( 'Please select <a href="%s">Member account page</a>.', 'ovabookpro' ), esc_url( $setting_url ) ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {

		do_action( 'before_ovabookpro_init' );

		register_activation_hook( OBP_PLUGIN_FILE, array( 'BookPro\\OBP_Install' , 'install' ) );

		OBP_Cron_Jobs::instance();

		add_action( 'obp_after_register_post_type', 'flush_rewrite_rules' );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'init', array( 'BookPro\\OBP_Install', 'create_roles' ) );
		add_action( 'activated_plugin', array( $this, 'activated_plugin' ), 10, 2 );
		add_action( 'deactivated_plugin', array( $this, 'deactivated_plugin' ), 10, 2 );
		add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
		

		add_action( 'admin_notices', array( $this, 'load_notices' ) );


		$this->woocommerce_install();

		$this->settings = OBP_Data_Setting::instance();
	
		do_action( 'ovabookpro_init' );
	}

	public function init(){
		$this->load_plugin_textdomain();
		OBP_Template_Loader::instance();
		OBP_Hooks::instance();

		$this->session = new OBP_Session_Handler();
		$this->session->init();
		$this->message = OBP_Message::instance();
		$this->cart    = new OBP_Cart();
	
		$this->endpoint = OBP_Endpoint::instance();
	}

	/**
	 * Load text domain.
	 */
	public function load_plugin_textdomain(){
		$locale = determine_locale();

		$locale = apply_filters( 'plugin_locale', $locale, 'ovabookpro' );

		unload_textdomain( 'ovabookpro' );
		load_textdomain( 'ovabookpro', WP_LANG_DIR . '/ovabookpro/ovabookpro-' . $locale . '.mo' );
		load_plugin_textdomain( 'ovabookpro', false, plugin_basename( dirname( OBP_PLUGIN_FILE ) ) . '/languages' );

	}

	/**
	 * Run when any plugin is activated.
	 */
	public function activated_plugin( $plugin, $network_wide ) {
		// Check if the current user has the necessary capabilities
	    if ( ! current_user_can( 'activate_plugins' ) ) {
	        return;
	    }

	    // Create Order tables
	    if ( $plugin === 'ovabookpro/ovabookpro.php' ) {
	    	OBP_Create_Tables::instance()->create_new_tables();
	    	OBP_Cron_Jobs::activation();
	    	OBP_Endpoint::install();
	    }
	}

	/**
	 * Run when any plugin is deactivated.
	 */
	public function deactivated_plugin( $plugin, $network_deactivating ) {
		// Check if the current user has the necessary capabilities
	    if ( ! current_user_can( 'activate_plugins' ) ) {
	        return;
	    }

	    // Drop Order tables
	    if ( $plugin === 'ovabookpro/ovabookpro.php' ) {
	    	OBP_Cron_Jobs::deactivate();
	    }
	}
	

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
		}
	}

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * Legacy REST requests should still run some extra code for backwards compatibility.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $rest_prefix ) );

		return apply_filters( 'obp_is_rest_api_request', $is_rest_api_request );
	}

	/**
	 * Wrapper for _doing_it_wrong().
	 *
	 */
	public function obp_doing_it_wrong( $function, $message, $version ) {
		// @codingStandardsIgnoreStart
		$message .= ' Backtrace: ' . wp_debug_backtrace_summary();

		if ( wp_doing_ajax() || $this->is_rest_api_request() ) {
			do_action( 'doing_it_wrong_run', $function, $message, $version );
			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
		} else {
			_doing_it_wrong( $function, $message, $version );
		}
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Include file
	 *
	 * @param $file string or array
	 */
	public function include( $file, $args = array(), $print = true ) {

		extract($args);

		// Start output buffering
	    ob_start();

	    // Include the template file
	    include OBP_PLUGIN_INC.$file;

	    // End buffering and return its contents
	    $output = ob_get_clean();
	    if (!$print) {
	        return $output;
	    }
	    echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

}