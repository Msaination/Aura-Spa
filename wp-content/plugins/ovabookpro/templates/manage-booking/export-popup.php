<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp_export_popup_wrapper">
	<h2 class="subtitle"><?php echo esc_html__( 'Export Booking', 'ovabookpro' ); ?></h2>
	<p class="description"><?php echo esc_html__( 'Filter Bookings by booking Date Range and Criteria to export to CSV file.', 'ovabookpro' ); ?></p>

	<div class="messages"></div>

	<form class="obp_export_form" method="POST" autocomplete="off">
		<div class="date_time_wrapper">
			<div class="date_range_field">
				<input type="text" id="from_date_export" name="from_date_export" placeholder="<?php echo esc_attr__( 'From', 'ovabookpro' ); ?>">
				<input type="text" id="to_date_export" name="to_date_export" placeholder="<?php echo esc_attr__( 'To', 'ovabookpro' ); ?>">
			</div>
			<div class="date_filter_field">
				<select name="date_filter" class="obp-select2">
					<option value="service_date">
						<?php esc_html_e( 'Service Execution Date', 'ovabookpro' ); ?>
					</option>
					<option value="booking_date">
						<?php esc_html_e( 'Booking Date', 'ovabookpro' ); ?>
					</option>
				</select>
			</div>
			<div class="status_field">
				<select name="status_export" id="status_export" class="obp-select2">
					<option value="All">
						<?php echo esc_html__( 'All', 'ovabookpro' ); ?>
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
		</div>

		<ul class="export_fields">

			<li class="field">
				<label for="field_id">
					<input type="checkbox" value="id"
					class="export_field" name="field_id" id="field_id" checked="checked" />
					<span><?php echo esc_html__( 'ID', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_name">
					<input type="checkbox" value="name"
					class="export_field" name="field_name" id="field_name" checked="checked" />
					<span><?php echo esc_html__( 'Name', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_phone">
					<input type="checkbox" value="phone"
					class="export_field" name="field_phone" id="field_phone" checked="checked" />
					<span><?php echo esc_html__( 'Phone', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_email">
					<input type="checkbox" value="email"
					class="export_field" name="field_email" id="field_email" checked="checked" />
					<span><?php echo esc_html__( 'Email', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_note">
					<input type="checkbox" value="note"
					class="export_field" name="field_note" id="field_note" checked="checked" />
					<span><?php echo esc_html__( 'Note', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_services">
					<input type="checkbox" value="service"
					class="export_field" name="field_services" id="field_services" checked="checked" />
					<span><?php echo esc_html__( 'Services', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_payment_gateway">
					<input type="checkbox" value="payment_gateway"
					class="export_field" name="field_payment_gateway" id="field_payment_gateway" checked="checked" />
					<span><?php echo esc_html__( 'Payment Gateway', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_payment_method">
					<input type="checkbox" value="payment_method"
					class="export_field" name="field_payment_method" id="field_payment_method" checked="checked" />
					<span><?php echo esc_html__( 'Payment Method', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_date_created">
					<input type="checkbox" value="date_created"
					class="export_field" name="field_date_created" id="field_date_created" checked="checked" />
					<span><?php echo esc_html__( 'Date Created', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_status">
					<input type="checkbox" value="status"
					class="export_field" name="field_status" id="field_status" checked="checked" />
					<span><?php echo esc_html__( 'Status', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_tax">
					<input type="checkbox" value="tax"
					class="export_field" name="field_tax" id="field_tax" checked="checked" />
					<span><?php echo esc_html__( 'Tax', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_system_fee">
					<input type="checkbox" value="system_fee"
					class="export_field" name="field_system_fee" id="field_system_fee" checked="checked" />
					<span><?php echo esc_html__( 'System Fee', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<li class="field">
				<label for="field_vendor_profit">
					<input type="checkbox" value="vendor_total"
					class="export_field" name="field_vendor_profit" id="field_vendor_profit" checked="checked" />
					<span><?php echo esc_html__( 'Profit', 'ovabookpro' ); ?></span>
				</label>
			</li>

			<?php do_action( 'obp_manager_orders_export_columns' ); ?>

			<li class="field">
				<label for="field_total">
					<input type="checkbox" value="total"
					class="export_field" name="field_total" id="field_total" checked="checked" />
					<span><?php echo esc_html__( 'Total', 'ovabookpro' ); ?></span>
				</label>
			</li>

		</ul>

		<button type="submit" class="obp_button order_export_btn">
			<?php echo esc_html__( 'Export', 'ovabookpro' ); ?>
		</button>

	</form>
</div>