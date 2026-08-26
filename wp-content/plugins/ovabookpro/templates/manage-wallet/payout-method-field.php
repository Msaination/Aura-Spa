<?php
defined( 'ABSPATH' ) || exit;

if ( $payout_method_id_selected ):
	$payout_method = obp_get_payout_method( $payout_method_id_selected );
	$payout_fields = $payout_method->get_setting_fields();
		
	if ( count( $payout_fields ) > 0 ): ?>
		<?php foreach ( $payout_fields as $item ):
			$field = obp_get_payout_field( $item );
			$value = isset( $payout_info[$field->get_key()] ) ? $payout_info[$field->get_key()] : '';
			$required = $field->get_required() ? ' *' : '';
			?>
			<tr>
				<td>
					<span class="label"><?php echo esc_html( $field->get_label().$required ); ?></span>
				</td>
				<td>
					<input type="text" class="input_setting" name="<?php echo esc_attr( $field->get_key() ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					placeholder="<?php echo esc_attr( $field->get_placeholder() ); ?>" data-required="<?php echo esc_attr( $field->get_required() ); ?>">
				</td>
			</tr>

		<?php endforeach;
	endif;
endif; ?>