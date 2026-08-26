<?php

namespace BookPro\Admin;

use BookPro\Traits\SingletonTrait;
use BookPro\Admin\Payout_Info_List_Table;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Admin_Payout_Info") ) {
	

	class OBP_Admin_Payout_Info {

		use SingletonTrait;


		public static function submenu_page_callback(){
			$table = new Payout_Info_List_Table();
	        $table->prepare_items();
	        ?>
	            <div class="wrap obp-payout-info">
	                <h1 class="wp-heading-inline"><?php esc_html_e( 'Payout Accounts', 'ovabookpro' ); ?></h2>
	                <form action="" class="obp_payout_info_form" method="POST">
	                <?php
	                $table->search_box(esc_html__( 'Search User', 'ovabookpro' ), 'obp_search_user');
	                $table->display();
	                ?>
	                </form>
	            </div>
	        <?php
		}
	}
}