<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( isset( $field['mesg'] ) && $field['mesg'] ): ?>
	<div id="obp-warning">
		<p class="obp-warning-mesg"><?php obp_esc_html( $field['mesg'] ); ?></p>
	</div>
<?php endif; ?>