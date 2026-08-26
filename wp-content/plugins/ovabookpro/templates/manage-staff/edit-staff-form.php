<?php defined( 'ABSPATH' ) || exit;

$required 	= ! $user_id ? 'required' : '';
?>

<form enctype="multipart/form-data" method="post" autocomplete="off">

	<div class="obp-form-part">

		<!-- alert -->
		<div class="obp_validate_alert"></div>
		<div class="obp_status_alert"></div>

		<!-- avatar -->
		<div class="staff_avatar">
			<label>
				<?php echo esc_html__( 'Avatar','ovabookpro' );?>	
			</label>
		
			<div class="profile-image">
				<?php if ($id_avatar && $avatar_url ){ ?>
					<img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr__('Avatar','ovabookpro');?>">
					<a href="#" class="remove_image" data-tippy-content="<?php esc_attr_e( 'Remove Avatar', 'ovabookpro' ); ?>">
						<i class="icon-close bookproicon-close"></i>
					</a>
				<?php } ?>
			</div>
			
			<a class="obp_button opb_button_add_media" href="#" data-uploader-title="<?php esc_attr_e( "Add image(s)", 'ovabookpro' ); ?>"
				data-button-text="<?php echo esc_attr__( "Add image", 'ovabookpro' ); ?>"
			>
				<?php echo esc_html__( "Browser", 'ovabookpro' ); ?>	
			</a>
			<input type="hidden" name="staff_avatar" class="" value="<?php echo esc_attr( $id_avatar ); ?>">
		</div>

		<!-- two_column -->
		<div class="obp_wrap_two_column">
			<div class="obp_column">
				<label for="username">
					<?php echo esc_html__('Username*','ovabookpro');?>	
				</label>
				<input type="text" id="username" name="username" placeholder="<?php echo esc_attr__('Username','ovabookpro');?>"
					<?php echo $user_id ? 'class="input-disabled" readonly' : ''; ?>
					value="<?php echo esc_attr( $username ); ?>" required />
			</div>
			<div class="obp_column">
				<label for="email">
					<?php echo esc_html__('Email*','ovabookpro');?>	
				</label>
				<input type="email" id="email" name="email" placeholder="<?php echo esc_attr__('email@gmail.com','ovabookpro');?>"
					value="<?php echo esc_attr( $email ); ?>" required />
			</div>
			<div class="obp_column">
				<label for="first_name">
					<?php echo esc_html__('First Name','ovabookpro');?>	
				</label>
				<input type="text" id="first_name" name="first_name" placeholder="<?php echo esc_attr__('John','ovabookpro');?>"
					value="<?php echo esc_attr( $first_name ); ?>"
				>
			</div>
			<div class="obp_column">
				<label for="last_name">
					<?php echo esc_html__('Last Name','ovabookpro');?>	
				</label>
				<input type="text" id="last_name" name="last_name" placeholder="<?php echo esc_attr__('Smith','ovabookpro');?>"
					value="<?php echo esc_attr( $last_name ); ?>"
				>
			</div>
			<div class="obp_column">
				<label for="nickname">
					<?php echo esc_html__('Nickname*','ovabookpro');?>	
				</label>
				<input type="text" id="nickname" name="nickname" placeholder="<?php echo esc_attr__('John Smith','ovabookpro');?>"
					value="<?php echo esc_attr( $nickname ); ?>" required
				>
			</div>
			<div class="obp_column">

				<label for="password">
					<?php esc_html_e( 'Password*', 'ovabookpro' ); ?>
				</label>
				<div class="obp-form-field">
					<div class="obp-password">
						<input type="password" name="password" id="password" class="" size="24" value="" <?php echo esc_attr( $required ); ?>>
						<i class="bookproicon-view"></i>
					</div>
				</div>
	
			</div>
			<div class="obp_column">
				<label for="position">
					<?php echo esc_html__('Position','ovabookpro');?>	
				</label>
				<input type="text" id="position" name="position" placeholder="<?php echo esc_attr__('Staff','ovabookpro');?>"
					value="<?php echo esc_attr( $position ); ?>"
				>
			</div>
			<div class="obp_column">
				<label for="staff_role">
					<?php echo esc_html__('Role*','ovabookpro');?>	
				</label>
				<select name="staff_role" id="staff_role">
					<option value="">
						<?php echo esc_html('Select Role','ovabookpro');?>
					</option>
					<?php if( !empty( $roles ) ) : foreach( $roles as $role_arr ): ?>
				        <option value="<?php echo esc_attr( $role_arr['id'] );?>"
				        	<?php echo $role == $role_arr['id'] ? 'selected' : ''; ?>
				        >
				            <?php echo esc_html( $role_arr['name'] ); ?>
				        </option>
				    <?php endforeach; endif; ?>
				</select>
			</div>
			<div class="obp_column">
				<label for="description">
					<?php esc_html_e( 'Description', 'ovabookpro' ); ?>
				</label>
				<textarea name="description" id="description" rows="5"><?php echo esc_html( $description ); ?></textarea>
			</div>
		</div>

		<!-- submit -->
		<div class="obp-form-submit align-right">
			<input type="submit" name="obp_update_staff" class="obp_button" value="<?php echo esc_attr( $text_button ); ?>">
			<input type="hidden" id="user_id" name="user_id" value="<?php echo esc_attr( $user_id ); ?>">
			<?php wp_nonce_field( 'obp_edit_staff_nonce', 'obp_edit_staff_nonce' ); ?>
		</div>

	</div>

</form>