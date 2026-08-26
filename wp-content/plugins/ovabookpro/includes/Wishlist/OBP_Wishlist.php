<?php

namespace BookPro\Wishlist;



class OBP_Wishlist {


	public static function init(){

		add_action( 'obp_load_member_account_my-wishlist_scripts', array( __CLASS__, 'load_scripts' ) );
	}

	public static function load_scripts( $assets ){

		wp_enqueue_style( 'zebra-dialog');
		wp_enqueue_script('zebra-dialog');

		wp_enqueue_script('obp-wishlist', OBP_PLUGIN_URI.'assets/js/frontend/my-wishlist.js' , array('jquery'), false, true );

		wp_localize_script( 'obp-wishlist', 'obp_wishlist_obj', array(
			'confirm_delete' => esc_html__( 'Are you sure you want to delete this record? This action cannot be undone', 'ovabookpro' ),
			'yes' 		=> esc_html__( 'Yes', 'ovabookpro' ),
			'no' 		=> esc_html__( 'No', 'ovabookpro' ),
		) );
	}
}