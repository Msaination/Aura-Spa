<?php 
namespace BookPro\Business;

use BookPro\Traits\SingletonTrait;
use BookPro\Service\OBP_Service;

use BookPro\OBP_Permission;
use BookPro\OBP_Message;


defined( 'ABSPATH' ) || exit;

if ( ! class_exists('OBP_Business_Ajax') ) {
	class OBP_Business_Ajax {

		use SingletonTrait;
		/**
		 * @var bool
		 */
		protected static $_loaded = false;

		public function __construct(){

			if ( self::$_loaded ) {
				return;
			}
			
			if (!defined('DOING_AJAX') || !DOING_AJAX)
				return;

			$this->init();

			self::$_loaded = true;
		}

		public function init(){

			$arr_ajax =  array(
				'obp_add_social',
				'obp_add_work_hour',
				'obp_add_hour',
				'obp_save_edit_business',
				'obp_add_wishlist',
				'obp_remove_wishlist',
				'obp_our_works_load_more',
				'obp_section_service_search',
				'obp_business_tags_complete',

			);

			foreach($arr_ajax as $val){
				add_action( 'wp_ajax_'.$val, array( $this, $val ) );
				add_action( 'wp_ajax_nopriv_'.$val, array( $this, $val ) );
			}
		}

		public function obp_business_tags_complete(){
	

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
				$business_tags = OBP_Business::get_business_tag_by_keyword( $keyword );
		
				obp_get_template( 'my-business/parts/input-tag-complete.php', array( 'business_tags' => $business_tags ) );

			}

	
			wp_die();
		}

		/* Add Social */
		public static function obp_add_social() {
			?>
			<div class="social_item">
				<select class="name_social" name="business_socials">
					<?php foreach ( OBP_Business::instance()->social_networks() as $key_name_social => $value_name_social ) : ?>
						<option value="<?php echo esc_attr($key_name_social); ?>">
							<?php echo esc_html( $value_name_social ); ?>	
						</option>
					<?php endforeach; ?>
				</select>

				<input type="text" class="link_social" name="link_social" value=""
					placeholder="<?php echo esc_attr('Enter social link','ovabookpro');?>"
				>

				<a href="#" class="remove_social">
					<i class="icon-close bookproicon-close"></i>
				</a>
			</div>

			<?php

			wp_die();
		}

		/* Add Work Hour */
		public static function obp_add_work_hour() {
			$time_format = OBP()->settings->general->get('time_format');
			?>
			
			<div class="work_hours_field">
				<input type="text" class="work_hour_label" name="work_hour_label" value="" placeholder="<?php esc_attr_e('Morning','ovabookpro');?>" required>

				<div class="work_hours">
					<input type="text" class="work_hour" name="start_hour" placeholder="<?php echo esc_attr($time_format);?>" value="" required>
					<i class="bookproicon-remove"></i>
					<input type="text" class="work_hour" name="end_hour" placeholder="<?php echo esc_attr($time_format);?>" value="" required>

					<a href="#" class="remove_work_hour">
						<i class="icon-close bookproicon-close"></i>
					</a>
				</div>
			</div>

			<?php

			wp_die();
		}

		/* Add Business Hour */
		public static function obp_add_hour() {
			$time_format = OBP()->settings->general->get('time_format', 'H:i');
			?>
			
			<div class="business-hour">
				<input type="text" class="business_hour" name="start_hour" placeholder="<?php echo esc_attr($time_format);?>">
				<i class="bookproicon-remove"></i>
				<input type="text" class="business_hour" name="end_hour" placeholder="<?php echo esc_attr($time_format);?>">
				<a href="#" class="remove_business_hour">
					<i class="icon-close bookproicon-close"></i>
				</a>
			</div>

			<?php

			wp_die();
		}

		/* Save Edit ( Add, Update Business ) */
		public static function obp_save_edit_business() {

			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) && OBP_Permission::user_can('my_business') ) {

				$vendor_id = obp_get_vendor_id();

				$post_id 		= isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
			
				$business_avatar = isset( $_POST['business_avatar'] ) ? sanitize_text_field( wp_unslash( $_POST['business_avatar'] ) ) : '';

				$business_name   = isset( $_POST['business_name'] ) ? sanitize_text_field( wp_unslash( $_POST['business_name'] ) ) : '';

				$phone  		 = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
				$email  		 = isset( $_POST['email'] ) ? sanitize_text_field( wp_unslash( $_POST['email'] ) ) : '';
				$tags  		 	 = isset( $_POST['tags'] ) ? explode(',', sanitize_text_field( wp_unslash( $_POST['tags'] ) ) ) : [];
				$categories      = isset( $_POST['categories'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['categories'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$amenities       = isset( $_POST['amenities'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['amenities'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$description     = isset( $_POST['description'] ) 	 ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
				$enable_map 		 = isset( $_POST['enable_map'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_map'] ) ) : 'yes';
				
				$country_code 	= isset( $_POST['country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['country_code'] ) ) : '';
				$state 			= isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '';
				$postcode 		= isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '';
				$city 			= isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
				$full_address 	= isset( $_POST['full_address'] ) ? sanitize_text_field( wp_unslash( $_POST['full_address'] ) ) : '';
				$latitude 		= isset( $_POST['latitude'] ) ? sanitize_text_field( wp_unslash( $_POST['latitude'] ) ) : '';
				$longitude 		= isset( $_POST['longitude'] ) ? sanitize_text_field( wp_unslash( $_POST['longitude'] ) ) : '';

				$socials          = isset( $_POST['socials'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['socials'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$work_hours       = isset( $_POST['work_hours'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['work_hours'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$business_hours   = isset( $_POST['business_hours'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['business_hours'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$main_images      = isset( $_POST['main_images'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['main_images'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

				$our_works_images = isset( $_POST['our_works_images'] ) ? obp_recursive_sanitize_text_field( wp_unslash( $_POST['our_works_images'] ) ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$video_url 		  = isset( $_POST['video_url'] ) ? sanitize_url( wp_unslash( $_POST['video_url'] ) ) : '';

				if ( ! $business_avatar ) {
					delete_post_thumbnail( $post_id );
				}

				$meta_input = array(
					OBP_METABOX.'vendor_id' 			=> $vendor_id,
					OBP_METABOX.'phone' 				=> $phone,
					OBP_METABOX.'email' 				=> $email,
					OBP_METABOX.'enable_map' 			=> $enable_map,
					OBP_METABOX.'country_code' 			=> strtoupper($country_code),
					OBP_METABOX.'state' 				=> $state,
					OBP_METABOX.'postcode' 				=> $postcode,
					OBP_METABOX.'city' 					=> $city,
					OBP_METABOX.'full_address' 			=> $full_address,
					OBP_METABOX.'longitude' 			=> $longitude,
					OBP_METABOX.'latitude' 				=> $latitude,
					OBP_METABOX.'socials' 				=> $socials,
					OBP_METABOX.'work_hours' 			=> $work_hours,
					OBP_METABOX.'business_hours' 		=> $business_hours,
					OBP_METABOX.'main_images' 			=> $main_images,
					OBP_METABOX.'our_works_images' 		=> $our_works_images,
					OBP_METABOX.'video_url' 			=> $video_url,
				);

				/* Save Edit */
				if ( ! empty( $post_id ) ) {

	   				$new_slug  = sanitize_title( $business_name );

					$post_info = get_post( $post_id );

					$post_arr_update  = array(
						'ID' 			=> $post_id,
						'post_name' 	=> $new_slug,
						'post_type' 	=> 'obp_business',
						'post_title' 	=> $business_name,
						'post_content' 	=> $description,
						'post_status' 	=> $post_info->post_status,
						'_thumbnail_id' => $business_avatar,
					);

					$new_post_id = wp_update_post( $post_arr_update, true );

					if ( ! is_wp_error( $new_post_id ) ) {

						// Update meta data
						foreach ( $meta_input as $meta_key => $meta_value ) {
							update_post_meta( $new_post_id, $meta_key, $meta_value );
						}

						do_action( 'obp_after_save_postmeta_business', $new_post_id );

						wp_set_post_terms( $new_post_id, $categories, 'business_cat' );
						wp_set_post_terms( $new_post_id, $amenities, 'business_amenity' );
						wp_set_post_terms( $new_post_id, $tags, 'business_tag' );

						OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
					} else {
						OBP()->message->add( esc_html__( 'Updated failed', 'ovabookpro' ), 'error' );
					}

				} else { // Add new post

					/* insert new post */
					$post_arr_new = array(
						'post_author' 	=> $vendor_id,
						'post_type' 	=> 'obp_business',
						'post_title' 	=> $business_name,
						'post_content' 	=> $description,
						'post_status' 	=> 'publish',
						'_thumbnail_id' => $business_avatar,
					);
					
					$new_post_id = wp_insert_post( $post_arr_new, true );

					if ( ! is_wp_error( $new_post_id ) ) {

						// Update meta data
						foreach ( $meta_input as $meta_key => $meta_value ) {
							update_post_meta( $new_post_id, $meta_key, $meta_value );
						}

						do_action( 'obp_after_save_postmeta_business', $new_post_id );

						wp_set_post_terms( $new_post_id, $categories, 'business_cat' );
						wp_set_post_terms( $new_post_id, $amenities, 'business_amenity' );
						wp_set_post_terms( $new_post_id, $tags, 'business_tag' );

						OBP()->message->add( esc_html__( 'Updated successfully', 'ovabookpro' ) );
					} else {
						OBP()->message->add( esc_html__( 'Updated failed', 'ovabookpro' ), 'error' );
					}
				}

			}

			
			echo "";
			wp_die();
		}

		/* Single Business: add wishlist */
		public function obp_add_wishlist() {
			?>
			<i class="flaticon bookproicon-heart" aria-hidden="true"></i>
			<?php
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				$business_id = isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';

				$user_id  = get_current_user_id(); 
				$user     = obp_get_user($user_id);   
				$value_wl = $user->get_wishlist();
				array_push( $value_wl, $business_id );

				// update user meta
				update_user_meta( $user_id, OBP_METABOX.'wishlist', $value_wl );
				?>
				<i class="flaticon bookproicon-like" aria-hidden="true" data-tippy-content="<?php echo esc_attr__( 'Added to wishlist', 'ovabookpro' ); ?>"></i>
				<?php
			}
		
			wp_die(); 
		}

		/* Single Business: remove wishlist */

		public function obp_remove_wishlist() {
			?>
			<i class="flaticon bookproicon-like" aria-hidden="true" data-tippy-content="<?php echo esc_attr__( 'Added to wishlist', 'ovabookpro' ); ?>"></i>
			<?php
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {

				$business_id = isset( $_POST['business_id'] ) ? sanitize_text_field( wp_unslash( $_POST['business_id'] ) ) : '';

				$user_id  = get_current_user_id();
				$user     = obp_get_user( $user_id );   
				$value_wl = $user->get_wishlist();

				if ( ! empty( $value_wl ) && is_array( $value_wl ) && in_array( $business_id, $value_wl ) ) {
					$value_wl = array_diff( $value_wl, [$business_id] );
				}

				// update user meta
				update_user_meta( $user_id, OBP_METABOX.'wishlist', $value_wl );
				?>
				<i class="flaticon bookproicon-heart" aria-hidden="true"></i>
				<?php
			}
			
			wp_die();
		}

		/* Single Business: Our works images load more */
		public static function obp_our_works_load_more() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if( !isset( $_POST['data'] ) ) wp_die();
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$post_data = wp_unslash( $_POST['data'] );
			// phpcs:enable WordPress.Security.NonceVerification.Missing
			$key 			  = isset( $post_data['key'] ) ? $post_data['key'] : '';
			$our_works_images = isset( $post_data['our_works_images'] ) ? $post_data['our_works_images'] : '';

		    if( !empty($our_works_images[$key]) ) {
		        ob_start();

		        foreach( $our_works_images[$key] as $img_id ) {
		            $img_url      = wp_get_attachment_image_url( $img_id, 'large');
		            $img_alt      = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
		            $img_caption  = wp_get_attachment_caption( $img_id );

		            if ( !$img_alt ) {
		                $img_alt = get_the_title( $img_id );
		            }
		            if ( !$img_caption ) {
		                $img_caption = $img_alt;
		            }
		        ?>
		            <div class="works-images-item" data-fancybox="<?php esc_attr_e('gallery','ovabookpro');?>"
		                data-caption="<?php echo esc_attr( $img_caption ); ?>"
		                data-src="<?php echo esc_url( $img_url ); ?>"
		            >
		                <img src="<?php echo esc_url( $img_url );?>" alt="<?php echo esc_attr( $img_alt );?>" title="<?php echo esc_attr( $img_alt );?>">
		            </div>
		            <?php
		        }

		        $html_output = ob_get_clean();
		        wp_send_json_success(['html' => $html_output]);
		    } else {
		        wp_send_json_error(['html' => '']);
		    }

		    wp_die();
		}

		/* Single Business: search Service */
		public function obp_section_service_search() {
		
			if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'obp_nonce' ) ) {
				
				$services = OBP_Service::get_list_service_ajax();

				obp_get_template("my-business/single-business/service-section.php", array( 'services' => $services ) );
			}
		
			wp_die();
		}
	}
}