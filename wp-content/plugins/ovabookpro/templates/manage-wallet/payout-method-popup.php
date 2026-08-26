<?php defined( 'ABSPATH' ) || exit; ?>


<div class="obp_payout_method_popup">
	<h2 class="obp_subtitle">
		<?php esc_html_e( 'Setup Payout Method', 'ovabookpro' ); ?>
	</h2>
	<form action="#" class="payout_method_setup_form"
	data-nonce="<?php echo esc_attr( wp_create_nonce('obp_update_payout_method') ); ?>"
	data-error="<?php esc_attr_e( 'Please fill in the information completely.', 'ovabookpro' ); ?>"
	method="POST"
	autocomplete="off">

		<div class="messages"></div>

		<table class="obp_table payout_method">
			<tr>
				<td>
					<span class="label"><?php esc_html_e( 'Payout method', 'ovabookpro' ); ?></span>
				</td>
				<td>
					<?php if ( count( $payout_methods ) > 0 ): ?>
						<?php foreach ( $payout_methods as $key => $payout_method ):
							$checked = $key == 0 ? $payout_method->get_id() : '';
							if ( $payout_method_id ) {
								$checked = $payout_method_id == $payout_method->get_id() ? $payout_method_id : '';
							}
							?>
							<label class="label_payout_method obp_radio">
								<input type="radio" name="payout_method"
								<?php checked( $checked, $payout_method->get_id() ); ?>
								value="<?php echo esc_attr( $payout_method->get_id() ); ?>">
								<span class="checkmark"></span>
								<span class="label_inline"><?php echo esc_html( $payout_method->get_name() ); ?></span>
							</label>
						<?php endforeach; ?>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<table class="obp_table payout_method_field_settings">
			<?php
			obp_payout_method_field( $args );
			?>
		</table>

		<div class="obp_payout_footer">
			<button type="submit"
			class="obp_update_payout_method">
				<?php esc_html_e( 'Update', 'ovabookpro' ); ?>
			</button>
		</div>
		

	</form>
</div>