<?php 
namespace BookPro\Business;

use BookPro\Language\OBP_Language;

defined( 'ABSPATH' ) || exit;


if ( ! class_exists("OBP_Business_Item") ) {
	

	class OBP_Business_Item {

		public $id = null;

		public $post = null;

		public function __construct( $id = null ){
			
			if ( ! empty( $id ) ) {
				$this->id = $id;
				$this->post = get_post( $id );
			}
		}

		public function get_id(){
			$id = $this->id;
			return $id;
		}

		public function get_vendor_id(){
			$business_id = $this->get_id();
			$vendor_id = OBP()->get_post_meta( $business_id, 'vendor_id' );
			return $vendor_id;
		}

		public function get_business_hours(){
			$business_id = $this->get_id();
			$business_hours = OBP()->get_post_meta( $business_id, 'business_hours' );
			return $business_hours;
		}

		public function get_work_hours(){
			$business_id = $this->get_id();
			$work_hours = OBP()->get_post_meta( $business_id, 'work_hours' );
			return $work_hours;
		}

		public function get_enable_map(){
			$business_id = $this->get_id();
			$value = OBP()->get_post_meta( $business_id, 'enable_map', 'yes' );
			return $value;
		}

		public function get_permalink(){
			$business_id = $this->get_id();
			$permalink = get_permalink( $business_id );
			return $permalink;
		}

		public function get_name(){
			$name = $this->post ? $this->post->post_title : '';
			return apply_filters( 'obp_get_business_name', $name, $this );
		}

		public function get_avatar(){
			$business_id = $this->get_id();
			$avatar = get_post_thumbnail_id( $business_id );
			return $avatar;
		}

		public function get_phone_number(){
			$business_id = $this->get_id();

			$phone = OBP()->get_post_meta( $business_id, 'phone' );
			return $phone;
		}

		public function get_email(){
			$business_id = $this->get_id();
			$email  = OBP()->get_post_meta( $business_id, 'email' );
			return $email;
		}

		public function get_tags(){
			$business_id = $this->get_id();
			$tags = wp_get_post_terms( $business_id, 'business_tag',  array("fields" => "names") );
			return $tags;
		}

		public function get_description(){
			$description = $this->post ? $this->post->post_content : '';
			return apply_filters( 'obp_get_business_description', $description, $this );
		}

		public function get_socials(){
			$business_id = $this->get_id();
			$socials = OBP()->get_post_meta( $business_id, 'socials' );
			return $socials;
		}

		public function get_country_code(){
			$country_code = OBP()->get_post_meta( $this->get_id(), 'country_code' );
			return $country_code;
		}

		public function get_city(){
			$city = OBP()->get_post_meta( $this->get_id(), 'city' );
			return $city;
		}

		public function get_postcode(){
			$postcode = OBP()->get_post_meta( $this->get_id(), 'postcode' );
			return $postcode;
		}

		public function get_state(){
			$state = OBP()->get_post_meta( $this->get_id(), 'state' );
			return $state;
		}

		public function get_full_address(){
			$address = OBP()->get_post_meta( $this->get_id(), 'full_address' );
			return $address;
		}

		public function get_map_lat(){
			$lat = OBP()->get_post_meta( $this->get_id(), 'latitude' );
			return $lat;
		}

		public function get_map_lng(){
			$lng = OBP()->get_post_meta( $this->get_id(), 'longitude' );
			return $lng;
		}

		public function get_main_images(){
			$business_id = $this->get_id();
			$main_images = OBP()->get_post_meta( $business_id, 'main_images', [] );
			return $main_images;
		}

		public function get_our_works_images(){
			$business_id = $this->get_id();
			$our_works_images = OBP()->get_post_meta( $business_id, 'our_works_images', [] );
			return $our_works_images;
		}

		public function get_video_url(){
			$business_id = $this->get_id();
			$video_url   = OBP()->get_post_meta( $business_id, 'video_url' );
			return $video_url;
		}
	}
}