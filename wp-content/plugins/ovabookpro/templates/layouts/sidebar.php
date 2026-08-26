<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-dashboard-inner">

    <div class="obp-dashboard-nav-top">
		<div class="obp-user-profile">
			<div class="profile-image">
				<?php if ( $user->get_avatar() ):
					echo wp_kses_post( $user->get_avatar() );
				else: ?>
					<img src="<?php echo esc_url( $user->get_avatar_url() );?>">
				<?php endif; ?>
			</div>

			<div class="profile-info">
				<h4 class="profile-name">
					<?php echo esc_html( $user->get_nickname() ); ?>	
				</h4>
				<?php if ( ! BookPro\OBP_Permission::is_customer() ): ?>
					<a
						href="<?php echo esc_url( $business->get_permalink() ); ?>"
						class="profile-link has-underline">
						<?php esc_html_e( 'View Business', 'ovabookpro' ); ?>	
					</a>
				<?php endif; ?>
				
			</div>
		</div>

		<div class="nav-main-mobile-toggle">
	    	<span></span>
	    </div>
    </div>

	<ul class="obp-dashboard-nav-main">
		<?php do_action( 'obp_dashboard_nav_content' ); ?>
	</ul>

</div>