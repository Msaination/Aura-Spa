<?php defined( 'ABSPATH' ) || exit;
	$settings = apply_filters( 'obp_admin_settings', array() );
?>
<?php if ( $settings ):
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : current( array_keys( $settings ) );
?>
	<form method="POST" name="obp_options" action="options.php">
		<?php settings_fields( $this->settings->id ); ?>
		<div class="ovabp-settings-wrapper">
			<!-- Tabs -->
			<h2 class="nav-tab-wrapper obp-nav-tab-wrapper">
				<?php foreach ( $settings as $key => $title ): ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=obp_settings&tab=' . $key ) ); ?>"
						class="nav-tab<?php echo $current_tab === $key ? ' nav-tab-active' : ''; ?>"
						data-tab="<?php obp_esc_attr( $key ); ?>">
						<?php echo esc_html( $title ); ?>
					</a>
				<?php endforeach; ?>
			</h2>
			<div class="obp-wrapper-content">
				<?php foreach ( $settings as $key => $title ): ?>
					<div id="<?php echo esc_attr( $key ); ?>" class="obp-tab<?php echo $current_tab === $key ? ' nav-tab-active' : ''; ?>">
						<?php do_action( 'obp_admin_setting_' . $key . '_content' ); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php submit_button( esc_html__( 'Save Changes', 'ovabookpro' ), 'primary button-hero obp_btn_setting' ); ?>
	</form>
<?php endif; ?>