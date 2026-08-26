<?php defined( 'ABSPATH' ) || exit;?>

<div class="obp_head">
	<h2 class="obp_subtitle"><?php esc_html_e( 'Payout Method', 'ovabookpro' ); ?></h2>
	<a href="#" class="obp_button obp_set_payout_method"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_set_payout_method' ) ); ?>">
		<?php esc_html_e( 'Set Payout Method', 'ovabookpro' ); ?>
	</a>
	
</div>
	
<div class="payout_method_info">
	<?php if ( ! empty( $payout_method_fields ) ): ?>
		<?php if ( $payout_method_name ): ?>
			<div class="info_item">
				<span class="name"><?php echo esc_html( $payout_method_name ); ?></span>
			</div>
		<?php endif; ?>
		<?php foreach ( $payout_method_fields as $key => $item ):
			$field = obp_get_payout_field( $item );
			$key = $field->get_key();
			$value = isset( $payout_info[$key] ) ? $payout_info[$key] : '';
			?>

			<div class="info_item">
				<div class="label">
					<?php echo esc_html( $field->get_label() ); ?>
				</div>
				<div class="value">
					<?php echo esc_html( $value ); ?>
				</div>
			</div>

		<?php endforeach; ?>
	<?php endif; ?>
</div>

