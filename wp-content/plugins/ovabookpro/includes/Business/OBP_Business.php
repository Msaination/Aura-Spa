<?php 
namespace BookPro\Business;

defined( 'ABSPATH' ) || exit;

use BookPro\Service\OBP_Service;


/**
 * OBP_Business class.
 */
class OBP_Business {

	protected static $_instance = null;

	/**
	 * Constructor.
	 */
	public function __construct() {

		// dropdown cats multiple
		add_filter( 'wp_dropdown_cats', array( $this, 'wp_dropdown_cats_multiple' ), 10, 2 );
	}

	/* Get dropdown taxonomies */
	public function get_dropdown_taxonomies( $taxonomy = 'business_cat', $name = '', $selected = '', $show_option_none = '', $required = false ) {

		$args = array(
			'show_option_all'   => '',
			'show_option_none'  => '',
			'orderby'           => 'name',
			'order'             => 'ASC',
			'show_count'        => 0,
			'hide_empty'        => 0,
			'child_of'          => 0,
			'echo'              => 1,
			'selected'          => $selected,
			'hierarchical'      => 1,
			'name'              => $name,
			'id'                => "",
			'class'             => 'obp-select2',
			'depth'             => 0,
			'tab_index'         => 0,
			'taxonomy'          => $taxonomy,
			'hide_if_empty'     => false,
			'option_none_value' => '',
			'value_field'       => 'term_id',
			'required'          => $required,
			'aria_describedby'  => '',
			'data_placeholder'  => $show_option_none,
			'multiple' 			=> true
		);
		
		return wp_dropdown_categories( $args );
	}

	public static function get_categories(){
		$args = array(
			'taxonomy' 		=> 'business_cat',
			'hide_empty' 	=> false,
			'orderby' 		=> 'name',
			'order' 		=> 'ASC',
			'fields' 		=> 'id=>name',
		);

		$terms = get_terms( apply_filters( 'get_categories_business_args', $args ) );

		return $terms;
	}

	public static function get_amenities(){
		$args = array(
			'taxonomy' 		=> 'business_amenity',
			'hide_empty' 	=> false,
			'orderby' 		=> 'name',
			'order' 		=> 'ASC',
			'fields' 		=> 'id=>name',
		);

		$terms = get_terms( apply_filters( 'get_amenities_business_args', $args ) );

		return $terms;
	}

