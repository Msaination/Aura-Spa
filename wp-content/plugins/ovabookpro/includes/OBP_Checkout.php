<?php

namespace BookPro;


defined( 'ABSPATH' ) || exit;

class OBP_Checkout {

	public static function processing( $order_id ){
		$response = [];
		$data = [];
		$data['order_id'] = $order_id;
		$response['status'] = 'success';
		$response['callback'] = 'obp_checkout_form';
		$response['data'] = $data;
		return $response;
	}
}