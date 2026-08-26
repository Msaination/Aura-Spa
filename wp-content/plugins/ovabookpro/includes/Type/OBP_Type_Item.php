<?php 
namespace BookPro\Type;


class OBP_Type_Item {


	public $id = null;

	public $post = null;

	public function __construct( $id = null ){

		if ( $id && get_post_type( $id ) == 'obp_type' ) {
			$this->post = get_post( $id );
			$this->id = $id;
		}

	}

	public function get_id(){
		return $this->id;
	}

	public function get_name(){
		$name = '';
		if ( $this->post ) {
			$name = $this->post->post_title;
		}
		return apply_filters( 'obp_get_type_name', $name, $this );
	}
}