	public function wp_dropdown_cats_multiple( $output, $r ) {

	    if( isset( $r['multiple'] ) && $r['multiple'] && $r['data_placeholder']  ) {

	        $output = preg_replace( '/^<select/i', "<select multiple data-placeholder='{$r['data_placeholder']}'", $output );

	        $output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );

	        foreach ( array_map( 'trim', explode( ",", $r['selected'] ) ) as $value )
	            $output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );

	    }

	    return $output;
	}

	public static function init(){
		add_action( 'obp_load_member_account_my-business_scripts', array( __CLASS__, 'load_scripts_my_business' ) );
		add_action( 'obp_frontend_scripts_loaded', array( __CLASS__, 'load_scripts_single_business_page' ) );
	}

	public static function load_scripts_single_business_page( $assets ){
		// Single Business
			if ( is_singular('obp_business') ) {

			$assets->load_map_js();

			// Jquery UI
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-accordion' );
			wp_enqueue_script( 'jquery-ui-sortable' );

			wp_enqueue_style('zebra-dialog');
			wp_enqueue_script('zebra-dialog');
			
			wp_enqueue_script( 'obp-single-business', OBP_PLUGIN_URI.'assets/js/frontend/single-business.js', array('jquery'), false, true );
			wp_enqueue_script( 'obp-service-section' );
			// Countdown
			wp_enqueue_style( 'jquery-countdown' );
			wp_enqueue_script( 'jquery-countdown-plugin' );
			wp_enqueue_script( 'jquery-countdown' );

			wp_enqueue_script('order-countdown', OBP_PLUGIN_URI.'assets/js/frontend/countdown.js', array('jquery'), false, true );
			// Booking Free
			wp_enqueue_script( 'obp-checkout' );

			// Booking
			wp_enqueue_script( 'obp-booking' );
		}
	}	

	public static function load_scripts_my_business( $assets ){

		// time settings
		$time_format = obp_get_time_format();
		$time_step   = apply_filters('obp_business_hour_time_step', 15);
		$min_time    = apply_filters('obp_business_hour_min_time', '06:00am');
		// WP Media
		wp_enqueue_media();

		wp_enqueue_style( 'obp-timepicker' );
		wp_enqueue_script( 'obp-timepicker' );

		$assets->load_map_js();


		wp_enqueue_style( 'inputTags', OBP_PLUGIN_URI.'assets/libs/inputTags/inputTags.min.css', 'all' );
		wp_enqueue_script( 'inputTags', OBP_PLUGIN_URI.'assets/libs/inputTags/inputTags.js', array( 'jquery' ), false, true );
		wp_localize_script( 'inputTags', 'obp_tags_obj', array(
			'empty' 	=> esc_html__( 'Please note, you cannot add an empty tag.', 'ovabookpro' ),
			'minLength' => esc_html__( 'Please note, your tag must have at least [value] characters.', 'ovabookpro' ),
			'maxLength' => esc_html__( 'Please note that your tag must not exceed [value] characters.', 'ovabookpro' ),
			'max' 		=> esc_html__( 'Please note that the number of tags must not exceed [value].', 'ovabookpro' ),
			'email' 	=> esc_html__( 'Please note, the email address you entered is not valid.', 'ovabookpro' ),
			'exists' 	=> esc_html__( 'Attention, this tag already exists!', 'ovabookpro' ),
			'autocomplete_only' => esc_html__( 'Please note, you must select a value from the list.', 'ovabookpro' ),
			'notFound' => esc_html__( '[undefined instance] No inputTags instance found.', 'ovabookpro' ),
			'methodNotExists' => esc_html__( '[undefined method] The method [value] does not exists.', 'ovabookpro' ),
		) );

		wp_enqueue_script( 'obp-frontend-business', OBP_PLUGIN_URI.'assets/js/frontend/business.js', array('jquery'), false, true );
		wp_localize_script( 'obp-frontend-business', 'obp_business_obj', array(
			'media_title' 			=> esc_html__( 'Add media', 'ovabookpro' ),
			'media_button' 			=> esc_html__( 'Select', 'ovabookpro' ),
			'time_format' 			=> $time_format,
			'time_step' 			=> $time_step,
			'min_time' 				=> $min_time,
			'business_req' 			=> esc_html__( 'Business name is required', 'ovabookpro' ),
			'business_hours_req' 	=> esc_html__( 'Business hours is required', 'ovabookpro' ),
			'phone_req' 			=> esc_html__( 'Phone is required', 'ovabookpro' ),
			'phone_invalid' 		=> esc_html__( 'Phone is not valid', 'ovabookpro' ),
			'email_req' 			=> esc_html__( 'Email is required', 'ovabookpro' ),
			'email_invalid' 		=> esc_html__( 'Email is not valid', 'ovabookpro' ),
			'category_req' 			=> esc_html__( 'Category is required', 'ovabookpro' ),
			'work_hours_invalid' 	=> esc_html__( 'The time period definition is invalid.', 'ovabookpro' ),
		) );
	}

	/* Get socials */
	public static function social_networks() {
		
		$socials = array_unique(
			apply_filters (
				'obp_social_networks',
				array(
					'facebook' 	 	=> esc_html__( 'Facebook', 'ovabookpro' ),
					'twitter' 	 	=> esc_html__( 'Twitter', 'ovabookpro' ),
					'tiktok' 	 	=> esc_html__( 'TikTok', 'ovabookpro' ),
					'pinterest'  	=> esc_html__( 'Pinterest', 'ovabookpro' ),
					'googleplus' 	=> esc_html__( 'Google Plus', 'ovabookpro' ),
					'tumblr' 	 	=> esc_html__( 'Tumblr', 'ovabookpro' ),
					'instagram'  	=> esc_html__( 'Instagram', 'ovabookpro' ),
					'vimeo' 	 	=> esc_html__( 'Vimeo', 'ovabookpro' ),
					'myspace' 	 	=> esc_html__( 'Myspace', 'ovabookpro' ),
					'skype' 	 	=> esc_html__( 'Skype', 'ovabookpro' ),
					'youtube' 	 	=> esc_html__( 'Youtube', 'ovabookpro' ),
					'googledrive' 	=> esc_html__( 'Google Drive', 'ovabookpro' ),
					'flickr' 		=> esc_html__( 'Flickr', 'ovabookpro' ),
				)
			)
		);

		return $socials;
	}

	public static function social_icons(){
        $icons = array_unique(
			apply_filters (
				'obp_social_icons',
				array(
					'facebook' 	 	=> 'bookproicon-facebook-app-symbol',
					'twitter' 	 	=> 'bookproicon-twitter',
					'tiktok' 	 	=> 'bookproicon-tiktok',
					'pinterest'  	=> 'bookproicon-pinterest',
					'googleplus' 	=> 'bookproicon-google-plus',
					'tumblr' 	 	=> 'bookproicon-tumblr',
					'instagram'  	=> 'bookproicon-instagram',
					'vimeo' 	 	=> 'bookproicon-vimeo',
					'myspace' 	 	=> 'bookproicon-social',
					'skype' 	 	=> 'bookproicon-skype',
					'youtube' 	 	=> 'bookproicon-youtube',
					'googledrive' 	=> 'bookproicon-google-drive',
					'flickr' 		=> 'bookproicon-flickr',
				)
			)
		);

		return $icons;
    }

	public static function get_social_icon($social) {
		
        if( isset(self::social_icons()[$social]) ) {
        	$icon = self::social_icons()[$social];
        	return $icon;
        }
        	
       return 'bookproicon-folder';
    }
    /* End get socials */

    /* Share social*/
    public static function share_social($id){
		$url     = get_permalink( $id );
		$title   = get_the_title( $id );

		// Share social
		$args_social = apply_filters( 'obp_share_social', array(
		    'facebook' => array(
		        'icon'  => 'bookproicon-facebook-app-symbol',
		        'url'   => 'https://www.facebook.com/sharer.php?u='.$url,
		    ),
		    'twitter'   => array(
		        'icon'  => 'bookproicon-twitter',
		        'url'   => 'https://twitter.com/share/?url='.$url.'&text='.$title,
		    ),
		    'pinterest'   => array(
		        'icon'  => 'bookproicon-pinterest',
		        'url'   => 'https://www.pinterest.com/pin/create/button/?url='.$url,
		    ),
		), $url, $title );

		return $args_social;
    }

	/* Get business id by vendor id */
	public static function get_id( $vendor_id ) {
		if( $vendor_id ) {
			$args = apply_filters( 'obp_get_business_id_args', array(
			    'post_type' 		=> 'obp_business',
		 		'post_status' 		=> 'publish',
			    'posts_per_page' 	=> 1,
			    'fields' 			=> 'ids',
			    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			    'meta_key' 			=> OBP_METABOX.'vendor_id',
			    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		    	'meta_value' 		=> $vendor_id,
			) );

			$post_id_arr = get_posts( $args );

			$post_id = isset( $post_id_arr[0] ) ? $post_id_arr[0] : '';

		} else {
			$post_id = '';
		}

		return apply_filters( 'obp_get_business_id', $post_id, $vendor_id );
	}

	/* Get business ids */
	public static function get_business_ids( $keyword = '' ){

		$args = array(
		    'post_type' 		=> 'obp_business',
	 		'post_status' 		=> 'publish',
		    'posts_per_page' 	=> -1,
		    'fields' 			=> 'ids',
		);

		$service_ids = [];

		if($keyword != '') {
			$args['s'] = $keyword;
			$args['search_columns'] = 'post_title';
			$service_ids = OBP_Service::instance()->get_list_service(-1,'title',$keyword);
		}

		$business_ids = get_posts( $args );

		if( !empty($service_ids) ) {
			foreach ($service_ids as $service_id) {
				$id = obp_get_service($service_id)->get_business_id();
				
				if( !in_array($id,$business_ids) ) {
					array_push($business_ids,$id);
				}
			}
		}

		return $business_ids;
	}

	/* Get manage business data */
	public static function get_business_data() {
		$_prefix = OBP_METABOX;

		$data = array();
	
		$args = array(
		    'post_type' 		=> 'obp_business',
	 		'post_status' 		=> 'publish',
		    'posts_per_page' 	=> -1,
		);

		$businesses = get_posts( $args );

		foreach( $businesses as $business ) {
			$post_id    = $business->ID;
			$post_date  = $business->post_date;
			$vendor_id  = get_post_meta( $post_id, $_prefix.'vendor_id', true) ? get_post_meta( $post_id, $_prefix.'vendor_id', true) : '';

			$data[] = array(
                'id'              => $post_id,
                'vendor_id'       => $vendor_id,
                'business_name'   => get_the_title($post_id),
                'manage_business' => '<a href="#'.$post_id.'">'.esc_html__('Manage','ovabookpro').'</a>',
                'date'            => $post_date,
            );
		}

		return $data;
	}

	/* Get selected taxonomies */
	public static function get_selected_taxonomies($taxonomy_name) {
		$vendor_id = obp_get_vendor_id();
		$post_id   = self::get_id($vendor_id);

		$obj_taxonomies = get_the_terms( $post_id, $taxonomy_name ) ? get_the_terms( $post_id, $taxonomy_name ) : '';

		$taxonomies = [];

		if ( !empty($obj_taxonomies) ) {
			foreach ($obj_taxonomies as $key => $value) {
				$taxonomies[] = $value->term_id;
			}
		}

		return $taxonomies;
	}

	public static function my_business_args(){
		$vendor_id = obp_get_vendor_id();

		$post_id   = self::get_id( $vendor_id );
		$business  = obp_get_business( $post_id );

		// name, avatar
		$avatar_url = $id_avatar = '';

		$description 	= $business->get_description();
		$business_name 	= $business->get_name();

		if( $post_id ) {
			$post 			= get_post( $post_id );
			$id_avatar     	= get_post_thumbnail_id( $post_id );
			$avatar_url    	= wp_get_attachment_image_url($id_avatar, 'thumbnail');
		}

		$time_format = obp_get_time_format();

		// text editor: description
		$settings_editor = array(
			'textarea_name' => 'obp_business_description',
			'media_buttons' => apply_filters( 'obp_add_media_business_description', true ),
			'textarea_rows' => 5,
			'wpautop' 		=> false,
		);

		/* get post meta */
		$phone  = $business->get_phone_number();
		$email  = $business->get_email();
		$tags   = implode(',', $business->get_tags() );

		// google map
		$enable_map = $business->get_enable_map();

		$map_address = $business->get_full_address();
		$map_lat 	 = $business->get_map_lat();
		$map_lng 	 = $business->get_map_lng();

		// socials
		$socials 	 = $business->get_socials();

		// work hours
		$work_hours  = $business->get_work_hours();

		// business hours
		$business_hours   = $business->get_business_hours();

		// media
		$main_images 	  = $business->get_main_images();
		$our_works_images = $business->get_our_works_images();
		$video_url 		  = $business->get_video_url();

		/* selected taxonomies : business_cat, business_amenity */
		$selected_categories = self::get_selected_taxonomies('business_cat');
		$selected_amenities  = self::get_selected_taxonomies('business_amenity');

		$args = array(
			'post_id' 		   		=> $post_id,
			'id_avatar'    	   		=> $id_avatar,
			'avatar_url'       		=> $avatar_url,
			'business_name'    		=> $business_name,
			'settings_editor'  		=> $settings_editor,
			'time_format'  	   		=> $time_format,
			'phone' 		   		=> $phone,
			'email' 		   		=> $email,
			'tags' 	  		   		=> $tags,
			'enable_map'      		=> $enable_map,
			'map_address'      		=> $map_address,
			'map_lat' 		   		=> $map_lat,
			'map_lng' 		   		=> $map_lng,
			'business' 				=> $business,
			'socials' 	  	   		=> $socials,
			'work_hours' 	   		=> $work_hours,
			'business_hours'   		=> $business_hours,
			'main_images'      		=> $main_images,
			'our_works_images' 		=> $our_works_images,
			'video_url'        		=> $video_url,
			'selected_categories' 	=> $selected_categories,
			'selected_amenities' 	=> $selected_amenities,
			'description' 			=> $description,
		);

		return apply_filters( 'obp_my_business_args', $args );
	}

	public static function single_business_args(){
		
		global $post;

		$post_id  = $post->ID;
	    $business = obp_get_business( $post_id );
		$user_id  = wp_get_current_user()->ID;
		$user     = obp_get_user( $user_id );

		$vendor_id   = $business->get_vendor_id();
		$main_images = $business->get_main_images();
		$video_url 	 = $business->get_video_url();
		$socials 	 = $business->get_socials();
		$phone 		 = $business->get_phone_number();
		
		$business_avatar = $business->get_avatar();
		if( !empty($business_avatar) ) {
			array_unshift($main_images , $business_avatar);
		}

		// check vendor id from shortcode obp_services
		if( isset($args['vendor_id']) && !empty($args['vendor_id']) ) {
			$vendor_id = $args['vendor_id'];
		}

		// share
		$args_social = self::share_social($post_id);

		// wishlist
		$icon_wishtlist  = '<i class="flaticon bookproicon-heart" aria-hidden="true"></i>';
		$wishlist   = $user->get_wishlist();
		if ( ! empty( $wishlist ) && is_array( $wishlist ) && in_array( $post_id, $wishlist )) {
			$icon_wishtlist = '<i class="flaticon bookproicon-like" aria-hidden="true" data-tippy-content="'.esc_attr__( 'Added to wishlist', 'ovabookpro' ).'"></i>';
		}

		// data to check logged in
		$status    = ''; 
		$login_url = add_query_arg( 'redirect_to',get_permalink($post_id), obp_login_url() );
		if ( is_user_logged_in() ) {
			$status  = 'logged-in';
		}

		// get our works images
		$our_works_images = $business->get_our_works_images();
		$our_work_endpoint = OBP()->settings->endpoint->get('our_work', 'our-work');
		$all_works_url = obp_get_our_work_url( $business->get_permalink() ); 
		$tags   = implode(', ', $business->get_tags() );
		// divide the our works images
		$chunked_works_images = array_chunk( $our_works_images, apply_filters('obp_business_all_our_works_images_number', 6) );

		// amenities
		$amenities = get_the_terms( $post_id, 'business_amenity' );

		// reviews


		// google map
		$enable_map    = $business->get_enable_map();

		$map_address = $business->get_full_address();
		$map_lat 	 = $business->get_map_lat();
		$map_lng 	 = $business->get_map_lng();

		// description
		$description = $business->get_description();
		$height_desc = apply_filters( 'obp_business_height_description', '400' );

		// business hours
		$business_hours = $business->get_business_hours();

		$args = array(
			'main_images' 			=> $main_images,
			'post_id'     			=> $post_id,
			'map_address' 			=> $map_address,
			'video_url'   			=> $video_url,
			'args_social' 			=> $args_social,
			'icon_wishtlist'  		=> $icon_wishtlist,
			'status'      			=> $status,
			'login_url'   			=> $login_url,
			'vendor_id' 			=> $vendor_id,
			'our_works_images' 		=> $our_works_images,
			'all_works_url'    		=> $all_works_url,
			'map_address' 			=> $map_address,
			'tags' 					=> $tags,
			'chunked_works_images' 	=> $chunked_works_images,
			'amenities' 			=> $amenities,
			'phone'          		=> $phone,
			'enable_map'       		=> $enable_map,
			'map_address'    		=> $map_address,
			'map_lat'        		=> $map_lat,
			'map_lng' 	     		=> $map_lng,
			'description'    		=> $description,
			'height_desc'    		=> $height_desc,
			'business_hours' 		=> $business_hours,
			'our_work_endpoint' 	=> $our_work_endpoint,
			'socials' 				=> $socials,
		);

		return apply_filters( 'obp_single_business_args', $args );
	}

	public static function get_business_tag_by_keyword( $keyword = '' ){
		$args = array(
			'taxonomy' => 'business_tag',
			'hide_empty' => false,
			'name__like' => $keyword,
		);

		$business_tags = get_terms( $args );
		return $business_tags;
	}

	public static function get_business_ids_by_vendor_id( $vendor_id ){

		$args = apply_filters( 'obp_get_business_ids_by_vendor_id_args', array(
			'post_type' 	=> 'obp_business',
			'post_status' 	=> 'publish',
			'fields' 		=> 'ids',
			'posts_per_page' => -1,
			'meta_query' 	=> array(
				array(
					'key' 	=> OBP_METABOX.'vendor_id',
					'value' => $vendor_id,
				),
			),
		) );

		$business_ids = get_posts( $args );
		return $business_ids;
	}

	/**
	 * Instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}