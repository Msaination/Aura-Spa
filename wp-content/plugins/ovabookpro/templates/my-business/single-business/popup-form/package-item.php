<?php defined( 'ABSPATH' ) || exit;
$k 				= isset( $k ) ? $k : '0';
$type 			= isset( $package['type'] ) ? $package['type'] : 'radio';
$label 			= isset( $package['label'] ) ? $package['label'] : '';
$data 			= isset( $package['data'] ) ? $package['data'] : [];
$package_ids 	= $item->get_package_ids();
if ( ! empty( $data ) ) {
	switch ( $type ) {
		case 'radio':
	?>
		<div class="package_group" data-type="<?php echo esc_attr( 'radio' ); ?>">
			<div class="package_label"><?php echo esc_html( $label ); ?></div>
			<?php
			foreach ( $data as $key => $package_id ) {
				$package_item 	= obp_get_package( $package_id );
				$checked 		= in_array($package_id, $package_ids );
				?>
				<div class="package-item">
					<label class="obp_radio">
						<input type="radio" name="package_<?php echo esc_attr( $k ); ?>"
						class="package_input package_input_radio"
						<?php checked( $checked ); ?>
						value="<?php echo esc_attr( $package_id ); ?>">
						<span class="checkmark"></span>
						<?php echo wp_kses_post( $package_item->get_label() ); ?>
					</label>
					<?php if ( $checked ): ?>
						<a href="#" class="obp_remove_package">
							<i class="flaticon bookproicon-close"></i>
						</a>
					<?php endif; ?>
				</div>
				<?php
			}
		?>
		</div>
	<?php
		break;
		case 'select':
		?>
		<div class="package_group" data-type="<?php echo esc_attr( 'select' ); ?>">
			<div class="package_label"><?php echo esc_html( $label ); ?></div>
			<select class="package_input package_select obp-select2" name="package_<?php echo esc_attr( $k ); ?>">
				<option value="">
					<?php esc_html_e( 'Choose a package', 'ovabookpro' ); ?>		
				</option>
			<?php
			foreach ( $data as $key => $package_id ) {
				$package_item 	= obp_get_package( $package_id );
				$selected 		= in_array($package_id, $package_ids);
				?>
				<option value="<?php echo esc_attr( $package_id ); ?>" <?php selected( $selected ); ?> >
					<?php echo wp_kses_post( $package_item->get_label() ); ?>
				</option>
				<?php
			}
			?>
			</select>
		</div>
		<?php
		break;

		case 'checkbox':
		?>
		<div class="package_group" data-type="<?php echo esc_attr( 'checkbox' ); ?>">
			<div class="package_label"><?php echo esc_html( $label ); ?></div>
			<?php
			foreach ( $data as $key => $package_id ) {
				$package_item 	= obp_get_package( $package_id );
				$checked 		= in_array( $package_id, $package_ids );
				?>
				<div class="package-item">
					<label class="obp_checkbox">
						<input type="checkbox" name="package_<?php echo esc_attr( $k ); ?>[]"
						class="package_input package_input_checkbox"
						<?php checked( $checked ); ?>
						value="<?php echo esc_attr( $package_id ); ?>">
					<?php echo wp_kses_post( $package_item->get_label() ); ?></label>
				</div>
				<?php
			}
		?>
		</div>
		<?php
		default:
		break;
	}
}

?>
