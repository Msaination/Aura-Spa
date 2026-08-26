<?php defined( 'ABSPATH' ) || exit; ?>

<?php if( !empty( $our_works_images) ) : ?>

	<div class="single-business-part business-our-work-wrap">
		<h2>
			<?php esc_html_e( 'See Our Work', 'ovabookpro' ); ?>
		</h2>
		<div class="works-images-gallery">
		   <?php foreach( $our_works_images as $k => $img_id ) :
		   		$img_url      = wp_get_attachment_image_url( $img_id, 'medium');
		   		$img_popup    = wp_get_attachment_image_url( $img_id, 'large');
		   		$img_alt      = get_post_meta($img_id, '_wp_attachment_image_alt', TRUE);
		   		$img_caption  = wp_get_attachment_caption( $img_id );

		   		if ( ! $img_alt ) {
	                $img_alt = get_the_title( $img_id );
	            }
	            if ( ! $img_caption ) {
	                $img_caption = $img_alt;
	            }
	            if ( $k == 0 ) {
	                $img_url = wp_get_attachment_image_url( $img_id, 'large');
	            }
		   	?>
	            <div class="works-images-item" data-fancybox="<?php esc_attr_e('gallery','ovabookpro');?>" 
	            	data-caption="<?php echo esc_attr( $img_caption ); ?>"
	            	data-src="<?php echo esc_attr( $img_popup ); ?>"
	            >
	                <img src="<?php echo esc_url( $img_url );?>" alt="<?php echo esc_attr( $img_alt );?>" title="<?php echo esc_attr( $img_alt );?>">
	            </div>
	        <?php if($k == 4) break; endforeach; ?>
		</div>
		
		<div class="work-button">
			<a href="<?php echo esc_url( $all_works_url );?>" class="obp_button see_all">
				<?php echo esc_html__('See All Works','ovabookpro');?>
			</a>
		</div>
	</div>
<?php endif; ?>