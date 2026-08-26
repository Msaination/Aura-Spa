<?php

defined( 'ABSPATH' ) || exit;


use BookPro\Payout\OBP_Payout_Method;
use BookPro\Payout\OBP_Payout_Method_Info;

$user = obp_get_user( $user_id );

$payout_method_id 	= $user->get_payout_method_id();
$payout_methods 	= OBP_Payout_Method::get_all();


?>


<div class="payout_info_dialog">
	<div class="user_info">
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Username:', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $user->get_user_login() ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Email:', 'ovabookpro' ); ?></th>
				<td>
					<?php echo esc_html( $user->get_user_email() ); ?>
				</td>
			</tr>
		</table>
	</div>

	<div id="tabs">
		<?php if ( ! empty( $payout_methods ) ): ?>
			<ul>
				<?php foreach ( $payout_methods as $key => $item ): ?>
				<li>
					<a href="#tabs-<?php echo esc_attr( $key + 1 ); ?>">
						<?php echo esc_html( $item->get_name() ); ?>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>

			<?php foreach ( $payout_methods as $key => $item ):
				$id 				= $item->get_id();
				$payout_fields 		= $item->get_setting_fields();
				$payout_info_row 	= OBP_Payout_Method_Info::get_row( $id, $user_id );
				$payout_info 		= isset( $payout_info_row['payout_info'] ) ? maybe_unserialize( $payout_info_row['payout_info'] ): array();
				?>
				<div id="tabs-<?php echo esc_attr( $key + 1 ); ?>">
					<?php if ( ! empty( $payout_fields ) ): ?>
						<table class="form-table">
							<?php foreach ( $payout_fields as $_value ):
								$field = obp_get_payout_field( $_value );
								$value = isset( $payout_info[$field->get_key()] ) ? $payout_info[$field->get_key()] : '';
								?>
								<tr>
									<th scope="row"><?php echo esc_html( $field->get_label() ); ?></th>
									<td>
										<p>
											<?php echo esc_html( $value ); ?>
										</p>
									</td>
								</tr>
							<?php endforeach; ?>
						</table>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>