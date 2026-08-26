<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_payout_info_wrapper">
	<table class="obp_table">
		<tr>
			<th>
				<?php esc_html_e( 'Amount', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo wp_kses_post( obp_get_price_html( $payout->get_amount() ) ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Time', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $payout->get_payout_date() ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Status', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $payout->get_payout_status_translate() ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php esc_html_e( 'Method', 'ovabookpro' ); ?>
			</th>
			<td>
				<?php echo esc_html( $payout->get_payout_method() ); ?>
			</td>
		</tr>

		<?php if ( ! empty( $field_settings ) ): ?>

			<tr>
				<th colspan="2"><?php esc_html_e( 'Payout Info', 'ovabookpro' ); ?></th>
			</tr>
			<?php foreach ( $field_settings as $item ):
				$field = obp_get_payout_field( $item );
				$value = isset( $payout_info[$field->get_key()] ) ? $payout_info[$field->get_key()] : '';
				?>
				<tr>
					<th>
						<?php echo esc_html( $field->get_label() ); ?>
					</th>
					<td>
						<?php echo esc_html( $value ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php endif; ?>
	</table>
</div>