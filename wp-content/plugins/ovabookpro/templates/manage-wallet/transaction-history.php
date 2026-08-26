<?php
defined( 'ABSPATH' ) || exit;
?>

<div class="obp_head">
	<h2 class="obp_subtitle"><?php esc_html_e( 'Transaction History', 'ovabookpro' ); ?></h2>
</div>

<div class="transaction_history_content">
	<table class="obp_table">
		<tr>
			<th>
				<?php esc_html_e( 'Amount', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Time', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Status', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Method', 'ovabookpro' ); ?>
			</th>
		</tr>
		<tbody class="transaction-table-body">
			<?php if ( $payouts->have_posts() ): ?>
				<?php while ( $payouts->have_posts() ) :
					$payouts->the_post();
					obp_get_template( "manage-wallet/transaction-item.php" );
				?>
				<?php endwhile;
				else:
					?>
				<tr>
					<td colspan="4"><?php esc_html_e( 'Payouts not found.', 'ovabookpro' ); ?></td>
				</tr>
					<?php
				endif;
				wp_reset_postdata();
			?>
		</tbody>
	</table>
</div>

<div class="obp-pagination-wrap">
	<?php obp_get_template( "manage-wallet/transaction-history-pagination.php", array( 'payouts' => $payouts ) ); ?>
</div>