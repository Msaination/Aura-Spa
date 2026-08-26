<?php defined( 'ABSPATH' ) || exit;

$total = $orders->max_num_pages;
$current_page = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Missing
$page_numbers = range(1, $total);
$page_numbers_chunk = array_chunk($page_numbers, 4);
?>

<div class="order-pagination obp-pagination">
	
	<?php
	if ( $total > 1 ) {

		foreach ( $page_numbers_chunk as $key => $pages ) {

			if ( in_array( $current_page , $pages ) ) {

				if ( $current_page > 1 ) {
					?>
					<a href="#" class="page_item prev" data-page="<?php echo esc_attr( $current_page - 1 ); ?>">
						<?php esc_html_e( 'Prev', 'ovabookpro' ); ?>
					</a>
					<?php
				}

				foreach ( $pages as $number ) {
					$class_active = $current_page == $number ? 'current_page' : '';
					$page_item_classes = array( 'page_item', 'page_number' , $class_active );
					?>
					<a href="#"
					data-page="<?php echo esc_attr( $number ); ?>"
					class="<?php echo esc_attr( implode(" ", $page_item_classes) ); ?>">
						<?php echo esc_html( $number ); ?>
					</a>
					<?php
				}

				if ( isset( $page_numbers_chunk[$key+1] ) ) {
					?>
					<a href="#" class="page_item next" data-page="<?php echo esc_attr( $current_page + 1 ); ?>">
						<?php esc_html_e( 'Next', 'ovabookpro' ); ?>
					</a>
					<?php
				}
			}
			
		}
	}


	?>
	
</div>