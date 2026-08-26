<?php defined( 'ABSPATH' ) || exit;

	// data option for carousel
	$data_options   = apply_filters( 'obp_single_business_carousel_options', array(
        'items'                 => 1,
        'slideBy'               => 1,
        'margin'                => 0,
        'autoplayHoverPause'    => true,
        'loop'                  => false,
        'autoplay'              => false,
        'autoplayTimeout'       => 3000,
        'smartSpeed'            => 500,
        'autoWidth'             => false,
        'center'                => false,
        'lazyLoad'              => true,
        'dots'                  => count($main_images) == 1 ? false : true,
        'nav'                   => count($main_images) == 1 ? false : true,
        'rtl'                   => is_rtl() ? true : false,
        'nav_left'              => is_rtl() ? 'bookproicon-arrow-right' : 'bookproicon-left',
        'nav_right'             => is_rtl() ? 'bookproicon-left' : 'bookproicon-arrow-right',
    ));

?>

<?php if( !empty($main_images) ) : ?>
    <div class="single-business-part business-gallery-wrap">
    	<div class="main-images-gallery owl-carousel owl-theme" data-options="<?php echo esc_attr( json_encode( $data_options ) ); ?>" >
    	   <?php foreach( $main_images as $img_id ) : 
                $img_url = wp_get_attachment_image_url( $img_id, 'large');
                $img_alt = get_post_meta($img_id, '_wp_attachment_image_alt', TRUE);
                              
                if ( ! $img_alt ) {
                    $img_alt = get_the_title( $img_id );
                }
            ?>
                <div class="main-images-item">
                    <img src="<?php echo esc_url($img_url);?>" alt="<?php echo esc_attr($img_alt);?>" title="<?php echo esc_attr($img_alt);?>">
                </div>
            <?php endforeach; ?>
    	</div>
    </div>
<?php endif; ?>