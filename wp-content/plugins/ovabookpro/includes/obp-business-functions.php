<?php
defined( 'ABSPATH' ) || exit;


/*
return (array) business categories id=>name
*/
function obp_get_business_categories(){
	$terms = BookPro\Business\OBP_Business::get_categories();
	return apply_filters( 'obp_get_business_categories' , $terms );
}

/*
return (array) business amenities id=>name
*/
function obp_get_business_amenities(){
	$terms = BookPro\Business\OBP_Business::get_amenities();
	return apply_filters( 'obp_get_business_amenities' , $terms );
}