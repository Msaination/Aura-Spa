<?php defined( 'ABSPATH' ) || exit; ?>

<!-- media -->
<div class="obp-form-part form-part-business">
	<h2 class="obp-second-title">
		<?php echo esc_html__('Media','ovabookpro' ); ?>	
	</h2>

	<div class="main_images business_images">
		<label>
			<?php echo esc_html__('Main Images','ovabookpro' ); ?>	
		</label>
		<a class="obp_button opb_button_add_media" href="#" data-uploader-title="<?php echo esc_attr__( "Add image(s)", 'ovabookpro' ); ?>"
			data-button-text="<?php echo esc_attr__( "Add image", 'ovabookpro' ); ?>"
		>
			<?php echo esc_html__( "Browser", 'ovabookpro' ); ?>	
		</a>

		<div class="gallery_list">
			<?php if ($main_images) : foreach ($main_images as $key => $value) : 
				$img_url = wp_get_attachment_image_src($value, 'thumbnail'); 
			?>
				<div class="gallery_item">
					<img src="<?php echo esc_url($img_url[0]); ?>" alt="<?php echo esc_attr__('Business images','ovabookpro' ); ?>">
					<a href="#" class="remove_image" data-tippy-content="<?php echo esc_attr__( 'Remove Image', 'ovabookpro' ); ?>">
						<i class="icon-close bookproicon-close"></i>
					</a>
					<input type="hidden" class="gallery_id" name="<?php echo esc_attr( 'main_images['.$key.']' ); ?>" 
						value="<?php echo esc_attr($value); ?>"
					>
				</div>
			<?php endforeach; endif; ?>
		</div>
	</div>

	<div class="our_works_images business_images">
		<label>
			<?php echo esc_html__('Our Work Images','ovabookpro' );?>	
		</label>
		<a class="obp_button opb_button_add_media" href="#" data-uploader-title="<?php echo esc_attr__( "Add image(s)", 'ovabookpro' ); ?>"
			data-button-text="<?php echo esc_attr__( "Add image", 'ovabookpro' ); ?>"
		>
			<?php echo esc_html__( "Browser", 'ovabookpro' ); ?>	
		</a>
		<div class="gallery_list">
			<?php if ( $our_works_images ) : foreach ( $our_works_images as $key => $value ) : 
				$img_url = wp_get_attachment_image_src($value, 'thumbnail'); 
			?>
				<div class="gallery_item">
					<img src="<?php echo esc_url( $img_url[0] ); ?>" alt="<?php echo esc_attr__('Business images','ovabookpro' ); ?>">
					<a href="#" class="remove_image" data-tippy-content="<?php echo esc_attr__( 'Remove Image', 'ovabookpro' ); ?>">
						<i class="icon-close bookproicon-close"></i>
					</a>
					<input type="hidden" class="gallery_id" name="<?php echo esc_attr( 'main_images['.$key.']' ); ?>" 
						value="<?php echo esc_attr($value); ?>"
					>
				</div>
			<?php endforeach; endif; ?>
		</div>
	</div>

	<div class="video_url">
		<label for="video_url">
			<?php echo esc_html__('Video URL','ovabookpro' );?>	
		</label>
		<input type="text" id="video_url" name="video_url" value="<?php echo esc_attr( $video_url ); ?>" 
			placeholder="<?php echo esc_attr( "https://www.youtube.com/watch?v=MLpWrANjFbI" ); ?>"
		>
	</div>
	<!-- Seo keywords -->
	<?php obp_my_business_tags( $args ); ?>
</div>