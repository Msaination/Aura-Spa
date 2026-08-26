<?php defined( 'ABSPATH' ) || exit;
$package_type = isset( $package['type'] ) ? $package['type'] : 'radio';
$package_label = isset( $package['label'] ) ? $package['label'] : '';
?>

<div class="obp_service_package_group">
	<a href="#" class="obp_remmove_package_group">
		<i class="flaticon bookproicon-close"></i>
	</a>
	<div class="obp_service_three_column">
		<div class="obp_service_package_type">
			<label><?php echo esc_html__( 'Type', 'ovabookpro' ); ?></label>
			<select name="package_type">
				<option value="radio" <?php selected( $package_type, 'radio' ); ?>>
					<?php echo esc_html__( 'Radio', 'ovabookpro' ); ?>
				</option>
				<option value="select" <?php selected( $package_type, 'select' ); ?>>
					<?php echo esc_html__( 'Select', 'ovabookpro' ); ?>
				</option>
				<option value="checkbox" <?php selected( $package_type, 'checkbox' ); ?>>
					<?php echo esc_html__( 'Checkbox', 'ovabookpro' ); ?>
				</option>
			</select>
		</div>

		<div class="obp_service_package_label">
			<label><?php echo esc_html__( 'Label', 'ovabookpro' ); ?></label>
			<input type="text" name="package_label"
				placeholder="<?php echo esc_attr__( 'Label', 'ovabookpro' ); ?>"
				value="<?php echo esc_attr( $package_label ); ?>" />
		</div>
	</div>

	<div class="obp_service_package_grid">
		<div class="obp_heading_line">
			<div class="obp_column_name"><?php echo esc_html__( 'Name', 'ovabookpro' ); ?></div>
			<div class="obp_column_time"><?php echo esc_html__( 'Time', 'ovabookpro' ); ?></div>
			<div class="obp_column_price"><?php echo esc_html__( 'Price', 'ovabookpro' ); ?></div>
		</div>
		<div class="obp_body_container">
			<?php if ( ! empty( $package['data'] ) ): ?>
				<?php foreach ( $package['data'] as $key => $package_id ): ?>
					<?php obp_get_template( 'manage-service/service-package-item.php', array( 'package_id' => $package_id ) ); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="obp_service_button_wrap">
		<button type="button" class="obp_button obp_add_option">
			<?php echo esc_html__( 'Add Package Item', 'ovabookpro' ); ?>
		</button>
	</div>
	
</div>