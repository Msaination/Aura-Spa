<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! empty( $services ) ): ?>
			
	<?php foreach ( $services as $key => $group ):
		$count = isset( $group['services'] ) ? count( $group['services'] ) : 0;
		if ( $count > 0 ) {
		?>
		<div class="service-section">
			<div class="service-type">
				<i class="bookproicon-right-arrow"></i>
				<h3 class="service-type-name">
					<?php echo esc_html( $group['category'] ); ?>
				</h3>
			</div>
			<div class="service-counter">
				<?php // translators: %s: number of services.
				echo wp_kses_post( sprintf( _n( '%s Service', '%s Services', $count, 'ovabookpro' ), number_format_i18n( $count ) ) ); ?>
			</div>
		</div>
		<?php
		obp_get_template( "my-business/single-business/service-items.php", array( 'service_ids' => $group['services'] ) );
		}
		?>
	<?php endforeach; ?>
<?php else: ?>
	<div class="empty-list">
		<?php esc_html_e( 'Servies not found.', 'ovabookpro' ); ?>
	</div>
<?php endif; ?>
