<?php defined( 'ABSPATH' ) || exit;
global $post;

$payout = obp_get_payout( $post->ID );


$user 				= obp_get_user( $payout->get_user_id() );
$payout_info 		= $payout->get_payout_info();
$payout_method_id 	= $payout->get_payout_method_id();
$payout_method 		= obp_get_payout_method( $payout_method_id );
$field_settings 	= $payout_method->get_setting_fields();
$payout_status 		= $payout->get_payout_status();
?>


<div class="payout_info_wrap">
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Name', 'ovabookpro' ); ?></th>
			<td>
				<?php echo esc_html( $user->get_fullname() ); ?>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Email', 'ovabookpro' ); ?></th>
			<td>
				<a href="mailto:<?php echo esc_attr( $user->get_user_email() ); ?>">
					<?php echo esc_html( $user->get_user_email() ); ?>
				</a>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Withdraw Date', 'ovabookpro' ); ?></th>
			<td>
				<?php echo esc_html( $payout->get_withdraw_date() ); ?>
			</td>
		</tr>

		<?php if ( $payout->get_payout_date() ): ?>
			<tr>
				<th scope="row"><?php esc_html_e( 'Payout Date', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $payout->get_payout_date() ); ?>
				</td>
			</tr>
		<?php endif; ?>

		<tr>
			<th scope="row"><?php esc_html_e( 'Payout method', 'ovabookpro' ); ?></th>
			<td>
				<?php echo esc_html( $payout->get_payout_method() ); ?>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Amount', 'ovabookpro' ); ?></th>
			<td>
				<?php echo wp_kses_post( obp_get_price_html( $payout->get_amount() ) ); ?>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Payout Info', 'ovabookpro' ); ?></th>
			<td>
				<?php if ( ! empty( $field_settings ) ): ?>
					
					<table>
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
					</table>

				<?php endif; ?>
			</td>
		</tr>

		<tr>
			<th scope="row"><?php esc_html_e( 'Payout Status', 'ovabookpro' ); ?></th>
			<td>
				<select name="<?php echo esc_attr( OBP_METABOX.'payout_status' ); ?>"
					id="<?php echo esc_attr( OBP_METABOX.'payout_status' ); ?>">
					<option value="obp_pending" <?php selected( $payout_status, 'obp_pending' ); ?>>
						<?php esc_html_e( 'Pending', 'ovabookpro' ); ?>
					</option>
					<option value="obp_completed" <?php selected( $payout_status, 'obp_completed' ); ?>>
						<?php esc_html_e( 'Completed', 'ovabookpro' ); ?>
					</option>
					<option value="obp_cancelled" <?php selected( $payout_status, 'obp_cancelled' ); ?>>
						<?php esc_html_e( 'Cancelled', 'ovabookpro' ); ?>
					</option>
				</select>
			</td>
		</tr>

	</table>
</div>