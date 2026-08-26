<?php defined( 'ABSPATH' ) || exit;

?>

<li class="field_setting">
	<a class="obp_payout_method_remove_field" href="#">
		<span class="dashicons dashicons-no-alt"></span>
	</a>
	<div class="field_setting_head">
		<div class="action_group">
			
			<span class="sort_handle">
				<span class="dashicons dashicons-move"></span>
			</span>
			<span class="toggle_field_setting">
				<span class="dashicons dashicons-arrow-up"></span>
			</span>
		</div>
		<h3 class="field_label"></h3>
	</div>
	
	<table class="form-table">
		<tr>
			<th scope="row"><label><?php esc_html_e( 'Label *', 'ovabookpro' ); ?></label></th>
			<td><input name="<?php echo esc_attr( OBP_METABOX.'label[]' ); ?>" type="text" value="" required class="obp_label regular-text"></td>
		</tr>
		<tr>
			<th scope="row"><label><?php esc_html_e( 'Key (unique)*', 'ovabookpro' ); ?></label></th>
			<td><input name="<?php echo esc_attr( OBP_METABOX.'key[]' ); ?>" type="text" value="" required class="regular-text" placeholder="<?php echo esc_attr( '[^a-z0-9_\-]' ); ?>"></td>
		</tr>
		<tr>
			<th scope="row"><label><?php esc_html_e( 'Placeholder', 'ovabookpro' ); ?></label></th>
			<td><input name="<?php echo esc_attr( OBP_METABOX.'placeholder[]' ); ?>" type="text" value="" class="regular-text"></td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Required', 'ovabookpro' ); ?></th>
			<td>
				<label>
					<input name="<?php echo esc_attr( OBP_METABOX.'required[]' ); ?>" type="checkbox" value="1">
				</label>
			</td>
		</tr>
	</table>
</li>