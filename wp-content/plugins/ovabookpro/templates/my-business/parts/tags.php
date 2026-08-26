<?php defined( 'ABSPATH' ) || exit;
?>


<div class="obp_column">
	<label for="business_tags">
		<?php echo esc_html__('Keywords for SEO','ovabookpro' ); ?>	
	</label>
	<input type="text" id="business_tags" name="business_tags" value="<?php echo esc_attr( $tags ); ?>"
		placeholder="<?php echo esc_attr__( 'Hair, Nail, Massage', 'ovabookpro' ); ?>" 
	>
	<div class="obp_business_tags_ajax"></div>
	<p class="business_description">
		<?php echo esc_html__( 'These keywords will serve the purpose of helping customers search for stores and services.', 'ovabookpro' ); ?>
		</p>
</div>
