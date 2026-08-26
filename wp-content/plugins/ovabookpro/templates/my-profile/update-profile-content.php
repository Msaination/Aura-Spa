<?php defined( 'ABSPATH' ) || exit;?>

<form action="" class="obp_update_profile_form" method="POST" enctype="multipart/form-data" autocomplete="off">
	<?php wp_nonce_field( 'update_my_profile', 'update_profile_nonce' ); ?>
	<div class="obp-form-part">

		<div class="obp_update_profile_notice">
			<?php if ( ! empty( $messages ) ): ?>
				<?php foreach ( $messages as $key => $mess ): ?>
					<div class="obp_alert_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $mess ); ?></div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<?php if ( obp_is_vendor() || obp_is_staff() ): ?>

			<div class="profile_avatar">
				<label>
					<?php echo esc_html__('Avatar','ovabookpro');?>	
				</label>
			
				<div class="profile-image">
					<?php if ( $user->get_obp_avatar_url() ): ?>
						<img src="<?php echo esc_url( $user->get_obp_avatar_url() ); ?>"
						alt="<?php echo esc_attr__('Avatar','ovabookpro');?>">
						<a href="#" class="remove_image" data-tippy-content="<?php esc_attr_e( 'Remove Avatar', 'ovabookpro' ); ?>">
							<i class="icon-close bookproicon-close"></i>
						</a>
					<?php endif; ?>
				</div>
				
				<a class="obp_button opb_button_add_media"
				href="#"
				data-uploader-title="<?php esc_attr_e( "Add image(s)", 'ovabookpro' ); ?>"
				data-button-text="<?php esc_attr_e( "Add image", 'ovabookpro' ); ?>">
					<?php esc_html_e( "Browser", 'ovabookpro' ); ?>	
				</a>
				<input type="hidden" name="avatar" id="avatar" value="<?php echo esc_attr( $user->get_avatar_id() ); ?>">
			</div>

		<?php endif; ?>

		<div class="obp_wrap_two_column">

			<div class="obp_column">

				<label for="user_login">
					<?php esc_html_e( 'Username*', 'ovabookpro' ); ?>
				</label>
				<input type="text" class="input-disabled" name="user_login" id="user_login" value="<?php echo esc_attr( $user->get_user_login() ); ?>" placeholder="<?php echo esc_attr( 'john' ); ?>" required readonly tabindex="0">

			</div>

			<div class="obp_column">
				
				<label for="user_email">
					<?php esc_html_e( 'Email*', 'ovabookpro' ); ?>
				</label>
				<input type="email" class="input-disabled" name="user_email" id="user_email" value="<?php echo esc_attr( $user->get_user_email() ); ?>" placeholder="<?php echo esc_attr( 'john@email.com' ); ?>" required readonly tabindex="1">
			</div>

			<div class="obp_column">

				<label for="first_name">
					<?php esc_html_e( 'First Name*', 'ovabookpro' ); ?>
				</label>
				<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $user->get_first_name() ); ?>" placeholder="<?php echo esc_attr( 'John' ); ?>" tabindex="2" />

			</div>
			
			<div class="obp_column">

				<label for="last_name">
					<?php esc_html_e( 'Last Name*', 'ovabookpro' ); ?>
				</label>
				<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $user->get_last_name() ); ?>" placeholder="<?php echo esc_attr( 'Smith' ); ?>" tabindex="3" />
			</div>

			<div class="obp_column">

				<label for="nickname">
					<?php esc_html_e( 'Nickname*', 'ovabookpro' ); ?>
				</label>
				<input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $user->get_nickname() ); ?>" placeholder="<?php echo esc_attr( 'John Smith' ); ?>"required tabindex="4" />

			</div>

			<div class="obp_column">
				<label for="phone_number">
					<?php esc_html_e( 'Phone*', 'ovabookpro' ); ?>
				</label>
				<input type="tel" name="phone_number" id="phone_number" value="<?php echo esc_attr( $user->get_phone_number() ); ?>" placeholder="<?php echo esc_attr( '+(0123) 456 789' ); ?>" required tabindex="5" />
			</div>

			<div class="obp_column">
			</div>
				
			<div class="obp_column">
				<label for="description">
					<?php esc_html_e( 'Description', 'ovabookpro' ); ?>
				</label>
				<textarea name="description" id="description" rows="5" tabindex="6"><?php echo esc_html( $user->get_description() ); ?></textarea>
			</div>

		</div>
		<div class="obp-form-submit">
			<input type="submit" name="obp_update_my_profile"
			class="obp_button" value="<?php esc_attr_e( 'Update', 'ovabookpro' ); ?>">
		</div>
	</div>
</form>
<div class="update_profile_errors" data-error="<?php echo esc_attr( json_encode( $errors ) ); ?>"></div>