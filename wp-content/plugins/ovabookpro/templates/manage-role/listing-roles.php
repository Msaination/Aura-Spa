<?php defined( 'ABSPATH' ) || exit;?>


<div class="obp_role_notice">
	<?php if ( ! empty( $messages ) ): ?>
		<?php foreach ( $messages as $key => $mess ): ?>
			<div class="obp_alert_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $mess ); ?></div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>

<h2 class="obp-second-title obp_no_margin">
	<?php echo esc_html__('Listing Roles','ovabookpro' );?>	
</h2>


<div class="listing_roles"
data-btn="<?php echo esc_attr( json_encode( $data_button ) ); ?>"
data-mess="<?php echo esc_attr( json_encode( $data_mess ) ); ?>">

	<table class="obp_listing_role_table">
		<tr>
			<th></th>
			<th>
				<?php esc_html_e( 'Name', 'ovabookpro' ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Action', 'ovabookpro' ); ?>
			</th>
		</tr>

		<?php if ( ! empty( $roles ) ): $i = 1; ?>
			<?php foreach ( $roles as $role ): ?>

				<tr class="obp_role_info_row" data-role-id="<?php echo esc_attr( $role['id'] ); ?>">
					<td>
						<?php echo esc_html( $i ); ?>
					</td>
					<td>
						<?php echo esc_html( $role['name'] ); ?>
					</td>
					<td>
						<div class="role_action">
							<a href="#" class="obp_edit_role"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_edit_role' ) ); ?>"
							data-tippy-content="<?php echo esc_attr__( 'Edit Role', 'ovabookpro' ); ?>"
							data-id="<?php echo esc_attr( $role['id'] ) ?>">
								<i class="bookproicon-edit"></i>
							</a>
							<a href="#" class="obp_remove_role"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_remove_role' ) ); ?>"
							data-tippy-content="<?php echo esc_attr__( 'Remove Role', 'ovabookpro' ); ?>"
							data-id="<?php echo esc_attr( $role['id'] ); ?>">
								<i class="bookproicon-close"></i>
							</a>
						</div>
					</td>
				</tr>

				<tr class="edit-role-wrap" data-role-id="<?php echo esc_attr( $role['id'] ); ?>"></tr>
				
			<?php $i++; endforeach; ?>

		<?php else: ?>
			<tr>
				<td colspan="3"><?php esc_html_e( 'Role not found.', 'ovabookpro' ); ?></td>
			</tr>
		<?php endif; ?>
	</table>
</div>

	
