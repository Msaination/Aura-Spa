<?php defined( 'ABSPATH' ) || exit;
$list_hours 	= BookPro\Service\OBP_Service::get_hours();
$list_minutes 	= BookPro\Service\OBP_Service::get_minutes();
$package 		= obp_get_package( $package_id );
?>

<div class="obp_package_item">
	<a href="#" class="obp_remove_package">
		<i class="flaticon bookproicon-close"></i>
	</a>
	<div class="obp_column_name">
		<input type="hidden" name="package_id" value="<?php echo esc_attr( $package->get_id() ); ?>" />
		<input type="text" name="package_name"
		placeholder="<?php echo esc_attr__( 'Package Name', 'ovabookpro' ); ?>"
		value="<?php echo esc_attr( $package->get_name() ); ?>" />
	</div>
	<div class="obp_column_time">
		<div class="obp_hour_part">
			<label><?php echo esc_html__( 'Hour', 'ovabookpro' ); ?></label>
			<select name="package_hours">
				<?php foreach ( $list_hours as $key => $value ): ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $package->get_hours(), $key ); ?>>
						<?php echo esc_html( $value ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="obp_minute_part">
			<label><?php echo esc_html__( 'Minutes', 'ovabookpro' ); ?></label>
			<select name="package_minutes">
				<?php foreach ( $list_minutes as $key => $value ): ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $package->get_minutes(), $key ); ?>>
						<?php echo esc_html( $value ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div class="obp_column_price">
		<input type="text" name="package_price" placeholder="100" value="<?php echo esc_attr( obp_convert_price( $package->get_price() ) ); ?>" />
	</div>
</div>