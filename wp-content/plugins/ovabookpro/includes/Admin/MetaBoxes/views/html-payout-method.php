<?php defined( 'ABSPATH' ) || exit;
global $post;
$post_id = $post->ID;

$payout_method 			= obp_get_payout_method( $post_id );
$payout_setting_fields 	= $payout_method->get_setting_fields();

?>
<ul class="payout_method_settings">
<?php
	if ( ! empty( $payout_setting_fields ) ) {
		foreach ( $payout_setting_fields as $item ) {
			$field = obp_get_payout_field( $item );
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
					<h3 class="field_label"><?php echo esc_html( $field->get_label() ); ?></h3>
				</div>

				<table class="form-table">
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Label *', 'ovabookpro' ); ?></label></th>
						<td><input name="<?php echo esc_attr( OBP_METABOX.'label[]' ); ?>" type="text" value="<?php echo esc_attr( $field->get_label() ); ?>" required class="obp_label regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Key (unique)*', 'ovabookpro' ); ?></label></th>
						<td><input name="<?php echo esc_attr( OBP_METABOX.'key[]' ); ?>" type="text" value="<?php echo esc_attr( $field->get_key() ); ?>" required class="regular-text" placeholder="<?php echo esc_attr( '[^a-z0-9_\-]' ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Placeholder', 'ovabookpro' ); ?></label></th>
						<td><input name="<?php echo esc_attr( OBP_METABOX.'placeholder[]' ); ?>" type="text" value="<?php echo esc_attr( $field->get_placeholder() ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Required', 'ovabookpro' ); ?></th>
						<td>
							<label>
								<input name="<?php echo esc_attr( OBP_METABOX.'required[]' ); ?>" <?php checked( $field->get_required(), '1' ); ?> type="checkbox" value="1">
							</label>
						</td>
					</tr>
				</table>
			</li>
			<?php
		}
	} else {
		OBP()->include('Admin/MetaBoxes/views/html-payout-method-field.php');
	}
?>
</ul>

<a href="#" class="obp_add_field_payout_method button button-primary"
data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_add_field_payout_method' ) ); ?>">
	<?php esc_html_e( 'Add Field', 'ovabookpro' ); ?>
</a>