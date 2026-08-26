<?php defined( 'ABSPATH' ) || exit; 
    $user       = obp_get_user( wp_get_current_user()->ID );
    $value_wl   = $user->get_wishlist();
?>

<h1 class="obp-title">
	<?php esc_html_e( 'My Wishlist', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-my-wishlist">
	<div class="obp-form-part">
        <div class="obp_wishlist_table_wrapper">
            <table class="obp-table">
                <tr>
                    <th>
                        <?php esc_html_e( 'Business Name', 'ovabookpro' ); ?>
                    </th>
                    <th>
                        <?php esc_html_e( 'Address', 'ovabookpro' ); ?>
                    </th>
                </tr>
                <?php if ( ! empty( $value_wl ) ): ?>
                    
               
                    <?php foreach( $value_wl as $business_id ) :
                        $business  = obp_get_business($business_id);
                    ?>
                    <tr>
                        <td>
                            <div class="obp_wishlist_title_wrapper">
                                <a data-id="<?php echo esc_attr( $business_id ); ?>" class="remove-wishlist" href="#">
                                    <i class="bookproicon-close"></i>
                                </a>
                                <a class="business-name" href="<?php echo esc_url( $business->get_permalink() ); ?>">
                                    <?php echo esc_html( $business->get_name() ); ?>    
                                </a>
                            </div>
                        </td>

                        <td>
                            <?php echo esc_html( $business->get_full_address() ); ?> 
                        </td>
                    </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="2">
                            <?php esc_html_e("You don't have any business in wishlist.",'ovabookpro'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>