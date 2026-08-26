<?php
namespace BookPro\Admin;
use BookPro\Traits\SingletonTrait;
use BookPro\Admin\OBP_Commission_Table;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Commission_Page') ) {
	

	class OBP_Commission_Page {

		use SingletonTrait;

		public static function submenu_page_callback(){

			$table = new OBP_Commission_Table();

			$table->prepare_items();
	        ?>
	            <div class="wrap obp_commission_wrapper">
	                <h2><?php esc_html_e( 'Commission', 'ovabookpro' ); ?></h2>
	                <form action="" class="obp_commission_form" method="GET">
	                	<input type="hidden" name="page" value="obp_commission" />
	                <?php
		                $table->display_filter();
		                $table->display();
	                ?>
	                </form>
	            </div>
	        <?php
		}
		
	}
}