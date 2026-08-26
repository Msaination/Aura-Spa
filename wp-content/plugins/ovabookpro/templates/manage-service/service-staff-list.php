<?php defined( 'ABSPATH' ) || exit; ?>

<?php if( ! empty( $list_user ) ) : ?>
	<?php foreach ( $list_user as $role_id => $staff ) :
		$role = get_post( $role_id );
		$role_name = $role_id ? $role->post_title : '';
		?>
		<div class="staff_group_item">
			<?php if ( $role_name ): ?>
				<label class="staff_group">
					<input type="checkbox" class="check_all_staff" value="1" />
					<?php echo esc_html( $role_name ); ?>
				</label>
			<?php endif; ?>
			
			<div class="obp-check-box-list-wrapper">
			<?php
				if ( $staff ) {
					foreach ( $staff as $staff_id => $nickname ) {
						?>
						<div class="check-box-list">
							<input type="checkbox" id="staff_id_<?php echo esc_attr( $staff_id );?>" name="staff_id" 
								value="<?php echo esc_attr( $staff_id );?>" <?php if( in_array( $staff_id, $staff_ids) ) { echo 'checked'; } ?> />
							<label for="staff_id_<?php echo esc_attr( $staff_id );?>">
								<?php echo esc_html( $nickname );?>	
							</label>
						</div>
						<?php
					}
				}
			?>
			</div>
		</div>
		<?php
	endforeach; ?>
<?php endif; ?>