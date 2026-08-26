<?php

namespace BookPro\Admin;

use WP_List_Table;
use Bookpro\Commission\OBP_Commission;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'OBP_Commission_Table' ) ) {
	
	class OBP_Commission_Table extends WP_List_Table {


		protected $total_system_fee = 0;

		protected $total_vendor_fee = 0;

		protected $total_tax = 0;

		protected $total_booking = 0;

		protected $total_profit = 0;

		protected $total_commission = 0;

		public function prepare_items(){
	        $columns 	= $this->get_columns();
	        $hidden 	= $this->get_hidden_columns();
	        $sortable 	= $this->get_sortable_columns();
	        $data 		= $this->table_data();
	        usort( $data, array( $this, 'usort_reorder' ) );

	        $perPage 		= apply_filters( 'obp_commission_per_page', 20 );
	        $currentPage 	= $this->get_pagenum();
	        $totalItems 	= count($data);

	        $this->set_pagination_args( array(
	            'total_items' => $totalItems,
	            'per_page'    => $perPage
	        ) );

	        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);
	
	        $this->_column_headers = array($columns, $hidden, $sortable);
	        $this->items = $data;

	        $total_system_fee = 0;
	        $total_vendor_fee = 0;
	        $total_tax = 0;
	        $total_profit = 0;
	        $total_commission = 0;
	        $total_booking = $totalItems;

	        if ( isset( $_GET['start_date'] ) && isset( $_GET['end_date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		        if ( ! empty( $data ) ) {
		        	foreach ($data as $value) {
		        		$system_fee = isset( $value['system_fee'] ) ? (float)$value['system_fee'] : 0;
		        		$tax_amount = isset( $value['tax_amount'] ) ? (float)$value['tax_amount'] : 0;
		        		$vendor_fee = isset( $value['vendor_fee'] ) ? (float)$value['vendor_fee'] : 0;
		        		$profit 	= isset( $value['profit'] ) ? (float)$value['profit'] : 0;
		        		$commission = isset( $value['commission'] ) ? (float)$value['commission'] : 0;

		        		$total_system_fee += $system_fee;
		        		$total_tax += $tax_amount;
		        		$total_vendor_fee += $vendor_fee;
		        		$total_profit += $profit;
		        		$total_commission += $commission;
		        	}
		        }
	        }

	        $this->total_system_fee = $total_system_fee;
	        $this->total_vendor_fee = $total_vendor_fee;
	        $this->total_tax = $total_tax;
	        $this->total_booking = $total_booking;
	        $this->total_profit = $total_profit;
	        $this->total_commission = $total_commission;
	    }

	    public function get_total_profit(){
	    	return $this->total_profit;
	    }

	    public function get_total_system_fee(){
	    	return $this->total_system_fee;
	    }

	    public function get_total_vendor_fee(){
	    	return $this->total_vendor_fee;
	    }

	    public function get_total_tax(){
	    	return $this->total_tax;
	    }

	    public function get_total_booking(){
	    	return $this->total_booking;
	    }

	    public function get_total_commission(){
	    	return $this->total_commission;
	    }

	    public function get_statistic_columns(){

	    	$columns = array(
	    		'total_system_fee' 	=> esc_html__( 'Total System Fee', 'ovabookpro' ),
	    		'total_tax' 		=> esc_html__( 'Total Tax', 'ovabookpro' ),
	    		'total_commission' 	=> esc_html__( 'Total Commission', 'ovabookpro' ),
	    		'total_profit' 		=> esc_html__( 'Total Profit', 'ovabookpro' ),
	    		'total_booking' 	=> esc_html__( 'Total Booking', 'ovabookpro' ),
	    	);

	    	return apply_filters( 'obp_get_statistic_columns', $columns, $this );
	    }

	    public function display_filter(){
	    	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	    	$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
	    	$end_date 	= isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
	    	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	    	?>
			<div class="obp_commission_filter">
				<input type="text" id="start_date" value="<?php echo esc_attr( $start_date ); ?>"
				placeholder="<?php esc_attr_e( 'Start date', 'ovabookpro' ); ?>" />
				<input type="text" id="end_date" value="<?php echo esc_attr( $end_date ); ?>"
				placeholder="<?php esc_attr_e( 'End date', 'ovabookpro' ); ?>" />
				<button type="submit" class="button button-secondary"><?php esc_html_e( 'Filter', 'ovabookpro' ); ?></button>

				<input type="hidden" id="start_date_hidden" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" />
				<input type="hidden" id="end_date_hidden" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" />
			</div>

			<?php if ( $start_date && $end_date ): ?>

				<div class="obp_statistic">
					<table class="obp_statistic_table">
						<tr>
							<?php foreach ($this->get_statistic_columns() as $key => $value): ?>
								<th>
									<?php echo esc_html( $value ); ?>
								</th>
							<?php endforeach; ?>
						</tr>
							
						<tr>
							<?php foreach ($this->get_statistic_columns() as $key => $value): ?>
								<td>
									<?php echo apply_filters( 'obp_statistic_'.$key, '', $this ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>
							<?php endforeach; ?>
						</tr>
					</table>
				</div>

				<div class="obp_export_btn_wrap">
					<a href="#" id="obp_export_commission" class="button button-primary button-hero" data-column="<?php echo esc_attr( json_encode( $this->get_columns() ) ) ?>">
						<?php esc_html_e( 'Export CSV', 'ovabookpro' ); ?>
					</a>
				</div>

			<?php endif;

	    }

	    private function table_data(){
	    	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	    	$start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
	    	$end_date 	= isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
	    	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	    	$data = OBP_Commission::get_all( $start_date, $end_date );

	    	return $data;
	    }

		public function get_columns(){
	        $columns = array(
	            'id' 			=> 'ID',
	            'order_id' 		=> esc_html__( 'Booking ID', 'ovabookpro' ),
	            'vendor_id' 	=> esc_html__( 'Vendor ID', 'ovabookpro' ),
	            'system_fee' 	=> esc_html__( 'System Fee', 'ovabookpro' ),
	            'tax_amount' 	=> esc_html__( 'Tax', 'ovabookpro' ),
	            'commission' 	=> esc_html__( 'Commission', 'ovabookpro' ),
	            'profit' 		=> esc_html__( 'Profit', 'ovabookpro' ),
	            'total' 		=> esc_html__( 'Total', 'ovabookpro' ),
	            'date_created' 	=> esc_html__( 'Date Created', 'ovabookpro' ),
	        );

	        return apply_filters( 'obp_commsision_get_columns', $columns, $this );
	    }

	    public function column_default( $item, $column_name ){
	        switch( $column_name ) {
	        	case 'id':
	        	
	        	case 'vendor_id':
	        		return $item[ $column_name ];
	        	break;
	        	case 'order_id':
	        		return '<a href="'.get_edit_post_link( $item[ $column_name ] ).'">'.$item[ $column_name ].'</a>';
	        	break;
	        	case 'system_fee':
	        	case 'tax_amount':
	        	case 'profit':
	        	case 'total':
	        	case 'commission':

	        		return obp_get_price_html( $item[ $column_name ] );
	        	break;
	        	case 'date_created':
	        		return obp_get_date_html( $item[$column_name] );
	   				break;
	            default:
	            	return apply_filters( 'obp_commission_column_'.$column_name, $item[$column_name], $this );
	            	break;
	        }
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
	        return array('id' => array('id', false));
	    }

	    /**
	 * Callback to allow sorting of example data.
	 *
	 * @param string $a First value.
	 * @param string $b Second value.
	 *
	 * @return int
	 */
	protected function usort_reorder( $a, $b ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		// If no sort, default to title.
		$orderby = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id'; // WPCS: Input var ok.

		// If no order, default to asc.
		$order = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'desc'; // WPCS: Input var ok.

		// Determine sort order.
		$result = strcmp( $a[ $orderby ], $b[ $orderby ] );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		return ( 'asc' === $order ) ? $result : - $result;
	}

	}
}