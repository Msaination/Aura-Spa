<?php defined( 'ABSPATH' ) || exit; 
	use BookPro\Business\OBP_Business;
	$enable_map_setting 	= OBP()->settings->general->get('enable_map','yes');
	$map_flatform 		= OBP()->settings->general->get('map_platform', 'google_map');
	$enable_map_fields 	= $enable_map_setting != 'yes' ? 'is_show' : '';

?>

<!-- business info -->
<div class="obp-form-part form-part-business">
	<h2 class="obp-second-title">
		<?php echo esc_html__( 'Business Infomation', 'ovabookpro' );?>	
	</h2>

	<!-- alert -->
	<div class="obp_validate_alert"></div>
	<div class="obp_status_alert"></div>

	<!-- avatar -->
	<div class="business_avatar">
		<label>
			<?php echo esc_html__( 'Avatar', 'ovabookpro' );?>	
		</label>
	
		<div class="profile-image">
			<?php if ( $id_avatar && $avatar_url ){ ?>
				<img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr__('Avatar','ovabookpro' ); ?>">
				<a href="#" class="remove_image" data-tippy-content="<?php echo esc_attr__( 'Remove Avatar', 'ovabookpro' ); ?>">
					<i class="icon-close bookproicon-close"></i>
				</a>
			<?php } ?>
		</div>
		
		<a class="obp_button opb_button_add_media" href="#" data-uploader-title="<?php echo esc_attr__( "Add image(s)", 'ovabookpro' ); ?>"
			data-button-text="<?php echo esc_attr__( "Add image", 'ovabookpro' ); ?>"
		>
			<?php echo esc_html__( "Browser", 'ovabookpro' ); ?>	
		</a>
		<input type="hidden" name="business_avatar" value="<?php echo esc_attr( $id_avatar ); ?>">
	</div>

	<!-- two_column: info -->
	<div class="obp_wrap_two_column">
		<div class="obp_column">
			<label for="business_name">
				<?php echo esc_html__('Business Name*','ovabookpro' );?>	
			</label>
			<input type="text" id="business_name" name="business_name" placeholder=""
				value="<?php echo esc_attr( $business_name ); ?>" required
			>
		</div>
		<div class="obp_column">
			<label for="business_phone">
				<?php echo esc_html__('Phone*','ovabookpro' );?>	
			</label>
			<input type="tel" id="business_phone" name="business_phone" value="<?php echo esc_attr( $phone ); ?>"required
			>
		</div>
		<div class="obp_column">
			<label for="business_email">
				<?php echo esc_html__('Email*','ovabookpro' );?>	
			</label>
			<input type="email" id="business_email" name="business_email"
				placeholder="<?php echo esc_attr__('email@gmail.com','ovabookpro');?>"
				value="<?php echo esc_attr( $email ); ?>" required
			>
		</div>
		
		<div class="obp_column">
			<label for="business_categories">
				<?php echo esc_html__('Categories*','ovabookpro' );?>	
			</label>

			<select name="business_categories" id="business_categories" class="obp-select2" data-placeholder="<?php echo esc_attr__( 'Select Multiple Categories','ovabookpro' ); ?>" multiple>
				<?php foreach ( obp_get_business_categories() as $term_id => $term_name ):
					$selected = in_array( $term_id, $selected_categories ) ? $term_id : false;
					?>
					<option value="<?php echo esc_attr( $term_id ); ?>" <?php selected( $selected, $term_id ); ?> >
						<?php echo esc_html( $term_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		
	</div>

	<div class="business_amenities">
		<label for="business_amenities">
			<?php echo esc_html__('Amenities','ovabookpro' );?>	
		</label>
		<select name="business_amenity" id="business_amenities" class="obp-select2" data-placeholder="<?php echo esc_attr__( 'Select Multiple Amenities','ovabookpro' ); ?>" multiple>

			<?php foreach ( obp_get_business_amenities() as $term_id => $term_name ):
					$selected = in_array( $term_id, $selected_amenities ) ? $term_id : false;
					?>
					<option value="<?php echo esc_attr( $term_id ); ?>" <?php selected( $selected, $term_id ); ?> >
						<?php echo esc_html( $term_name ); ?>
					</option>
				<?php endforeach; ?>
		</select>
	</div>

	<!-- description -->
	<div class="business_description">
		<label for="business_description">
			<?php echo esc_html__('Description','ovabookpro' );?>	
		</label>
		<?php 
			wp_editor( wpautop($description), 'business_description', $settings_editor );
		?>
	</div>

	<!-- map -->
	
	<div class="obp_map">
		<label>
			<?php echo esc_html__('Map','ovabookpro' );?>	
		</label>

		<div class="obp_map_show_hide">
			<label for="enable_map" class="obp_radio inline">
				<input type="radio" id="enable_map" name="enable_map" value="yes" <?php checked( $enable_map, 'yes' ); ?> >
				<span class="checkmark"></span>
				<?php echo esc_html__( 'Show', 'ovabookpro' ); ?>
			</label>
			<label for="hide_map" class="obp_radio inline">
				<input type="radio" id="hide_map" name="enable_map" value="no" <?php checked( $enable_map, 'no' ); ?> >
				<span class="checkmark"></span>
				<?php echo esc_html__( 'Hide', 'ovabookpro' ); ?>
			</label>
		</div>
		<div class="obp_map_container">
			<?php if ( $enable_map_setting == 'yes' ): ?>
				<?php if ( $map_flatform == 'google_map' ): ?>
					<div class="place-autocomplete-card" id="place-autocomplete-card">
				    	<p><?php echo esc_html__( 'Search for a place here:', 'ovabookpro' ); ?></p>
				    </div>

				<?php else: ?>
					<div class="auto-search-wrapper">
					  <input
					    type="text"
					    autocomplete="off"
					    id="search"
					    class="full-width"
					    placeholder="<?php echo esc_attr__( 'Search for a place here', 'ovabookpro' ); ?>"
					  />
					</div>
				<?php endif; ?>
				
				<div id="obp_enable_map"></div>
			<?php endif; ?>
			<div class="obp_map_fields <?php echo esc_attr( $enable_map_fields ); ?>">
				<div class="obp_wrap_two_column">

					<div class="obp_column">
						<label for="country_code">
							<?php echo esc_html__( 'Country Code', 'ovabookpro' ); ?>
						</label>
						<input type="text" id="country_code" name="country_code" value="<?php echo esc_attr( $business->get_country_code() ); ?>" />
					</div>

					<div class="obp_column">
						<label for="state">
							<?php echo esc_html__( 'State', 'ovabookpro' ); ?>
						</label>
						<input type="text" id="state" name="state" value="<?php echo esc_attr( $business->get_state() ); ?>" />
					</div>

					<div class="obp_column">
						<label for="postcode">
							<?php echo esc_html__( 'Postcode/ZIP', 'ovabookpro' ); ?>
						</label>
						<input type="text" id="postcode" name="postcode" value="<?php echo esc_attr( $business->get_postcode() ); ?>" />
					</div>

					<div class="obp_column">
						<label for="city">
							<?php echo esc_html__( 'City', 'ovabookpro' ); ?>
						</label>
						<input type="text" id="city" name="city" value="<?php echo esc_attr( $business->get_city() ); ?>" />
					</div>

					<div class="obp_column">
						<label for="full_address">
							<?php echo esc_html__( 'Full Address', 'ovabookpro' ); ?>
						</label>
						<input type="text" id="full_address" name="full_address" value="<?php echo esc_attr( $map_address ); ?>" />
					</div>
				</div>
			</div>
			<input type="hidden" name="map_latitude" value="<?php echo esc_attr( $map_lat ); ?>">
	        <input type="hidden" name="map_longitude" value="<?php echo esc_attr( $map_lng ); ?>">
		</div>
		
	</div>
	

	<!-- social -->
	<div class="business_socials">
		<label>
			<?php echo esc_html__( 'Socials', 'ovabookpro' ); ?>
		</label>
		
		<div class="social_list">
			<?php if( !empty( $socials ) ) : foreach ( $socials as $key => $value ) : ?>
				<div class="social_item">
					<select class="name_social" name="business_socials">
						<?php foreach ( OBP_Business::social_networks() as $key_name_social => $value_name_social ) : ?>
							<option value="<?php echo esc_attr( $key_name_social ); ?>"
								<?php echo esc_attr( $value['name_social'] == $key_name_social ? 'selected' : ''); ?>
							>
								<?php echo esc_html( $value_name_social ); ?>	
							</option>
						<?php endforeach; ?>
					</select>

					<input type="text" class="link_social" name="link_social" 
						value="<?php echo esc_attr( $value['link_social'] ); ?>"
						placeholder="<?php echo esc_attr__('Enter social link','ovabookpro' ); ?>"
					>

					<a href="#" class="remove_social">
						<i class="icon-close bookproicon-close"></i>
					</a>
				</div>
			<?php endforeach; endif; ?>				
		</div>

		<a href="#" class="obp_button add_social">
			<?php echo esc_html__( 'Add Social', 'ovabookpro' ); ?>
		</a>
	</div>
</div>