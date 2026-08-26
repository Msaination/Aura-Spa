<?php
namespace BookPro;

use BookPro\Abstracts\OBP_Session;
use PasswordHash;

defined( 'ABSPATH' ) || exit;


class OBP_Session_Handler extends OBP_Session {

	protected $_cookie;

	protected $_table;

	protected $_session_expiring;

	protected $_session_expiration;

	public function __construct(){
		$this->_cookie = apply_filters( 'obp_cookie', 'wp_obp_session_' . COOKIEHASH );
		$this->_table = $GLOBALS['wpdb']->prefix . 'obp_sessions';
	}

	public function init(){

		if ( isset( $_COOKIE[$this->_cookie] ) ) {
			$cookie_val = wp_unslash( $_COOKIE[$this->_cookie] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$cookie_arr = explode('|', $cookie_val );
			$this->_customer_id 		= $cookie_arr[0];
			$this->_session_expiring 	= $cookie_arr[1];
			$this->_session_expiration 	= $cookie_arr[2];
			$this->_data               	= $this->get_session_data();
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
			}
		} else {

			require_once ABSPATH . 'wp-includes/pluggable.php';
			if ( is_user_logged_in() ) {
				$this->_customer_id = get_current_user_id();
			} else {
				$this->_customer_id = $this->generate_customer_id();
			}

			$cookie_val = $this->_customer_id.'|'.$this->_session_expiring.'|'.$this->_session_expiration;
			$this->set_session_expiration();
			$this->_data = $this->get_session_data();

			obp_setcookie( $this->_cookie, $cookie_val, $this->_session_expiration, $this->use_secure_cookie(), true );
		}
	}

	public function generate_customer_id(){
		$customer_id = '';

		if ( is_user_logged_in() ) {
			$customer_id = strval( get_current_user_id() );
		}

		if ( empty( $customer_id ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hasher      = new PasswordHash( 8, false );
			$customer_id = 't_' . substr( md5( $hasher->get_random_bytes( 32 ) ), 2 );
		}

		return $customer_id;
	}

	public function cleanup_sessions() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE session_expiry < %d", time() ) );
	}

	public function set_session_expiration() {
		$this->_session_expiring   = time() + intval( apply_filters( 'obp_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		$this->_session_expiration = time() + intval( apply_filters( 'obp_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
	}

	protected function use_secure_cookie() {
		return apply_filters( 'obp_session_use_secure_cookie', obp_site_is_https() && is_ssl() );
	}

	public function has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || is_user_logged_in(); // @codingStandardsIgnoreLine.
	}

	public function save_data( $old_session_key = '' ){

		if ( $this->_dirty && $this->has_session() ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"INSERT INTO $this->_table (`session_key`, `session_value`, `session_expiry`) VALUES (%s, %s, %d)
 					ON DUPLICATE KEY UPDATE `session_value` = VALUES(`session_value`), `session_expiry` = VALUES(`session_expiry`)",
					$this->_customer_id,
					maybe_serialize( $this->_data ),
					$this->_session_expiration
				)
			);
		}
	}

	public function get_session_data() {
		return $this->has_session() ? (array) $this->get_session( $this->_customer_id, array() ) : array();
	}

	public function destroy_session() {
		$this->delete_session( $this->_customer_id );
		$this->forget_session();
	}

	public function forget_session() {
		obp_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie(), true );

		$this->_data        = array();
		$this->_dirty       = false;
		$this->_customer_id = $this->generate_customer_id();
	}

	public function delete_session( $customer_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete(
			$this->_table,
			array(
				'session_key' => esc_sql( $customer_id ),
			)
		);
	}

	public function get_session( $customer_id, $default = false ) {
		global $wpdb;
		
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM $this->_table WHERE session_key = %s", esc_sql( $customer_id ) ) ); // @codingStandardsIgnoreLine.

		if ( is_null( $value ) ) {
			$value = $default;
		}

		return maybe_unserialize( $value );
	}
}