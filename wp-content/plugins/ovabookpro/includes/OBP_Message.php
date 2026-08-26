<?php
namespace BookPro;

use BookPro\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

class OBP_Message {

	use SingletonTrait;

	public function __construct( $message = '', $type = 'success' ){
		if ( ! empty( $message ) ) {
			$this->add( $message, $type );
		}
	}

	public function add( $message = '', $type = 'success' ){

		$messages = OBP()->session->get('messages', array() );

		$messages[$type][] = $message;
		OBP()->session->set( 'messages', $messages );
		OBP()->session->save_data();
	}

	public function clear(){
		OBP()->session->set('messages', null );
		OBP()->session->save_data();
	}

	public function print(){
		$all_messages 		= OBP()->session->get( 'messages', array() );
		$available_types 	= array( 'success', 'error' );

	
		foreach ( $available_types as $type ) {
			if ( isset( $all_messages[$type] ) ) {
				foreach ( $all_messages[$type] as $message ) {
					obp_get_template( "message/".$type.".php", array( 'message' => $message ) );
				}
			}
		}
		$this->clear();
	
	}
}