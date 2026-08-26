<?php defined( 'ABSPATH' ) || exit;
global $post;
$obj = obp_get_tax( $post->ID );
$class_is_show = $obj->get_country_code() ? 'is_showing' : '';
?>


<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="country_code">
					<?php esc_html_e( 'Country', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				<select name="country_code" id="country_code" class="obp-select2">
					<option value="">
						<?php esc_html_e( 'All', 'ovabookpro' ); ?>
					</option>
					<?php foreach ( obp_get_countries() as $key => $country ): ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $obj->get_country_code(), $key ); ?>>
							<?php echo esc_html( $country ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

		<tr class="obp_state_code <?php echo esc_attr( $class_is_show ); ?>">
			<th scope="row">
				<label for="state_code">
					<?php esc_html_e( 'State', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				
				<select name="state_code" id="state_code" style="width: 270px;" class="obp-select2" data-placeholder="<?php echo esc_attr( '*' ); ?>">
					<?php if ( isset( obp_get_states()[$obj->get_country_code()] ) ): ?>
						<?php foreach (obp_get_states()[$obj->get_country_code()] as $key => $value ): ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $obj->get_state_code(), $key ); ?>>
								<?php echo esc_html( $value ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</td>
		</tr>

		<tr class="obp_postcode_zip <?php echo esc_attr( $class_is_show ); ?>">
			<th scope="row">
				<label for="postcode_zip">
					<?php esc_html_e( 'Postcode/ ZIP', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				<input name="postcode_zip" type="text" id="postcode_zip" value="<?php echo esc_attr( $obj->get_postcode_zip() ); ?>" class="regular-text" autocomplete="off"  placeholder="*">
			</td>
		</tr>

		<tr class="obp_city <?php echo esc_attr( $class_is_show ); ?>">
			<th scope="row">
				<label for="city">
					<?php esc_html_e( 'City', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				<input name="city" type="text" id="city" value="<?php echo esc_attr( $obj->get_city() ); ?>" class="regular-text" autocomplete="off"  placeholder="*">
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="rate">
					<?php esc_html_e( 'Rate(%)', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				<input name="rate" type="text" id="rate" value="<?php echo esc_attr( obp_convert_price( $obj->get_rate() ) ); ?>" class="regular-text" required autocomplete="off">
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="priority">
					<?php esc_html_e( 'Priority', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				<input name="priority" type="number" id="priority" value="<?php echo esc_attr( $obj->get_priority() ); ?>" class="regular-number" autocomplete="off"  placeholder="1">
			</td>
		</tr>
	</tbody>
</table>