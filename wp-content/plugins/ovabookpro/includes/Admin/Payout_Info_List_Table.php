<?php

namespace BookPro\Admin;

use BookPro\User\OBP_User;

defined( 'ABSPATH' ) || exit;

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

use WP_List_Table;


/**
 * Create a new table class that will extend the WP_List_Table
 */
class Payout_Info_List_Table extends WP_List_Table {
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items(){
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $data = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $perPage = apply_filters( 'obp_payout_info_per_page', 20 );
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns(){
        $columns = array(
            'id'                => 'ID',
            'user_login'        => esc_html__( 'Username', 'ovabookpro' ),
            'user_email'        => esc_html__( 'Email', 'ovabookpro' ),
            'user_registered'   => esc_html__( 'Registered time', 'ovabookpro' ),
            'action'            => esc_html__( 'Action', 'ovabookpro' ),
        );

        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns(){
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns(){
        return array('title' => array('title', false));
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data(){
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    	$keywords 		= isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
        $users 			= OBP_User::get_all_user( $keywords );
        $data 			= array();
        $date_format 	= get_option( 'date_format' );
        $time_format 	= get_option( 'time_format' );

        // convert object to array
        if ( $users ) {
            foreach ( $users as $key => $user ) {
                $data[] = (array)$user;
                $data[$key]['user_registered'] = date_i18n( $date_format.' '.$time_format, $data[$key]['user_registered'] );
            }
        }

        return $data;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name ){
        switch( $column_name ) {
            case 'id':
            case 'user_login':
            
            case 'user_registered':
                return $item[ $column_name ];
            break;
            case 'user_email':
                return '<a href="mailto:'.$item[ $column_name ].'">'.$item[ $column_name ].'</a>';
            break;
            case 'action':
                return '<a href="#" class="button button-secondary obp_show_payout_info" data-id="'.$item['id'].'" data-nonce="'.wp_create_nonce( 'obp_show_payout_info' ).'">'.esc_html__( 'Show info', 'ovabookpro' ).'</a>';
            break;
            default:
                return print_r( $item, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
        }
    }

    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b ){
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        // Set defaults
        $orderby = 'id';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if( !empty( $_GET['orderby'] ) ){ 
            $orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
        }

        // If order is set use this as the order
        if( !empty( $_GET['order'] ) ){
            $order = sanitize_text_field( wp_unslash( $_GET['order'] ) );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc'){
            return $result;
        }

        return -$result;
    }

}