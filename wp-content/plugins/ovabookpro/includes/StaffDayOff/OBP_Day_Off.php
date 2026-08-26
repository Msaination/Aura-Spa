<?php
namespace BookPro\StaffDayOff;
use BookPro\OBP_Calendar;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * OBP_Day_Off class.
 */
class OBP_Day_Off {

	use SingletonTrait;

	protected static $table = 'obp_day_off';

	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	public static function add( $data ){
		global $wpdb;

		$data_column = array(
			'vendor_id' 	=> esc_sql( $data['vendor_id'] ),
			'staff_id' 		=> esc_sql( $data['staff_id'] ),
			'time' 			=> esc_sql( $data['time'] ),
			'hour_off' 		=> $data['hour_off'],
			'start_date' 	=> esc_sql( $data['start_date'] ),
			'end_date' 		=> esc_sql( $data['end_date'] ),
		);

		$table_name = $wpdb->prefix . self::$table; 
		$inserted   = $wpdb->insert($table_name,$data_column); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

	    if ( $inserted === false ) {
	        return false;
	    }

		return $wpdb->insert_id;
	}

	public static function update( $data, $id ){
		global $wpdb;

		$data_column = array(
			'time' 			=> esc_sql( $data['time'] ),
			'hour_off' 		=> $data['hour_off'],
			'start_date' 	=> esc_sql( $data['start_date'] ),
			'end_date' 		=> esc_sql( $data['end_date'] ),
		);

	    $where_conditions = array(
	        'id' => esc_sql( $id )
	    );

		$table_name = $wpdb->prefix . self::$table; 
		$updated    = $wpdb->update($table_name, $data_column, $where_conditions); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	    if ($updated === false) {
	        return false;
	    }

	    return true;
	}

	/* Get list day off by user id */
	public static function get_list_staff_day_off($user_id) {
		global $wpdb;

		$results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_day_off WHERE staff_id = %d ORDER BY start_date ASC", esc_sql( $user_id ) ), ARRAY_A); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $results;
	}

	/* Get day off item by day off id */
	public static function get_day_off_item( $id ) {
		global $wpdb;

		$results = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_day_off WHERE id = %d ORDER BY start_date ASC", esc_sql( $id ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $results;
	}
	
	/* Delete all day off by user id */
	public static function delete_staff_all_day_off($user_id) {
	    global $wpdb; 

	    // Execute the query
	    $result = $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}obp_day_off WHERE staff_id = %d", esc_sql( $user_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	    return $result;
	}

	/* Delete day off by id */
	public static function delete_staff_day_off( $id ) {
	    global $wpdb;

	    // Execute the query
	    $result = $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->prefix}obp_day_off WHERE id = %d", esc_sql( $id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	    return $result;
	}

	public static function get_data_date( $days_off ){
		$results = [];
		if ( $days_off ) {
			foreach ( $days_off as $day_off ) {
				$obj = obp_get_day_off( $day_off );
				$results[] = array(
					'id' 	=> $obj->get_id(),
					'from' 	=> gmdate( "Y-m-d", $obj->get_start_date() ),
					'to' 	=> gmdate( "Y-m-d", $obj->get_end_date() ),
				);
			}
		}

		return $results;
	}

	public static function get_data_calendar_dayoff_by_user_id( $user_id ){
		$days_off 	= self::get_list_staff_day_off( $user_id );
		$results 	= array( 'color' => '#d9d9d9', 'events' => array() );

		if ( $days_off ) {
			foreach ( $days_off as $value ) {

				switch ( $value['time'] ) {
					case 'custom_time':
						$hours_off = maybe_unserialize( $value['hour_off'] );
						foreach ( $hours_off as $key => $_val ) {

							if ( absint( $value['end_date'] ) - absint( $value['start_date'] ) > 0 ) {
								$start_point = absint( $value['start_date'] );

								while ( absint( $value['end_date'] ) - $start_point >= 0 ) {
					
									$results['events'][] = array(
										'title' => esc_html__( 'On leave', 'ovabookpro' ),
										'start' => gmdate("Y-m-d", $start_point ).'T'.$_val['start_hour'].':00',
										'end' 	=> gmdate("Y-m-d", $start_point ).'T'.$_val['end_hour'].':00',
									);

									// add 1 day
									$start_point += 86400;
								}

								
							} else {
								$results['events'][] = array(
									'title' => esc_html__( 'On leave', 'ovabookpro' ),
									'start' => gmdate("Y-m-d", $value['start_date'] ).'T'.$_val['start_hour'].':00',
									'end' 	=> gmdate("Y-m-d", $value['end_date'] ).'T'.$_val['end_hour'].':00',
								);
							}
							
						}
						break;

					case 'full_time':

						$results['events'][] = array(
							'title' => esc_html__( 'On leave', 'ovabookpro' ),
							'start' => gmdate("Y-m-d", $value['start_date'] ).'T00:00:00',
							'end' 	=> gmdate("Y-m-d", strtotime( '+1 day', $value['end_date'] ) ).'T00:00:00',
						);
						break;
					
					default:
						break;
				}

				
			}
		}

		return $results;
	}

	public static function get_day_off_object( $day_off_ids = array() ){
		$day_off_object = array();
		if ( count( $day_off_ids ) > 0 ) {
			foreach ( $day_off_ids as $key => $day_off_id ) {
				$day_off_object[] = new OBP_Day_Off_Item( $day_off_id );
			}
		}

		return $day_off_object;
	}

	public static function get_row( $staff_id, $date_timestamp ){
		global $wpdb;

		$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}obp_day_off WHERE start_date <= %d AND end_date >= %d AND staff_id = %d", esc_sql( $date_timestamp ), esc_sql( $date_timestamp ) , esc_sql( $staff_id ) ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $row;
	}
}