<div class="obp-second-title-with-filter">
	<div class="obp-filter-part">
		<div class="obp-col obp-col-left">
			<input type="text" name="customer_name" value="" class="obp-search-name" autocomplete="off"
			placeholder="<?php echo esc_attr__( 'Customer Name', 'ovabookpro' ); ?>" />

			<div class="obp-date-range">
				<input type="text" class="from_date"
				name="from_date" id="from_date" value=""
				autocomplete="off"
				placeholder="<?php echo esc_attr__('From ...','ovabookpro'); ?>">

				<input type="text" class="to_date"
				name="to_date" id="to_date" value=""
				autocomplete="off"
				placeholder="<?php echo esc_attr__('To ...','ovabookpro'); ?>">
			</div>
		</div>
		<div class="obp-col obp-col-right">
			<div class="obp-order">
				<label for="date_filter" class="visuallyhidden">
					<?php echo esc_html__('Booking Date:','ovabookpro');?>	
				</label>
				<select id="date_filter" name="date_filter">
					<option value="service_date">
						<?php esc_html_e( 'Service Execution Date', 'ovabookpro' ); ?>
					</option>
					<option value="booking_date">
						<?php esc_html_e( 'Booking Date', 'ovabookpro' ); ?>
					</option>
				</select>
			</div>
			<div class="obp-order">
				<label for="post_order_status" class="visuallyhidden">
					<?php echo esc_html__( 'Status:', 'ovabookpro' ); ?>	
				</label>
				<select name="post_order_status" id="post_order_status">
					<option value="All">
						<?php echo esc_html__('All','ovabookpro'); ?>
					</option>
					<option value="obp_pending">
						<?php echo esc_html__('Pending','ovabookpro'); ?>
					</option>
					<option value="obp_processing">
						<?php echo esc_html__('Processing','ovabookpro'); ?>
					</option>
					<option value="obp_completed">
						<?php echo esc_html__('Completed','ovabookpro'); ?>
					</option>
					<option value="obp_cancelled">
						<?php echo esc_html__('Cancelled','ovabookpro'); ?>
					</option>
					<option value="obp_refunded">
						<?php echo esc_html__('Refunded','ovabookpro'); ?>
					</option>
					<option value="obp_expired">
						<?php echo esc_html__('Expired','ovabookpro'); ?>
					</option>
				</select>
			</div>
			<i class="search-order bookproicon-search"
			title="<?php echo esc_attr__('Search','ovabookpro'); ?>"></i>
		</div>
	</div>
</div>