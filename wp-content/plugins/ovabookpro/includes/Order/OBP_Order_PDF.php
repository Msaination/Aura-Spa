<?php 
namespace BookPro\Order;

use BookPro\Business\OBP_Business;
use BookPro\Order\OBP_Order_Meta;

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'OBP_Order_PDF' ) ) {
	class OBP_Order_PDF {

		public function make_pdf_invoice( $order_id ) {
			$html = $css = $attach_file = '';
			$upload_dir  = wp_upload_dir();

			// config mpdf
			$config_mpdf = array(
				'tempDir' 			=> $upload_dir['basedir'],
				'default_font_size' => apply_filters( 'obp_pdf_invoice_font_size_'.apply_filters( 'wpml_current_language', NULL ), 12 ),
				'default_font' 		=> apply_filters( 'obp_pdf_invoice_font_'.apply_filters( 'wpml_current_language', NULL ), 'DejaVuSans' ),
				'format' 			=> apply_filters( 'obp_pdf_invoice_format_'.apply_filters( 'wpml_current_language', NULL ), 'A4' ),
			);

			// make folder invoices
			$invoices_dir 	= trailingslashit( wp_upload_dir()['basedir'] ) . 'invoices';

			if ( ! is_dir( $invoices_dir ) ) {
	            wp_mkdir_p( $invoices_dir );
	        }

			// delete pdf files: will create only 1 new pdf file for download
			$invoice_files 	= glob( $invoices_dir . '/*.pdf' );

			if ( ! empty( $invoice_files ) && is_array( $invoice_files ) ) {
				foreach ( $invoice_files as $file ) {
					wp_delete_file( $file );
				}
			}

	        // PDF file name
	        $pdf_name = apply_filters( 'obp_pdf_invoice_name', 'bookpro_invoice_'.$order_id );

	        /* Data */
	        $data = [];

	        ob_start();
	        	obp_get_template( 'invoice-pdf/styles.php');
				$css = ob_get_contents();
			ob_get_clean();
			$data['css'] = $css;

			// Business
			$vendor_id   = get_post_meta( $order_id, OBP_METABOX.'vendor_id', true );
			$business_id = OBP_Business::get_id( $vendor_id );

			$google_map  = get_post_meta( $business_id, OBP_METABOX.'google_map', true);

			$current_domain = wp_parse_url(home_url(), PHP_URL_HOST);
			$current_year   = gmdate('Y');
			$footer = '© ' . $current_year . ' ' . esc_html($current_domain) . '. ' . esc_html__('All Rights Reserved.','ovabookpro');

			$data['title'] 			  = esc_html__('Invoice','ovabookpro');
			$data['footer'] 		  = apply_filters('obp_ft_pdf_footer_content',$footer);
			$data['business_name'] 	  = get_the_title( $business_id );
			$data['business_link'] 	  = get_the_permalink( $business_id ).'.';
			$data['business_address'] = isset($google_map['address']) ? $google_map['address'] : '';
			$data['direction'] 		  = is_rtl() ? 'rtl' : 'ltr';
			// Logo
			$logo_id = get_theme_mod( 'custom_logo' );
			$data['logo_url'] = wp_get_attachment_url( $logo_id );

			// Order item
			$order = obp_get_order($order_id);
			$order_items = OBP_Order_Meta::get_order_items( $order_id );
			
			$data['order'] = $order;
			$data['order_items'] = $order_items;

	        ob_start();
				obp_get_template( 'invoice-pdf/invoice.php', $data );
				$html = ob_get_contents();
			ob_get_clean();

			// Output
			try {
			    $mpdf = new \Mpdf\Mpdf( apply_filters( 'obp_config_mpdf_invoice', $config_mpdf ) );
				$mpdf->WriteHTML( $html );
				$attach_file = WP_CONTENT_DIR.'/uploads/invoices/'.$pdf_name.'.pdf';
				$mpdf->Output( $attach_file, 'F' );
			} catch (\Mpdf\MpdfException $e) {
			    echo esc_html( $e->getMessage() );
			}

			return $attach_file;
		}

	}
}