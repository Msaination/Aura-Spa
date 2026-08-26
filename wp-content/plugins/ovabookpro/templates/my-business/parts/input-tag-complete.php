<?php defined( 'ABSPATH' ) || exit; ?>

<?php if ( ! empty( $business_tags ) ): ?>
	<ul class="obp_business_tag_complete">
		<?php foreach ( $business_tags as $tag ): ?>
			<li class="item" data-name="<?php echo esc_attr( $tag->name ); ?>">
				<?php echo esc_html( $tag->name ); ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>