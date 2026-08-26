<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_plan_table_wrap">

	<h2 class="obp-second-title">
		<?php echo esc_html__('All Plans','ovabookpro'); ?>	
	</h2>

	<div class="obp_plan_list_items"
		data-time-slots="<?php echo esc_attr( json_encode( $time_slots ) ); ?>"
		data-btn="<?php echo esc_attr( json_encode( $data_button ) ); ?>"
		data-mess="<?php echo esc_attr( json_encode( $data_mess ) ); ?>">

		<?php if ( ! empty( $list_plans ) ): ?>
			
			<?php foreach ( $list_plans as $item ): ?>
				<div class="obp_plan_item">
				<table>
					<tr>
						<td>
							<div class="date-column <?php echo esc_attr( $item->get_class_color() ); ?>" data-plan-id="<?php echo esc_attr( $item->get_id() ); ?>">
								<div class="date"><?php echo esc_html( date_i18n( $date_format, $item->get_start_date() ) ); ?></div>
								<i class="bookproicon-remove"></i>
								<div class="date"><?php echo esc_html( date_i18n( $date_format, $item->get_end_date() ) ); ?></div>
							</div>
							
						</td>
						<td>
							<div class="status"><?php echo esc_html( $status_arr[$item->get_status()] ); ?></div>
						</td>
						<td>
							<div class="services">
								<?php
								if (  $item->get_service_type() != 'all_services' ) {
									echo esc_html__( 'Some Services', 'ovabookpro' );
								} else {
									echo esc_html__( 'All Services', 'ovabookpro' );
								}
								?>
							</div>
						</td>
						<td>
							<div class="times">
								<?php
								$time_type = $item->get_time_type();
								if ( $time_type !== 'full_time' ) {
									echo esc_html__( 'Custom Time', 'ovabookpro' );
								} else {
									echo esc_html__( 'Full Time', 'ovabookpro' );
								}
								?>
							</div>
						</td>
						<td>
							<a href="#" class="obp_edit_plan"
							data-tippy-content="<?php echo esc_attr__( 'Edit Plan', 'ovabookpro' ); ?>"
							data-nonce="<?php echo esc_attr( wp_create_nonce('obp_edit_plan') ); ?>"
							data-id="<?php echo esc_attr( $item->get_id() ); ?>">
								<i class="bookproicon-edit"></i>
							</a>
							<a href="#" class="obp_remove_plan"
							data-tippy-content="<?php echo esc_attr__( 'Remove Plan', 'ovabookpro' ); ?>"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_remove_plan' ) ); ?>"
							data-id="<?php echo esc_attr( $item->get_id() ); ?>">
								<i class="bookproicon-close"></i>
							</a>
						</td>
					</tr>
				</table>
			<div class="edit-plan-wrapper"></div>
		</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<div class="obp-button-wrapper align-right">

		<input type="button"
		name="obp_add_plan"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'obp_add_plan' ) ); ?>"
		class="obp_button"
		value="<?php echo esc_attr__( 'Add Plan', 'ovabookpro' ); ?>" />

	</div>

	<div class="add-plan-wrapper"></div>

</div>
