<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( $tags ): ?>
	
	<div class="single-business-part business-tags">
		<h2><?php esc_html_e( 'Tags', 'ovabookpro' ); ?></h2>
		<p class="tags-list"><?php echo esc_html( $tags ); ?></p>
	</div>

<?php endif; ?>