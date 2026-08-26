<?php defined( 'ABSPATH' ) || exit; ?>

<h2 class="obp-second-title add_role_title">
	<?php echo esc_html__('Add role','ovabookpro' );?>	
</h2>

<form action="" class="obp_add_role_form" method="POST" autocomplete="off">
	<div class="obp_add_role_notice"></div>
	<?php wp_nonce_field( 'obp_save_role', 'obp_add_role_nonce' ); ?>
	<div class="obp_add_role_wrap">
		<div class="obp_add_role_column">

				<label for="role_name">
					<?php echo esc_html__( 'Role Name*', 'ovabookpro' ); ?>
				</label>
				<input type="text" id="role_name" class="no-margin"
				placeholder="<?php echo esc_attr__( 'Staff', 'ovabookpro' ); ?>">
		</div>
		<div class="obp_add_role_column">
			<label><?php echo esc_html__( 'Capabilities', 'ovabookpro' ); ?></label>
			<table class="obp_cap_table">
				<?php if ( ! empty( $capabilities ) ): ?>
					<?php foreach ( $capabilities as $cap ): ?>
						<tr>
							<?php foreach ( $cap as $key => $value ):
								$checked = $key == 'staff_schedule' ? true : false;
								?>
								<td>
									<label for="<?php echo esc_attr( $key ); ?>" class="obp_label_cap">
										<input type="checkbox" value="<?php echo esc_attr( $key ); ?>"
										class="capabilities" <?php checked( $checked ); ?> name="capabilities[]" id="<?php echo esc_attr( $key ); ?>">
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
		name="obp_add_role"
		class="obp_button"
		value="<?php echo esc_attr__( 'Add Role', 'ovabookpro' ); ?>" />
	</div>
</form>
<div class="add_role_errors" data-error="<?php echo esc_attr( json_encode( $errors ) ); ?>"></div>