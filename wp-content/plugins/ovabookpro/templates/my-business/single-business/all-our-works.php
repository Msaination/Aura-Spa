<?php defined( 'ABSPATH' ) || exit; ?>

<div class="single-business-part business-all-our-works-wrap">

	<div class="obp_breadcrumb_our_work">
		<a href="<?php echo esc_url( get_the_permalink($post_id) );?>" class="back-to-detail">
			<span class="back-symbol">
				<?php esc_html_e('&larr;','ovabookpro');?>
			</span>
			<?php esc_html_e('Back to business details','ovabookpro');?>
		</a>
	</div>

	<div class="all-our-works-header">
		<h1 class="business-name">
			<?php echo esc_html( get_the_title($post_id) );?>
		</h1>
		<span class="address">
			<?php echo esc_html($map_address);?>
		</span>
	</div>

	<?php if( !empty($chunked_works_images[0]) ) : ?>
		<div class="works-images-gallery">
		   <?php foreach( $chunked_works_images[0] as $img_id ) :
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
		   	?>
	            <div class="works-images-item" data-fancybox="<?php esc_attr_e('gallery','ovabookpro');?>" 
	            	data-caption="<?php echo esc_attr( $img_caption ); ?>"
	            	data-src="<?php echo esc_url( $img_popup ); ?>"
	            >
	                <img src="<?php echo esc_url( $img_url );?>" alt="<?php echo esc_attr( $img_alt );?>" title="<?php echo esc_attr( $img_alt );?>">
	            </div>
	        <?php endforeach; ?>
		</div>

		<div class="work-button">
			<a href="#" class="obp_button load_more" data-no_data="<?php echo esc_attr('No Data','ovabookpro');?>" data-key="1" 
				data-our_works_images="<?php echo esc_attr(json_encode($chunked_works_images));?>"
			>
				<?php echo esc_html__('Load More','ovabookpro');?>
			</a>
		</div>
	<?php else: ?>
		<div class="empty-list">
			<?php esc_html_e( 'This business has no portfolio photos yet.', 'ovabookpro' ); ?>
		</div>
	<?php endif; ?>
</div>