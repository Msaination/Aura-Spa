<?php
namespace BookPro\Admin;

use BookPro\Traits\SingletonTrait;
use BookPro\Admin\MetaBoxes\OBP_Metabox_Order_Info;
use BookPro\Admin\MetaBoxes\OBP_Metabox_Payout_Method;
use BookPro\Admin\MetaBoxes\OBP_Metabox_Payout;
use BookPro\Admin\MetaBoxes\OBP_Metabox_Tax_Info;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists("OBP_Metaboxs") ) {
	
	class OBP_Metaboxs {

		use SingletonTrait;

		public function __construct(){
			add_action( 'add_meta_boxes', array( $this, 'obp_add_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'obp_save_meta_boxs') );


			add_action( 'obp_tax_classes_edit_form_fields', array( $this, 'obp_tax_classes_edit_form_fields' ) );
			add_action( 'obp_tax_classes_add_form_fields', array( $this, 'obp_tax_classes_add_form_fields' ) );

			add_action( 'edited_obp_tax_classes', array( $this, 'obp_tax_classes_save_form_fields' ) );
			add_action( 'created_obp_tax_classes', array( $this, 'obp_tax_classes_save_form_fields' ) );
		}

		public function obp_tax_classes_save_form_fields( $term_id ){
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$default = isset( $_POST['default'] ) ? sanitize_text_field( wp_unslash( $_POST['default'] ) ) : '';
			update_term_meta( $term_id, OBP_METABOX.'default', $default );
		}

		public function obp_tax_classes_add_form_fields( $term_obj ){
			$term_id = isset( $term_obj->term_id ) ? $term_obj->term_id : '';
			$default = get_term_meta( $term_id, OBP_METABOX.'default', true );
			?>
			<div class="form-field obp_default_tax">
	            <p valign="top" scope="row">
	                <label for="default">
	                	<?php esc_html_e( 'Default Tax Class', 'ovabookpro' ); ?>
	                </label>
	            </p>
	            <p>
					<label for="default">
						<input name="default" type="checkbox" id="default" value="1" <?php checked( $default, '1' ) ?>>
						<?php esc_html_e( 'Set as default', 'ovabookpro' ); ?>
					</label>
	            </p>
	        </div>
			<?php
		}

		public function obp_tax_classes_edit_form_fields( $term_obj ){
			$term_id = isset( $term_obj->term_id ) ? $term_obj->term_id : '';
			$default = get_term_meta( $term_id, OBP_METABOX.'default', true );
			?>
			<tr class="form-field obp_default_tax">
	            <td valign="top" scope="row">
	                <label for="default">
	                	<?php esc_html_e( 'Default Tax Class', 'ovabookpro' ); ?>
	                </label>
	            </td>
	            <td>
					<label for="default">
						<input name="default" type="checkbox" id="default" value="1" <?php checked( $default, '1' ) ?>>
						<?php esc_html_e( 'Set as default', 'ovabookpro' ); ?>
					</label>
	            </td>
	        </tr>
			<?php
		}

		public function obp_add_meta_boxes(){
			// Order Info
			add_meta_box( 'obp_order_info_metabox', esc_html__( 'Booking Infomation', 'ovabookpro' ),  array( 'BookPro\\Admin\\MetaBoxes\\OBP_Metabox_Order_Info', 'output' ) , 'obp_order', 'advanced', 'high' );
			
	
			// Payout Method
			add_meta_box( 'obp_payout_method_metabox', esc_html__( 'Payout Method Settings', 'ovabookpro' ),  array( 'BookPro\\Admin\\MetaBoxes\\OBP_Metabox_Payout_Method', 'output' ) , 'obp_payout_method', 'advanced', 'high' );

			// Payout
			add_meta_box( 'obp_payout_info_metabox', esc_html__( 'Withdrawal information', 'ovabookpro' ),  array( 'BookPro\\Admin\\MetaBoxes\\OBP_Metabox_Payout', 'output' ) , 'obp_payout', 'advanced', 'high' );

			// Tax
			add_meta_box( 'obp_tax_info_metabox', esc_html__( 'Tax information', 'ovabookpro' ),  array( 'BookPro\\Admin\\MetaBoxes\\OBP_Metabox_Tax_Info', 'output' ) , 'obp_tax', 'advanced', 'high' );
		}

		public function obp_save_meta_boxs( $post_id ){
			// Order Info
			OBP_Metabox_Order_Info::save( $post_id );
			// Payout Method
			OBP_Metabox_Payout_Method::save( $post_id );
			// Payout
			OBP_Metabox_Payout::save( $post_id );
			// Tax
			OBP_Metabox_Tax_Info::save( $post_id );
		}
	}
}