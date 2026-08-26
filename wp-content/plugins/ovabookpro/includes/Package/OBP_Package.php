<?php
namespace BookPro\Package;

class OBP_Package {


	public static function get_package_exclude( $service_id, $exclude_ids = array() ){
		$args = array(
			'post_type' 		=> 'obp_package',
			'post_status' 		=> 'publish',
			'post__not_in' 		=> $exclude_ids,
			'posts_per_page' 	=> -1,
			'meta_query' => array(
				array(
					'key' 		=> OBP_METABOX.'service_id',
					'value' 	=> $service_id,
				),
			),
			'fields' => 'ids',
		);

		$package_ids = get_posts( $args );
		return $package_ids;
	}
}