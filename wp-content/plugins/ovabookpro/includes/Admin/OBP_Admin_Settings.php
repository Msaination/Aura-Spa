<?php
namespace BookPro\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * OBP_Admin_Settings class.
 */
class OBP_Admin_Settings {
	/**
	 * The single instance of the class.
	 *
	 * @var Admin Settings
	 */
	protected static $_instance = null;

	/**
	 * Settings group name.
	 *
	 * @var string
	 */
	public $option_group = 'obp_settings';

	/**
	 * The name of an option to sanitize and save.
	 *
	 * @var string
	 */
	public $option_name = null;

	/**
	 * Data used to describe the setting when registered.
	 *
	 * @var array
	 */
	public $option_args = array();

	/**
	 * Data options.
	 *
	 * @var array
	 */
	public $options = null;

	/**
	 * Constructor.
	 */
	public function __construct( $option_group = null, $option_name = null, $option_args = array() ) {
		if ( $option_group ) $this->option_group = $option_group;

		$this->option_name = $option_name;
		$this->option_args = $option_args;

		// Load options
		$this->get_options();

		// Register OBP Settings
		add_action( 'admin_init', array( $this, 'register_setting' ) );
	}

	public function __get( $option_name = null ) {
		$settings = apply_filters( 'obp_settings_fields', array() );

		if ( array_key_exists( $option_name, $settings ) ) {
			return $settings[ $option_name ];
		}

		return null;
	}

	// Register setting
	public function register_setting() {
		register_setting( $this->option_group, $this->option_group, $this->option_args );
	}

	// View settings
	public static function view_setting() {
		OBP()->include( 'Admin/Settings/views/settings.php' );
	}

	/**
	 * Get options
	 * @return array || null
	 */
	protected function get_options() {
		if ( $this->options ) return $this->options;

		return $this->options = get_option( $this->option_group, null );
	}

	/**
	 * Get option value
	 *
	 * @param  $name
	 *
	 * @return option value. array, string, boolean
	 */
	public function get( $name = null, $default = null ) {
		if ( ! $this->options ) {
			$this->options = $this->get_options();
		}

		if ( $name && isset( $this->options[ $name ] ) ) {
			return $this->options[ $name ];
		}

		return $default;
	}

	/**
	 * BookPro Admin Settings Instance.
	 */
	static function instance( $option_group = null, $option_name = null, $args = array() ) {
		if ( ! empty( self::$_instance[ $option_group ] ) ) {
			return self::$_instance[ $option_group ];
		}

		return self::$_instance[ $option_group ] = new self( $option_group, $option_name, $args );
	}
}