<div class="obp-second-title-with-filter">
	<div class="obp-filter-part">
		<div class="obp-col obp-col-left">
			<div class="obp-date-range">
				<input type="text" class="from_date"
				name="from_date" id="from_date" value=""
				autocomplete="off"
				placeholder="<?php esc_attr_e('From ...','ovabookpro');?>">

				<input type="text" class="to_date"
				name="to_date" id="to_date" value=""
				autocomplete="off"
				placeholder="<?php esc_attr_e('To ...','ovabookpro');?>">
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
					<?php echo esc_html__('Status:','ovabookpro');?>	
				</label>
				<select name="post_order_status" id="post_order_status">
					<option value="All">
						<?php esc_html_e('All','ovabookpro');?>
					</option>
					<option value="obp_pending">
						<?php esc_html_e('Pending','ovabookpro');?>
					</option>
					<option value="obp_processing">
						<?php esc_html_e('Processing','ovabookpro');?>
					</option>
					<option value="obp_completed">
						<?php esc_html_e('Completed','ovabookpro');?>
					</option>
					<option value="obp_cancelled">
						<?php esc_html_e('Cancelled','ovabookpro');?>
					</option>
					<option value="obp_refunded">
						<?php esc_html_e('Refunded','ovabookpro');?>
					</option>
					<option value="obp_expired">
						<?php esc_html_e('Expired','ovabookpro');?>
					</option>
				</select>
			</div>
			<i class="search-order bookproicon-search"
			title="<?php echo esc_attr__('Search','ovabookpro');?>"></i>
		</div>
	</div>
</div>