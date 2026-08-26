<?php defined( 'ABSPATH' ) || exit;

use BookPro\Type\OBP_Type;

$types = OBP_Type::get_type_ajax();

?>

<h1 class="obp-title">
	<?php echo esc_html__( 'Listing Type', 'ovabookpro' ); ?>	
</h1>

<div class="obp-content obp-content-type">

	<div class="obp-form-part">

		<!-- list type -->
		<div class="obp-data-list-wrapper type-list-wrapper" >
			<table class="obp_type_list_table">
				<tr>
					<th>
						<?php echo esc_html__( 'ID', 'ovabookpro' ); ?>
					</th>
					<th>
						<?php echo esc_html__( 'Name', 'ovabookpro' ); ?>
					</th>
					<th>
						<?php echo esc_html__( 'Action', 'ovabookpro' ); ?>
					</th>
				</tr>
				<?php if ( $types->have_posts() ):
					?>
					<?php while ( $types->have_posts() ) {
						$types->the_post();
						$type = obp_get_type( get_the_ID() );
						obp_get_template( "manage-type/type-item.php", array( 'type' => $type ) );
					} ?>

				<?php else: ?>
					<tr>
						<td colspan="3">
							<?php echo esc_html__( 'Types not found.', 'ovabookpro' ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		</div>

		<!-- Add type: show popup -->
		<div class="obp-button-wrapper align-right">
			<input type="hidden" id="current_language" name="current_language" value="<?php echo esc_attr( obp_get_current_language() ); ?>" />
			<a href="#" class="obp_add_type_popup">
				<input type="button" name="obp_add_type" class="obp_button"
					value="<?php echo esc_attr__( 'Add Type', 'ovabookpro' ); ?>">
			</a>
		</div>
	</div>

</div>