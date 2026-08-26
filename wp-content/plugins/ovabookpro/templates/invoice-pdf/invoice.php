<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<meta content="width=device-width, initial-scale=1.0" name="viewport">
		<title><?php echo esc_html( $title ); ?></title>
		<style type="text/css">
			<?php
				echo $css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
		</style>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<table class="wrapper <?php echo esc_attr( $direction ); ?>" border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
			<tr>
				<td class="header">
					<!-- Header -->
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width="60%">
								<?php if ( $logo_url ): ?>
									<img src="<?php echo esc_url( $logo_url ); ?>" style="max-width: 100%;">
								<?php endif; ?>
							</td>
							<td>
								<div class="shop-name">
									<h2><?php echo esc_html( $business_name ); ?></h2>
								</div>
								<div class="shop-address"><?php echo esc_html( $business_address ); ?></div>
							</td>
						</tr>
					</table>
			
				</td>
			</tr>
			
			<tr>
				<td class="document-type-label">
					<?php if ( $title ): ?>
						<h1>
							<?php echo esc_html( $title ); ?>
						</h1>
					<?php endif; ?>
					<!-- Booking -->
				</td>
			</tr>
			
			<tr>
				<td class="booking_info">
	
					<table border="0" cellpadding="0" cellspacing="0" width="100%">

						<tr>
							<td width="60%">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
								
									<?php if ( $order->get_customer_name() ): ?>
										<tr>
											<td>
												<?php echo esc_html( $order->get_customer_name() ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $order->get_customer_phone() ): ?>
										<tr>
											<td>
												<?php echo esc_html( $order->get_customer_phone() ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $order->get_customer_email() ): ?>
										<tr>
											<td>
												<a href="mailto:<?php echo esc_attr( $order->get_customer_email() ); ?>">
													<?php echo esc_html( $order->get_customer_email() ); ?>
												</a>
											</td>
										</tr>
									<?php endif; ?>
								</table>
							</td>

							<td>
								<table border="0" cellpadding="0" cellspacing="0" width="100%">

									<?php if ( $order->get_id() ) : ?>
										<tr>
											<td>
												<?php esc_html_e( 'Booking Number:', 'ovabookpro' ); ?>
											</td>
											<td>
												<?php echo esc_html( '#'.$order->get_id() ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php if ( $order->get_date_created() ) : ?>
										<tr>
											<td>
												<?php esc_html_e( 'Date Created:', 'ovabookpro' ); ?>
											</td>
											<td>
												<?php echo esc_html( $order->get_date_created() ); ?>
											</td>
										</tr>
									<?php endif; ?>

									<?php do_action( 'obp_order_invoice_middle', $order ); ?>

									<?php if ( $order->get_payment_method() ) : ?>
										<tr>
											<td><?php esc_html_e( 'Payment Method:', 'ovabookpro' ); ?></td>
											<td><?php echo esc_html( $order->get_payment_method() ); ?></td>
										</tr>
									<?php endif; ?>

									<?php if ( $order->get_order_status_translate() ) : ?>
										<tr>
											<td><?php esc_html_e( 'Booking Status:', 'ovabookpro' ); ?></td>
											<td><?php echo esc_html( $order->get_order_status_translate() ); ?></td>
										</tr>
									<?php endif; ?>

								</table>
							</td>
						</tr>
						
					</table>
	
				</td>
			</tr>

			<tr>
				<td class="cart">
			
					<table border="1" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<th><?php esc_html_e( 'Service Name', 'ovabookpro' ); ?></th>
							<th><?php esc_html_e( 'Time', 'ovabookpro' ); ?></th>
							<th><?php esc_html_e( 'Staff', 'ovabookpro' ); ?></th>
							<th><?php esc_html_e( 'Total', 'ovabookpro' ); ?></th>
						</tr>

						<?php if ( ! empty( $order_items ) ): ?>
							<?php foreach ( $order_items as $key => $item ):
								$order_item = obp_get_order_meta( $item );
								$service = obp_get_service( $order_item->get_service_id() );
								?>
								<tr>
									<td>
										<?php echo esc_html( $order_item->get_service_name() ); ?>
										<?php if ( $order_item->get_package_names() ): ?>
											<br />
											<?php echo wp_kses_post( $order_item->get_package_names() ); ?>
										<?php endif; ?>
									</td>
									<td>
										<?php echo esc_html( $order_item->get_time() ); ?>
									</td>
									<td>
										<?php echo esc_html( $order_item->get_staff_name() ); ?>
									</td>
									
									<td>
										<?php echo wp_kses_post( obp_get_price_html( $order_item->get_price(), $service->get_price_type() ) ); ?>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>

					</table>
		
				</td>
			</tr>

			<tr>
				<td class="total_wrapper">
					<table border="0" cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td width="60%">

							</td>
							<td class="total_inner">
								<table border="0" cellpadding="0" cellspacing="0" width="100%">
		
									<tr>
										<th><?php esc_html_e( 'Subtotal', 'ovabookpro' ); ?></th>
										<td>
											<?php echo wp_kses_post( obp_get_price_html( $order->get_subtotal() ) ); ?>
										</td>
									</tr>
		
									<?php if ( $order->get_discount() ): ?>
										<tr>
											<th><?php esc_html_e( 'Discount', 'ovabookpro' ); ?></th>
											<td>
												<?php echo '-'.wp_kses_post( obp_get_price_html( $order->get_discount() ) ); ?>
											</td>
										</tr>
									<?php endif; ?>
									<?php if ( $order->get_tax_amount() ): ?>
										<tr>
											<th><?php esc_html_e( 'Tax', 'ovabookpro' ); ?></th>
											<td>
												<span><?php echo wp_kses_post( obp_get_price_html( $order->get_tax_amount() ) ); ?></span>
											</td>
										</tr>
									<?php endif; ?>
									<?php if ( $order->get_system_fee() ): ?>
										<tr>
											<th><?php esc_html_e( 'System fee', 'ovabookpro' ); ?></th>
											<td>
												<span><?php echo wp_kses_post( obp_get_price_html( $order->get_system_fee() ) ); ?></span>
											</td>
										</tr>
									<?php endif; ?>
						
									<tr>
										<th><?php esc_html_e( 'Total', 'ovabookpro' ); ?></th>
										<td>
											<span><?php echo wp_kses_post( obp_show_booking_total( $order->get_total(), $order->has_varies() ) ); ?></span>
										</td>
									</tr>
				
								</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr>
				<td>
					<?php if ( $footer ): ?>
						<div id="footer">
							<hr>
							<?php echo $footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					<?php endif; ?>
				</td>
			</tr>
		</table>
	</body>
</html>