<?php defined( 'ABSPATH' ) || exit;
?>

<tr class="obp_edit_type_info" data-id="<?php echo esc_attr( $type->get_id() ); ?>">
	<td>
		<?php echo esc_html( $type->get_id() ); ?>
	</td>
	<td>
		<?php echo esc_html( $type->get_name() ); ?>
	</td>
	<td>
		<div class="obp_type_action_wrapper">
			<input type="hidden" name="type_id" value="<?php echo esc_attr( $type->get_id() ); ?>" />
			<a href="#" class="obp_show_edit_type"
				data-tippy-content="<?php echo esc_attr__( 'Edit Type', 'ovabookpro' ); ?>">
				<i class="bookproicon-edit"></i>
			</a>
			<a href="#" class="obp_delete_type"
				data-tippy-content="<?php echo esc_attr__( 'Remove Type', 'ovabookpro' ); ?>">
				<i class="bookproicon-close"></i>
			</a>
		</div>
	</td>
</tr>
<tr class="obp_edit_type_row" data-id="<?php echo esc_attr( $type->get_id() ); ?>">
	<td colspan="3" class="obp_edit_type_wrapper" data-id="<?php echo esc_attr( $type->get_id() ); ?>">
	</td>
</tr>