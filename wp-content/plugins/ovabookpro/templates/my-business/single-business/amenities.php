<?php defined( 'ABSPATH' ) || exit; ?>

<?php if( !empty($amenities) ) : ?>
	<div class="single-business-part business-amenities-wrap">
		<h2 class="">
			<?php esc_html_e( 'Amenities', 'ovabookpro' ); ?>
		</h2>

		<?php if( is_array($amenities) ) : ?>
			<div class="amenities-wrap">
				<?php foreach($amenities as $amenity) : 
					$class_icon = get_term_meta( $amenity->term_id, 'class_icon', true );
				?>
					<div class="amenity-item">
						<i class="<?php echo esc_attr($class_icon);?>"></i>
						<?php echo esc_html($amenity->name);?>
					</div>
				<?php endforeach;?>
			</div>
		<?php endif;?>
	</div>
<?php endif;?>