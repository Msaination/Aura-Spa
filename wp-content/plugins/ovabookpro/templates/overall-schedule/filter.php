<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_all_schedules_filter" data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_all_schedule_nonce' ) ); ?>">
	<div class="filter_title"><?php esc_html_e( 'Overall Schedule', 'ovabookpro' ); ?></div>
	<form class="filter_main" action="" method="POST" autocomplete="off" >
		<input type="text" id="obp_customer_name" placeholder="<?php esc_attr_e( 'Customer Name...', 'ovabookpro' ); ?>" />
		<select name="obp_staff" id="obp_staff">
			<option value=""><?php esc_html_e( 'All Staff', 'ovabookpro' ); ?></option>
			<?php if ( ! empty( $staff_list ) ): ?>
				<?php foreach ( $staff_list as $staff_id => $nickname ): ?>
					<option value="<?php echo esc_attr( $staff_id ); ?>">
						<?php echo esc_html( $nickname ); ?>
					</option>
				<?php endforeach; ?>
			<?php endif; ?>
		</select>
		<button type="submit" class="obp_all_schedule_submit">
			<i class="flaticon bookproicon-search"></i>
		</button>
	</form>
</div>