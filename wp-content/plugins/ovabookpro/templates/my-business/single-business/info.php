<?php defined( 'ABSPATH' ) || exit; ?>

<div class="single-business-part business-info-wrap">

	<div class="business-info">
		<h1 class="business-name">
			<?php echo esc_html( get_the_title($post_id) );?>
		</h1>
		<span class="address">
			<?php echo esc_html($map_address);?>
		</span>
	</div>

	<div class="other-buttons">
		<?php if( !empty($video_url) ) : ?>
			<div class="btn-video" data-fancybox="<?php echo esc_attr('business-video');?>" data-src="<?php echo esc_url( $video_url ); ?>">
	            <i class="bookproicon-play" title="<?php esc_html_e('Watch video','ovabookpro');?>"></i>
	        </div>
	    <?php endif; ?>
	    <div class="btn-share">
	    	<ul class="share-social">
                <?php foreach ( $args_social as $name => $item_social ): ?>
                    <li>
                        <a href="<?php echo esc_url( $item_social['url'] ); ?>" target="_blank" rel="nofollow"
                        	class="<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( ucfirst($name) ); ?>"
                        >
                            <i aria-hidden="true" class="<?php echo esc_attr( $item_social['icon'] ); ?>"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
	    	<i class="bookproicon-upload" title="<?php esc_html_e('Share','ovabookpro');?>"></i>
	    </div>
		<div class="business-add-to-wishlist" role="button"
			data-id="<?php echo esc_attr($post_id);?>" data-status="<?php echo esc_attr( $status );?>"
			data-url="<?php echo esc_attr($login_url);?>" 
		>
		<?php echo wp_kses_post( $icon_wishtlist ); ?>
		</div>
	</div>

</div>