<?php defined( 'ABSPATH' ) || exit; ?>
<td colspan="3">
	<form action="" method="POST" class="obp_save_role_form" autocomplete="off">
		<div class="obp_save_role_notice"></div>
		<?php wp_nonce_field( 'obp_save_role', 'obp_save_role_nonce', false ); ?>
		<span class="obp_remove_edit_form">
			<i class="flaticon bookproicon-close"></i>
		</span>
		<div class="obp_role_edit_wrap">
			<div class="obp_role_edit_column">
				<label for="edit_role_name"><?php echo esc_html__( 'Role Name*', 'ovabookpro' ); ?></label>
				<input type="text" id="edit_role_name" class="no-margin"
				value="<?php echo esc_attr( $role_name ); ?>"
				placeholder="<?php echo esc_attr__( 'Employee', 'ovabookpro' ); ?>" />
				<input type="hidden" name="role_id" id="role_id" value="<?php echo esc_attr( $post_id ); ?>" />
			</div>
			<div class="obp_role_edit_column">
				<label><?php echo esc_html__( 'Capabilities', 'ovabookpro' ); ?></label>
				<table class="obp_cap_table">
					<?php if ( ! empty( $capabilities ) ): ?>
						<?php foreach ( $capabilities as $cap ): ?>
							<tr>
								<?php foreach ( $cap as $key => $value ):
									$checked = in_array($key, $caps) ? $key : '';
									?>
									<td>
										<label for="<?php echo esc_attr( 'edit_'.$key ); ?>" class="obp_label_cap">
											<input type="checkbox" value="<?php echo esc_attr( $key ); ?>"
											class="capabilities" name="capabilities[]" id="<?php echo esc_attr( 'edit_'.$key ); ?>" <?php checked( $checked, $key ); ?> />
											<span><?php echo esc_html( $value ); ?></span>
										</label>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</table>
			</div>
		</div>
		<div class="obp-button-wrapper align-right">
			<input type="submit"
			name="obp_save_role"
			class="obp_button"
			value="<?php echo esc_attr__( 'Update Role', 'ovabookpro' ); ?>" />
		</div>
	</form>
</td>