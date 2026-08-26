<?php defined( 'ABSPATH' ) || exit; ?>

<div class="obp-wrapper <?php echo esc_attr( $class ); ?>">

	<div class="obp-dashboard">

		<div class="obp-dashboard-nav">
			<?php do_action( 'obp_member_account_sidebar' ); ?>
		</div>

		<div class="obp-dashboard-content">
			<?php do_action( 'obp_before_main_content' ); ?>

			<div class="obp-main-content">
				<?php do_action( 'obp_main_content' ); ?>
			</div>

			<?php do_action( 'obp_after_main_content' ); ?>
		</div>

	</div>

</div>