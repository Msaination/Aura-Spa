<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * OBP_Post_Types class.
 */
class OBP_Post_Types {
	use SingletonTrait;
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );

		// add icon for taxonomy: business amenity
        add_action('business_amenity_add_form_fields', array( $this, 'add_obp_business_amenity_class_icon' ) );
        add_action('business_amenity_edit_form_fields', array( $this, 'edit_obp_business_amenity_class_icon' ) );
        add_action('created_term', array( $this, 'save_obp_business_amenity_class_icon' ), 10, 3);
        add_action('edited_term', array( $this, 'save_obp_business_amenity_class_icon' ), 10, 3);
	}

	/**
	 * register post types
	 */
	public function register_post_types(){

		// business
		$supports_business   = array( 'author', 'title', 'editor', 'comments', 'excerpt', 'thumbnail' );
		$taxonomies_business = array( 'business_cat', 'business_amenity' );


		do_action( 'obp_register_post_type' );
		
		$args_business = array(

			'labels' => array(
				'name'                  => __( 'Businesses', 'ovabookpro' ),
				'singular_name'         => __( 'Business', 'ovabookpro' ),
				'all_items'             => __( 'All Businesses', 'ovabookpro' ),
				'menu_name'             => _x( 'Businesses', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new business', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit business', 'ovabookpro' ),
				'new_item'              => __( 'New business', 'ovabookpro' ),
				'view_item'             => __( 'View business', 'ovabookpro' ),
				'view_items'            => __( 'View businesses', 'ovabookpro' ),
				'search_items'          => __( 'Search businesses', 'ovabookpro' ),
				'not_found'             => __( 'No business found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No businesss found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent business', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set business image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove business image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as business image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into business', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this business', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter businesses', 'ovabookpro' ),
				'items_list_navigation' => __( 'Businesses navigation', 'ovabookpro' ),
				'items_list'            => __( 'Businesses list', 'ovabookpro' ),
			),
			
			'public'             => true,
			'query_var'          => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'has_archive'        => true,
			'show_in_menu' 		  => false,
			'capability_type'    => 'obp_business',
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => false,
			'taxonomies'         => $taxonomies_business,
			'supports'           => $supports_business,
			'hierarchical'       => false,
			'rewrite'            => array(
				'slug' => _x('business','Business Slug', 'ovabookpro'),
			),
			'menu_position'      => 30,
			'menu_icon'          => 'dashicons-store'
		);

		register_post_type( 'obp_business', $args_business );

		
		$args_service = array(

			'labels' => array(
				'name'                  => __( 'Services', 'ovabookpro' ),
				'singular_name'         => __( 'Service', 'ovabookpro' ),
				'all_items'             => __( 'All Services', 'ovabookpro' ),
				'menu_name'             => _x( 'Services', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new service', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit service', 'ovabookpro' ),
				'new_item'              => __( 'New service', 'ovabookpro' ),
				'view_item'             => __( 'View service', 'ovabookpro' ),
				'view_items'            => __( 'View service', 'ovabookpro' ),
				'search_items'          => __( 'Search services', 'ovabookpro' ),
				'not_found'             => __( 'No service found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No services found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent service', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set service image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove service image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as service image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into service', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this service', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter services', 'ovabookpro' ),
				'items_list_navigation' => __( 'Services navigation', 'ovabookpro' ),
				'items_list'            => __( 'Services list', 'ovabookpro' ),
			),
			
			'public'             => false,
			'query_var'          => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'has_archive'        => false,
			'capability_type'    => 'obp_service',
			'show_in_menu'       => false,	
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'supports'           => array( 'author', 'title', 'editor', 'excerpt', 'thumbnail' ),
			'hierarchical'       => false,
			'rewrite'            => false,
			'menu_position'      => 30,
			'menu_icon'          => 'dashicons-businessman'
		);

		register_post_type( 'obp_service', $args_service );

		// Type

		$args_type = array(

			'labels' => array(
				'name'                  => __( 'Types', 'ovabookpro' ),
				'singular_name'         => __( 'Type', 'ovabookpro' ),
				'all_items'             => __( 'All Types', 'ovabookpro' ),
				'menu_name'             => _x( 'Types', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new type', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit type', 'ovabookpro' ),
				'new_item'              => __( 'New type', 'ovabookpro' ),
				'view_item'             => __( 'View type', 'ovabookpro' ),
				'view_items'            => __( 'View type', 'ovabookpro' ),
				'search_items'          => __( 'Search types', 'ovabookpro' ),
				'not_found'             => __( 'No type found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No types found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent type', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set type image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove type image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as type image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into type', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this type', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter types', 'ovabookpro' ),
				'items_list_navigation' => __( 'Types navigation', 'ovabookpro' ),
				'items_list'            => __( 'Types list', 'ovabookpro' ),
			),
			
			'public'             => false,
			'query_var'          => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'has_archive'        => false,
			'capability_type'    => 'obp_type',
			'show_in_menu'       => false,	
			'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
			'supports'           => array( 'author', 'title' ),
			'hierarchical'       => false,
			'rewrite'            => false,
			'menu_position'      => 30,
			'menu_icon'          => 'dashicons-businessman'
		);

		register_post_type( 'obp_type', $args_type );

		// Plan
		$args_plan = array(
			'labels' => array(
				'name'                  => __( 'Plans', 'ovabookpro' ),
				'singular_name'         => __( 'Plan', 'ovabookpro' ),
				'all_items'             => __( 'All Plans', 'ovabookpro' ),
				'menu_name'             => _x( 'Plans', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new plan', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit plan', 'ovabookpro' ),
				'new_item'              => __( 'New plan', 'ovabookpro' ),
				'view_item'             => __( 'View plan', 'ovabookpro' ),
				'view_items'            => __( 'View plan', 'ovabookpro' ),
				'search_items'          => __( 'Search plans', 'ovabookpro' ),
				'not_found'             => __( 'No plan found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No plans found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent plan', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set plan image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove plan image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as plan image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into plan', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this plan', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter plans', 'ovabookpro' ),
				'items_list_navigation' => __( 'Plans navigation', 'ovabookpro' ),
				'items_list'            => __( 'Plans list', 'ovabookpro' ),
			),
			
			'public'              => false,
			'show_ui'             => false,
			'capability_type'     => 'obp_plan',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'query_var'           => false,
			'supports'            => array( 'title', 'author' ),
			'has_archive'         => false,
			'menu_position'      => 30,
			'rewrite'            => array(
				'slug' => _x('plan','Plan Slug', 'ovabookpro'),
			),
		);

		register_post_type( 'obp_plan', $args_plan );

		// Role
		$args_role = array(
			'labels' => array(
				'name'                  => __( 'Roles', 'ovabookpro' ),
				'singular_name'         => __( 'Role', 'ovabookpro' ),
				'all_items'             => __( 'All Roles', 'ovabookpro' ),
				'menu_name'             => _x( 'Roles', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new role', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit role', 'ovabookpro' ),
				'new_item'              => __( 'New role', 'ovabookpro' ),
				'view_item'             => __( 'View role', 'ovabookpro' ),
				'view_items'            => __( 'View role', 'ovabookpro' ),
				'search_items'          => __( 'Search roles', 'ovabookpro' ),
				'not_found'             => __( 'No role found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No roles found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent role', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set role image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove role image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as role image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into role', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this role', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter roles', 'ovabookpro' ),
				'items_list_navigation' => __( 'Roles navigation', 'ovabookpro' ),
				'items_list'            => __( 'Roles list', 'ovabookpro' ),
			),
			
			'public'              => false,
			'show_ui'             => false,
			'capability_type'     => 'obp_role',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'query_var'           => false,
			'supports'            => array( 'title', 'author' ),
			'has_archive'         => false,
			'menu_position'      => 30,
			'rewrite'            => array(
				'slug' => _x('role','Role Slug', 'ovabookpro'),
			),
		);

		register_post_type( 'obp_role', $args_role );

		// Payout

		$args_payout = array(
			'labels' => array(
				'name'                  => __( 'Withdrawal requests', 'ovabookpro' ),
				'singular_name'         => __( 'Withdrawal request', 'ovabookpro' ),
				'all_items'             => __( 'All Withdrawal requests', 'ovabookpro' ),
				'menu_name'             => _x( 'Withdrawal requests', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new ưithdrawal request', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit withdrawal request', 'ovabookpro' ),
				'new_item'              => __( 'New withdrawal request', 'ovabookpro' ),
				'view_item'             => __( 'View withdrawal request', 'ovabookpro' ),
				'view_items'            => __( 'View withdrawal request', 'ovabookpro' ),
				'search_items'          => __( 'Search withdrawal requests', 'ovabookpro' ),
				'not_found'             => __( 'No withdrawal request found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No withdrawal requests found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent withdrawal request', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set withdrawal request image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove withdrawal request image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as withdrawal request image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into withdrawal request', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this withdrawal request', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter withdrawal requests', 'ovabookpro' ),
				'items_list_navigation' => __( 'Withdrawal requests navigation', 'ovabookpro' ),
				'items_list'            => __( 'Withdrawal requests list', 'ovabookpro' ),
			),

			'public'              => false,
			'show_ui'             => true,
			'show_in_menu' 		=> false,
			'capability_type'     => 'obp_payout',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_position' 	  => 30,
			'menu_icon' 	      => 'dashicons-money-alt',
			'query_var'           => false,
			'supports'            => array( 'title', 'author' ),
			'has_archive'         => false,
			'rewrite'            => array(
				'slug' => _x('payout','Withdrawal Request Slug', 'ovabookpro'),
			),
		);

		register_post_type( 'obp_payout', $args_payout );

		// Payout Method

		$args_payout_method = array(
			'labels' => array(
				'name'                  => __( 'Payout Methods', 'ovabookpro' ),
				'singular_name'         => __( 'Payout Method', 'ovabookpro' ),
				'all_items'             => __( 'All Payout Methods', 'ovabookpro' ),
				'menu_name'             => _x( 'Payout Methods', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new payout method', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit payout method', 'ovabookpro' ),
				'new_item'              => __( 'New payout method', 'ovabookpro' ),
				'view_item'             => __( 'View payout method', 'ovabookpro' ),
				'view_items'            => __( 'View payout method', 'ovabookpro' ),
				'search_items'          => __( 'Search payout methods', 'ovabookpro' ),
				'not_found'             => __( 'No payout method found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No payout methods found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent payout method', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set payout method image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove payout method image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as payout method image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into payout method', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this payout method', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter orders', 'ovabookpro' ),
				'items_list_navigation' => __( 'Payout Methods navigation', 'ovabookpro' ),
				'items_list'            => __( 'Payout Methods list', 'ovabookpro' ),
			),
			
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu' 		  => false,
			'capability_type'     => 'obp_payout_method',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 30,
			'menu_icon' 	      => 'dashicons-bank',
			'query_var'           => false,
			'supports'            => array( 'title', 'author' ),
			'has_archive'         => false,
			'rewrite'            => array(
				'slug' => _x('payout','Payout Method Slug', 'ovabookpro'),
			),
		);

		register_post_type( 'obp_payout_method', $args_payout_method );

		// Order
		$args_order = array(
			'labels' => array(
				'name'                  => __( 'Bookings', 'ovabookpro' ),
				'singular_name'         => __( 'Booking', 'ovabookpro' ),
				'all_items'             => __( 'All Bookings', 'ovabookpro' ),
				'menu_name'             => _x( 'Bookings', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new booking', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit booking', 'ovabookpro' ),
				'new_item'              => __( 'New booking', 'ovabookpro' ),
				'view_item'             => __( 'View booking', 'ovabookpro' ),
				'view_items'            => __( 'View booking', 'ovabookpro' ),
				'search_items'          => __( 'Search bookings', 'ovabookpro' ),
				'not_found'             => __( 'No booking found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No bookings found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent booking', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set booking image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove booking image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as booking image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into booking', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this booking', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter bookings', 'ovabookpro' ),
				'items_list_navigation' => __( 'Bookings navigation', 'ovabookpro' ),
				'items_list'            => __( 'Bookings list', 'ovabookpro' ),
			),
			
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu' 		  => false,
			'capability_type'     => 'obp_order',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_position' 	  => 30,
			'menu_icon' 	      => 'dashicons-archive',
			'query_var'           => false,
			'supports'            => array( 'title', 'author' ),
			'has_archive'         => false,
			'rewrite'            => array(
				'slug'       => _x('order','Order Slug', 'ovabookpro'),
			),
		);

		register_post_type( 'obp_order', $args_order );


		$args_package = array(
			'labels' => array(
				'name'                  => __( 'Packages', 'ovabookpro' ),
				'singular_name'         => __( 'Package', 'ovabookpro' ),
				'all_items'             => __( 'All Packages', 'ovabookpro' ),
				'menu_name'             => _x( 'Packages', 'Admin menu name', 'ovabookpro' ),
				'add_new'               => __( 'Add New', 'ovabookpro' ),
				'add_new_item'          => __( 'Add new package', 'ovabookpro' ),
				'edit'                  => __( 'Edit', 'ovabookpro' ),
				'edit_item'             => __( 'Edit package', 'ovabookpro' ),
				'new_item'              => __( 'New package', 'ovabookpro' ),
				'view_item'             => __( 'View package', 'ovabookpro' ),
				'view_items'            => __( 'View package', 'ovabookpro' ),
				'search_items'          => __( 'Search packages', 'ovabookpro' ),
				'not_found'             => __( 'No package found', 'ovabookpro' ),
				'not_found_in_trash'    => __( 'No packages found in trash', 'ovabookpro' ),
				'parent'                => __( 'Parent package', 'ovabookpro' ),
				'featured_image'        => __( 'Features image', 'ovabookpro' ),
				'set_featured_image'    => __( 'Set package image', 'ovabookpro' ),
				'remove_featured_image' => __( 'Remove package image', 'ovabookpro' ),
				'use_featured_image'    => __( 'Use as package image', 'ovabookpro' ),
				'insert_into_item'      => __( 'Insert into package', 'ovabookpro' ),
				'uploaded_to_this_item' => __( 'Uploaded to this package', 'ovabookpro' ),
				'filter_items_list'     => __( 'Filter packages', 'ovabookpro' ),
				'items_list_navigation' => __( 'Packages navigation', 'ovabookpro' ),
				'items_list'            => __( 'Packages list', 'ovabookpro' ),
			),
			'public'              => false,
			'show_ui'             => false,
			'capability_type'     => 'post',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'hierarchical'        => false,
			'show_in_nav_menus'   => false,
			'menu_position' 	  => 30,
			'menu_icon' 	      => 'dashicons-archive',
			'query_var'           => false,
			'supports'            => array( 'title', 'author' ),
			'has_archive'         => false,
			'rewrite'            => array(
				'slug'       => _x('package','Package Slug', 'ovabookpro'),
			),
		);

		register_post_type( 'obp_package', $args_package );


		$tax_labels = array(
			'name'                  => _x( 'Taxes', 'Post type general name', 'ovabookpro' ),
			'singular_name'         => _x( 'Tax', 'Post type singular name', 'ovabookpro' ),
			'menu_name'             => _x( 'Taxes', 'Admin Menu text', 'ovabookpro' ),
			'name_admin_bar'        => _x( 'Tax', 'Add New on Toolbar', 'ovabookpro' ),
			'add_new'               => __( 'Add New', 'ovabookpro' ),
			'add_new_item'          => __( 'Add New Tax', 'ovabookpro' ),
			'new_item'              => __( 'New Tax', 'ovabookpro' ),
			'edit_item'             => __( 'Edit Tax', 'ovabookpro' ),
			'view_item'             => __( 'View Tax', 'ovabookpro' ),
			'all_items'             => __( 'All Taxes', 'ovabookpro' ),
			'search_items'          => __( 'Search Taxes', 'ovabookpro' ),
			'parent_item_colon'     => __( 'Parent Taxes:', 'ovabookpro' ),
			'not_found'             => __( 'No taxes found.', 'ovabookpro' ),
			'not_found_in_trash'    => __( 'No taxes found in Trash.', 'ovabookpro' ),
		);

		$tax_args = array(
	        'labels'             => $tax_labels,
	        'description'        => 'Tax custom post type.',
	        'public'             => false,
	        'publicly_queryable' => false,
	        'show_ui'            => true,
	        'show_in_menu'       => false,
	        'query_var'          => false,
	        'rewrite'            => array( 'slug' => 'tax' ),
	        'capability_type'    => 'obp_tax',
	        'has_archive'        => false,
	        'hierarchical'       => false,
	        'show_in_nav_menus'  => false,
			'show_in_admin_bar'  => false,
	        'menu_position'      => 30,
	        'menu_icon' 		 => 'dashicons-calculator',
	        'supports'           => array( 'title', 'author' ),
	        'taxonomies'         => array( 'obp_tax_classes' ),
	        'show_in_rest'       => false
	    );

	    register_post_type( 'obp_tax', $tax_args );

		do_action( 'obp_after_register_post_type' );

	}

	/**
	 * register taxonomies
	 */
	public function register_taxonomies() {
		// Business Categories
		$labels = array(
			'name'              => _x( 'Business Categories', 'Taxonomy general name', 'ovabookpro' ),
			'singular_name'     => _x( 'Category', 'Taxonomy singular name', 'ovabookpro' ),
			'search_items'      => __( 'Search Categories', 'ovabookpro' ),
			'all_items'         => __( 'All Categories', 'ovabookpro' ),
			'parent_item'       => __( 'Parent Category', 'ovabookpro' ),
			'parent_item_colon' => __( 'Parent Category:', 'ovabookpro' ),
			'edit_item'         => __( 'Edit Category', 'ovabookpro' ),
			'update_item'       => __( 'Update Category', 'ovabookpro' ),
			'add_new_item'      => __( 'Add New Category', 'ovabookpro' ),
			'new_item_name'     => __( 'New Category', 'ovabookpro' ),
			'menu_name'         => __( 'Categories', 'ovabookpro' )
		);

		$args = array(
			'hierarchical'       => true,
			'label'              => __( 'Categories', 'ovabookpro' ),
			'labels'             => $labels,
			'public'             => true,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'show_in_nav_menus'  => true,
			'publicly_queryable' => true,
			'query_var'          => true,
			'capabilities'      => array (
			    'manage_terms' => 'manage_obp_business_terms',
				'edit_terms'   => 'edit_obp_business_terms',
				'delete_terms' => 'delete_obp_business_terms',
				'assign_terms' => 'assign_obp_business_terms',
			),
			'rewrite'            => array(
				'slug'       => _x('business_category','Business Category Slug', 'ovabookpro'),
			),
			
		);

		$args = apply_filters( 'obp_register_tax_business_cat', $args );
		register_taxonomy( 'business_cat', array( 'obp_business' ), $args );

		unset( $args );
		unset( $labels );

		// Business Amenity
		$labels = array(
			'name'              => _x( 'Amenities', 'Taxonomy general name', 'ovabookpro' ),
			'singular_name'     => _x( 'Amenity', 'Taxonomy singular name', 'ovabookpro' ),
			'search_items'      => __( 'Search Amenity', 'ovabookpro' ),
			'all_items'         => __( 'All Amenities', 'ovabookpro' ),
			'parent_item'       => __( 'Parent Amenity', 'ovabookpro' ),
			'parent_item_colon' => __( 'Parent Amenity:', 'ovabookpro' ),
			'edit_item'         => __( 'Edit Amenity', 'ovabookpro' ),
			'update_item'       => __( 'Update Amenity', 'ovabookpro' ),
			'add_new_item'      => __( 'Add New Amenity', 'ovabookpro' ),
			'new_item_name'     => __( 'New Amenity', 'ovabookpro' ),
			'menu_name'         => __( 'Amenities', 'ovabookpro' )
		);

		$args = array(
			'hierarchical'      => true,
			'public'            => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'capabilities'      => array (
			    'manage_terms' => 'manage_obp_business_terms',
				'edit_terms'   => 'edit_obp_business_terms',
				'delete_terms' => 'delete_obp_business_terms',
				'assign_terms' => 'assign_obp_business_terms',
			),
			'rewrite'           => array(
				'slug'       => _x('business_amenity','Business Amenity Slug', 'ovabookpro'),
			),
		);

		$args = apply_filters( 'obp_register_tax_business_amenity', $args );

		register_taxonomy( 'business_amenity', array( 'obp_business' ), $args );

		unset( $args );
		unset( $labels );

		$labels = array(
			'name'                       => _x( 'Tax Classes', 'taxonomy general name', 'ovabookpro' ),
			'singular_name'              => _x( 'Tax Class', 'taxonomy singular name', 'ovabookpro' ),
			'search_items'               => __( 'Search Tax Classes', 'ovabookpro' ),
			'popular_items'              => __( 'Popular Tax Classes', 'ovabookpro' ),
			'all_items'                  => __( 'All Tax Classes', 'ovabookpro' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tax Class', 'ovabookpro' ),
			'update_item'                => __( 'Update Tax Class', 'ovabookpro' ),
			'add_new_item'               => __( 'Add New Tax Class', 'ovabookpro' ),
			'new_item_name'              => __( 'New Tax Class Name', 'ovabookpro' ),
			'separate_items_with_commas' => __( 'Separate tax classes with commas', 'ovabookpro' ),
			'add_or_remove_items'        => __( 'Add or remove tax classes', 'ovabookpro' ),
			'choose_from_most_used'      => __( 'Choose from the most used tax classes', 'ovabookpro' ),
			'not_found'                  => __( 'No tax classes found.', 'ovabookpro' ),
			'menu_name'                  => __( 'Tax Classes', 'ovabookpro' ),
		);

		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'tax-class' ),
		);

		register_taxonomy( 'obp_tax_classes', 'obp_tax', $args );

		unset( $args );
		unset( $labels );

		$labels = array(
			'name'                       => _x( 'Tags', 'taxonomy general name', 'ovabookpro' ),
			'singular_name'              => _x( 'tag', 'taxonomy singular name', 'ovabookpro' ),
			'search_items'               => __( 'Search tags', 'ovabookpro' ),
			'popular_items'              => __( 'Popular Tags', 'ovabookpro' ),
			'all_items'                  => __( 'All Tags', 'ovabookpro' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Tag', 'ovabookpro' ),
			'update_item'                => __( 'Update Tag', 'ovabookpro' ),
			'add_new_item'               => __( 'Add New Tag', 'ovabookpro' ),
			'new_item_name'              => __( 'New Tag Name', 'ovabookpro' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'ovabookpro' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'ovabookpro' ),
			'choose_from_most_used'      => __( 'Choose from the most used tags', 'ovabookpro' ),
			'not_found'                  => __( 'No tags found.', 'ovabookpro' ),
			'menu_name'                  => __( 'Tags', 'ovabookpro' ),
		);

		$args = array(
			'hierarchical'          => false,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'business-tag' ),
		);

		register_taxonomy( 'business_tag', 'obp_business', $args );
	}
	

	/**
	 * add icon for taxonomy: business amenity
	 */
	function add_obp_business_amenity_class_icon(){
	    ?>
	    <div class="form-field">
			<label>
				<?php esc_html_e( 'Class Icon', 'ovabookpro' ); ?>	
			</label>
			<input type="text" id="business_amenity_class_icon" name="business_amenity_class_icon" value="" />
			<div class="clear"></div>
		</div>
	    <?php
	}

	function edit_obp_business_amenity_class_icon($term) {
	    $class_icon = get_term_meta( $term->term_id, 'class_icon', true );
	?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label>
					<?php esc_html_e( 'Class Icon', 'ovabookpro' ); ?>
				</label>
			</th>
			<td>
				<input type="text" id="business_amenity_class_icon" name="business_amenity_class_icon" value="<?php echo esc_attr( $class_icon ); ?>" />
				<div class="clear"></div>
			</td>
		</tr>
		<?php 
	}

	function save_obp_business_amenity_class_icon( $term_id, $tt_id = '', $taxonomy = '' ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
	    if ( isset( $_POST['business_amenity_class_icon'] ) && 'business_amenity' === $taxonomy ) { // WPCS: CSRF ok, input var ok.
			update_term_meta( $term_id, 'class_icon', sanitize_text_field( wp_unslash( $_POST['business_amenity_class_icon'] ) ) ) ; // WPCS: CSRF ok, input var ok.
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

}