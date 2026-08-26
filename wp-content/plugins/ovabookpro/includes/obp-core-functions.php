<?php
defined( 'ABSPATH' ) || exit;

// Dump die
if ( ! function_exists( 'dd' ) ) {
	function dd( ...$agrs ) {
		echo '<pre>';
		var_dump( ...$agrs ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
		echo '</pre>';
		die;
	}
}

// array exists
if ( ! function_exists( 'obp_array_exists' ) ) {
	function obp_array_exists( $arr ) {
		if ( ! empty( $arr ) && is_array( $arr ) ) {
			return true;
		}
		return false;
	}
}

// echo esc_attr()
if ( ! function_exists( 'obp_esc_attr' ) ) {
	function obp_esc_attr( $text = '', $echo = true ) {
		if ( $echo ) echo esc_attr( $text );
		if ( ! $echo ) return esc_attr( $text );
	}
}

// echo esc_html()
if ( ! function_exists( 'obp_esc_html' ) ) {
	function obp_esc_html( $text = '', $echo = true ) {
		if ( $echo ) echo esc_html( $text );
		if ( ! $echo ) return esc_html( $text );
	}
}

// Get all page IDs
if ( ! function_exists( 'obp_get_all_page_ids' ) ) {
	function obp_get_all_page_ids() {
		$args = [
			'post_type' 		=> 'page',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'order' 			=> 'ASC',
			'orderby' 			=> 'title',
		];
		// check page has shorcodes of plugin
		$arr_shorcode = obp_get_shorcodes();

		$pages 	= get_posts( apply_filters( 'obp_query_get_all_page_ids', $args ) );
		$page_ids 	= [];

		foreach ( $pages as $page ) {
			$page_content = $page->post_content;
			foreach ( $arr_shorcode as $shorcode ) {
				if ( has_shortcode( $page_content, $shorcode ) ) {
					$page_ids[] = $page->ID;
					break;
				}
			}
		}

		return apply_filters( 'obp_get_all_page_ids', $page_ids );
	}
}


if ( ! function_exists( 'obp_get_shorcodes' ) ) {
	function obp_get_shorcodes(){
		$arr_shorcode = array(
			'obp_member_account',
			'obp_login',
			'obp_register_user',
			'obp_forgot_password',
			'obp_reset_password',
			'obp_booking_info',
		);
		return apply_filters( 'obp_get_shorcodes', $arr_shorcode );
	}
}

/* Sanitize */
if ( ! function_exists( 'obp_recursive_sanitize_text_field' ) ) { 
	function obp_recursive_sanitize_text_field( $array ) {
	  	foreach ( $array as $key => &$value ) {
	        if ( is_array( $value ) ) {
	            $value = obp_recursive_sanitize_text_field( $value );
	        } else {
	            $value = sanitize_text_field( $value );
	        }
	    }
	    return $array;
	}
}



// ISO 3166-1 alpha-2 codes
if ( ! function_exists( 'obp_iso_alpha2' ) ) {
	function obp_iso_alpha2() {
		$countries = [
		    'AD' => esc_html__('Andorra', 'ovabookpro'),
		    'AE' => esc_html__('United Arab Emirates', 'ovabookpro'),
		    'AF' => esc_html__('Afghanistan', 'ovabookpro'),
		    'AG' => esc_html__('Antigua and Barbuda', 'ovabookpro'),
		    'AI' => esc_html__('Anguilla', 'ovabookpro'),
		    'AL' => esc_html__('Albania', 'ovabookpro'),
		    'AM' => esc_html__('Armenia', 'ovabookpro'),
		    'AO' => esc_html__('Angola', 'ovabookpro'),
		    'AQ' => esc_html__('Antarctica', 'ovabookpro'),
		    'AR' => esc_html__('Argentina', 'ovabookpro'),
		    'AS' => esc_html__('American Samoa', 'ovabookpro'),
		    'AT' => esc_html__('Austria', 'ovabookpro'),
		    'AU' => esc_html__('Australia', 'ovabookpro'),
		    'AW' => esc_html__('Aruba', 'ovabookpro'),
		    'AX' => esc_html__('Åland Islands', 'ovabookpro'),
		    'AZ' => esc_html__('Azerbaijan', 'ovabookpro'),
		    'BA' => esc_html__('Bosnia and Herzegovina', 'ovabookpro'),
		    'BB' => esc_html__('Barbados', 'ovabookpro'),
		    'BD' => esc_html__('Bangladesh', 'ovabookpro'),
		    'BE' => esc_html__('Belgium', 'ovabookpro'),
		    'BF' => esc_html__('Burkina Faso', 'ovabookpro'),
		    'BG' => esc_html__('Bulgaria', 'ovabookpro'),
		    'BH' => esc_html__('Bahrain', 'ovabookpro'),
		    'BI' => esc_html__('Burundi', 'ovabookpro'),
		    'BJ' => esc_html__('Benin', 'ovabookpro'),
		    'BL' => esc_html__('Saint Barthélemy', 'ovabookpro'),
		    'BM' => esc_html__('Bermuda', 'ovabookpro'),
		    'BN' => esc_html__('Brunei Darussalam', 'ovabookpro'),
		    'BO' => esc_html__('Bolivia (Plurinational State of)', 'ovabookpro'),
		    'BQ' => esc_html__('Bonaire, Sint Eustatius and Saba', 'ovabookpro'),
		    'BR' => esc_html__('Brazil', 'ovabookpro'),
		    'BS' => esc_html__('Bahamas', 'ovabookpro'),
		    'BT' => esc_html__('Bhutan', 'ovabookpro'),
		    'BV' => esc_html__('Bouvet Island', 'ovabookpro'),
		    'BW' => esc_html__('Botswana', 'ovabookpro'),
		    'BY' => esc_html__('Belarus', 'ovabookpro'),
		    'BZ' => esc_html__('Belize', 'ovabookpro'),
		    'CA' => esc_html__('Canada', 'ovabookpro'),
		    'CC' => esc_html__('Cocos (Keeling) Islands', 'ovabookpro'),
		    'CD' => esc_html__('Congo, Democratic Republic of the', 'ovabookpro'),
		    'CF' => esc_html__('Central African Republic', 'ovabookpro'),
		    'CG' => esc_html__('Congo', 'ovabookpro'),
		    'CH' => esc_html__('Switzerland', 'ovabookpro'),
		    'CI' => esc_html__('Côte d\'Ivoire', 'ovabookpro'),
		    'CK' => esc_html__('Cook Islands', 'ovabookpro'),
		    'CL' => esc_html__('Chile', 'ovabookpro'),
		    'CM' => esc_html__('Cameroon', 'ovabookpro'),
		    'CN' => esc_html__('China', 'ovabookpro'),
		    'CO' => esc_html__('Colombia', 'ovabookpro'),
		    'CR' => esc_html__('Costa Rica', 'ovabookpro'),
		    'CU' => esc_html__('Cuba', 'ovabookpro'),
		    'CV' => esc_html__('Cabo Verde', 'ovabookpro'),
		    'CW' => esc_html__('Curaçao', 'ovabookpro'),
		    'CX' => esc_html__('Christmas Island', 'ovabookpro'),
		    'CY' => esc_html__('Cyprus', 'ovabookpro'),
		    'CZ' => esc_html__('Czechia', 'ovabookpro'),
		    'DE' => esc_html__('Germany', 'ovabookpro'),
		    'DJ' => esc_html__('Djibouti', 'ovabookpro'),
		    'DK' => esc_html__('Denmark', 'ovabookpro'),
		    'DM' => esc_html__('Dominica', 'ovabookpro'),
		    'DO' => esc_html__('Dominican Republic', 'ovabookpro'),
		    'DZ' => esc_html__('Algeria', 'ovabookpro'),
		    'EC' => esc_html__('Ecuador', 'ovabookpro'),
		    'EE' => esc_html__('Estonia', 'ovabookpro'),
		    'EG' => esc_html__('Egypt', 'ovabookpro'),
		    'EH' => esc_html__('Western Sahara', 'ovabookpro'),
		    'ER' => esc_html__('Eritrea', 'ovabookpro'),
		    'ES' => esc_html__('Spain', 'ovabookpro'),
		    'ET' => esc_html__('Ethiopia', 'ovabookpro'),
		    'FI' => esc_html__('Finland', 'ovabookpro'),
		    'FJ' => esc_html__('Fiji', 'ovabookpro'),
		    'FK' => esc_html__('Falkland Islands (Malvinas)', 'ovabookpro'),
		    'FM' => esc_html__('Micronesia (Federated States of)', 'ovabookpro'),
		    'FO' => esc_html__('Faroe Islands', 'ovabookpro'),
		    'FR' => esc_html__('France', 'ovabookpro'),
		    'GA' => esc_html__('Gabon', 'ovabookpro'),
		    'GB' => esc_html__('United Kingdom of Great Britain and Northern Ireland', 'ovabookpro'),
		    'GD' => esc_html__('Grenada', 'ovabookpro'),
		    'GE' => esc_html__('Georgia', 'ovabookpro'),
		    'GF' => esc_html__('French Guiana', 'ovabookpro'),
		    'GG' => esc_html__('Guernsey', 'ovabookpro'),
		    'GH' => esc_html__('Ghana', 'ovabookpro'),
		    'GI' => esc_html__('Gibraltar', 'ovabookpro'),
		    'GL' => esc_html__('Greenland', 'ovabookpro'),
		    'GM' => esc_html__('Gambia', 'ovabookpro'),
		    'GN' => esc_html__('Guinea', 'ovabookpro'),
		    'GP' => esc_html__('Guadeloupe', 'ovabookpro'),
		    'GQ' => esc_html__('Equatorial Guinea', 'ovabookpro'),
		    'GR' => esc_html__('Greece', 'ovabookpro'),
		    'GS' => esc_html__('South Georgia and the South Sandwich Islands', 'ovabookpro'),
		    'GT' => esc_html__('Guatemala', 'ovabookpro'),
		    'GU' => esc_html__('Guam', 'ovabookpro'),
		    'GW' => esc_html__('Guinea-Bissau', 'ovabookpro'),
		    'GY' => esc_html__('Guyana', 'ovabookpro'),
		    'HK' => esc_html__('Hong Kong', 'ovabookpro'),
		    'HM' => esc_html__('Heard Island and McDonald Islands', 'ovabookpro'),
		    'HN' => esc_html__('Honduras', 'ovabookpro'),
		    'HR' => esc_html__('Croatia', 'ovabookpro'),
		    'HT' => esc_html__('Haiti', 'ovabookpro'),
		    'HU' => esc_html__('Hungary', 'ovabookpro'),
		    'ID' => esc_html__('Indonesia', 'ovabookpro'),
		    'IE' => esc_html__('Ireland', 'ovabookpro'),
		    'IL' => esc_html__('Israel', 'ovabookpro'),
		    'IM' => esc_html__('Isle of Man', 'ovabookpro'),
		    'IN' => esc_html__('India', 'ovabookpro'),
		    'IO' => esc_html__('British Indian Ocean Territory', 'ovabookpro'),
		    'IQ' => esc_html__('Iraq', 'ovabookpro'),
		    'IR' => esc_html__('Iran (Islamic Republic of)', 'ovabookpro'),
		    'IS' => esc_html__('Iceland', 'ovabookpro'),
		    'IT' => esc_html__('Italy', 'ovabookpro'),
		    'JE' => esc_html__('Jersey', 'ovabookpro'),
		    'JM' => esc_html__('Jamaica', 'ovabookpro'),
		    'JO' => esc_html__('Jordan', 'ovabookpro'),
		    'JP' => esc_html__('Japan', 'ovabookpro'),
		    'KE' => esc_html__('Kenya', 'ovabookpro'),
		    'KG' => esc_html__('Kyrgyzstan', 'ovabookpro'),
		    'KH' => esc_html__('Cambodia', 'ovabookpro'),
		    'KI' => esc_html__('Kiribati', 'ovabookpro'),
		    'KM' => esc_html__('Comoros', 'ovabookpro'),
		    'KN' => esc_html__('Saint Kitts and Nevis', 'ovabookpro'),
		    'KP' => esc_html__('Korea (Democratic People\'s Republic of)', 'ovabookpro'),
		    'KR' => esc_html__('Korea, Republic of', 'ovabookpro'),
		    'KW' => esc_html__('Kuwait', 'ovabookpro'),
		    'KY' => esc_html__('Cayman Islands', 'ovabookpro'),
		    'KZ' => esc_html__('Kazakhstan', 'ovabookpro'),
		    'LA' => esc_html__('Lao People\'s Democratic Republic', 'ovabookpro'),
		    'LB' => esc_html__('Lebanon', 'ovabookpro'),
		    'LC' => esc_html__('Saint Lucia', 'ovabookpro'),
		    'LI' => esc_html__('Liechtenstein', 'ovabookpro'),
		    'LK' => esc_html__('Sri Lanka', 'ovabookpro'),
		    'LR' => esc_html__('Liberia', 'ovabookpro'),
		    'LS' => esc_html__('Lesotho', 'ovabookpro'),
		    'LT' => esc_html__('Lithuania', 'ovabookpro'),
		    'LU' => esc_html__('Luxembourg', 'ovabookpro'),
		    'LV' => esc_html__('Latvia', 'ovabookpro'),
		    'LY' => esc_html__('Libya', 'ovabookpro'),
		    'MA' => esc_html__('Morocco', 'ovabookpro'),
		    'MC' => esc_html__('Monaco', 'ovabookpro'),
		    'MD' => esc_html__('Moldova, Republic of', 'ovabookpro'),
		    'ME' => esc_html__('Montenegro', 'ovabookpro'),
		    'MF' => esc_html__('Saint Martin (French part)', 'ovabookpro'),
		    'MG' => esc_html__('Madagascar', 'ovabookpro'),
		    'MH' => esc_html__('Marshall Islands', 'ovabookpro'),
		    'MK' => esc_html__('North Macedonia', 'ovabookpro'),
		    'ML' => esc_html__('Mali', 'ovabookpro'),
		    'MM' => esc_html__('Myanmar', 'ovabookpro'),
		    'MN' => esc_html__('Mongolia', 'ovabookpro'),
		    'MO' => esc_html__('Macao', 'ovabookpro'),
		    'MP' => esc_html__('Northern Mariana Islands', 'ovabookpro'),
		    'MQ' => esc_html__('Martinique', 'ovabookpro'),
		    'MR' => esc_html__('Mauritania', 'ovabookpro'),
		    'MS' => esc_html__('Montserrat', 'ovabookpro'),
		    'MT' => esc_html__('Malta', 'ovabookpro'),
		    'MU' => esc_html__('Mauritius', 'ovabookpro'),
		    'MV' => esc_html__('Maldives', 'ovabookpro'),
		    'MW' => esc_html__('Malawi', 'ovabookpro'),
		    'MX' => esc_html__('Mexico', 'ovabookpro'),
		    'MY' => esc_html__('Malaysia', 'ovabookpro'),
		    'MZ' => esc_html__('Mozambique', 'ovabookpro'),
		    'NA' => esc_html__('Namibia', 'ovabookpro'),
		    'NC' => esc_html__('New Caledonia', 'ovabookpro'),
		    'NE' => esc_html__('Niger', 'ovabookpro'),
		    'NF' => esc_html__('Norfolk Island', 'ovabookpro'),
		    'NG' => esc_html__('Nigeria', 'ovabookpro'),
		    'NI' => esc_html__('Nicaragua', 'ovabookpro'),
		    'NL' => esc_html__('Netherlands, Kingdom of the', 'ovabookpro'),
		    'NO' => esc_html__('Norway', 'ovabookpro'),
		    'NP' => esc_html__('Nepal', 'ovabookpro'),
		    'NR' => esc_html__('Nauru', 'ovabookpro'),
		    'NU' => esc_html__('Niue', 'ovabookpro'),
		    'NZ' => esc_html__('New Zealand', 'ovabookpro'),
		    'OM' => esc_html__('Oman', 'ovabookpro'),
		    'PA' => esc_html__('Panama', 'ovabookpro'),
		    'PE' => esc_html__('Peru', 'ovabookpro'),
		    'PF' => esc_html__('French Polynesia', 'ovabookpro'),
		    'PG' => esc_html__('Papua New Guinea', 'ovabookpro'),
		    'PH' => esc_html__('Philippines', 'ovabookpro'),
		    'PK' => esc_html__('Pakistan', 'ovabookpro'),
		    'PL' => esc_html__('Poland', 'ovabookpro'),
		    'PM' => esc_html__('Saint Pierre and Miquelon', 'ovabookpro'),
		    'PN' => esc_html__('Pitcairn', 'ovabookpro'),
		    'PR' => esc_html__('Puerto Rico', 'ovabookpro'),
		    'PS' => esc_html__('Palestine, State of', 'ovabookpro'),
		    'PT' => esc_html__('Portugal', 'ovabookpro'),
		    'PW' => esc_html__('Palau', 'ovabookpro'),
		    'PY' => esc_html__('Paraguay', 'ovabookpro'),
		    'QA' => esc_html__('Qatar', 'ovabookpro'),
		    'RE' => esc_html__('Réunion', 'ovabookpro'),
		    'RO' => esc_html__('Romania', 'ovabookpro'),
		    'RS' => esc_html__('Serbia', 'ovabookpro'),
		    'RU' => esc_html__('Russian Federation', 'ovabookpro'),
		    'RW' => esc_html__('Rwanda', 'ovabookpro'),
		    'SA' => esc_html__('Saudi Arabia', 'ovabookpro'),
		    'SB' => esc_html__('Solomon Islands', 'ovabookpro'),
		    'SC' => esc_html__('Seychelles', 'ovabookpro'),
		    'SD' => esc_html__('Sudan', 'ovabookpro'),
		    'SE' => esc_html__('Sweden', 'ovabookpro'),
		    'SG' => esc_html__('Singapore', 'ovabookpro'),
		    'SH' => esc_html__('Saint Helena, Ascension and Tristan da Cunha', 'ovabookpro'),
		    'SI' => esc_html__('Slovenia', 'ovabookpro'),
		    'SJ' => esc_html__('Svalbard and Jan Mayen', 'ovabookpro'),
		    'SK' => esc_html__('Slovakia', 'ovabookpro'),
		    'SL' => esc_html__('Sierra Leone', 'ovabookpro'),
		    'SM' => esc_html__('San Marino', 'ovabookpro'),
		    'SN' => esc_html__('Senegal', 'ovabookpro'),
		    'SO' => esc_html__('Somalia', 'ovabookpro'),
		    'SR' => esc_html__('Suriname', 'ovabookpro'),
		    'SS' => esc_html__('South Sudan', 'ovabookpro'),
		    'ST' => esc_html__('Sao Tome and Principe', 'ovabookpro'),
		    'SV' => esc_html__('El Salvador', 'ovabookpro'),
		    'SX' => esc_html__('Sint Maarten (Dutch part)', 'ovabookpro'),
		    'SY' => esc_html__('Syrian Arab Republic', 'ovabookpro'),
		    'SZ' => esc_html__('Eswatini', 'ovabookpro'),
		    'TC' => esc_html__('Turks and Caicos Islands', 'ovabookpro'),
		    'TD' => esc_html__('Chad', 'ovabookpro'),
		    'TF' => esc_html__('French Southern Territories', 'ovabookpro'),
		    'TG' => esc_html__('Togo', 'ovabookpro'),
		    'TH' => esc_html__('Thailand', 'ovabookpro'),
		    'TJ' => esc_html__('Tajikistan', 'ovabookpro'),
		    'TK' => esc_html__('Tokelau', 'ovabookpro'),
		    'TL' => esc_html__('Timor-Leste', 'ovabookpro'),
		    'TM' => esc_html__('Turkmenistan', 'ovabookpro'),
		    'TN' => esc_html__('Tunisia', 'ovabookpro'),
		    'TO' => esc_html__('Tonga', 'ovabookpro'),
		    'TR' => esc_html__('Türkiye', 'ovabookpro'),
		    'TT' => esc_html__('Trinidad and Tobago', 'ovabookpro'),
		    'TV' => esc_html__('Tuvalu', 'ovabookpro'),
		    'TW' => esc_html__('Taiwan, Province of China', 'ovabookpro'),
		    'TZ' => esc_html__('Tanzania, United Republic of', 'ovabookpro'),
		    'UA' => esc_html__('Ukraine', 'ovabookpro'),
		    'UG' => esc_html__('Uganda', 'ovabookpro'),
		    'UM' => esc_html__('United States Minor Outlying Islands', 'ovabookpro'),
		    'US' => esc_html__('United States of America', 'ovabookpro'),
		    'UY' => esc_html__('Uruguay', 'ovabookpro'),
		    'UZ' => esc_html__('Uzbekistan', 'ovabookpro'),
		    'VA' => esc_html__('Holy See', 'ovabookpro'),
		    'VC' => esc_html__('Saint Vincent and the Grenadines', 'ovabookpro'),
		    'VE' => esc_html__('Venezuela (Bolivarian Republic of)', 'ovabookpro'),
		    'VG' => esc_html__('Virgin Islands (British)', 'ovabookpro'),
		    'VI' => esc_html__('Virgin Islands (U.S.)', 'ovabookpro'),
		    'VN' => esc_html__('Viet Nam', 'ovabookpro'),
		    'VU' => esc_html__('Vanuatu', 'ovabookpro'),
		    'WF' => esc_html__('Wallis and Futuna', 'ovabookpro'),
		    'WS' => esc_html__('Samoa', 'ovabookpro'),
		    'YE' => esc_html__('Yemen', 'ovabookpro'),
		    'YT' => esc_html__('Mayotte', 'ovabookpro'),
		    'ZA' => esc_html__('South Africa', 'ovabookpro'),
		    'ZM' => esc_html__('Zambia', 'ovabookpro'),
		    'ZW' => esc_html__('Zimbabwe', 'ovabookpro'),
		];

		return apply_filters( 'obp_iso_alpha2', $countries );
	}
}

// Get currency
if ( ! function_exists( 'obp_get_currency' ) ) {
	function obp_get_currency() {
		$currency = OBP()->settings->general->get('currency', 'USD');
		return apply_filters( 'obp_get_currency', $currency );
	}
}

// Get currencies
if ( ! function_exists( 'obp_get_currencies' ) ) {
	function obp_get_currencies() {
		static $currencies;

		if ( ! isset( $currencies ) ) {
			$currencies = array_unique(
				apply_filters(
					'obp_get_currencies',
					array(
						'AED' => __( 'United Arab Emirates dirham', 'ovabookpro' ),
						'AFN' => __( 'Afghan afghani', 'ovabookpro' ),
						'ALL' => __( 'Albanian lek', 'ovabookpro' ),
						'AMD' => __( 'Armenian dram', 'ovabookpro' ),
						'ANG' => __( 'Netherlands Antillean guilder', 'ovabookpro' ),
						'AOA' => __( 'Angolan kwanza', 'ovabookpro' ),
						'ARS' => __( 'Argentine peso', 'ovabookpro' ),
						'AUD' => __( 'Australian dollar', 'ovabookpro' ),
						'AWG' => __( 'Aruban florin', 'ovabookpro' ),
						'AZN' => __( 'Azerbaijani manat', 'ovabookpro' ),
						'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'ovabookpro' ),
						'BBD' => __( 'Barbadian dollar', 'ovabookpro' ),
						'BDT' => __( 'Bangladeshi taka', 'ovabookpro' ),
						'BGN' => __( 'Bulgarian lev', 'ovabookpro' ),
						'BHD' => __( 'Bahraini dinar', 'ovabookpro' ),
						'BIF' => __( 'Burundian franc', 'ovabookpro' ),
						'BMD' => __( 'Bermudian dollar', 'ovabookpro' ),
						'BND' => __( 'Brunei dollar', 'ovabookpro' ),
						'BOB' => __( 'Bolivian boliviano', 'ovabookpro' ),
						'BRL' => __( 'Brazilian real', 'ovabookpro' ),
						'BSD' => __( 'Bahamian dollar', 'ovabookpro' ),
						'BTC' => __( 'Bitcoin', 'ovabookpro' ),
						'BTN' => __( 'Bhutanese ngultrum', 'ovabookpro' ),
						'BWP' => __( 'Botswana pula', 'ovabookpro' ),
						'BYR' => __( 'Belarusian ruble (old)', 'ovabookpro' ),
						'BYN' => __( 'Belarusian ruble', 'ovabookpro' ),
						'BZD' => __( 'Belize dollar', 'ovabookpro' ),
						'CAD' => __( 'Canadian dollar', 'ovabookpro' ),
						'CDF' => __( 'Congolese franc', 'ovabookpro' ),
						'CHF' => __( 'Swiss franc', 'ovabookpro' ),
						'CLP' => __( 'Chilean peso', 'ovabookpro' ),
						'CNY' => __( 'Chinese yuan', 'ovabookpro' ),
						'COP' => __( 'Colombian peso', 'ovabookpro' ),
						'CRC' => __( 'Costa Rican col&oacute;n', 'ovabookpro' ),
						'CUC' => __( 'Cuban convertible peso', 'ovabookpro' ),
						'CUP' => __( 'Cuban peso', 'ovabookpro' ),
						'CVE' => __( 'Cape Verdean escudo', 'ovabookpro' ),
						'CZK' => __( 'Czech koruna', 'ovabookpro' ),
						'DJF' => __( 'Djiboutian franc', 'ovabookpro' ),
						'DKK' => __( 'Danish krone', 'ovabookpro' ),
						'DOP' => __( 'Dominican peso', 'ovabookpro' ),
						'DZD' => __( 'Algerian dinar', 'ovabookpro' ),
						'EGP' => __( 'Egyptian pound', 'ovabookpro' ),
						'ERN' => __( 'Eritrean nakfa', 'ovabookpro' ),
						'ETB' => __( 'Ethiopian birr', 'ovabookpro' ),
						'EUR' => __( 'Euro', 'ovabookpro' ),
						'FJD' => __( 'Fijian dollar', 'ovabookpro' ),
						'FKP' => __( 'Falkland Islands pound', 'ovabookpro' ),
						'GBP' => __( 'Pound sterling', 'ovabookpro' ),
						'GEL' => __( 'Georgian lari', 'ovabookpro' ),
						'GGP' => __( 'Guernsey pound', 'ovabookpro' ),
						'GHS' => __( 'Ghana cedi', 'ovabookpro' ),
						'GIP' => __( 'Gibraltar pound', 'ovabookpro' ),
						'GMD' => __( 'Gambian dalasi', 'ovabookpro' ),
						'GNF' => __( 'Guinean franc', 'ovabookpro' ),
						'GTQ' => __( 'Guatemalan quetzal', 'ovabookpro' ),
						'GYD' => __( 'Guyanese dollar', 'ovabookpro' ),
						'HKD' => __( 'Hong Kong dollar', 'ovabookpro' ),
						'HNL' => __( 'Honduran lempira', 'ovabookpro' ),
						'HRK' => __( 'Croatian kuna', 'ovabookpro' ),
						'HTG' => __( 'Haitian gourde', 'ovabookpro' ),
						'HUF' => __( 'Hungarian forint', 'ovabookpro' ),
						'IDR' => __( 'Indonesian rupiah', 'ovabookpro' ),
						'ILS' => __( 'Israeli new shekel', 'ovabookpro' ),
						'IMP' => __( 'Manx pound', 'ovabookpro' ),
						'INR' => __( 'Indian rupee', 'ovabookpro' ),
						'IQD' => __( 'Iraqi dinar', 'ovabookpro' ),
						'IRR' => __( 'Iranian rial', 'ovabookpro' ),
						'IRT' => __( 'Iranian toman', 'ovabookpro' ),
						'ISK' => __( 'Icelandic kr&oacute;na', 'ovabookpro' ),
						'JEP' => __( 'Jersey pound', 'ovabookpro' ),
						'JMD' => __( 'Jamaican dollar', 'ovabookpro' ),
						'JOD' => __( 'Jordanian dinar', 'ovabookpro' ),
						'JPY' => __( 'Japanese yen', 'ovabookpro' ),
						'KES' => __( 'Kenyan shilling', 'ovabookpro' ),
						'KGS' => __( 'Kyrgyzstani som', 'ovabookpro' ),
						'KHR' => __( 'Cambodian riel', 'ovabookpro' ),
						'KMF' => __( 'Comorian franc', 'ovabookpro' ),
						'KPW' => __( 'North Korean won', 'ovabookpro' ),
						'KRW' => __( 'South Korean won', 'ovabookpro' ),
						'KWD' => __( 'Kuwaiti dinar', 'ovabookpro' ),
						'KYD' => __( 'Cayman Islands dollar', 'ovabookpro' ),
						'KZT' => __( 'Kazakhstani tenge', 'ovabookpro' ),
						'LAK' => __( 'Lao kip', 'ovabookpro' ),
						'LBP' => __( 'Lebanese pound', 'ovabookpro' ),
						'LKR' => __( 'Sri Lankan rupee', 'ovabookpro' ),
						'LRD' => __( 'Liberian dollar', 'ovabookpro' ),
						'LSL' => __( 'Lesotho loti', 'ovabookpro' ),
						'LYD' => __( 'Libyan dinar', 'ovabookpro' ),
						'MAD' => __( 'Moroccan dirham', 'ovabookpro' ),
						'MDL' => __( 'Moldovan leu', 'ovabookpro' ),
						'MGA' => __( 'Malagasy ariary', 'ovabookpro' ),
						'MKD' => __( 'Macedonian denar', 'ovabookpro' ),
						'MMK' => __( 'Burmese kyat', 'ovabookpro' ),
						'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'ovabookpro' ),
						'MOP' => __( 'Macanese pataca', 'ovabookpro' ),
						'MRU' => __( 'Mauritanian ouguiya', 'ovabookpro' ),
						'MUR' => __( 'Mauritian rupee', 'ovabookpro' ),
						'MVR' => __( 'Maldivian rufiyaa', 'ovabookpro' ),
						'MWK' => __( 'Malawian kwacha', 'ovabookpro' ),
						'MXN' => __( 'Mexican peso', 'ovabookpro' ),
						'MYR' => __( 'Malaysian ringgit', 'ovabookpro' ),
						'MZN' => __( 'Mozambican metical', 'ovabookpro' ),
						'NAD' => __( 'Namibian dollar', 'ovabookpro' ),
						'NGN' => __( 'Nigerian naira', 'ovabookpro' ),
						'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'ovabookpro' ),
						'NOK' => __( 'Norwegian krone', 'ovabookpro' ),
						'NPR' => __( 'Nepalese rupee', 'ovabookpro' ),
						'NZD' => __( 'New Zealand dollar', 'ovabookpro' ),
						'OMR' => __( 'Omani rial', 'ovabookpro' ),
						'PAB' => __( 'Panamanian balboa', 'ovabookpro' ),
						'PEN' => __( 'Sol', 'ovabookpro' ),
						'PGK' => __( 'Papua New Guinean kina', 'ovabookpro' ),
						'PHP' => __( 'Philippine peso', 'ovabookpro' ),
						'PKR' => __( 'Pakistani rupee', 'ovabookpro' ),
						'PLN' => __( 'Polish z&#x142;oty', 'ovabookpro' ),
						'PRB' => __( 'Transnistrian ruble', 'ovabookpro' ),
						'PYG' => __( 'Paraguayan guaran&iacute;', 'ovabookpro' ),
						'QAR' => __( 'Qatari riyal', 'ovabookpro' ),
						'RON' => __( 'Romanian leu', 'ovabookpro' ),
						'RSD' => __( 'Serbian dinar', 'ovabookpro' ),
						'RUB' => __( 'Russian ruble', 'ovabookpro' ),
						'RWF' => __( 'Rwandan franc', 'ovabookpro' ),
						'SAR' => __( 'Saudi riyal', 'ovabookpro' ),
						'SBD' => __( 'Solomon Islands dollar', 'ovabookpro' ),
						'SCR' => __( 'Seychellois rupee', 'ovabookpro' ),
						'SDG' => __( 'Sudanese pound', 'ovabookpro' ),
						'SEK' => __( 'Swedish krona', 'ovabookpro' ),
						'SGD' => __( 'Singapore dollar', 'ovabookpro' ),
						'SHP' => __( 'Saint Helena pound', 'ovabookpro' ),
						'SLL' => __( 'Sierra Leonean leone', 'ovabookpro' ),
						'SOS' => __( 'Somali shilling', 'ovabookpro' ),
						'SRD' => __( 'Surinamese dollar', 'ovabookpro' ),
						'SSP' => __( 'South Sudanese pound', 'ovabookpro' ),
						'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'ovabookpro' ),
						'SYP' => __( 'Syrian pound', 'ovabookpro' ),
						'SZL' => __( 'Swazi lilangeni', 'ovabookpro' ),
						'THB' => __( 'Thai baht', 'ovabookpro' ),
						'TJS' => __( 'Tajikistani somoni', 'ovabookpro' ),
						'TMT' => __( 'Turkmenistan manat', 'ovabookpro' ),
						'TND' => __( 'Tunisian dinar', 'ovabookpro' ),
						'TOP' => __( 'Tongan pa&#x2bb;anga', 'ovabookpro' ),
						'TRY' => __( 'Turkish lira', 'ovabookpro' ),
						'TTD' => __( 'Trinidad and Tobago dollar', 'ovabookpro' ),
						'TWD' => __( 'New Taiwan dollar', 'ovabookpro' ),
						'TZS' => __( 'Tanzanian shilling', 'ovabookpro' ),
						'UAH' => __( 'Ukrainian hryvnia', 'ovabookpro' ),
						'UGX' => __( 'Ugandan shilling', 'ovabookpro' ),
						'USD' => __( 'United States (US) dollar', 'ovabookpro' ),
						'UYU' => __( 'Uruguayan peso', 'ovabookpro' ),
						'UZS' => __( 'Uzbekistani som', 'ovabookpro' ),
						'VEF' => __( 'Venezuelan bol&iacute;var (2008–2018)', 'ovabookpro' ),
						'VES' => __( 'Venezuelan bol&iacute;var', 'ovabookpro' ),
						'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'ovabookpro' ),
						'VUV' => __( 'Vanuatu vatu', 'ovabookpro' ),
						'WST' => __( 'Samoan t&#x101;l&#x101;', 'ovabookpro' ),
						'XAF' => __( 'Central African CFA franc', 'ovabookpro' ),
						'XCD' => __( 'East Caribbean dollar', 'ovabookpro' ),
						'XOF' => __( 'West African CFA franc', 'ovabookpro' ),
						'XPF' => __( 'CFP franc', 'ovabookpro' ),
						'YER' => __( 'Yemeni rial', 'ovabookpro' ),
						'ZAR' => __( 'South African rand', 'ovabookpro' ),
						'ZMW' => __( 'Zambian kwacha', 'ovabookpro' ),
					)
				)
			);
		}
		return $currencies;
	}
}

// Get currency symbols
if ( ! function_exists( 'obp_get_currency_symbols' ) ) {
	function obp_get_currency_symbols() {
		$symbols = apply_filters(
			'obp_get_currency_symbols',
			array(
				'AED' => '&#x62f;.&#x625;',
				'AFN' => '&#x60b;',
				'ALL' => 'L',
				'AMD' => 'AMD',
				'ANG' => '&fnof;',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '&#36;',
				'AWG' => 'Afl.',
				'AZN' => '&#8380;',
				'BAM' => 'KM',
				'BBD' => '&#36;',
				'BDT' => '&#2547;&nbsp;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'Fr',
				'BMD' => '&#36;',
				'BND' => '&#36;',
				'BOB' => 'Bs.',
				'BRL' => '&#82;&#36;',
				'BSD' => '&#36;',
				'BTC' => '&#3647;',
				'BTN' => 'Nu.',
				'BWP' => 'P',
				'BYR' => 'Br',
				'BYN' => 'Br',
				'BZD' => '&#36;',
				'CAD' => '&#36;',
				'CDF' => 'Fr',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&yen;',
				'COP' => '&#36;',
				'CRC' => '&#x20a1;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => '&#36;',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fr',
				'DKK' => 'kr.',
				'DOP' => 'RD&#36;',
				'DZD' => '&#x62f;.&#x62c;',
				'EGP' => 'EGP',
				'ERN' => 'Nfk',
				'ETB' => 'Br',
				'EUR' => '&euro;',
				'FJD' => '&#36;',
				'FKP' => '&pound;',
				'GBP' => '&pound;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&pound;',
				'GMD' => 'D',
				'GNF' => 'Fr',
				'GTQ' => 'Q',
				'GYD' => '&#36;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x62f;.&#x639;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => 'kr.',
				'JEP' => '&pound;',
				'JMD' => '&#36;',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&yen;',
				'KES' => 'KSh',
				'KGS' => '&#x441;&#x43e;&#x43c;',
				'KHR' => '&#x17db;',
				'KMF' => 'Fr',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '&#36;',
				'KZT' => '&#8376;',
				'LAK' => '&#8365;',
				'LBP' => '&#x644;.&#x644;',
				'LKR' => '&#xdbb;&#xdd4;',
				'LRD' => '&#36;',
				'LSL' => 'L',
				'LYD' => '&#x62f;.&#x644;',
				'MAD' => '&#x62f;.&#x645;.',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'Ks',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRU' => 'UM',
				'MUR' => '&#x20a8;',
				'MVR' => '.&#x783;',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => 'MT',
				'NAD' => 'N&#36;',
				'NGN' => '&#8358;',
				'NIO' => 'C&#36;',
				'NOK' => '&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => '&#8370;',
				'QAR' => '&#x631;.&#x642;',
				'RMB' => '&yen;',
				'RON' => 'lei',
				'RSD' => '&#1088;&#1089;&#1076;',
				'RUB' => '&#8381;',
				'RWF' => 'Fr',
				'SAR' => '&#x631;.&#x633;',
				'SBD' => '&#36;',
				'SCR' => '&#x20a8;',
				'SDG' => '&#x62c;.&#x633;.',
				'SEK' => '&#107;&#114;',
				'SGD' => '&#36;',
				'SHP' => '&pound;',
				'SLL' => 'Le',
				'SOS' => 'Sh',
				'SRD' => '&#36;',
				'SSP' => '&pound;',
				'STN' => 'Db',
				'SYP' => '&#x644;.&#x633;',
				'SZL' => 'E',
				'THB' => '&#3647;',
				'TJS' => '&#x405;&#x41c;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T&#36;',
				'TRY' => '&#8378;',
				'TTD' => '&#36;',
				'TWD' => '&#78;&#84;&#36;',
				'TZS' => 'Sh',
				'UAH' => '&#8372;',
				'UGX' => 'UGX',
				'USD' => '&#36;',
				'UYU' => '&#36;',
				'UZS' => 'UZS',
				'VEF' => 'Bs F',
				'VES' => 'Bs.',
				'VND' => '&#8363;',
				'VUV' => 'Vt',
				'WST' => 'T',
				'XAF' => 'CFA',
				'XCD' => '&#36;',
				'XOF' => 'CFA',
				'XPF' => 'Fr',
				'YER' => '&#xfdfc;',
				'ZAR' => '&#82;',
				'ZMW' => 'ZK',
			)
		);

		return $symbols;
	}
}

// Get currency symbol
if ( ! function_exists( 'obp_get_currency_symbol' ) ) {
	function obp_get_currency_symbol( $currency = '' ) {
		if ( ! $currency ) {
			$currency = obp_get_currency();
		}

		$symbols = obp_get_currency_symbols();

		$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

		return apply_filters( 'obp_get_currency_symbol', $currency_symbol, $currency );
	}
}

if ( ! function_exists('obp_get_currency_position') ) {
	function obp_get_currency_position(){
		$currency_position = OBP()->settings->general->get('currency_position','left');
		return apply_filters( 'obp_get_currency_position', $currency_position );
	}
}

if ( ! function_exists('obp_get_decimal_separator') ) {
	function obp_get_decimal_separator(){
		$decimal_separator = OBP()->settings->general->get('decimal_separator', '.');
		return apply_filters( 'obp_get_decimal_separator', $decimal_separator );
	}
}

if ( ! function_exists('obp_get_thousand_separator') ) {
	function obp_get_thousand_separator(){
		$thousand_separator = OBP()->settings->general->get('thousand_separator', ',');
		return apply_filters( 'obp_get_thousand_separator', $thousand_separator );
	}
}

if ( ! function_exists('obp_get_price_num_decimals') ) {
	function obp_get_price_num_decimals(){
		$price_num_decimals = OBP()->settings->general->get('price_num_decimals', '2');
		return apply_filters( 'obp_get_price_num_decimals', $price_num_decimals );
	}
}

// Get price html
if ( ! function_exists( 'obp_get_price_html' ) ) { 
	function obp_get_price_html( $price, $price_type = 'fixed', $note = '' ) {
		$args = apply_filters(
			'obp_price_args',
			array(
				'currency'           => obp_get_currency_symbol(),
				'currency_position'  => obp_get_currency_position(),
				'decimal_separator'  => obp_get_decimal_separator(),
				'thousand_separator' => obp_get_thousand_separator(),
				'num_decimals'       => obp_get_price_num_decimals(),
			)
		);

		$original_price = $price;

		// Convert to float to avoid issues on PHP 8.
		$price = (float) $price;

		// Filter price : num_decimals, decimal_separator, thousand_separator 
		$price = apply_filters( 'obp_formatted_price', number_format( $price, absint( $args['num_decimals'] ), $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['num_decimals'], $args['decimal_separator'], $args['thousand_separator'], $original_price );

		// Currency position
		switch ($args['currency_position']) {

		  	case 'left_space':
		    	$formatted_price = '<span class="currencySymbol">' . $args['currency'] . '</span>' . ' ' . $price;
		    break;

		  	case 'right':
		  		$formatted_price = $price . '<span class="currencySymbol">' . $args['currency'] . '</span>';
		    break;

		    case 'right_space':
		    	$formatted_price = $price . ' ' . '<span class="currencySymbol">' . $args['currency'] . '</span>';
		    break;

		  	default:
		   	 	$formatted_price = '<span class="currencySymbol">' . $args['currency'] . '</span>' . $price;
		}

		$return  = '<span class="obp_amount"><bdi>' . $formatted_price . '</bdi></span>';

		// Check price type

		switch ( $price_type ) {
			case 'free':
				$return  = '<span class="obp_amount">' . esc_html__('Free','ovabookpro') . '</span>';
				break;

			case 'start_at':
				$return  = '<span class="obp_amount"><bdi>' . $formatted_price .'<span class="plus">+</span>'. '</bdi></span>';
				break;

			case 'varies':
				$return  = '<span class="obp_amount">' . esc_html__('Varies','ovabookpro') . '</span>';
				if ( $note ) {
					$return .= '<span class="dashicons dashicons-info" data-tippy-content="'.esc_attr( $note ).'"></span>';
				}
				break;

			case 'not_show':
				$return  = '<span class="obp_amount">-</span>';
				break;
			
			default:
				break;
		}

		// Filters the html of price
		return apply_filters( 'obp_get_price_html', $return, $price, $args, $original_price );
	}
}

// Get date formats
if ( ! function_exists( 'obp_get_date_formats' ) ) {
	function obp_get_date_formats() {
		return apply_filters( 'obp_get_date_formats', array(
			// translators: %s: date format.
			'Y-m-d' 	=> sprintf( _x( 'Y-m-d (%s)', 'date in format' , 'ovabookpro' ), date_i18n('Y-m-d') ),
			// translators: %s: date format.
			'Y/m/d' 	=> sprintf( _x( 'Y/m/d (%s)', 'date in format' ,'ovabookpro' ), date_i18n('Y/m/d') ),
			// translators: %s: date format.
			'd-m-Y' 	=> sprintf( _x( 'd-m-Y (%s)', 'date in format' ,'ovabookpro' ), date_i18n('d-m-Y') ),
			// translators: %s: date format.
			'm/d/Y' 	=> sprintf( _x( 'm/d/Y (%s)', 'date in format' ,'ovabookpro' ), date_i18n('m/d/Y') ),
			// translators: %s: date format.
			'F j, Y' 	=> sprintf( _x( 'F j, Y (%s)', 'date in format' ,'ovabookpro' ), date_i18n('F j, Y') )
		));
	}
}

// Get time formats
if ( ! function_exists( 'obp_get_time_formats' ) ) {
	function obp_get_time_formats() {
		return apply_filters( 'obp_get_time_formats', array(
			'H:i' 	=> 'H:i (15:00)',
			'h:i' 	=> 'h:i (03:00)',
			'h:i a' => 'h:i a (03:00 pm)',
			'h:i A' => 'h:i A (03:00 PM)',
			'g:i a' => 'g:i a (3:00 pm)',
			'g:i A' => 'g:i A (3:00 PM)',
		));
	}
}

if ( ! function_exists('obp_get_date_html') ) {
	function obp_get_date_html( $timestamp = 0 ){
		$date_format = OBP()->settings->general->get('date_format','Y-m-d');
		$time_format = OBP()->settings->general->get('time_format', 'H:i');
		$date_created = '';

		$date_created = date_i18n( $date_format.' '.$time_format, $timestamp );
		return $date_created;
	}
}

// Get weekend
if ( ! function_exists( 'obp_get_weekend' ) ) {
	function obp_get_weekend() {
		return apply_filters( 'obp_get_weekend', array(
			'monday' 	=> esc_html__( 'Monday', 'ovabookpro' ),
			'tuesday' 	=> esc_html__( 'Tuesday', 'ovabookpro' ),
			'wednesday' => esc_html__( 'Wednesday', 'ovabookpro' ),
			'thursday' 	=> esc_html__( 'Thursday', 'ovabookpro' ),
			'friday' 	=> esc_html__( 'Friday', 'ovabookpro' ),
			'saturday' 	=> esc_html__( 'Saturday', 'ovabookpro' ),
			'sunday' 	=> esc_html__( 'Sunday', 'ovabookpro' )
		));
	}
}


// Locate template
if ( ! function_exists( 'obp_locate_template' ) ) {
    function obp_locate_template( $template_name, $template_path = '', $default_path = '' ) {
        // Set variable to search in obp-templates folder of theme.
        if ( ! $template_path ) {
            $template_path = 'obp-templates/';
        }

        // Set default plugin templates path.
        if ( ! $default_path ) {
            $default_path = OBP_PLUGIN_PATH . 'templates/'; // Path to the template folder
        }

        // Search template file in theme folder.
        $template = locate_template( array( $template_path . $template_name ) );

        // Get plugins template file.
        if ( ! $template ) {
            $template = $default_path . $template_name;
        }

        return apply_filters( 'obp_locate_template', $template, $template_name, $template_path, $default_path );
    }
}

// Get template
if ( ! function_exists( 'obp_get_template' ) ) {
    function obp_get_template( $template_name, $args = array(), $tempate_path = '', $default_path = '' ) {
        if ( is_array( $args ) && isset( $args ) ) {
            extract( $args );
        }

        $template_file = obp_locate_template( $template_name, $tempate_path, $default_path );

        if ( ! file_exists( $template_file ) ) {
            _doing_it_wrong( __FUNCTION__, sprintf( "<code>%s</code> doesn't exist.", esc_html( $template_file ) ), '1.0.0' );
            return;
        }

        include $template_file;
    }
}


if ( ! function_exists('obp_member_account_page_id') ) {
	function obp_member_account_page_id(){
		$obp_member_account_page_id = OBP()->settings->general->get('member_account_page_id','');
		return apply_filters( 'obp_member_account_page_id', $obp_member_account_page_id );
	}
}

function obp_post_content_has_shortcode( $tag = '' ) {
	global $post;

	return is_singular() && is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $tag );
}

if ( ! function_exists('is_obp_has_shortcode_page') ) {
	function is_obp_has_shortcode_page( $shortcode = '' ){
		global $post;

	    if ( isset( $post->post_content ) ) {
	    	if( has_shortcode($post->post_content, $shortcode) ) {
	    		return true;
	    	}
	    }

	    return false;
	}
}

if ( ! function_exists('is_obp_member_account_page') ) {
	function is_obp_member_account_page(){
		$obp_member_account_page_id = obp_member_account_page_id();
		if ( ( $obp_member_account_page_id && is_page( $obp_member_account_page_id ) ) || obp_post_content_has_shortcode('obp_member_account') ) {
			return apply_filters( 'is_obp_member_account_page', true );
		} else {
			return apply_filters( 'is_obp_member_account_page', false );
		}
	}
}

if ( ! function_exists('obp_login_page_id') ) {
	function obp_login_page_id(){
		$obp_login_page_id = OBP()->settings->general->get('login_page_id','' );
		return apply_filters( 'obp_login_page_id', $obp_login_page_id );
	}
}

if ( ! function_exists('is_obp_login_page') ) {
	function is_obp_login_page(){
		$obp_login_page_id = obp_login_page_id();
		if ( $obp_login_page_id && is_page( $obp_login_page_id ) ) {
			return apply_filters( 'is_obp_login_page', true );
		} else {
			return apply_filters( 'is_obp_login_page', false );
		}
	}
}

if ( ! function_exists('obp_login_url') ) {
	function obp_login_url(){
		$obp_login_page_id = obp_login_page_id();
		if ( $obp_login_page_id ) {
			return apply_filters( 'obp_login_url', get_permalink( $obp_login_page_id ) );
		}
		return apply_filters( 'obp_login_url', wp_login_url() );
	}
}

if ( ! function_exists('obp_thank_page_id') ) {
	function obp_thank_page_id(){
		$obp_thank_page_id = OBP()->settings->general->get('thank_page_id');
		return apply_filters( 'obp_thank_page_id', $obp_thank_page_id );
	}
}

if ( ! function_exists('is_obp_thank_page') ) {
	function is_obp_thank_page(){
		$obp_thank_page_id = obp_thank_page_id();
		if ( $obp_thank_page_id && is_page( $obp_thank_page_id ) ) {
			return apply_filters( 'is_obp_thank_page', true );
		} else {
			return apply_filters( 'is_obp_thank_page', false );
		}
	}
}

if ( ! function_exists('obp_thank_you_url') ) {
	function obp_thank_you_url(){
		$obp_thank_page_id = obp_thank_page_id();
		if ( $obp_thank_page_id ) {
			$url = get_permalink( $obp_thank_page_id );
		} else {
			$url = get_home_url();
		}
		return apply_filters( 'obp_thank_you_url', $url );
	}
}

if ( ! function_exists('obp_register_user_page_id') ) {
	function obp_register_user_page_id(){
		$obp_register_user_page_id = OBP()->settings->general->get('register_user_page_id','');
		return apply_filters( 'obp_register_user_page_id', $obp_register_user_page_id );
	}
}

if ( ! function_exists('is_obp_register_user_page') ) {
	function is_obp_register_user_page(){
		$obp_register_user_page_id = obp_register_user_page_id();
		if ( $obp_register_user_page_id && is_page( $obp_register_user_page_id ) ) {
			return apply_filters( 'is_obp_register_user_page', true );
		} else {
			return apply_filters( 'is_obp_register_user_page', false );
		}
	}
}

if ( ! function_exists('obp_register_user_url') ) {
	function obp_register_user_url(){
		$page_id = obp_register_user_page_id();
		if ( $page_id ) {
			return apply_filters( 'obp_register_user_url', get_permalink( $page_id ) );
		} else {
			return apply_filters( 'obp_register_user_url', wp_registration_url() );
		}
	}
}

if ( ! function_exists('obp_forgot_password_page_id') ) {
	function obp_forgot_password_page_id(){
		$obp_forgot_password_page_id = OBP()->settings->general->get('forgot_password_page_id','');
		return apply_filters( 'obp_forgot_password_page_id', $obp_forgot_password_page_id );
	}
}

if ( ! function_exists('is_obp_forgot_password_page') ) {
	function is_obp_forgot_password_page(){
		$obp_forgot_password_page_id = obp_forgot_password_page_id();
		if ( $obp_forgot_password_page_id && is_page( $obp_forgot_password_page_id ) ) {
			return apply_filters( 'is_obp_forgot_password_page', true );
		} else {
			return apply_filters( 'is_obp_forgot_password_page', false );
		}
	}
}

if ( ! function_exists('obp_forgot_password_url') ) {
	function obp_forgot_password_url(){
		$page_id = obp_forgot_password_page_id();
		if ( $page_id ) {
			return apply_filters( 'obp_forgot_password_url', get_permalink( $page_id ) );
		} else {
			return apply_filters( 'obp_forgot_password_url', wp_lostpassword_url() );
		}
	}
}

if ( ! function_exists('obp_reset_password_page_id') ) {
	function obp_reset_password_page_id(){
		$obp_reset_password_page_id = OBP()->settings->general->get('reset_password_page_id','');
		return apply_filters( 'obp_reset_password_page_id', $obp_reset_password_page_id );
	}
}

if ( ! function_exists('is_obp_reset_password_page') ) {
	function is_obp_reset_password_page(){
		$obp_reset_password_page_id = obp_reset_password_page_id();
		if ( $obp_reset_password_page_id && is_page( $obp_reset_password_page_id ) ) {
			return apply_filters( 'is_obp_reset_password_page', true );
		} else {
			return apply_filters( 'is_obp_reset_password_page', false );
		}
	}
}

if ( ! function_exists('obp_reset_password_url') ) {
	function obp_reset_password_url(){
		$obp_reset_password_page_id = obp_reset_password_page_id();
		return apply_filters( 'obp_reset_password_url', get_permalink( $obp_reset_password_page_id ) );
	}
}

if ( ! function_exists('obp_member_account_url') ) {
	function obp_member_account_url(){
		$obp_member_account_page_id = obp_member_account_page_id();
		if ( $obp_member_account_page_id ) {
			$url = get_permalink( $obp_member_account_page_id );
		} else {
			$url = get_home_url();
		}
		return apply_filters( 'obp_member_account_url', $url );
	}
}

if ( ! function_exists('obp_get_list_endpoint_title') ) {
	function obp_get_list_endpoint_title(){
		$data = array();
		$obp_data_endpoint = OBP()->endpoint->get_navigation_items();
		if ( ! empty( $obp_data_endpoint ) ) {
			foreach ( $obp_data_endpoint as $key => $value ) {
				if ( isset( $value['title'] ) && ! empty( $value['title'] ) ) {
					$data[$key] = $value['title'];
				}
			}
		}
		return apply_filters( 'obp_get_list_endpoint_title', $data );
	}
}

if ( ! function_exists('obp_json_decode_array') ) {
	function obp_json_decode_array( $array ) { 
		$new_arr = array();
		foreach ( $array as $key => $value ) { 
			if ( json_decode(wp_unslash( $value ), true) ) {
				$new_arr[$key] = json_decode(wp_unslash( $value ), true);
			} else {
				$new_arr[$key] = $value;
			}
		}
		return $new_arr;
	}
}

if ( ! function_exists('obp_get_filesize') ) {
	function obp_get_filesize( $bytes ) {
		$sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		if ($bytes == 0) return '0 Byte';
		$i = (int)(floor(log($bytes) / log(1024)));
		return round($bytes / pow(1024, $i), 2). ' '.$sizes[$i];
	}
}


if ( ! function_exists('obp_get_flatpickr_language') ) {
	function obp_get_flatpickr_language(){
		return apply_filters( 'flatpickr_language' ,'default' );
	}
}

if ( ! function_exists('obp_array_chunk_key') ) {
	function obp_array_chunk_key( $array = array(), $size = 1 ){
		if ( empty( $array ) || $size < 1 ) {
			return $array;
		}
		$array_chunk = array();
		$count_arr = count( $array );
		if ( $count_arr > 0 ) {
			$i = 1;
			$data = array();
			foreach ( $array as $key => $value ) {
				$data[$key] = $value;
				if ( $i % $size == 0 ) {
					$array_chunk[] = $data;
					$data = array();
				} elseif ( $count_arr == $i ) {
					$array_chunk[] = $data;
				}
				$i++;
			}
		}
		return $array_chunk;
	}
}


if ( ! function_exists("obp_get_vendor_id") ) {
	function obp_get_vendor_id() {
		$vendor_id = BookPro\User\OBP_User::get_vendor_id();
		return $vendor_id;
	}
}

if ( ! function_exists( "obp_get_woocommerce_order_status" ) ) {
	function obp_get_woocommerce_order_status(){
		$order_status = OBP()->settings->payment->get('woo_order_status', array( 'wc-completed' ));
		return $order_status;
	}
}

if ( ! function_exists("obp_get_order") ) {
	function obp_get_order( $order_id ){
		return new BookPro\Order\OBP_Order_Item( $order_id );
	}
}


if ( ! function_exists("obp_get_order_meta") ) {
	function obp_get_order_meta( $item ){
		return new BookPro\Order\OBP_Order_Meta_Item( $item );
	}
}

if ( ! function_exists("obp_get_service") ) {
	function obp_get_service( $service_id ){
		return new BookPro\Service\OBP_Service_Item( $service_id );
	}
}

if ( ! function_exists("obp_get_plan") ) {
	function obp_get_plan( $plan_id ){
		return new BookPro\Plan\OBP_Plan_Item( $plan_id );
	}
}

if ( ! function_exists("obp_get_business") ) {
	function obp_get_business( $business_id ){
		return new BookPro\Business\OBP_Business_Item( $business_id );
	}
}

if ( ! function_exists("obp_get_payout_method") ) {
	function obp_get_payout_method( $payout_method_id ){
		return new BookPro\Payout\OBP_Payout_Method_Item( $payout_method_id );
	}
}

if ( ! function_exists("obp_get_user") ) {
	function obp_get_user( $user_id ){
		return new BookPro\User\OBP_User_Item( $user_id );
	}
}

if ( ! function_exists("obp_get_payout_field") ) {
	function obp_get_payout_field( $item ){
		return new BookPro\Payout\OBP_Payout_Field_Item( $item );
	}
}

if ( ! function_exists("obp_get_day_off") ) {
	function obp_get_day_off( $row ){
		return new BookPro\StaffDayOff\OBP_Day_Off_Item( $row );
	}
}

if ( ! function_exists("obp_get_order_balance") ) {
	function obp_get_order_balance( $row ){
		return new BookPro\Order\OBP_Order_Balance_Item( $row );
	}
}

if ( ! function_exists("obp_get_payout") ) {
	function obp_get_payout( $payout_id ){
		return new BookPro\Payout\OBP_Payout_Item( $payout_id );
	}
}

if ( ! function_exists("obp_get_date_format") ) {
	function obp_get_date_format(){
		$date_format = OBP()->settings->general->get('date_format','Y-m-d');
		return apply_filters( 'obp_get_date_format', $date_format );
	}
}

if ( ! function_exists("obp_get_time_format") ) {
	function obp_get_time_format(){
		$time_format = OBP()->settings->general->get('time_format','H:i');
		return apply_filters( 'obp_get_time_format', $time_format );
	}
}

if ( ! function_exists('obp_get_calendar_language') ) {
	function obp_get_calendar_language(){
		$calendar_language = OBP()->settings->general->get('calendar_language','en-gb');
		return apply_filters( 'obp_get_calendar_language', $calendar_language );
	}
}

if ( ! function_exists('obp_get_first_day') ) {
	function obp_get_first_day(){
		$first_day = OBP()->settings->general->get('first_day', 'monday');
		return apply_filters( 'obp_get_first_day', $first_day );
	}
}

if ( ! function_exists("obp_search_array_in_array") ) {
	function obp_search_array_in_array( $search , $array ){
		foreach ( $array as $key => $value) {
			if ( in_array( $search, $value ) ) {
				return $key;
			}
		}
		return 0;
	}
}

if ( ! function_exists("obp_is_vendor") ) {
	function obp_is_vendor(){
		return BookPro\OBP_Permission::is_vendor();
	}
}

if ( ! function_exists("obp_is_staff") ) {
	function obp_is_staff(){
		return BookPro\OBP_Permission::is_staff();
	}
}

if ( ! function_exists("obp_calendar_Hi_to_seconds") ) {
	function obp_calendar_Hi_to_seconds( $Hi_time ){
		return BookPro\OBP_Calendar::Hi_to_seconds( $Hi_time );
	}
}

if ( ! function_exists("obp_timestamp_to_hour_minute") ) {
	function obp_timestamp_to_hour_minute( $timestamp ){
		$hours = floor($timestamp / (60*60) );
		$minutes = floor( ($timestamp % (60*60)) / 60 );

		$total_time = array();
		if ( $hours ) {
			// translators: %s: number of hours.
			$total_time[] = sprintf( esc_html__( '%sh', 'ovabookpro' ), number_format_i18n( $hours ) );
		}
		if ( $minutes ) {
			// translators: %s: number of minutes.
			$total_time[] = sprintf( esc_html__( '%smin', 'ovabookpro' ), number_format_i18n( $minutes ) );
		}
		$total_time_str = '';
		if ( ! empty( $total_time ) ) {
			$total_time_str = implode(" ", $total_time);
		}
		return $total_time_str;
	}
}
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
function obp_truncate_tables(){
	global $wpdb;
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_meta");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_balance");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_holding");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_order_meta_queue");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_payout_method_info");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_day_off");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}obp_sessions");
	$wpdb->flush();
	die("ok");
}

function obp_drop_tables(){
	global $wpdb;
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_order_meta");
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_order_balance");
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_order_holding");
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_order_meta_queue");
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_payout_method_info");
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_day_off");
	$wpdb->query("DROP TABLE {$wpdb->prefix}obp_sessions");
	$wpdb->flush();
	die("ok");
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
/**
 * Switch OvaBookPro language to original.
 *
 * @since 3.1.0
 */
function obp_restore_locale() {
	global $wp_locale_switcher;

	if ( function_exists( 'restore_previous_locale' ) && isset( $wp_locale_switcher ) ) {
		restore_previous_locale();

		// Remove filter.
		remove_filter( 'plugin_locale', 'get_locale' );

		// Init WC locale.
		OBP()->load_plugin_textdomain();
	}
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @param  string  $name   Name of the cookie being set.
 * @param  string  $value  Value of the cookie.
 * @param  integer $expire Expiry of the cookie.
 * @param  bool    $secure Whether the cookie should be served only over https.
 * @param  bool    $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript. @since 3.6.0.
 */
function obp_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
	/**
	 * Controls whether the cookie should be set via obp_setcookie().
	 *
	 * @since 6.3.0
	 *
	 * @param bool    $set_cookie_enabled If obp_setcookie() should set the cookie.
	 * @param string  $name               Cookie name.
	 * @param string  $value              Cookie value.
	 * @param integer $expire             When the cookie should expire.
	 * @param bool    $secure             If the cookie should only be served over HTTPS.
	 */
	if ( ! apply_filters( 'obp_set_cookie_enabled', true, $name, $value, $expire, $secure ) ) {
		return;
	}

	if ( ! headers_sent() ) {
		/**
		 * Controls the options to be specified when setting the cookie.
		 *
		 * @see   https://www.php.net/manual/en/function.setcookie.php
		 * @since 6.7.0
		 *
		 * @param array  $cookie_options Cookie options.
		 * @param string $name           Cookie name.
		 * @param string $value          Cookie value.
		 */
		$options = apply_filters(
			'obp_set_cookie_options',
			array(
				'expires'  => $expire,
				'secure'   => $secure,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				/**
				 * Controls whether the cookie should only be accessible via the HTTP protocol, or if it should also be
				 * accessible to Javascript.
				 *
				 * @see   https://www.php.net/manual/en/function.setcookie.php
				 * @since 3.3.0
				 *
				 * @param bool   $httponly If the cookie should only be accessible via the HTTP protocol.
				 * @param string $name     Cookie name.
				 * @param string $value    Cookie value.
				 * @param int    $expire   When the cookie should expire.
				 * @param bool   $secure   If the cookie should only be served over HTTPS.
				 */
				'httponly' => apply_filters( 'obp_cookie_httponly', $httponly, $name, $value, $expire, $secure ),
			),
			$name,
			$value
		);

		setcookie( $name, $value, $options );
	}
}

function obp_site_is_https() {
	return false !== strstr( get_option( 'home' ), 'https:' );
}


/**
 * Cleans up session data - cron callback.
 *
 */
function obp_cleanup_session_data() {
	$session       = new BookPro\OBP_Session_Handler();
	if ( is_callable( array( $session, 'cleanup_sessions' ) ) ) {
		$session->cleanup_sessions();
	}
}



function obp_get_coupon( $id = null ){
	return new BookPro\Coupon\OBP_Coupon_Item( $id );
}

function obp_convert_price( $price = '' ){
	$decimal_separator = OBP()->settings->general->get('decimal_separator', '.');
	$price = str_replace('.', $decimal_separator, $price );
	return $price;
}

function obp_format_price( $price = '' ){
	$decimal_separator = OBP()->settings->general->get('decimal_separator', '.');
	$price = str_replace($decimal_separator, '.', $price );
	return $price;
}

function obp_format_date_Ymd( $timestamp = '' ){
	if ( $timestamp ) {
		return gmdate( "Y-m-d", $timestamp );
	}
	return $timestamp;
}

function obp_clear_coupon_message(){
	OBP()->cart->session->set('coupon_message', null);
	OBP()->cart->session->save_data();
}

if ( ! function_exists('obp_clear_session') ) {
	function obp_clear_session(){
	    OBP()->cart->session->set('messages', null );
	    OBP()->cart->session->set('cart', [] );
	    OBP()->cart->session->set('coupon_code', null );
	    OBP()->cart->session->set('has_coupon', null );
	    OBP()->cart->session->set('coupon', [] );
	    OBP()->cart->session->set('order_id', null );
	    OBP()->cart->session->set('order_countdown', null );
	    OBP()->cart->session->set('coupon_message', null);
	    OBP()->cart->session->save_data();
	}
}


function obp_get_datepicker_languages(){
	$languages = array(
		'ar_dz' => esc_html__( 'AlgerianArabic', 'ovabookpro' ),
		'ar' => esc_html__( 'Arabic', 'ovabookpro' ),
	    'at' => esc_html__( 'Austria', 'ovabookpro' ),
	    'az' => esc_html__( 'Azerbaijan', 'ovabookpro' ),
	    'be' => esc_html__( 'Belarusian', 'ovabookpro' ),
	    'bg' => esc_html__( 'Bulgarian', 'ovabookpro' ),
	    'bn' => esc_html__( 'Bangla', 'ovabookpro' ),
	    'bs' => esc_html__( 'Bosnian', 'ovabookpro' ),
	    'ckb' => esc_html__( 'Kurdish', 'ovabookpro' ),
	    'cat' => esc_html__( 'Catalan', 'ovabookpro' ),
	    'cs' => esc_html__( 'Czech', 'ovabookpro' ),
	    'cy' => esc_html__( 'Welsh', 'ovabookpro' ),
	    'da' => esc_html__( 'Danish', 'ovabookpro' ),
	    'de' => esc_html__( 'German', 'ovabookpro' ),
	    'default' => esc_html__( 'English', 'ovabookpro' ),
	    'eo' => esc_html__( 'Esperanto', 'ovabookpro' ),
	    'es' => esc_html__( 'Spanish', 'ovabookpro' ),
	    'et' => esc_html__( 'Estonian', 'ovabookpro' ),
	    'fa' => esc_html__( 'Persian', 'ovabookpro' ),
	    'fi' => esc_html__( 'Finnish', 'ovabookpro' ),
	    'fo' => esc_html__( 'Faroese', 'ovabookpro' ),
	    'fr' => esc_html__( 'French', 'ovabookpro' ),
	    'gr' => esc_html__( 'Greek', 'ovabookpro' ),
	    'ga' => esc_html__( 'Irish', 'ovabookpro' ),
	    'he' => esc_html__( 'Hebrew', 'ovabookpro' ),
	    'hi' => esc_html__( 'Hindi', 'ovabookpro' ),
	    'hr' => esc_html__( 'Croatian', 'ovabookpro' ),
	    'hu' => esc_html__( 'Hungarian', 'ovabookpro' ),
	    'hy' => esc_html__( 'Armenian', 'ovabookpro' ),
	    'id' => esc_html__( 'Indonesian', 'ovabookpro' ),
	    'is' => esc_html__( 'Icelandic', 'ovabookpro' ),
	    'it' => esc_html__( 'Italian', 'ovabookpro' ),
	    'ja' => esc_html__( 'Japanese', 'ovabookpro' ),
	    'ka' => esc_html__( 'Georgian', 'ovabookpro' ),
	    'ko' => esc_html__( 'Korean', 'ovabookpro' ),
	    'km' => esc_html__( 'Khmer', 'ovabookpro' ),
	    'kz' => esc_html__( 'Kazakh', 'ovabookpro' ),
	    'lt' => esc_html__( 'Lithuanian', 'ovabookpro' ),
	    'lv' => esc_html__( 'Latvian', 'ovabookpro' ),
	    'mk' => esc_html__( 'Macedonian', 'ovabookpro' ),
	    'mn' => esc_html__( 'Mongolian', 'ovabookpro' ),
	    'ms' => esc_html__( 'Malaysian', 'ovabookpro' ),
	    'my' => esc_html__( 'Burmese', 'ovabookpro' ),
	    'nl' => esc_html__( 'Dutch', 'ovabookpro' ),
	    'nn' => esc_html__( 'NorwegianNynorsk', 'ovabookpro' ),
	    'no' => esc_html__( 'Norwegian', 'ovabookpro' ),
	    'pa' => esc_html__( 'Punjabi', 'ovabookpro' ),
	    'pl' => esc_html__( 'Polish', 'ovabookpro' ),
	    'pt' => esc_html__( 'Portuguese', 'ovabookpro' ),
	    'ro' => esc_html__( 'Romanian', 'ovabookpro' ),
	    'ru' => esc_html__( 'Russian', 'ovabookpro' ),
	    'si' => esc_html__( 'Sinhala', 'ovabookpro' ),
	    'sk' => esc_html__( 'Slovak', 'ovabookpro' ),
	    'sl' => esc_html__( 'Slovenian', 'ovabookpro' ),
	    'sq' => esc_html__( 'Albanian', 'ovabookpro' ),
	    'sr' => esc_html__( 'Serbian', 'ovabookpro' ),
	    'sr_cyr' => esc_html__( 'SerbianCyrillic', 'ovabookpro' ),
	    'sv' => esc_html__( 'Swedish', 'ovabookpro' ),
	    'th' => esc_html__( 'Thai', 'ovabookpro' ),
	    'tr' => esc_html__( 'Turkish', 'ovabookpro' ),
	    'uk' => esc_html__( 'Ukrainian', 'ovabookpro' ),
	    'vn' => esc_html__( 'Vietnamese', 'ovabookpro' ),
	    'zh' => esc_html__( 'Mandarin', 'ovabookpro' ),
	    'zh_tw' => esc_html__( 'MandarinTraditional', 'ovabookpro' ),
	    'uz' => esc_html__( 'Uzbek', 'ovabookpro' ),
	    'uz_latn' => esc_html__( 'UzbekLatin', 'ovabookpro' ),
	);

	return $languages;
}

if ( ! function_exists('obp_get_package') ) {
	function obp_get_package( $package_id = null ){
		return new BookPro\Package\OBP_Package_Item( $package_id );
	}
}

if ( ! function_exists('obp_calculate_inc_tax') ) {
	function obp_calculate_inc_tax( $price = 0, $rates = array() ){
		$enabled = OBP()->settings->tax->get('prices_include_tax', 'no');
		$tax_fee = array_sum( BookPro\Tax\OBP_Tax::calc_tax( $price, $rates, $enabled ) );
		$new_price = $price;
		if ( $enabled == 'yes' ) {
			$new_price = $new_price - $tax_fee;
	    }
		return $new_price;
	}
}

if ( ! function_exists('obp_show_price_item') ) {
	function obp_show_price_item( $price = 0, $rates = array() ){

		$price_tax 	= OBP()->settings->tax->get('prices_include_tax', 'no');
		$display 	= OBP()->settings->tax->get('tax_display_item', 'incl');
		$tax_fee 	= array_sum( BookPro\Tax\OBP_Tax::calc_tax( $price, $rates, $price_tax ) );

		if ( $price_tax == 'yes' ) {
				
			if ( $display == 'excl' ) {
				$price = $price - $tax_fee;
			}

		} else {
			if ( $display == 'incl' ) {
				$price = $price + $tax_fee;
			}
		}

		return $price;
	}
}

if ( ! function_exists('obp_show_price_cart') ) {
	function obp_show_price_cart( $price = 0, $rates = array() ){
		$price_tax 	= OBP()->settings->tax->get('prices_include_tax', 'no');
		$display 	= OBP()->settings->tax->get('tax_display_cart', 'incl');
		$tax_fee 	= array_sum( BookPro\Tax\OBP_Tax::calc_tax( $price, $rates, $price_tax ) );

		if ( $price_tax == 'yes' ) {
			
			if ( $display == 'excl' ) {
				$price = $price - $tax_fee;
			}

		} else {

			if ( $display == 'incl' ) {
				$price = $price + $tax_fee;
			}
		}

		return $price;
	}
}

if ( ! function_exists('obp_show_booking_total') ) {
	function obp_show_booking_total( $total = 0, $has_varies = true ){

		if ( $total == 0 && $has_varies &&
			apply_filters( 'obp_show_booking_total_varies', true ) == true ) {
			return esc_html__( 'Varies', 'ovabookpro' );
		}
		return obp_get_price_html( $total );
	}
}

if ( ! function_exists('obp_get_tax') ) {
	function obp_get_tax( $id = null ){
		return new BookPro\Tax\OBP_Tax_Item( $id );
	}
}

if ( ! function_exists('obp_get_countries') ) {
	function obp_get_countries(){
		return array(
			'AF' => __( 'Afghanistan', 'ovabookpro' ),
			'AX' => __( 'Åland Islands', 'ovabookpro' ),
			'AL' => __( 'Albania', 'ovabookpro' ),
			'DZ' => __( 'Algeria', 'ovabookpro' ),
			'AS' => __( 'American Samoa', 'ovabookpro' ),
			'AD' => __( 'Andorra', 'ovabookpro' ),
			'AO' => __( 'Angola', 'ovabookpro' ),
			'AI' => __( 'Anguilla', 'ovabookpro' ),
			'AQ' => __( 'Antarctica', 'ovabookpro' ),
			'AG' => __( 'Antigua and Barbuda', 'ovabookpro' ),
			'AR' => __( 'Argentina', 'ovabookpro' ),
			'AM' => __( 'Armenia', 'ovabookpro' ),
			'AW' => __( 'Aruba', 'ovabookpro' ),
			'AU' => __( 'Australia', 'ovabookpro' ),
			'AT' => __( 'Austria', 'ovabookpro' ),
			'AZ' => __( 'Azerbaijan', 'ovabookpro' ),
			'BS' => __( 'Bahamas', 'ovabookpro' ),
			'BH' => __( 'Bahrain', 'ovabookpro' ),
			'BD' => __( 'Bangladesh', 'ovabookpro' ),
			'BB' => __( 'Barbados', 'ovabookpro' ),
			'BY' => __( 'Belarus', 'ovabookpro' ),
			'BE' => __( 'Belgium', 'ovabookpro' ),
			'PW' => __( 'Belau', 'ovabookpro' ),
			'BZ' => __( 'Belize', 'ovabookpro' ),
			'BJ' => __( 'Benin', 'ovabookpro' ),
			'BM' => __( 'Bermuda', 'ovabookpro' ),
			'BT' => __( 'Bhutan', 'ovabookpro' ),
			'BO' => __( 'Bolivia', 'ovabookpro' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'ovabookpro' ),
			'BA' => __( 'Bosnia and Herzegovina', 'ovabookpro' ),
			'BW' => __( 'Botswana', 'ovabookpro' ),
			'BV' => __( 'Bouvet Island', 'ovabookpro' ),
			'BR' => __( 'Brazil', 'ovabookpro' ),
			'IO' => __( 'British Indian Ocean Territory', 'ovabookpro' ),
			'BN' => __( 'Brunei', 'ovabookpro' ),
			'BG' => __( 'Bulgaria', 'ovabookpro' ),
			'BF' => __( 'Burkina Faso', 'ovabookpro' ),
			'BI' => __( 'Burundi', 'ovabookpro' ),
			'KH' => __( 'Cambodia', 'ovabookpro' ),
			'CM' => __( 'Cameroon', 'ovabookpro' ),
			'CA' => __( 'Canada', 'ovabookpro' ),
			'CV' => __( 'Cape Verde', 'ovabookpro' ),
			'KY' => __( 'Cayman Islands', 'ovabookpro' ),
			'CF' => __( 'Central African Republic', 'ovabookpro' ),
			'TD' => __( 'Chad', 'ovabookpro' ),
			'CL' => __( 'Chile', 'ovabookpro' ),
			'CN' => __( 'China', 'ovabookpro' ),
			'CX' => __( 'Christmas Island', 'ovabookpro' ),
			'CC' => __( 'Cocos (Keeling) Islands', 'ovabookpro' ),
			'CO' => __( 'Colombia', 'ovabookpro' ),
			'KM' => __( 'Comoros', 'ovabookpro' ),
			'CG' => __( 'Congo (Brazzaville)', 'ovabookpro' ),
			'CD' => __( 'Congo (Kinshasa)', 'ovabookpro' ),
			'CK' => __( 'Cook Islands', 'ovabookpro' ),
			'CR' => __( 'Costa Rica', 'ovabookpro' ),
			'HR' => __( 'Croatia', 'ovabookpro' ),
			'CU' => __( 'Cuba', 'ovabookpro' ),
			'CW' => __( 'Cura&ccedil;ao', 'ovabookpro' ),
			'CY' => __( 'Cyprus', 'ovabookpro' ),
			'CZ' => __( 'Czech Republic', 'ovabookpro' ),
			'DK' => __( 'Denmark', 'ovabookpro' ),
			'DJ' => __( 'Djibouti', 'ovabookpro' ),
			'DM' => __( 'Dominica', 'ovabookpro' ),
			'DO' => __( 'Dominican Republic', 'ovabookpro' ),
			'EC' => __( 'Ecuador', 'ovabookpro' ),
			'EG' => __( 'Egypt', 'ovabookpro' ),
			'SV' => __( 'El Salvador', 'ovabookpro' ),
			'GQ' => __( 'Equatorial Guinea', 'ovabookpro' ),
			'ER' => __( 'Eritrea', 'ovabookpro' ),
			'EE' => __( 'Estonia', 'ovabookpro' ),
			'ET' => __( 'Ethiopia', 'ovabookpro' ),
			'FK' => __( 'Falkland Islands', 'ovabookpro' ),
			'FO' => __( 'Faroe Islands', 'ovabookpro' ),
			'FJ' => __( 'Fiji', 'ovabookpro' ),
			'FI' => __( 'Finland', 'ovabookpro' ),
			'FR' => __( 'France', 'ovabookpro' ),
			'GF' => __( 'French Guiana', 'ovabookpro' ),
			'PF' => __( 'French Polynesia', 'ovabookpro' ),
			'TF' => __( 'French Southern Territories', 'ovabookpro' ),
			'GA' => __( 'Gabon', 'ovabookpro' ),
			'GM' => __( 'Gambia', 'ovabookpro' ),
			'GE' => __( 'Georgia', 'ovabookpro' ),
			'DE' => __( 'Germany', 'ovabookpro' ),
			'GH' => __( 'Ghana', 'ovabookpro' ),
			'GI' => __( 'Gibraltar', 'ovabookpro' ),
			'GR' => __( 'Greece', 'ovabookpro' ),
			'GL' => __( 'Greenland', 'ovabookpro' ),
			'GD' => __( 'Grenada', 'ovabookpro' ),
			'GP' => __( 'Guadeloupe', 'ovabookpro' ),
			'GU' => __( 'Guam', 'ovabookpro' ),
			'GT' => __( 'Guatemala', 'ovabookpro' ),
			'GG' => __( 'Guernsey', 'ovabookpro' ),
			'GN' => __( 'Guinea', 'ovabookpro' ),
			'GW' => __( 'Guinea-Bissau', 'ovabookpro' ),
			'GY' => __( 'Guyana', 'ovabookpro' ),
			'HT' => __( 'Haiti', 'ovabookpro' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'ovabookpro' ),
			'HN' => __( 'Honduras', 'ovabookpro' ),
			'HK' => __( 'Hong Kong', 'ovabookpro' ),
			'HU' => __( 'Hungary', 'ovabookpro' ),
			'IS' => __( 'Iceland', 'ovabookpro' ),
			'IN' => __( 'India', 'ovabookpro' ),
			'ID' => __( 'Indonesia', 'ovabookpro' ),
			'IR' => __( 'Iran', 'ovabookpro' ),
			'IQ' => __( 'Iraq', 'ovabookpro' ),
			'IE' => __( 'Ireland', 'ovabookpro' ),
			'IM' => __( 'Isle of Man', 'ovabookpro' ),
			'IL' => __( 'Israel', 'ovabookpro' ),
			'IT' => __( 'Italy', 'ovabookpro' ),
			'CI' => __( 'Ivory Coast', 'ovabookpro' ),
			'JM' => __( 'Jamaica', 'ovabookpro' ),
			'JP' => __( 'Japan', 'ovabookpro' ),
			'JE' => __( 'Jersey', 'ovabookpro' ),
			'JO' => __( 'Jordan', 'ovabookpro' ),
			'KZ' => __( 'Kazakhstan', 'ovabookpro' ),
			'KE' => __( 'Kenya', 'ovabookpro' ),
			'KI' => __( 'Kiribati', 'ovabookpro' ),
			'KW' => __( 'Kuwait', 'ovabookpro' ),
			'KG' => __( 'Kyrgyzstan', 'ovabookpro' ),
			'LA' => __( 'Laos', 'ovabookpro' ),
			'LV' => __( 'Latvia', 'ovabookpro' ),
			'LB' => __( 'Lebanon', 'ovabookpro' ),
			'LS' => __( 'Lesotho', 'ovabookpro' ),
			'LR' => __( 'Liberia', 'ovabookpro' ),
			'LY' => __( 'Libya', 'ovabookpro' ),
			'LI' => __( 'Liechtenstein', 'ovabookpro' ),
			'LT' => __( 'Lithuania', 'ovabookpro' ),
			'LU' => __( 'Luxembourg', 'ovabookpro' ),
			'MO' => __( 'Macao', 'ovabookpro' ),
			'MK' => __( 'North Macedonia', 'ovabookpro' ),
			'MG' => __( 'Madagascar', 'ovabookpro' ),
			'MW' => __( 'Malawi', 'ovabookpro' ),
			'MY' => __( 'Malaysia', 'ovabookpro' ),
			'MV' => __( 'Maldives', 'ovabookpro' ),
			'ML' => __( 'Mali', 'ovabookpro' ),
			'MT' => __( 'Malta', 'ovabookpro' ),
			'MH' => __( 'Marshall Islands', 'ovabookpro' ),
			'MQ' => __( 'Martinique', 'ovabookpro' ),
			'MR' => __( 'Mauritania', 'ovabookpro' ),
			'MU' => __( 'Mauritius', 'ovabookpro' ),
			'YT' => __( 'Mayotte', 'ovabookpro' ),
			'MX' => __( 'Mexico', 'ovabookpro' ),
			'FM' => __( 'Micronesia', 'ovabookpro' ),
			'MD' => __( 'Moldova', 'ovabookpro' ),
			'MC' => __( 'Monaco', 'ovabookpro' ),
			'MN' => __( 'Mongolia', 'ovabookpro' ),
			'ME' => __( 'Montenegro', 'ovabookpro' ),
			'MS' => __( 'Montserrat', 'ovabookpro' ),
			'MA' => __( 'Morocco', 'ovabookpro' ),
			'MZ' => __( 'Mozambique', 'ovabookpro' ),
			'MM' => __( 'Myanmar', 'ovabookpro' ),
			'NA' => __( 'Namibia', 'ovabookpro' ),
			'NR' => __( 'Nauru', 'ovabookpro' ),
			'NP' => __( 'Nepal', 'ovabookpro' ),
			'NL' => __( 'Netherlands', 'ovabookpro' ),
			'NC' => __( 'New Caledonia', 'ovabookpro' ),
			'NZ' => __( 'New Zealand', 'ovabookpro' ),
			'NI' => __( 'Nicaragua', 'ovabookpro' ),
			'NE' => __( 'Niger', 'ovabookpro' ),
			'NG' => __( 'Nigeria', 'ovabookpro' ),
			'NU' => __( 'Niue', 'ovabookpro' ),
			'NF' => __( 'Norfolk Island', 'ovabookpro' ),
			'MP' => __( 'Northern Mariana Islands', 'ovabookpro' ),
			'KP' => __( 'North Korea', 'ovabookpro' ),
			'NO' => __( 'Norway', 'ovabookpro' ),
			'OM' => __( 'Oman', 'ovabookpro' ),
			'PK' => __( 'Pakistan', 'ovabookpro' ),
			'PS' => __( 'Palestinian Territory', 'ovabookpro' ),
			'PA' => __( 'Panama', 'ovabookpro' ),
			'PG' => __( 'Papua New Guinea', 'ovabookpro' ),
			'PY' => __( 'Paraguay', 'ovabookpro' ),
			'PE' => __( 'Peru', 'ovabookpro' ),
			'PH' => __( 'Philippines', 'ovabookpro' ),
			'PN' => __( 'Pitcairn', 'ovabookpro' ),
			'PL' => __( 'Poland', 'ovabookpro' ),
			'PT' => __( 'Portugal', 'ovabookpro' ),
			'PR' => __( 'Puerto Rico', 'ovabookpro' ),
			'QA' => __( 'Qatar', 'ovabookpro' ),
			'RE' => __( 'Reunion', 'ovabookpro' ),
			'RO' => __( 'Romania', 'ovabookpro' ),
			'RU' => __( 'Russia', 'ovabookpro' ),
			'RW' => __( 'Rwanda', 'ovabookpro' ),
			'BL' => __( 'Saint Barth&eacute;lemy', 'ovabookpro' ),
			'SH' => __( 'Saint Helena', 'ovabookpro' ),
			'KN' => __( 'Saint Kitts and Nevis', 'ovabookpro' ),
			'LC' => __( 'Saint Lucia', 'ovabookpro' ),
			'MF' => __( 'Saint Martin (French part)', 'ovabookpro' ),
			'SX' => __( 'Saint Martin (Dutch part)', 'ovabookpro' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'ovabookpro' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'ovabookpro' ),
			'SM' => __( 'San Marino', 'ovabookpro' ),
			'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'ovabookpro' ),
			'SA' => __( 'Saudi Arabia', 'ovabookpro' ),
			'SN' => __( 'Senegal', 'ovabookpro' ),
			'RS' => __( 'Serbia', 'ovabookpro' ),
			'SC' => __( 'Seychelles', 'ovabookpro' ),
			'SL' => __( 'Sierra Leone', 'ovabookpro' ),
			'SG' => __( 'Singapore', 'ovabookpro' ),
			'SK' => __( 'Slovakia', 'ovabookpro' ),
			'SI' => __( 'Slovenia', 'ovabookpro' ),
			'SB' => __( 'Solomon Islands', 'ovabookpro' ),
			'SO' => __( 'Somalia', 'ovabookpro' ),
			'ZA' => __( 'South Africa', 'ovabookpro' ),
			'GS' => __( 'South Georgia/Sandwich Islands', 'ovabookpro' ),
			'KR' => __( 'South Korea', 'ovabookpro' ),
			'SS' => __( 'South Sudan', 'ovabookpro' ),
			'ES' => __( 'Spain', 'ovabookpro' ),
			'LK' => __( 'Sri Lanka', 'ovabookpro' ),
			'SD' => __( 'Sudan', 'ovabookpro' ),
			'SR' => __( 'Suriname', 'ovabookpro' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'ovabookpro' ),
			'SZ' => __( 'Eswatini', 'ovabookpro' ),
			'SE' => __( 'Sweden', 'ovabookpro' ),
			'CH' => __( 'Switzerland', 'ovabookpro' ),
			'SY' => __( 'Syria', 'ovabookpro' ),
			'TW' => __( 'Taiwan', 'ovabookpro' ),
			'TJ' => __( 'Tajikistan', 'ovabookpro' ),
			'TZ' => __( 'Tanzania', 'ovabookpro' ),
			'TH' => __( 'Thailand', 'ovabookpro' ),
			'TL' => __( 'Timor-Leste', 'ovabookpro' ),
			'TG' => __( 'Togo', 'ovabookpro' ),
			'TK' => __( 'Tokelau', 'ovabookpro' ),
			'TO' => __( 'Tonga', 'ovabookpro' ),
			'TT' => __( 'Trinidad and Tobago', 'ovabookpro' ),
			'TN' => __( 'Tunisia', 'ovabookpro' ),
			'TR' => __( 'Turkey', 'ovabookpro' ),
			'TM' => __( 'Turkmenistan', 'ovabookpro' ),
			'TC' => __( 'Turks and Caicos Islands', 'ovabookpro' ),
			'TV' => __( 'Tuvalu', 'ovabookpro' ),
			'UG' => __( 'Uganda', 'ovabookpro' ),
			'UA' => __( 'Ukraine', 'ovabookpro' ),
			'AE' => __( 'United Arab Emirates', 'ovabookpro' ),
			'GB' => __( 'United Kingdom (UK)', 'ovabookpro' ),
			'US' => __( 'United States (US)', 'ovabookpro' ),
			'UM' => __( 'United States (US) Minor Outlying Islands', 'ovabookpro' ),
			'UY' => __( 'Uruguay', 'ovabookpro' ),
			'UZ' => __( 'Uzbekistan', 'ovabookpro' ),
			'VU' => __( 'Vanuatu', 'ovabookpro' ),
			'VA' => __( 'Vatican', 'ovabookpro' ),
			'VE' => __( 'Venezuela', 'ovabookpro' ),
			'VN' => __( 'Vietnam', 'ovabookpro' ),
			'VG' => __( 'Virgin Islands (British)', 'ovabookpro' ),
			'VI' => __( 'Virgin Islands (US)', 'ovabookpro' ),
			'WF' => __( 'Wallis and Futuna', 'ovabookpro' ),
			'EH' => __( 'Western Sahara', 'ovabookpro' ),
			'WS' => __( 'Samoa', 'ovabookpro' ),
			'YE' => __( 'Yemen', 'ovabookpro' ),
			'ZM' => __( 'Zambia', 'ovabookpro' ),
			'ZW' => __( 'Zimbabwe', 'ovabookpro' ),
		);
	}
}

if ( ! function_exists('obp_get_states') ) {
	function obp_get_states(){
		return array(
			'AF' => array(),
			'AL' => array( // Albanian states.
				'AL-01' => __( 'Berat', 'ovabookpro' ),
				'AL-09' => __( 'Dibër', 'ovabookpro' ),
				'AL-02' => __( 'Durrës', 'ovabookpro' ),
				'AL-03' => __( 'Elbasan', 'ovabookpro' ),
				'AL-04' => __( 'Fier', 'ovabookpro' ),
				'AL-05' => __( 'Gjirokastër', 'ovabookpro' ),
				'AL-06' => __( 'Korçë', 'ovabookpro' ),
				'AL-07' => __( 'Kukës', 'ovabookpro' ),
				'AL-08' => __( 'Lezhë', 'ovabookpro' ),
				'AL-10' => __( 'Shkodër', 'ovabookpro' ),
				'AL-11' => __( 'Tirana', 'ovabookpro' ),
				'AL-12' => __( 'Vlorë', 'ovabookpro' ),
			),
			'AO' => array( // Angolan states.
				'BGO' => __( 'Bengo', 'ovabookpro' ),
				'BLU' => __( 'Benguela', 'ovabookpro' ),
				'BIE' => __( 'Bié', 'ovabookpro' ),
				'CAB' => __( 'Cabinda', 'ovabookpro' ),
				'CNN' => __( 'Cunene', 'ovabookpro' ),
				'HUA' => __( 'Huambo', 'ovabookpro' ),
				'HUI' => __( 'Huíla', 'ovabookpro' ),
				'CCU' => __( 'Kuando Kubango', 'ovabookpro' ),
				'CNO' => __( 'Kwanza-Norte', 'ovabookpro' ),
				'CUS' => __( 'Kwanza-Sul', 'ovabookpro' ),
				'LUA' => __( 'Luanda', 'ovabookpro' ),
				'LNO' => __( 'Lunda-Norte', 'ovabookpro' ),
				'LSU' => __( 'Lunda-Sul', 'ovabookpro' ),
				'MAL' => __( 'Malanje', 'ovabookpro' ),
				'MOX' => __( 'Moxico', 'ovabookpro' ),
				'NAM' => __( 'Namibe', 'ovabookpro' ),
				'UIG' => __( 'Uíge', 'ovabookpro' ),
				'ZAI' => __( 'Zaire', 'ovabookpro' ),
			),
			'AR' => array( // Argentinian provinces.
				'C' => __( 'Ciudad Autónoma de Buenos Aires', 'ovabookpro' ),
				'B' => __( 'Buenos Aires', 'ovabookpro' ),
				'K' => __( 'Catamarca', 'ovabookpro' ),
				'H' => __( 'Chaco', 'ovabookpro' ),
				'U' => __( 'Chubut', 'ovabookpro' ),
				'X' => __( 'Córdoba', 'ovabookpro' ),
				'W' => __( 'Corrientes', 'ovabookpro' ),
				'E' => __( 'Entre Ríos', 'ovabookpro' ),
				'P' => __( 'Formosa', 'ovabookpro' ),
				'Y' => __( 'Jujuy', 'ovabookpro' ),
				'L' => __( 'La Pampa', 'ovabookpro' ),
				'F' => __( 'La Rioja', 'ovabookpro' ),
				'M' => __( 'Mendoza', 'ovabookpro' ),
				'N' => __( 'Misiones', 'ovabookpro' ),
				'Q' => __( 'Neuquén', 'ovabookpro' ),
				'R' => __( 'Río Negro', 'ovabookpro' ),
				'A' => __( 'Salta', 'ovabookpro' ),
				'J' => __( 'San Juan', 'ovabookpro' ),
				'D' => __( 'San Luis', 'ovabookpro' ),
				'Z' => __( 'Santa Cruz', 'ovabookpro' ),
				'S' => __( 'Santa Fe', 'ovabookpro' ),
				'G' => __( 'Santiago del Estero', 'ovabookpro' ),
				'V' => __( 'Tierra del Fuego', 'ovabookpro' ),
				'T' => __( 'Tucumán', 'ovabookpro' ),
			),
			'AT' => array(),
			'AU' => array( // Australian states.
				'ACT' => __( 'Australian Capital Territory', 'ovabookpro' ),
				'NSW' => __( 'New South Wales', 'ovabookpro' ),
				'NT'  => __( 'Northern Territory', 'ovabookpro' ),
				'QLD' => __( 'Queensland', 'ovabookpro' ),
				'SA'  => __( 'South Australia', 'ovabookpro' ),
				'TAS' => __( 'Tasmania', 'ovabookpro' ),
				'VIC' => __( 'Victoria', 'ovabookpro' ),
				'WA'  => __( 'Western Australia', 'ovabookpro' ),
			),
			'AX' => array(),
			'BD' => array( // Bangladeshi districts.
				'BD-05' => __( 'Bagerhat', 'ovabookpro' ),
				'BD-01' => __( 'Bandarban', 'ovabookpro' ),
				'BD-02' => __( 'Barguna', 'ovabookpro' ),
				'BD-06' => __( 'Barishal', 'ovabookpro' ),
				'BD-07' => __( 'Bhola', 'ovabookpro' ),
				'BD-03' => __( 'Bogura', 'ovabookpro' ),
				'BD-04' => __( 'Brahmanbaria', 'ovabookpro' ),
				'BD-09' => __( 'Chandpur', 'ovabookpro' ),
				'BD-10' => __( 'Chattogram', 'ovabookpro' ),
				'BD-12' => __( 'Chuadanga', 'ovabookpro' ),
				'BD-11' => __( "Cox's Bazar", 'ovabookpro' ),
				'BD-08' => __( 'Cumilla', 'ovabookpro' ),
				'BD-13' => __( 'Dhaka', 'ovabookpro' ),
				'BD-14' => __( 'Dinajpur', 'ovabookpro' ),
				'BD-15' => __( 'Faridpur ', 'ovabookpro' ),
				'BD-16' => __( 'Feni', 'ovabookpro' ),
				'BD-19' => __( 'Gaibandha', 'ovabookpro' ),
				'BD-18' => __( 'Gazipur', 'ovabookpro' ),
				'BD-17' => __( 'Gopalganj', 'ovabookpro' ),
				'BD-20' => __( 'Habiganj', 'ovabookpro' ),
				'BD-21' => __( 'Jamalpur', 'ovabookpro' ),
				'BD-22' => __( 'Jashore', 'ovabookpro' ),
				'BD-25' => __( 'Jhalokati', 'ovabookpro' ),
				'BD-23' => __( 'Jhenaidah', 'ovabookpro' ),
				'BD-24' => __( 'Joypurhat', 'ovabookpro' ),
				'BD-29' => __( 'Khagrachhari', 'ovabookpro' ),
				'BD-27' => __( 'Khulna', 'ovabookpro' ),
				'BD-26' => __( 'Kishoreganj', 'ovabookpro' ),
				'BD-28' => __( 'Kurigram', 'ovabookpro' ),
				'BD-30' => __( 'Kushtia', 'ovabookpro' ),
				'BD-31' => __( 'Lakshmipur', 'ovabookpro' ),
				'BD-32' => __( 'Lalmonirhat', 'ovabookpro' ),
				'BD-36' => __( 'Madaripur', 'ovabookpro' ),
				'BD-37' => __( 'Magura', 'ovabookpro' ),
				'BD-33' => __( 'Manikganj ', 'ovabookpro' ),
				'BD-39' => __( 'Meherpur', 'ovabookpro' ),
				'BD-38' => __( 'Moulvibazar', 'ovabookpro' ),
				'BD-35' => __( 'Munshiganj', 'ovabookpro' ),
				'BD-34' => __( 'Mymensingh', 'ovabookpro' ),
				'BD-48' => __( 'Naogaon', 'ovabookpro' ),
				'BD-43' => __( 'Narail', 'ovabookpro' ),
				'BD-40' => __( 'Narayanganj', 'ovabookpro' ),
				'BD-42' => __( 'Narsingdi', 'ovabookpro' ),
				'BD-44' => __( 'Natore', 'ovabookpro' ),
				'BD-45' => __( 'Nawabganj', 'ovabookpro' ),
				'BD-41' => __( 'Netrakona', 'ovabookpro' ),
				'BD-46' => __( 'Nilphamari', 'ovabookpro' ),
				'BD-47' => __( 'Noakhali', 'ovabookpro' ),
				'BD-49' => __( 'Pabna', 'ovabookpro' ),
				'BD-52' => __( 'Panchagarh', 'ovabookpro' ),
				'BD-51' => __( 'Patuakhali', 'ovabookpro' ),
				'BD-50' => __( 'Pirojpur', 'ovabookpro' ),
				'BD-53' => __( 'Rajbari', 'ovabookpro' ),
				'BD-54' => __( 'Rajshahi', 'ovabookpro' ),
				'BD-56' => __( 'Rangamati', 'ovabookpro' ),
				'BD-55' => __( 'Rangpur', 'ovabookpro' ),
				'BD-58' => __( 'Satkhira', 'ovabookpro' ),
				'BD-62' => __( 'Shariatpur', 'ovabookpro' ),
				'BD-57' => __( 'Sherpur', 'ovabookpro' ),
				'BD-59' => __( 'Sirajganj', 'ovabookpro' ),
				'BD-61' => __( 'Sunamganj', 'ovabookpro' ),
				'BD-60' => __( 'Sylhet', 'ovabookpro' ),
				'BD-63' => __( 'Tangail', 'ovabookpro' ),
				'BD-64' => __( 'Thakurgaon', 'ovabookpro' ),
			),
			'BE' => array(),
			'BG' => array( // Bulgarian states.
				'BG-01' => __( 'Blagoevgrad', 'ovabookpro' ),
				'BG-02' => __( 'Burgas', 'ovabookpro' ),
				'BG-08' => __( 'Dobrich', 'ovabookpro' ),
				'BG-07' => __( 'Gabrovo', 'ovabookpro' ),
				'BG-26' => __( 'Haskovo', 'ovabookpro' ),
				'BG-09' => __( 'Kardzhali', 'ovabookpro' ),
				'BG-10' => __( 'Kyustendil', 'ovabookpro' ),
				'BG-11' => __( 'Lovech', 'ovabookpro' ),
				'BG-12' => __( 'Montana', 'ovabookpro' ),
				'BG-13' => __( 'Pazardzhik', 'ovabookpro' ),
				'BG-14' => __( 'Pernik', 'ovabookpro' ),
				'BG-15' => __( 'Pleven', 'ovabookpro' ),
				'BG-16' => __( 'Plovdiv', 'ovabookpro' ),
				'BG-17' => __( 'Razgrad', 'ovabookpro' ),
				'BG-18' => __( 'Ruse', 'ovabookpro' ),
				'BG-27' => __( 'Shumen', 'ovabookpro' ),
				'BG-19' => __( 'Silistra', 'ovabookpro' ),
				'BG-20' => __( 'Sliven', 'ovabookpro' ),
				'BG-21' => __( 'Smolyan', 'ovabookpro' ),
				'BG-23' => __( 'Sofia District', 'ovabookpro' ),
				'BG-22' => __( 'Sofia', 'ovabookpro' ),
				'BG-24' => __( 'Stara Zagora', 'ovabookpro' ),
				'BG-25' => __( 'Targovishte', 'ovabookpro' ),
				'BG-03' => __( 'Varna', 'ovabookpro' ),
				'BG-04' => __( 'Veliko Tarnovo', 'ovabookpro' ),
				'BG-05' => __( 'Vidin', 'ovabookpro' ),
				'BG-06' => __( 'Vratsa', 'ovabookpro' ),
				'BG-28' => __( 'Yambol', 'ovabookpro' ),
			),
			'BH' => array(),
			'BI' => array(),
			'BJ' => array( // Beninese states.
				'AL' => __( 'Alibori', 'ovabookpro' ),
				'AK' => __( 'Atakora', 'ovabookpro' ),
				'AQ' => __( 'Atlantique', 'ovabookpro' ),
				'BO' => __( 'Borgou', 'ovabookpro' ),
				'CO' => __( 'Collines', 'ovabookpro' ),
				'KO' => __( 'Kouffo', 'ovabookpro' ),
				'DO' => __( 'Donga', 'ovabookpro' ),
				'LI' => __( 'Littoral', 'ovabookpro' ),
				'MO' => __( 'Mono', 'ovabookpro' ),
				'OU' => __( 'Ouémé', 'ovabookpro' ),
				'PL' => __( 'Plateau', 'ovabookpro' ),
				'ZO' => __( 'Zou', 'ovabookpro' ),
			),
			'BO' => array( // Bolivian states.
				'BO-B' => __( 'Beni', 'ovabookpro' ),
				'BO-H' => __( 'Chuquisaca', 'ovabookpro' ),
				'BO-C' => __( 'Cochabamba', 'ovabookpro' ),
				'BO-L' => __( 'La Paz', 'ovabookpro' ),
				'BO-O' => __( 'Oruro', 'ovabookpro' ),
				'BO-N' => __( 'Pando', 'ovabookpro' ),
				'BO-P' => __( 'Potosí', 'ovabookpro' ),
				'BO-S' => __( 'Santa Cruz', 'ovabookpro' ),
				'BO-T' => __( 'Tarija', 'ovabookpro' ),
			),
			'BR' => array( // Brazilian states.
				'AC' => __( 'Acre', 'ovabookpro' ),
				'AL' => __( 'Alagoas', 'ovabookpro' ),
				'AP' => __( 'Amapá', 'ovabookpro' ),
				'AM' => __( 'Amazonas', 'ovabookpro' ),
				'BA' => __( 'Bahia', 'ovabookpro' ),
				'CE' => __( 'Ceará', 'ovabookpro' ),
				'DF' => __( 'Distrito Federal', 'ovabookpro' ),
				'ES' => __( 'Espírito Santo', 'ovabookpro' ),
				'GO' => __( 'Goiás', 'ovabookpro' ),
				'MA' => __( 'Maranhão', 'ovabookpro' ),
				'MT' => __( 'Mato Grosso', 'ovabookpro' ),
				'MS' => __( 'Mato Grosso do Sul', 'ovabookpro' ),
				'MG' => __( 'Minas Gerais', 'ovabookpro' ),
				'PA' => __( 'Pará', 'ovabookpro' ),
				'PB' => __( 'Paraíba', 'ovabookpro' ),
				'PR' => __( 'Paraná', 'ovabookpro' ),
				'PE' => __( 'Pernambuco', 'ovabookpro' ),
				'PI' => __( 'Piauí', 'ovabookpro' ),
				'RJ' => __( 'Rio de Janeiro', 'ovabookpro' ),
				'RN' => __( 'Rio Grande do Norte', 'ovabookpro' ),
				'RS' => __( 'Rio Grande do Sul', 'ovabookpro' ),
				'RO' => __( 'Rondônia', 'ovabookpro' ),
				'RR' => __( 'Roraima', 'ovabookpro' ),
				'SC' => __( 'Santa Catarina', 'ovabookpro' ),
				'SP' => __( 'São Paulo', 'ovabookpro' ),
				'SE' => __( 'Sergipe', 'ovabookpro' ),
				'TO' => __( 'Tocantins', 'ovabookpro' ),
			),
			'CA' => array( // Canadian states.
				'AB' => __( 'Alberta', 'ovabookpro' ),
				'BC' => __( 'British Columbia', 'ovabookpro' ),
				'MB' => __( 'Manitoba', 'ovabookpro' ),
				'NB' => __( 'New Brunswick', 'ovabookpro' ),
				'NL' => __( 'Newfoundland and Labrador', 'ovabookpro' ),
				'NT' => __( 'Northwest Territories', 'ovabookpro' ),
				'NS' => __( 'Nova Scotia', 'ovabookpro' ),
				'NU' => __( 'Nunavut', 'ovabookpro' ),
				'ON' => __( 'Ontario', 'ovabookpro' ),
				'PE' => __( 'Prince Edward Island', 'ovabookpro' ),
				'QC' => __( 'Quebec', 'ovabookpro' ),
				'SK' => __( 'Saskatchewan', 'ovabookpro' ),
				'YT' => __( 'Yukon Territory', 'ovabookpro' ),
			),
			'CH' => array( // Swiss cantons.
				'AG' => __( 'Aargau', 'ovabookpro' ),
				'AR' => __( 'Appenzell Ausserrhoden', 'ovabookpro' ),
				'AI' => __( 'Appenzell Innerrhoden', 'ovabookpro' ),
				'BL' => __( 'Basel-Landschaft', 'ovabookpro' ),
				'BS' => __( 'Basel-Stadt', 'ovabookpro' ),
				'BE' => __( 'Bern', 'ovabookpro' ),
				'FR' => __( 'Fribourg', 'ovabookpro' ),
				'GE' => __( 'Geneva', 'ovabookpro' ),
				'GL' => __( 'Glarus', 'ovabookpro' ),
				'GR' => __( 'Graubünden', 'ovabookpro' ),
				'JU' => __( 'Jura', 'ovabookpro' ),
				'LU' => __( 'Luzern', 'ovabookpro' ),
				'NE' => __( 'Neuchâtel', 'ovabookpro' ),
				'NW' => __( 'Nidwalden', 'ovabookpro' ),
				'OW' => __( 'Obwalden', 'ovabookpro' ),
				'SH' => __( 'Schaffhausen', 'ovabookpro' ),
				'SZ' => __( 'Schwyz', 'ovabookpro' ),
				'SO' => __( 'Solothurn', 'ovabookpro' ),
				'SG' => __( 'St. Gallen', 'ovabookpro' ),
				'TG' => __( 'Thurgau', 'ovabookpro' ),
				'TI' => __( 'Ticino', 'ovabookpro' ),
				'UR' => __( 'Uri', 'ovabookpro' ),
				'VS' => __( 'Valais', 'ovabookpro' ),
				'VD' => __( 'Vaud', 'ovabookpro' ),
				'ZG' => __( 'Zug', 'ovabookpro' ),
				'ZH' => __( 'Zürich', 'ovabookpro' ),
			),
			'CL' => array( // Chilean states.
				'CL-AI' => __( 'Aisén del General Carlos Ibañez del Campo', 'ovabookpro' ),
				'CL-AN' => __( 'Antofagasta', 'ovabookpro' ),
				'CL-AP' => __( 'Arica y Parinacota', 'ovabookpro' ),
				'CL-AR' => __( 'La Araucanía', 'ovabookpro' ),
				'CL-AT' => __( 'Atacama', 'ovabookpro' ),
				'CL-BI' => __( 'Biobío', 'ovabookpro' ),
				'CL-CO' => __( 'Coquimbo', 'ovabookpro' ),
				'CL-LI' => __( 'Libertador General Bernardo O\'Higgins', 'ovabookpro' ),
				'CL-LL' => __( 'Los Lagos', 'ovabookpro' ),
				'CL-LR' => __( 'Los Ríos', 'ovabookpro' ),
				'CL-MA' => __( 'Magallanes', 'ovabookpro' ),
				'CL-ML' => __( 'Maule', 'ovabookpro' ),
				'CL-NB' => __( 'Ñuble', 'ovabookpro' ),
				'CL-RM' => __( 'Región Metropolitana de Santiago', 'ovabookpro' ),
				'CL-TA' => __( 'Tarapacá', 'ovabookpro' ),
				'CL-VS' => __( 'Valparaíso', 'ovabookpro' ),
			),
			'CN' => array( // Chinese states.
				'CN1'  => __( 'Yunnan / 云南', 'ovabookpro' ),
				'CN2'  => __( 'Beijing / 北京', 'ovabookpro' ),
				'CN3'  => __( 'Tianjin / 天津', 'ovabookpro' ),
				'CN4'  => __( 'Hebei / 河北', 'ovabookpro' ),
				'CN5'  => __( 'Shanxi / 山西', 'ovabookpro' ),
				'CN6'  => __( 'Inner Mongolia / 內蒙古', 'ovabookpro' ),
				'CN7'  => __( 'Liaoning / 辽宁', 'ovabookpro' ),
				'CN8'  => __( 'Jilin / 吉林', 'ovabookpro' ),
				'CN9'  => __( 'Heilongjiang / 黑龙江', 'ovabookpro' ),
				'CN10' => __( 'Shanghai / 上海', 'ovabookpro' ),
				'CN11' => __( 'Jiangsu / 江苏', 'ovabookpro' ),
				'CN12' => __( 'Zhejiang / 浙江', 'ovabookpro' ),
				'CN13' => __( 'Anhui / 安徽', 'ovabookpro' ),
				'CN14' => __( 'Fujian / 福建', 'ovabookpro' ),
				'CN15' => __( 'Jiangxi / 江西', 'ovabookpro' ),
				'CN16' => __( 'Shandong / 山东', 'ovabookpro' ),
				'CN17' => __( 'Henan / 河南', 'ovabookpro' ),
				'CN18' => __( 'Hubei / 湖北', 'ovabookpro' ),
				'CN19' => __( 'Hunan / 湖南', 'ovabookpro' ),
				'CN20' => __( 'Guangdong / 广东', 'ovabookpro' ),
				'CN21' => __( 'Guangxi Zhuang / 广西壮族', 'ovabookpro' ),
				'CN22' => __( 'Hainan / 海南', 'ovabookpro' ),
				'CN23' => __( 'Chongqing / 重庆', 'ovabookpro' ),
				'CN24' => __( 'Sichuan / 四川', 'ovabookpro' ),
				'CN25' => __( 'Guizhou / 贵州', 'ovabookpro' ),
				'CN26' => __( 'Shaanxi / 陕西', 'ovabookpro' ),
				'CN27' => __( 'Gansu / 甘肃', 'ovabookpro' ),
				'CN28' => __( 'Qinghai / 青海', 'ovabookpro' ),
				'CN29' => __( 'Ningxia Hui / 宁夏', 'ovabookpro' ),
				'CN30' => __( 'Macao / 澳门', 'ovabookpro' ),
				'CN31' => __( 'Tibet / 西藏', 'ovabookpro' ),
				'CN32' => __( 'Xinjiang / 新疆', 'ovabookpro' ),
			),
			'CO' => array( // Colombian states.
				'CO-AMA' => __( 'Amazonas', 'ovabookpro' ),
				'CO-ANT' => __( 'Antioquia', 'ovabookpro' ),
				'CO-ARA' => __( 'Arauca', 'ovabookpro' ),
				'CO-ATL' => __( 'Atlántico', 'ovabookpro' ),
				'CO-BOL' => __( 'Bolívar', 'ovabookpro' ),
				'CO-BOY' => __( 'Boyacá', 'ovabookpro' ),
				'CO-CAL' => __( 'Caldas', 'ovabookpro' ),
				'CO-CAQ' => __( 'Caquetá', 'ovabookpro' ),
				'CO-CAS' => __( 'Casanare', 'ovabookpro' ),
				'CO-CAU' => __( 'Cauca', 'ovabookpro' ),
				'CO-CES' => __( 'Cesar', 'ovabookpro' ),
				'CO-CHO' => __( 'Chocó', 'ovabookpro' ),
				'CO-COR' => __( 'Córdoba', 'ovabookpro' ),
				'CO-CUN' => __( 'Cundinamarca', 'ovabookpro' ),
				'CO-DC' => __( 'Capital District', 'ovabookpro' ),
				'CO-GUA' => __( 'Guainía', 'ovabookpro' ),
				'CO-GUV' => __( 'Guaviare', 'ovabookpro' ),
				'CO-HUI' => __( 'Huila', 'ovabookpro' ),
				'CO-LAG' => __( 'La Guajira', 'ovabookpro' ),
				'CO-MAG' => __( 'Magdalena', 'ovabookpro' ),
				'CO-MET' => __( 'Meta', 'ovabookpro' ),
				'CO-NAR' => __( 'Nariño', 'ovabookpro' ),
				'CO-NSA' => __( 'Norte de Santander', 'ovabookpro' ),
				'CO-PUT' => __( 'Putumayo', 'ovabookpro' ),
				'CO-QUI' => __( 'Quindío', 'ovabookpro' ),
				'CO-RIS' => __( 'Risaralda', 'ovabookpro' ),
				'CO-SAN' => __( 'Santander', 'ovabookpro' ),
				'CO-SAP' => __( 'San Andrés & Providencia', 'ovabookpro' ),
				'CO-SUC' => __( 'Sucre', 'ovabookpro' ),
				'CO-TOL' => __( 'Tolima', 'ovabookpro' ),
				'CO-VAC' => __( 'Valle del Cauca', 'ovabookpro' ),
				'CO-VAU' => __( 'Vaupés', 'ovabookpro' ),
				'CO-VID' => __( 'Vichada', 'ovabookpro' ),
			),
			'CR' => array( // Costa Rican states.
				'CR-A' => __( 'Alajuela', 'ovabookpro' ),
				'CR-C' => __( 'Cartago', 'ovabookpro' ),
				'CR-G' => __( 'Guanacaste', 'ovabookpro' ),
				'CR-H' => __( 'Heredia', 'ovabookpro' ),
				'CR-L' => __( 'Limón', 'ovabookpro' ),
				'CR-P' => __( 'Puntarenas', 'ovabookpro' ),
				'CR-SJ' => __( 'San José', 'ovabookpro' ),
			),
			'CZ' => array(),
			'DE' => array( // German states.
				'DE-BW' => __( 'Baden-Württemberg', 'ovabookpro' ),
				'DE-BY' => __( 'Bavaria', 'ovabookpro' ),
				'DE-BE' => __( 'Berlin', 'ovabookpro' ),
				'DE-BB' => __( 'Brandenburg', 'ovabookpro' ),
				'DE-HB' => __( 'Bremen', 'ovabookpro' ),
				'DE-HH' => __( 'Hamburg', 'ovabookpro' ),
				'DE-HE' => __( 'Hesse', 'ovabookpro' ),
				'DE-MV' => __( 'Mecklenburg-Vorpommern', 'ovabookpro' ),
				'DE-NI' => __( 'Lower Saxony', 'ovabookpro' ),
				'DE-NW' => __( 'North Rhine-Westphalia', 'ovabookpro' ),
				'DE-RP' => __( 'Rhineland-Palatinate', 'ovabookpro' ),
				'DE-SL' => __( 'Saarland', 'ovabookpro' ),
				'DE-SN' => __( 'Saxony', 'ovabookpro' ),
				'DE-ST' => __( 'Saxony-Anhalt', 'ovabookpro' ),
				'DE-SH' => __( 'Schleswig-Holstein', 'ovabookpro' ),
				'DE-TH' => __( 'Thuringia', 'ovabookpro' ),
			),
			'DK' => array(),
			'DO' => array( // Dominican states.
				'DO-01' => __( 'Distrito Nacional', 'ovabookpro' ),
				'DO-02' => __( 'Azua', 'ovabookpro' ),
				'DO-03' => __( 'Baoruco', 'ovabookpro' ),
				'DO-04' => __( 'Barahona', 'ovabookpro' ),
				'DO-33' => __( 'Cibao Nordeste', 'ovabookpro' ),
				'DO-34' => __( 'Cibao Noroeste', 'ovabookpro' ),
				'DO-35' => __( 'Cibao Norte', 'ovabookpro' ),
				'DO-36' => __( 'Cibao Sur', 'ovabookpro' ),
				'DO-05' => __( 'Dajabón', 'ovabookpro' ),
				'DO-06' => __( 'Duarte', 'ovabookpro' ),
				'DO-08' => __( 'El Seibo', 'ovabookpro' ),
				'DO-37' => __( 'El Valle', 'ovabookpro' ),
				'DO-07' => __( 'Elías Piña', 'ovabookpro' ),
				'DO-38' => __( 'Enriquillo', 'ovabookpro' ),
				'DO-09' => __( 'Espaillat', 'ovabookpro' ),
				'DO-30' => __( 'Hato Mayor', 'ovabookpro' ),
				'DO-19' => __( 'Hermanas Mirabal', 'ovabookpro' ),
				'DO-39' => __( 'Higüamo', 'ovabookpro' ),
				'DO-10' => __( 'Independencia', 'ovabookpro' ),
				'DO-11' => __( 'La Altagracia', 'ovabookpro' ),
				'DO-12' => __( 'La Romana', 'ovabookpro' ),
				'DO-13' => __( 'La Vega', 'ovabookpro' ),
				'DO-14' => __( 'María Trinidad Sánchez', 'ovabookpro' ),
				'DO-28' => __( 'Monseñor Nouel', 'ovabookpro' ),
				'DO-15' => __( 'Monte Cristi', 'ovabookpro' ),
				'DO-29' => __( 'Monte Plata', 'ovabookpro' ),
				'DO-40' => __( 'Ozama', 'ovabookpro' ),
				'DO-16' => __( 'Pedernales', 'ovabookpro' ),
				'DO-17' => __( 'Peravia', 'ovabookpro' ),
				'DO-18' => __( 'Puerto Plata', 'ovabookpro' ),
				'DO-20' => __( 'Samaná', 'ovabookpro' ),
				'DO-21' => __( 'San Cristóbal', 'ovabookpro' ),
				'DO-31' => __( 'San José de Ocoa', 'ovabookpro' ),
				'DO-22' => __( 'San Juan', 'ovabookpro' ),
				'DO-23' => __( 'San Pedro de Macorís', 'ovabookpro' ),
				'DO-24' => __( 'Sánchez Ramírez', 'ovabookpro' ),
				'DO-25' => __( 'Santiago', 'ovabookpro' ),
				'DO-26' => __( 'Santiago Rodríguez', 'ovabookpro' ),
				'DO-32' => __( 'Santo Domingo', 'ovabookpro' ),
				'DO-41' => __( 'Valdesia', 'ovabookpro' ),
				'DO-27' => __( 'Valverde', 'ovabookpro' ),
				'DO-42' => __( 'Yuma', 'ovabookpro' ),
			),
			'DZ' => array( // Algerian states.
				'DZ-01' => __( 'Adrar', 'ovabookpro' ),
				'DZ-02' => __( 'Chlef', 'ovabookpro' ),
				'DZ-03' => __( 'Laghouat', 'ovabookpro' ),
				'DZ-04' => __( 'Oum El Bouaghi', 'ovabookpro' ),
				'DZ-05' => __( 'Batna', 'ovabookpro' ),
				'DZ-06' => __( 'Béjaïa', 'ovabookpro' ),
				'DZ-07' => __( 'Biskra', 'ovabookpro' ),
				'DZ-08' => __( 'Béchar', 'ovabookpro' ),
				'DZ-09' => __( 'Blida', 'ovabookpro' ),
				'DZ-10' => __( 'Bouira', 'ovabookpro' ),
				'DZ-11' => __( 'Tamanghasset', 'ovabookpro' ),
				'DZ-12' => __( 'Tébessa', 'ovabookpro' ),
				'DZ-13' => __( 'Tlemcen', 'ovabookpro' ),
				'DZ-14' => __( 'Tiaret', 'ovabookpro' ),
				'DZ-15' => __( 'Tizi Ouzou', 'ovabookpro' ),
				'DZ-16' => __( 'Algiers', 'ovabookpro' ),
				'DZ-17' => __( 'Djelfa', 'ovabookpro' ),
				'DZ-18' => __( 'Jijel', 'ovabookpro' ),
				'DZ-19' => __( 'Sétif', 'ovabookpro' ),
				'DZ-20' => __( 'Saïda', 'ovabookpro' ),
				'DZ-21' => __( 'Skikda', 'ovabookpro' ),
				'DZ-22' => __( 'Sidi Bel Abbès', 'ovabookpro' ),
				'DZ-23' => __( 'Annaba', 'ovabookpro' ),
				'DZ-24' => __( 'Guelma', 'ovabookpro' ),
				'DZ-25' => __( 'Constantine', 'ovabookpro' ),
				'DZ-26' => __( 'Médéa', 'ovabookpro' ),
				'DZ-27' => __( 'Mostaganem', 'ovabookpro' ),
				'DZ-28' => __( 'M’Sila', 'ovabookpro' ),
				'DZ-29' => __( 'Mascara', 'ovabookpro' ),
				'DZ-30' => __( 'Ouargla', 'ovabookpro' ),
				'DZ-31' => __( 'Oran', 'ovabookpro' ),
				'DZ-32' => __( 'El Bayadh', 'ovabookpro' ),
				'DZ-33' => __( 'Illizi', 'ovabookpro' ),
				'DZ-34' => __( 'Bordj Bou Arréridj', 'ovabookpro' ),
				'DZ-35' => __( 'Boumerdès', 'ovabookpro' ),
				'DZ-36' => __( 'El Tarf', 'ovabookpro' ),
				'DZ-37' => __( 'Tindouf', 'ovabookpro' ),
				'DZ-38' => __( 'Tissemsilt', 'ovabookpro' ),
				'DZ-39' => __( 'El Oued', 'ovabookpro' ),
				'DZ-40' => __( 'Khenchela', 'ovabookpro' ),
				'DZ-41' => __( 'Souk Ahras', 'ovabookpro' ),
				'DZ-42' => __( 'Tipasa', 'ovabookpro' ),
				'DZ-43' => __( 'Mila', 'ovabookpro' ),
				'DZ-44' => __( 'Aïn Defla', 'ovabookpro' ),
				'DZ-45' => __( 'Naama', 'ovabookpro' ),
				'DZ-46' => __( 'Aïn Témouchent', 'ovabookpro' ),
				'DZ-47' => __( 'Ghardaïa', 'ovabookpro' ),
				'DZ-48' => __( 'Relizane', 'ovabookpro' ),
			),
			'EE' => array(),
			'EC' => array( // Ecuadorian states.
				'EC-A' => __( 'Azuay', 'ovabookpro' ),
				'EC-B' => __( 'Bolívar', 'ovabookpro' ),
				'EC-F' => __( 'Cañar', 'ovabookpro' ),
				'EC-C' => __( 'Carchi', 'ovabookpro' ),
				'EC-H' => __( 'Chimborazo', 'ovabookpro' ),
				'EC-X' => __( 'Cotopaxi', 'ovabookpro' ),
				'EC-O' => __( 'El Oro', 'ovabookpro' ),
				'EC-E' => __( 'Esmeraldas', 'ovabookpro' ),
				'EC-W' => __( 'Galápagos', 'ovabookpro' ),
				'EC-G' => __( 'Guayas', 'ovabookpro' ),
				'EC-I' => __( 'Imbabura', 'ovabookpro' ),
				'EC-L' => __( 'Loja', 'ovabookpro' ),
				'EC-R' => __( 'Los Ríos', 'ovabookpro' ),
				'EC-M' => __( 'Manabí', 'ovabookpro' ),
				'EC-S' => __( 'Morona-Santiago', 'ovabookpro' ),
				'EC-N' => __( 'Napo', 'ovabookpro' ),
				'EC-D' => __( 'Orellana', 'ovabookpro' ),
				'EC-Y' => __( 'Pastaza', 'ovabookpro' ),
				'EC-P' => __( 'Pichincha', 'ovabookpro' ),
				'EC-SE' => __( 'Santa Elena', 'ovabookpro' ),
				'EC-SD' => __( 'Santo Domingo de los Tsáchilas', 'ovabookpro' ),
				'EC-U' => __( 'Sucumbíos', 'ovabookpro' ),
				'EC-T' => __( 'Tungurahua', 'ovabookpro' ),
				'EC-Z' => __( 'Zamora-Chinchipe', 'ovabookpro' ),
			),
			'EG' => array( // Egyptian states.
				'EGALX' => __( 'Alexandria', 'ovabookpro' ),
				'EGASN' => __( 'Aswan', 'ovabookpro' ),
				'EGAST' => __( 'Asyut', 'ovabookpro' ),
				'EGBA'  => __( 'Red Sea', 'ovabookpro' ),
				'EGBH'  => __( 'Beheira', 'ovabookpro' ),
				'EGBNS' => __( 'Beni Suef', 'ovabookpro' ),
				'EGC'   => __( 'Cairo', 'ovabookpro' ),
				'EGDK'  => __( 'Dakahlia', 'ovabookpro' ),
				'EGDT'  => __( 'Damietta', 'ovabookpro' ),
				'EGFYM' => __( 'Faiyum', 'ovabookpro' ),
				'EGGH'  => __( 'Gharbia', 'ovabookpro' ),
				'EGGZ'  => __( 'Giza', 'ovabookpro' ),
				'EGIS'  => __( 'Ismailia', 'ovabookpro' ),
				'EGJS'  => __( 'South Sinai', 'ovabookpro' ),
				'EGKB'  => __( 'Qalyubia', 'ovabookpro' ),
				'EGKFS' => __( 'Kafr el-Sheikh', 'ovabookpro' ),
				'EGKN'  => __( 'Qena', 'ovabookpro' ),
				'EGLX'  => __( 'Luxor', 'ovabookpro' ),
				'EGMN'  => __( 'Minya', 'ovabookpro' ),
				'EGMNF' => __( 'Monufia', 'ovabookpro' ),
				'EGMT'  => __( 'Matrouh', 'ovabookpro' ),
				'EGPTS' => __( 'Port Said', 'ovabookpro' ),
				'EGSHG' => __( 'Sohag', 'ovabookpro' ),
				'EGSHR' => __( 'Al Sharqia', 'ovabookpro' ),
				'EGSIN' => __( 'North Sinai', 'ovabookpro' ),
				'EGSUZ' => __( 'Suez', 'ovabookpro' ),
				'EGWAD' => __( 'New Valley', 'ovabookpro' ),
			),
			'ES' => array( // Spanish states.
				'C'  => __( 'A Coruña', 'ovabookpro' ),
				'VI' => __( 'Araba/Álava', 'ovabookpro' ),
				'AB' => __( 'Albacete', 'ovabookpro' ),
				'A'  => __( 'Alicante', 'ovabookpro' ),
				'AL' => __( 'Almería', 'ovabookpro' ),
				'O'  => __( 'Asturias', 'ovabookpro' ),
				'AV' => __( 'Ávila', 'ovabookpro' ),
				'BA' => __( 'Badajoz', 'ovabookpro' ),
				'PM' => __( 'Baleares', 'ovabookpro' ),
				'B'  => __( 'Barcelona', 'ovabookpro' ),
				'BU' => __( 'Burgos', 'ovabookpro' ),
				'CC' => __( 'Cáceres', 'ovabookpro' ),
				'CA' => __( 'Cádiz', 'ovabookpro' ),
				'S'  => __( 'Cantabria', 'ovabookpro' ),
				'CS' => __( 'Castellón', 'ovabookpro' ),
				'CE' => __( 'Ceuta', 'ovabookpro' ),
				'CR' => __( 'Ciudad Real', 'ovabookpro' ),
				'CO' => __( 'Córdoba', 'ovabookpro' ),
				'CU' => __( 'Cuenca', 'ovabookpro' ),
				'GI' => __( 'Girona', 'ovabookpro' ),
				'GR' => __( 'Granada', 'ovabookpro' ),
				'GU' => __( 'Guadalajara', 'ovabookpro' ),
				'SS' => __( 'Gipuzkoa', 'ovabookpro' ),
				'H'  => __( 'Huelva', 'ovabookpro' ),
				'HU' => __( 'Huesca', 'ovabookpro' ),
				'J'  => __( 'Jaén', 'ovabookpro' ),
				'LO' => __( 'La Rioja', 'ovabookpro' ),
				'GC' => __( 'Las Palmas', 'ovabookpro' ),
				'LE' => __( 'León', 'ovabookpro' ),
				'L'  => __( 'Lleida', 'ovabookpro' ),
				'LU' => __( 'Lugo', 'ovabookpro' ),
				'M'  => __( 'Madrid', 'ovabookpro' ),
				'MA' => __( 'Málaga', 'ovabookpro' ),
				'ML' => __( 'Melilla', 'ovabookpro' ),
				'MU' => __( 'Murcia', 'ovabookpro' ),
				'NA' => __( 'Navarra', 'ovabookpro' ),
				'OR' => __( 'Ourense', 'ovabookpro' ),
				'P'  => __( 'Palencia', 'ovabookpro' ),
				'PO' => __( 'Pontevedra', 'ovabookpro' ),
				'SA' => __( 'Salamanca', 'ovabookpro' ),
				'TF' => __( 'Santa Cruz de Tenerife', 'ovabookpro' ),
				'SG' => __( 'Segovia', 'ovabookpro' ),
				'SE' => __( 'Sevilla', 'ovabookpro' ),
				'SO' => __( 'Soria', 'ovabookpro' ),
				'T'  => __( 'Tarragona', 'ovabookpro' ),
				'TE' => __( 'Teruel', 'ovabookpro' ),
				'TO' => __( 'Toledo', 'ovabookpro' ),
				'V'  => __( 'Valencia', 'ovabookpro' ),
				'VA' => __( 'Valladolid', 'ovabookpro' ),
				'BI' => __( 'Biscay', 'ovabookpro' ),
				'ZA' => __( 'Zamora', 'ovabookpro' ),
				'Z'  => __( 'Zaragoza', 'ovabookpro' ),
			),
			'ET' => array(),
			'FI' => array(),
			'FR' => array(),
			'GF' => array(),
			'GH' => array( // Ghanaian regions.
				'AF' => __( 'Ahafo', 'ovabookpro' ),
				'AH' => __( 'Ashanti', 'ovabookpro' ),
				'BA' => __( 'Brong-Ahafo', 'ovabookpro' ),
				'BO' => __( 'Bono', 'ovabookpro' ),
				'BE' => __( 'Bono East', 'ovabookpro' ),
				'CP' => __( 'Central', 'ovabookpro' ),
				'EP' => __( 'Eastern', 'ovabookpro' ),
				'AA' => __( 'Greater Accra', 'ovabookpro' ),
				'NE' => __( 'North East', 'ovabookpro' ),
				'NP' => __( 'Northern', 'ovabookpro' ),
				'OT' => __( 'Oti', 'ovabookpro' ),
				'SV' => __( 'Savannah', 'ovabookpro' ),
				'UE' => __( 'Upper East', 'ovabookpro' ),
				'UW' => __( 'Upper West', 'ovabookpro' ),
				'TV' => __( 'Volta', 'ovabookpro' ),
				'WP' => __( 'Western', 'ovabookpro' ),
				'WN' => __( 'Western North', 'ovabookpro' ),
			),
			'GP' => array(),
			'GR' => array( // Greek regions.
				'I' => __( 'Attica', 'ovabookpro' ),
				'A' => __( 'East Macedonia and Thrace', 'ovabookpro' ),
				'B' => __( 'Central Macedonia', 'ovabookpro' ),
				'C' => __( 'West Macedonia', 'ovabookpro' ),
				'D' => __( 'Epirus', 'ovabookpro' ),
				'E' => __( 'Thessaly', 'ovabookpro' ),
				'F' => __( 'Ionian Islands', 'ovabookpro' ),
				'G' => __( 'West Greece', 'ovabookpro' ),
				'H' => __( 'Central Greece', 'ovabookpro' ),
				'J' => __( 'Peloponnese', 'ovabookpro' ),
				'K' => __( 'North Aegean', 'ovabookpro' ),
				'L' => __( 'South Aegean', 'ovabookpro' ),
				'M' => __( 'Crete', 'ovabookpro' ),
			),
			'GT' => array( // Guatemalan states.
				'GT-AV' => __( 'Alta Verapaz', 'ovabookpro' ),
				'GT-BV' => __( 'Baja Verapaz', 'ovabookpro' ),
				'GT-CM' => __( 'Chimaltenango', 'ovabookpro' ),
				'GT-CQ' => __( 'Chiquimula', 'ovabookpro' ),
				'GT-PR' => __( 'El Progreso', 'ovabookpro' ),
				'GT-ES' => __( 'Escuintla', 'ovabookpro' ),
				'GT-GU' => __( 'Guatemala', 'ovabookpro' ),
				'GT-HU' => __( 'Huehuetenango', 'ovabookpro' ),
				'GT-IZ' => __( 'Izabal', 'ovabookpro' ),
				'GT-JA' => __( 'Jalapa', 'ovabookpro' ),
				'GT-JU' => __( 'Jutiapa', 'ovabookpro' ),
				'GT-PE' => __( 'Petén', 'ovabookpro' ),
				'GT-QZ' => __( 'Quetzaltenango', 'ovabookpro' ),
				'GT-QC' => __( 'Quiché', 'ovabookpro' ),
				'GT-RE' => __( 'Retalhuleu', 'ovabookpro' ),
				'GT-SA' => __( 'Sacatepéquez', 'ovabookpro' ),
				'GT-SM' => __( 'San Marcos', 'ovabookpro' ),
				'GT-SR' => __( 'Santa Rosa', 'ovabookpro' ),
				'GT-SO' => __( 'Sololá', 'ovabookpro' ),
				'GT-SU' => __( 'Suchitepéquez', 'ovabookpro' ),
				'GT-TO' => __( 'Totonicapán', 'ovabookpro' ),
				'GT-ZA' => __( 'Zacapa', 'ovabookpro' ),
			),
			'HK' => array( // Hong Kong states.
				'HONG KONG'       => __( 'Hong Kong Island', 'ovabookpro' ),
				'KOWLOON'         => __( 'Kowloon', 'ovabookpro' ),
				'NEW TERRITORIES' => __( 'New Territories', 'ovabookpro' ),
			),
			'HN' => array( // Honduran states.
				'HN-AT' => __( 'Atlántida', 'ovabookpro' ),
				'HN-IB' => __( 'Bay Islands', 'ovabookpro' ),
				'HN-CH' => __( 'Choluteca', 'ovabookpro' ),
				'HN-CL' => __( 'Colón', 'ovabookpro' ),
				'HN-CM' => __( 'Comayagua', 'ovabookpro' ),
				'HN-CP' => __( 'Copán', 'ovabookpro' ),
				'HN-CR' => __( 'Cortés', 'ovabookpro' ),
				'HN-EP' => __( 'El Paraíso', 'ovabookpro' ),
				'HN-FM' => __( 'Francisco Morazán', 'ovabookpro' ),
				'HN-GD' => __( 'Gracias a Dios', 'ovabookpro' ),
				'HN-IN' => __( 'Intibucá', 'ovabookpro' ),
				'HN-LE' => __( 'Lempira', 'ovabookpro' ),
				'HN-LP' => __( 'La Paz', 'ovabookpro' ),
				'HN-OC' => __( 'Ocotepeque', 'ovabookpro' ),
				'HN-OL' => __( 'Olancho', 'ovabookpro' ),
				'HN-SB' => __( 'Santa Bárbara', 'ovabookpro' ),
				'HN-VA' => __( 'Valle', 'ovabookpro' ),
				'HN-YO' => __( 'Yoro', 'ovabookpro' ),
			),
			'HU' => array( // Hungarian states.
				'BK' => __( 'Bács-Kiskun', 'ovabookpro' ),
				'BE' => __( 'Békés', 'ovabookpro' ),
				'BA' => __( 'Baranya', 'ovabookpro' ),
				'BZ' => __( 'Borsod-Abaúj-Zemplén', 'ovabookpro' ),
				'BU' => __( 'Budapest', 'ovabookpro' ),
				'CS' => __( 'Csongrád-Csanád', 'ovabookpro' ),
				'FE' => __( 'Fejér', 'ovabookpro' ),
				'GS' => __( 'Győr-Moson-Sopron', 'ovabookpro' ),
				'HB' => __( 'Hajdú-Bihar', 'ovabookpro' ),
				'HE' => __( 'Heves', 'ovabookpro' ),
				'JN' => __( 'Jász-Nagykun-Szolnok', 'ovabookpro' ),
				'KE' => __( 'Komárom-Esztergom', 'ovabookpro' ),
				'NO' => __( 'Nógrád', 'ovabookpro' ),
				'PE' => __( 'Pest', 'ovabookpro' ),
				'SO' => __( 'Somogy', 'ovabookpro' ),
				'SZ' => __( 'Szabolcs-Szatmár-Bereg', 'ovabookpro' ),
				'TO' => __( 'Tolna', 'ovabookpro' ),
				'VA' => __( 'Vas', 'ovabookpro' ),
				'VE' => __( 'Veszprém', 'ovabookpro' ),
				'ZA' => __( 'Zala', 'ovabookpro' ),
			),
			'ID' => array( // Indonesian provinces.
				'AC' => __( 'Daerah Istimewa Aceh', 'ovabookpro' ),
				'SU' => __( 'Sumatera Utara', 'ovabookpro' ),
				'SB' => __( 'Sumatera Barat', 'ovabookpro' ),
				'RI' => __( 'Riau', 'ovabookpro' ),
				'KR' => __( 'Kepulauan Riau', 'ovabookpro' ),
				'JA' => __( 'Jambi', 'ovabookpro' ),
				'SS' => __( 'Sumatera Selatan', 'ovabookpro' ),
				'BB' => __( 'Bangka Belitung', 'ovabookpro' ),
				'BE' => __( 'Bengkulu', 'ovabookpro' ),
				'LA' => __( 'Lampung', 'ovabookpro' ),
				'JK' => __( 'DKI Jakarta', 'ovabookpro' ),
				'JB' => __( 'Jawa Barat', 'ovabookpro' ),
				'BT' => __( 'Banten', 'ovabookpro' ),
				'JT' => __( 'Jawa Tengah', 'ovabookpro' ),
				'JI' => __( 'Jawa Timur', 'ovabookpro' ),
				'YO' => __( 'Daerah Istimewa Yogyakarta', 'ovabookpro' ),
				'BA' => __( 'Bali', 'ovabookpro' ),
				'NB' => __( 'Nusa Tenggara Barat', 'ovabookpro' ),
				'NT' => __( 'Nusa Tenggara Timur', 'ovabookpro' ),
				'KB' => __( 'Kalimantan Barat', 'ovabookpro' ),
				'KT' => __( 'Kalimantan Tengah', 'ovabookpro' ),
				'KI' => __( 'Kalimantan Timur', 'ovabookpro' ),
				'KS' => __( 'Kalimantan Selatan', 'ovabookpro' ),
				'KU' => __( 'Kalimantan Utara', 'ovabookpro' ),
				'SA' => __( 'Sulawesi Utara', 'ovabookpro' ),
				'ST' => __( 'Sulawesi Tengah', 'ovabookpro' ),
				'SG' => __( 'Sulawesi Tenggara', 'ovabookpro' ),
				'SR' => __( 'Sulawesi Barat', 'ovabookpro' ),
				'SN' => __( 'Sulawesi Selatan', 'ovabookpro' ),
				'GO' => __( 'Gorontalo', 'ovabookpro' ),
				'MA' => __( 'Maluku', 'ovabookpro' ),
				'MU' => __( 'Maluku Utara', 'ovabookpro' ),
				'PA' => __( 'Papua', 'ovabookpro' ),
				'PB' => __( 'Papua Barat', 'ovabookpro' ),
			),
			'IE' => array( // Irish states.
				'CW' => __( 'Carlow', 'ovabookpro' ),
				'CN' => __( 'Cavan', 'ovabookpro' ),
				'CE' => __( 'Clare', 'ovabookpro' ),
				'CO' => __( 'Cork', 'ovabookpro' ),
				'DL' => __( 'Donegal', 'ovabookpro' ),
				'D'  => __( 'Dublin', 'ovabookpro' ),
				'G'  => __( 'Galway', 'ovabookpro' ),
				'KY' => __( 'Kerry', 'ovabookpro' ),
				'KE' => __( 'Kildare', 'ovabookpro' ),
				'KK' => __( 'Kilkenny', 'ovabookpro' ),
				'LS' => __( 'Laois', 'ovabookpro' ),
				'LM' => __( 'Leitrim', 'ovabookpro' ),
				'LK' => __( 'Limerick', 'ovabookpro' ),
				'LD' => __( 'Longford', 'ovabookpro' ),
				'LH' => __( 'Louth', 'ovabookpro' ),
				'MO' => __( 'Mayo', 'ovabookpro' ),
				'MH' => __( 'Meath', 'ovabookpro' ),
				'MN' => __( 'Monaghan', 'ovabookpro' ),
				'OY' => __( 'Offaly', 'ovabookpro' ),
				'RN' => __( 'Roscommon', 'ovabookpro' ),
				'SO' => __( 'Sligo', 'ovabookpro' ),
				'TA' => __( 'Tipperary', 'ovabookpro' ),
				'WD' => __( 'Waterford', 'ovabookpro' ),
				'WH' => __( 'Westmeath', 'ovabookpro' ),
				'WX' => __( 'Wexford', 'ovabookpro' ),
				'WW' => __( 'Wicklow', 'ovabookpro' ),
			),
			'IN' => array( // Indian states.
				'AP' => __( 'Andhra Pradesh', 'ovabookpro' ),
				'AR' => __( 'Arunachal Pradesh', 'ovabookpro' ),
				'AS' => __( 'Assam', 'ovabookpro' ),
				'BR' => __( 'Bihar', 'ovabookpro' ),
				'CT' => __( 'Chhattisgarh', 'ovabookpro' ),
				'GA' => __( 'Goa', 'ovabookpro' ),
				'GJ' => __( 'Gujarat', 'ovabookpro' ),
				'HR' => __( 'Haryana', 'ovabookpro' ),
				'HP' => __( 'Himachal Pradesh', 'ovabookpro' ),
				'JK' => __( 'Jammu and Kashmir', 'ovabookpro' ),
				'JH' => __( 'Jharkhand', 'ovabookpro' ),
				'KA' => __( 'Karnataka', 'ovabookpro' ),
				'KL' => __( 'Kerala', 'ovabookpro' ),
				'LA' => __( 'Ladakh', 'ovabookpro' ),
				'MP' => __( 'Madhya Pradesh', 'ovabookpro' ),
				'MH' => __( 'Maharashtra', 'ovabookpro' ),
				'MN' => __( 'Manipur', 'ovabookpro' ),
				'ML' => __( 'Meghalaya', 'ovabookpro' ),
				'MZ' => __( 'Mizoram', 'ovabookpro' ),
				'NL' => __( 'Nagaland', 'ovabookpro' ),
				'OR' => __( 'Odisha', 'ovabookpro' ),
				'PB' => __( 'Punjab', 'ovabookpro' ),
				'RJ' => __( 'Rajasthan', 'ovabookpro' ),
				'SK' => __( 'Sikkim', 'ovabookpro' ),
				'TN' => __( 'Tamil Nadu', 'ovabookpro' ),
				'TS' => __( 'Telangana', 'ovabookpro' ),
				'TR' => __( 'Tripura', 'ovabookpro' ),
				'UK' => __( 'Uttarakhand', 'ovabookpro' ),
				'UP' => __( 'Uttar Pradesh', 'ovabookpro' ),
				'WB' => __( 'West Bengal', 'ovabookpro' ),
				'AN' => __( 'Andaman and Nicobar Islands', 'ovabookpro' ),
				'CH' => __( 'Chandigarh', 'ovabookpro' ),
				'DN' => __( 'Dadra and Nagar Haveli', 'ovabookpro' ),
				'DD' => __( 'Daman and Diu', 'ovabookpro' ),
				'DL' => __( 'Delhi', 'ovabookpro' ),
				'LD' => __( 'Lakshadeep', 'ovabookpro' ),
				'PY' => __( 'Pondicherry (Puducherry)', 'ovabookpro' ),
			),
			'IR' => array( // Iranian states.
				'KHZ' => __( 'Khuzestan (خوزستان)', 'ovabookpro' ),
				'THR' => __( 'Tehran (تهران)', 'ovabookpro' ),
				'ILM' => __( 'Ilaam (ایلام)', 'ovabookpro' ),
				'BHR' => __( 'Bushehr (بوشهر)', 'ovabookpro' ),
				'ADL' => __( 'Ardabil (اردبیل)', 'ovabookpro' ),
				'ESF' => __( 'Isfahan (اصفهان)', 'ovabookpro' ),
				'YZD' => __( 'Yazd (یزد)', 'ovabookpro' ),
				'KRH' => __( 'Kermanshah (کرمانشاه)', 'ovabookpro' ),
				'KRN' => __( 'Kerman (کرمان)', 'ovabookpro' ),
				'HDN' => __( 'Hamadan (همدان)', 'ovabookpro' ),
				'GZN' => __( 'Ghazvin (قزوین)', 'ovabookpro' ),
				'ZJN' => __( 'Zanjan (زنجان)', 'ovabookpro' ),
				'LRS' => __( 'Luristan (لرستان)', 'ovabookpro' ),
				'ABZ' => __( 'Alborz (البرز)', 'ovabookpro' ),
				'EAZ' => __( 'East Azarbaijan (آذربایجان شرقی)', 'ovabookpro' ),
				'WAZ' => __( 'West Azarbaijan (آذربایجان غربی)', 'ovabookpro' ),
				'CHB' => __( 'Chaharmahal and Bakhtiari (چهارمحال و بختیاری)', 'ovabookpro' ),
				'SKH' => __( 'South Khorasan (خراسان جنوبی)', 'ovabookpro' ),
				'RKH' => __( 'Razavi Khorasan (خراسان رضوی)', 'ovabookpro' ),
				'NKH' => __( 'North Khorasan (خراسان شمالی)', 'ovabookpro' ),
				'SMN' => __( 'Semnan (سمنان)', 'ovabookpro' ),
				'FRS' => __( 'Fars (فارس)', 'ovabookpro' ),
				'QHM' => __( 'Qom (قم)', 'ovabookpro' ),
				'KRD' => __( 'Kurdistan / کردستان)', 'ovabookpro' ),
				'KBD' => __( 'Kohgiluyeh and BoyerAhmad (کهگیلوییه و بویراحمد)', 'ovabookpro' ),
				'GLS' => __( 'Golestan (گلستان)', 'ovabookpro' ),
				'GIL' => __( 'Gilan (گیلان)', 'ovabookpro' ),
				'MZN' => __( 'Mazandaran (مازندران)', 'ovabookpro' ),
				'MKZ' => __( 'Markazi (مرکزی)', 'ovabookpro' ),
				'HRZ' => __( 'Hormozgan (هرمزگان)', 'ovabookpro' ),
				'SBN' => __( 'Sistan and Baluchestan (سیستان و بلوچستان)', 'ovabookpro' ),
			),
			'IS' => array(),
			'IT' => array( // Italian provinces.
				'AG' => __( 'Agrigento', 'ovabookpro' ),
				'AL' => __( 'Alessandria', 'ovabookpro' ),
				'AN' => __( 'Ancona', 'ovabookpro' ),
				'AO' => __( 'Aosta', 'ovabookpro' ),
				'AR' => __( 'Arezzo', 'ovabookpro' ),
				'AP' => __( 'Ascoli Piceno', 'ovabookpro' ),
				'AT' => __( 'Asti', 'ovabookpro' ),
				'AV' => __( 'Avellino', 'ovabookpro' ),
				'BA' => __( 'Bari', 'ovabookpro' ),
				'BT' => __( 'Barletta-Andria-Trani', 'ovabookpro' ),
				'BL' => __( 'Belluno', 'ovabookpro' ),
				'BN' => __( 'Benevento', 'ovabookpro' ),
				'BG' => __( 'Bergamo', 'ovabookpro' ),
				'BI' => __( 'Biella', 'ovabookpro' ),
				'BO' => __( 'Bologna', 'ovabookpro' ),
				'BZ' => __( 'Bolzano', 'ovabookpro' ),
				'BS' => __( 'Brescia', 'ovabookpro' ),
				'BR' => __( 'Brindisi', 'ovabookpro' ),
				'CA' => __( 'Cagliari', 'ovabookpro' ),
				'CL' => __( 'Caltanissetta', 'ovabookpro' ),
				'CB' => __( 'Campobasso', 'ovabookpro' ),
				'CE' => __( 'Caserta', 'ovabookpro' ),
				'CT' => __( 'Catania', 'ovabookpro' ),
				'CZ' => __( 'Catanzaro', 'ovabookpro' ),
				'CH' => __( 'Chieti', 'ovabookpro' ),
				'CO' => __( 'Como', 'ovabookpro' ),
				'CS' => __( 'Cosenza', 'ovabookpro' ),
				'CR' => __( 'Cremona', 'ovabookpro' ),
				'KR' => __( 'Crotone', 'ovabookpro' ),
				'CN' => __( 'Cuneo', 'ovabookpro' ),
				'EN' => __( 'Enna', 'ovabookpro' ),
				'FM' => __( 'Fermo', 'ovabookpro' ),
				'FE' => __( 'Ferrara', 'ovabookpro' ),
				'FI' => __( 'Firenze', 'ovabookpro' ),
				'FG' => __( 'Foggia', 'ovabookpro' ),
				'FC' => __( 'Forlì-Cesena', 'ovabookpro' ),
				'FR' => __( 'Frosinone', 'ovabookpro' ),
				'GE' => __( 'Genova', 'ovabookpro' ),
				'GO' => __( 'Gorizia', 'ovabookpro' ),
				'GR' => __( 'Grosseto', 'ovabookpro' ),
				'IM' => __( 'Imperia', 'ovabookpro' ),
				'IS' => __( 'Isernia', 'ovabookpro' ),
				'SP' => __( 'La Spezia', 'ovabookpro' ),
				'AQ' => __( "L'Aquila", 'ovabookpro' ),
				'LT' => __( 'Latina', 'ovabookpro' ),
				'LE' => __( 'Lecce', 'ovabookpro' ),
				'LC' => __( 'Lecco', 'ovabookpro' ),
				'LI' => __( 'Livorno', 'ovabookpro' ),
				'LO' => __( 'Lodi', 'ovabookpro' ),
				'LU' => __( 'Lucca', 'ovabookpro' ),
				'MC' => __( 'Macerata', 'ovabookpro' ),
				'MN' => __( 'Mantova', 'ovabookpro' ),
				'MS' => __( 'Massa-Carrara', 'ovabookpro' ),
				'MT' => __( 'Matera', 'ovabookpro' ),
				'ME' => __( 'Messina', 'ovabookpro' ),
				'MI' => __( 'Milano', 'ovabookpro' ),
				'MO' => __( 'Modena', 'ovabookpro' ),
				'MB' => __( 'Monza e della Brianza', 'ovabookpro' ),
				'NA' => __( 'Napoli', 'ovabookpro' ),
				'NO' => __( 'Novara', 'ovabookpro' ),
				'NU' => __( 'Nuoro', 'ovabookpro' ),
				'OR' => __( 'Oristano', 'ovabookpro' ),
				'PD' => __( 'Padova', 'ovabookpro' ),
				'PA' => __( 'Palermo', 'ovabookpro' ),
				'PR' => __( 'Parma', 'ovabookpro' ),
				'PV' => __( 'Pavia', 'ovabookpro' ),
				'PG' => __( 'Perugia', 'ovabookpro' ),
				'PU' => __( 'Pesaro e Urbino', 'ovabookpro' ),
				'PE' => __( 'Pescara', 'ovabookpro' ),
				'PC' => __( 'Piacenza', 'ovabookpro' ),
				'PI' => __( 'Pisa', 'ovabookpro' ),
				'PT' => __( 'Pistoia', 'ovabookpro' ),
				'PN' => __( 'Pordenone', 'ovabookpro' ),
				'PZ' => __( 'Potenza', 'ovabookpro' ),
				'PO' => __( 'Prato', 'ovabookpro' ),
				'RG' => __( 'Ragusa', 'ovabookpro' ),
				'RA' => __( 'Ravenna', 'ovabookpro' ),
				'RC' => __( 'Reggio Calabria', 'ovabookpro' ),
				'RE' => __( 'Reggio Emilia', 'ovabookpro' ),
				'RI' => __( 'Rieti', 'ovabookpro' ),
				'RN' => __( 'Rimini', 'ovabookpro' ),
				'RM' => __( 'Roma', 'ovabookpro' ),
				'RO' => __( 'Rovigo', 'ovabookpro' ),
				'SA' => __( 'Salerno', 'ovabookpro' ),
				'SS' => __( 'Sassari', 'ovabookpro' ),
				'SV' => __( 'Savona', 'ovabookpro' ),
				'SI' => __( 'Siena', 'ovabookpro' ),
				'SR' => __( 'Siracusa', 'ovabookpro' ),
				'SO' => __( 'Sondrio', 'ovabookpro' ),
				'SU' => __( 'Sud Sardegna', 'ovabookpro' ),
				'TA' => __( 'Taranto', 'ovabookpro' ),
				'TE' => __( 'Teramo', 'ovabookpro' ),
				'TR' => __( 'Terni', 'ovabookpro' ),
				'TO' => __( 'Torino', 'ovabookpro' ),
				'TP' => __( 'Trapani', 'ovabookpro' ),
				'TN' => __( 'Trento', 'ovabookpro' ),
				'TV' => __( 'Treviso', 'ovabookpro' ),
				'TS' => __( 'Trieste', 'ovabookpro' ),
				'UD' => __( 'Udine', 'ovabookpro' ),
				'VA' => __( 'Varese', 'ovabookpro' ),
				'VE' => __( 'Venezia', 'ovabookpro' ),
				'VB' => __( 'Verbano-Cusio-Ossola', 'ovabookpro' ),
				'VC' => __( 'Vercelli', 'ovabookpro' ),
				'VR' => __( 'Verona', 'ovabookpro' ),
				'VV' => __( 'Vibo Valentia', 'ovabookpro' ),
				'VI' => __( 'Vicenza', 'ovabookpro' ),
				'VT' => __( 'Viterbo', 'ovabookpro' ),
			),
			'IL' => array(),
			'IM' => array(),
			'JM' => array( // Jamaican parishes.
				'JM-01' => __( 'Kingston', 'ovabookpro' ),
				'JM-02' => __( 'Saint Andrew', 'ovabookpro' ),
				'JM-03' => __( 'Saint Thomas', 'ovabookpro' ),
				'JM-04' => __( 'Portland', 'ovabookpro' ),
				'JM-05' => __( 'Saint Mary', 'ovabookpro' ),
				'JM-06' => __( 'Saint Ann', 'ovabookpro' ),
				'JM-07' => __( 'Trelawny', 'ovabookpro' ),
				'JM-08' => __( 'Saint James', 'ovabookpro' ),
				'JM-09' => __( 'Hanover', 'ovabookpro' ),
				'JM-10' => __( 'Westmoreland', 'ovabookpro' ),
				'JM-11' => __( 'Saint Elizabeth', 'ovabookpro' ),
				'JM-12' => __( 'Manchester', 'ovabookpro' ),
				'JM-13' => __( 'Clarendon', 'ovabookpro' ),
				'JM-14' => __( 'Saint Catherine', 'ovabookpro' ),
			),

			/**
			 * Japanese states.
			 *
			 * English notation of prefectures conform to the notation of Japan Post.
			 * The suffix corresponds with the Japanese translation file.
			 */
			'JP' => array(
				'JP01' => __( 'Hokkaido', 'ovabookpro' ),
				'JP02' => __( 'Aomori', 'ovabookpro' ),
				'JP03' => __( 'Iwate', 'ovabookpro' ),
				'JP04' => __( 'Miyagi', 'ovabookpro' ),
				'JP05' => __( 'Akita', 'ovabookpro' ),
				'JP06' => __( 'Yamagata', 'ovabookpro' ),
				'JP07' => __( 'Fukushima', 'ovabookpro' ),
				'JP08' => __( 'Ibaraki', 'ovabookpro' ),
				'JP09' => __( 'Tochigi', 'ovabookpro' ),
				'JP10' => __( 'Gunma', 'ovabookpro' ),
				'JP11' => __( 'Saitama', 'ovabookpro' ),
				'JP12' => __( 'Chiba', 'ovabookpro' ),
				'JP13' => __( 'Tokyo', 'ovabookpro' ),
				'JP14' => __( 'Kanagawa', 'ovabookpro' ),
				'JP15' => __( 'Niigata', 'ovabookpro' ),
				'JP16' => __( 'Toyama', 'ovabookpro' ),
				'JP17' => __( 'Ishikawa', 'ovabookpro' ),
				'JP18' => __( 'Fukui', 'ovabookpro' ),
				'JP19' => __( 'Yamanashi', 'ovabookpro' ),
				'JP20' => __( 'Nagano', 'ovabookpro' ),
				'JP21' => __( 'Gifu', 'ovabookpro' ),
				'JP22' => __( 'Shizuoka', 'ovabookpro' ),
				'JP23' => __( 'Aichi', 'ovabookpro' ),
				'JP24' => __( 'Mie', 'ovabookpro' ),
				'JP25' => __( 'Shiga', 'ovabookpro' ),
				'JP26' => __( 'Kyoto', 'ovabookpro' ),
				'JP27' => __( 'Osaka', 'ovabookpro' ),
				'JP28' => __( 'Hyogo', 'ovabookpro' ),
				'JP29' => __( 'Nara', 'ovabookpro' ),
				'JP30' => __( 'Wakayama', 'ovabookpro' ),
				'JP31' => __( 'Tottori', 'ovabookpro' ),
				'JP32' => __( 'Shimane', 'ovabookpro' ),
				'JP33' => __( 'Okayama', 'ovabookpro' ),
				'JP34' => __( 'Hiroshima', 'ovabookpro' ),
				'JP35' => __( 'Yamaguchi', 'ovabookpro' ),
				'JP36' => __( 'Tokushima', 'ovabookpro' ),
				'JP37' => __( 'Kagawa', 'ovabookpro' ),
				'JP38' => __( 'Ehime', 'ovabookpro' ),
				'JP39' => __( 'Kochi', 'ovabookpro' ),
				'JP40' => __( 'Fukuoka', 'ovabookpro' ),
				'JP41' => __( 'Saga', 'ovabookpro' ),
				'JP42' => __( 'Nagasaki', 'ovabookpro' ),
				'JP43' => __( 'Kumamoto', 'ovabookpro' ),
				'JP44' => __( 'Oita', 'ovabookpro' ),
				'JP45' => __( 'Miyazaki', 'ovabookpro' ),
				'JP46' => __( 'Kagoshima', 'ovabookpro' ),
				'JP47' => __( 'Okinawa', 'ovabookpro' ),
			),
			'KE' => array( // Kenyan counties.
				'KE01' => __( 'Baringo', 'ovabookpro' ),
				'KE02' => __( 'Bomet', 'ovabookpro' ),
				'KE03' => __( 'Bungoma', 'ovabookpro' ),
				'KE04' => __( 'Busia', 'ovabookpro' ),
				'KE05' => __( 'Elgeyo-Marakwet', 'ovabookpro' ),
				'KE06' => __( 'Embu', 'ovabookpro' ),
				'KE07' => __( 'Garissa', 'ovabookpro' ),
				'KE08' => __( 'Homa Bay', 'ovabookpro' ),
				'KE09' => __( 'Isiolo', 'ovabookpro' ),
				'KE10' => __( 'Kajiado', 'ovabookpro' ),
				'KE11' => __( 'Kakamega', 'ovabookpro' ),
				'KE12' => __( 'Kericho', 'ovabookpro' ),
				'KE13' => __( 'Kiambu', 'ovabookpro' ),
				'KE14' => __( 'Kilifi', 'ovabookpro' ),
				'KE15' => __( 'Kirinyaga', 'ovabookpro' ),
				'KE16' => __( 'Kisii', 'ovabookpro' ),
				'KE17' => __( 'Kisumu', 'ovabookpro' ),
				'KE18' => __( 'Kitui', 'ovabookpro' ),
				'KE19' => __( 'Kwale', 'ovabookpro' ),
				'KE20' => __( 'Laikipia', 'ovabookpro' ),
				'KE21' => __( 'Lamu', 'ovabookpro' ),
				'KE22' => __( 'Machakos', 'ovabookpro' ),
				'KE23' => __( 'Makueni', 'ovabookpro' ),
				'KE24' => __( 'Mandera', 'ovabookpro' ),
				'KE25' => __( 'Marsabit', 'ovabookpro' ),
				'KE26' => __( 'Meru', 'ovabookpro' ),
				'KE27' => __( 'Migori', 'ovabookpro' ),
				'KE28' => __( 'Mombasa', 'ovabookpro' ),
				'KE29' => __( 'Murang’a', 'ovabookpro' ),
				'KE30' => __( 'Nairobi County', 'ovabookpro' ),
				'KE31' => __( 'Nakuru', 'ovabookpro' ),
				'KE32' => __( 'Nandi', 'ovabookpro' ),
				'KE33' => __( 'Narok', 'ovabookpro' ),
				'KE34' => __( 'Nyamira', 'ovabookpro' ),
				'KE35' => __( 'Nyandarua', 'ovabookpro' ),
				'KE36' => __( 'Nyeri', 'ovabookpro' ),
				'KE37' => __( 'Samburu', 'ovabookpro' ),
				'KE38' => __( 'Siaya', 'ovabookpro' ),
				'KE39' => __( 'Taita-Taveta', 'ovabookpro' ),
				'KE40' => __( 'Tana River', 'ovabookpro' ),
				'KE41' => __( 'Tharaka-Nithi', 'ovabookpro' ),
				'KE42' => __( 'Trans Nzoia', 'ovabookpro' ),
				'KE43' => __( 'Turkana', 'ovabookpro' ),
				'KE44' => __( 'Uasin Gishu', 'ovabookpro' ),
				'KE45' => __( 'Vihiga', 'ovabookpro' ),
				'KE46' => __( 'Wajir', 'ovabookpro' ),
				'KE47' => __( 'West Pokot', 'ovabookpro' ),
			),
			'KN' => array( // Saint Kitts and Nevis parishes.
				'KNK'  => __( 'Saint Kitts', 'ovabookpro' ),
				'KNN'  => __( 'Nevis', 'ovabookpro' ),
				'KN01' => __( 'Christ Church Nichola Town', 'ovabookpro' ),
				'KN02' => __( 'Saint Anne Sandy Point', 'ovabookpro' ),
				'KN03' => __( 'Saint George Basseterre', 'ovabookpro' ),
				'KN04' => __( 'Saint George Gingerland', 'ovabookpro' ),
				'KN05' => __( 'Saint James Windward', 'ovabookpro' ),
				'KN06' => __( 'Saint John Capisterre', 'ovabookpro' ),
				'KN07' => __( 'Saint John Figtree', 'ovabookpro' ),
				'KN08' => __( 'Saint Mary Cayon', 'ovabookpro' ),
				'KN09' => __( 'Saint Paul Capisterre', 'ovabookpro' ),
				'KN10' => __( 'Saint Paul Charlestown', 'ovabookpro' ),
				'KN11' => __( 'Saint Peter Basseterre', 'ovabookpro' ),
				'KN12' => __( 'Saint Thomas Lowland', 'ovabookpro' ),
				'KN13' => __( 'Saint Thomas Middle Island', 'ovabookpro' ),
				'KN15' => __( 'Trinity Palmetto Point', 'ovabookpro' ),
			),
			'KR' => array(),
			'KW' => array(),
			'LA' => array( // Laotian provinces.
				'AT' => __( 'Attapeu', 'ovabookpro' ),
				'BK' => __( 'Bokeo', 'ovabookpro' ),
				'BL' => __( 'Bolikhamsai', 'ovabookpro' ),
				'CH' => __( 'Champasak', 'ovabookpro' ),
				'HO' => __( 'Houaphanh', 'ovabookpro' ),
				'KH' => __( 'Khammouane', 'ovabookpro' ),
				'LM' => __( 'Luang Namtha', 'ovabookpro' ),
				'LP' => __( 'Luang Prabang', 'ovabookpro' ),
				'OU' => __( 'Oudomxay', 'ovabookpro' ),
				'PH' => __( 'Phongsaly', 'ovabookpro' ),
				'SL' => __( 'Salavan', 'ovabookpro' ),
				'SV' => __( 'Savannakhet', 'ovabookpro' ),
				'VI' => __( 'Vientiane Province', 'ovabookpro' ),
				'VT' => __( 'Vientiane', 'ovabookpro' ),
				'XA' => __( 'Sainyabuli', 'ovabookpro' ),
				'XE' => __( 'Sekong', 'ovabookpro' ),
				'XI' => __( 'Xiangkhouang', 'ovabookpro' ),
				'XS' => __( 'Xaisomboun', 'ovabookpro' ),
			),
			'LB' => array(),
			'LI' => array(),
			'LR' => array( // Liberian provinces.
				'BM' => __( 'Bomi', 'ovabookpro' ),
				'BN' => __( 'Bong', 'ovabookpro' ),
				'GA' => __( 'Gbarpolu', 'ovabookpro' ),
				'GB' => __( 'Grand Bassa', 'ovabookpro' ),
				'GC' => __( 'Grand Cape Mount', 'ovabookpro' ),
				'GG' => __( 'Grand Gedeh', 'ovabookpro' ),
				'GK' => __( 'Grand Kru', 'ovabookpro' ),
				'LO' => __( 'Lofa', 'ovabookpro' ),
				'MA' => __( 'Margibi', 'ovabookpro' ),
				'MY' => __( 'Maryland', 'ovabookpro' ),
				'MO' => __( 'Montserrado', 'ovabookpro' ),
				'NM' => __( 'Nimba', 'ovabookpro' ),
				'RV' => __( 'Rivercess', 'ovabookpro' ),
				'RG' => __( 'River Gee', 'ovabookpro' ),
				'SN' => __( 'Sinoe', 'ovabookpro' ),
			),
			'LU' => array(),
			'MA' => array( // Moroccan regions.
				'maagd' => __( 'Agadir-Ida Ou Tanane', 'ovabookpro' ),
				'maazi' => __( 'Azilal', 'ovabookpro' ),
				'mabem' => __( 'Béni-Mellal', 'ovabookpro' ),
				'maber' => __( 'Berkane', 'ovabookpro' ),
				'mabes' => __( 'Ben Slimane', 'ovabookpro' ),
				'mabod' => __( 'Boujdour', 'ovabookpro' ),
				'mabom' => __( 'Boulemane', 'ovabookpro' ),
				'mabrr' => __( 'Berrechid', 'ovabookpro' ),
				'macas' => __( 'Casablanca', 'ovabookpro' ),
				'mache' => __( 'Chefchaouen', 'ovabookpro' ),
				'machi' => __( 'Chichaoua', 'ovabookpro' ),
				'macht' => __( 'Chtouka Aït Baha', 'ovabookpro' ),
				'madri' => __( 'Driouch', 'ovabookpro' ),
				'maedi' => __( 'Essaouira', 'ovabookpro' ),
				'maerr' => __( 'Errachidia', 'ovabookpro' ),
				'mafah' => __( 'Fahs-Beni Makada', 'ovabookpro' ),
				'mafes' => __( 'Fès-Dar-Dbibegh', 'ovabookpro' ),
				'mafig' => __( 'Figuig', 'ovabookpro' ),
				'mafqh' => __( 'Fquih Ben Salah', 'ovabookpro' ),
				'mafes' => __( 'Fès-Dar-Dbibegh', 'ovabookpro' ),
				'mague' => __( 'Guelmim', 'ovabookpro' ),
				'maguf' => __( 'Guercif', 'ovabookpro' ),
				'mahaj' => __( 'El Hajeb', 'ovabookpro' ),
				'mahao' => __( 'Al Haouz', 'ovabookpro' ),
				'mahoc' => __( 'Al Hoceïma', 'ovabookpro' ),
				'maifr' => __( 'Ifrane', 'ovabookpro' ),
				'maine' => __( 'Inezgane-Aït Melloul', 'ovabookpro' ),
				'majdi' => __( 'El Jadida', 'ovabookpro' ),
				'majra' => __( 'Jerada', 'ovabookpro' ),
				'maken' => __( 'Kénitra', 'ovabookpro' ),
				'makes' => __( 'Kelaat Sraghna', 'ovabookpro' ),
				'makhe' => __( 'Khemisset', 'ovabookpro' ),
				'makhn' => __( 'Khénifra', 'ovabookpro' ),
				'makho' => __( 'Khouribga', 'ovabookpro' ),
				'malaa' => __( 'Laâyoune', 'ovabookpro' ),
				'malar' => __( 'Larache', 'ovabookpro' ),
				'mamar' => __( 'Marrakech', 'ovabookpro' ),
				'mamdf' => __( 'M’diq-Fnideq', 'ovabookpro' ),
				'mamed' => __( 'Médiouna', 'ovabookpro' ),
				'mamek' => __( 'Meknès', 'ovabookpro' ),
				'mamid' => __( 'Midelt', 'ovabookpro' ),
				'mammd' => __( 'Marrakech-Medina', 'ovabookpro' ),
				'mammn' => __( 'Marrakech-Menara', 'ovabookpro' ),
				'mamoh' => __( 'Mohammedia', 'ovabookpro' ),
				'mamou' => __( 'Moulay Yacoub', 'ovabookpro' ),
				'manad' => __( 'Nador', 'ovabookpro' ),
				'manou' => __( 'Nouaceur', 'ovabookpro' ),
				'maoua' => __( 'Ouarzazate', 'ovabookpro' ),
				'maoud' => __( 'Oued Ed-Dahab', 'ovabookpro' ),
				'maouj' => __( 'Oujda-Angad', 'ovabookpro' ),
				'maouz' => __( 'Ouezzane', 'ovabookpro' ),
				'marab' => __( 'Rabat', 'ovabookpro' ),
				'mareh' => __( 'Rehamna', 'ovabookpro' ),
				'masaf' => __( 'Safi', 'ovabookpro' ),
				'masal' => __( 'Salé', 'ovabookpro' ),
				'masef' => __( 'Sefrou', 'ovabookpro' ),
				'maset' => __( 'Settat', 'ovabookpro' ),
				'masib' => __( 'Sidi Bennour', 'ovabookpro' ),
				'masif' => __( 'Sidi Ifni', 'ovabookpro' ),
				'masik' => __( 'Sidi Kacem', 'ovabookpro' ),
				'masil' => __( 'Sidi Slimane', 'ovabookpro' ),
				'maskh' => __( 'Skhirat-Témara', 'ovabookpro' ),
				'masyb' => __( 'Sidi Youssef Ben Ali', 'ovabookpro' ),
				'mataf' => __( 'Tarfaya (EH-partial)', 'ovabookpro' ),
				'matai' => __( 'Taourirt', 'ovabookpro' ),
				'matao' => __( 'Taounate', 'ovabookpro' ),
				'matar' => __( 'Taroudant', 'ovabookpro' ),
				'matat' => __( 'Tata', 'ovabookpro' ),
				'mataz' => __( 'Taza', 'ovabookpro' ),
				'matet' => __( 'Tétouan', 'ovabookpro' ),
				'matin' => __( 'Tinghir', 'ovabookpro' ),
				'matiz' => __( 'Tiznit', 'ovabookpro' ),
				'matng' => __( 'Tangier-Assilah', 'ovabookpro' ),
				'matnt' => __( 'Tan-Tan', 'ovabookpro' ),
				'mayus' => __( 'Youssoufia', 'ovabookpro' ),
				'mazag' => __( 'Zagora', 'ovabookpro' )
			),
			'MD' => array( // Moldovan states.
				'C'  => __( 'Chișinău', 'ovabookpro' ),
				'BL' => __( 'Bălți', 'ovabookpro' ),
				'AN' => __( 'Anenii Noi', 'ovabookpro' ),
				'BS' => __( 'Basarabeasca', 'ovabookpro' ),
				'BR' => __( 'Briceni', 'ovabookpro' ),
				'CH' => __( 'Cahul', 'ovabookpro' ),
				'CT' => __( 'Cantemir', 'ovabookpro' ),
				'CL' => __( 'Călărași', 'ovabookpro' ),
				'CS' => __( 'Căușeni', 'ovabookpro' ),
				'CM' => __( 'Cimișlia', 'ovabookpro' ),
				'CR' => __( 'Criuleni', 'ovabookpro' ),
				'DN' => __( 'Dondușeni', 'ovabookpro' ),
				'DR' => __( 'Drochia', 'ovabookpro' ),
				'DB' => __( 'Dubăsari', 'ovabookpro' ),
				'ED' => __( 'Edineț', 'ovabookpro' ),
				'FL' => __( 'Fălești', 'ovabookpro' ),
				'FR' => __( 'Florești', 'ovabookpro' ),
				'GE' => __( 'UTA Găgăuzia', 'ovabookpro' ),
				'GL' => __( 'Glodeni', 'ovabookpro' ),
				'HN' => __( 'Hîncești', 'ovabookpro' ),
				'IL' => __( 'Ialoveni', 'ovabookpro' ),
				'LV' => __( 'Leova', 'ovabookpro' ),
				'NS' => __( 'Nisporeni', 'ovabookpro' ),
				'OC' => __( 'Ocnița', 'ovabookpro' ),
				'OR' => __( 'Orhei', 'ovabookpro' ),
				'RZ' => __( 'Rezina', 'ovabookpro' ),
				'RS' => __( 'Rîșcani', 'ovabookpro' ),
				'SG' => __( 'Sîngerei', 'ovabookpro' ),
				'SR' => __( 'Soroca', 'ovabookpro' ),
				'ST' => __( 'Strășeni', 'ovabookpro' ),
				'SD' => __( 'Șoldănești', 'ovabookpro' ),
				'SV' => __( 'Ștefan Vodă', 'ovabookpro' ),
				'TR' => __( 'Taraclia', 'ovabookpro' ),
				'TL' => __( 'Telenești', 'ovabookpro' ),
				'UN' => __( 'Ungheni', 'ovabookpro' ),
			),
			'MF' => array(),
			'MQ' => array(),
			'MT' => array(),
			'MX' => array( // Mexican states.
				'DF' => __( 'Ciudad de México', 'ovabookpro' ),
				'JA' => __( 'Jalisco', 'ovabookpro' ),
				'NL' => __( 'Nuevo León', 'ovabookpro' ),
				'AG' => __( 'Aguascalientes', 'ovabookpro' ),
				'BC' => __( 'Baja California', 'ovabookpro' ),
				'BS' => __( 'Baja California Sur', 'ovabookpro' ),
				'CM' => __( 'Campeche', 'ovabookpro' ),
				'CS' => __( 'Chiapas', 'ovabookpro' ),
				'CH' => __( 'Chihuahua', 'ovabookpro' ),
				'CO' => __( 'Coahuila', 'ovabookpro' ),
				'CL' => __( 'Colima', 'ovabookpro' ),
				'DG' => __( 'Durango', 'ovabookpro' ),
				'GT' => __( 'Guanajuato', 'ovabookpro' ),
				'GR' => __( 'Guerrero', 'ovabookpro' ),
				'HG' => __( 'Hidalgo', 'ovabookpro' ),
				'MX' => __( 'Estado de México', 'ovabookpro' ),
				'MI' => __( 'Michoacán', 'ovabookpro' ),
				'MO' => __( 'Morelos', 'ovabookpro' ),
				'NA' => __( 'Nayarit', 'ovabookpro' ),
				'OA' => __( 'Oaxaca', 'ovabookpro' ),
				'PU' => __( 'Puebla', 'ovabookpro' ),
				'QT' => __( 'Querétaro', 'ovabookpro' ),
				'QR' => __( 'Quintana Roo', 'ovabookpro' ),
				'SL' => __( 'San Luis Potosí', 'ovabookpro' ),
				'SI' => __( 'Sinaloa', 'ovabookpro' ),
				'SO' => __( 'Sonora', 'ovabookpro' ),
				'TB' => __( 'Tabasco', 'ovabookpro' ),
				'TM' => __( 'Tamaulipas', 'ovabookpro' ),
				'TL' => __( 'Tlaxcala', 'ovabookpro' ),
				'VE' => __( 'Veracruz', 'ovabookpro' ),
				'YU' => __( 'Yucatán', 'ovabookpro' ),
				'ZA' => __( 'Zacatecas', 'ovabookpro' ),
			),
			'MY' => array( // Malaysian states.
				'JHR' => __( 'Johor', 'ovabookpro' ),
				'KDH' => __( 'Kedah', 'ovabookpro' ),
				'KTN' => __( 'Kelantan', 'ovabookpro' ),
				'LBN' => __( 'Labuan', 'ovabookpro' ),
				'MLK' => __( 'Malacca (Melaka)', 'ovabookpro' ),
				'NSN' => __( 'Negeri Sembilan', 'ovabookpro' ),
				'PHG' => __( 'Pahang', 'ovabookpro' ),
				'PNG' => __( 'Penang (Pulau Pinang)', 'ovabookpro' ),
				'PRK' => __( 'Perak', 'ovabookpro' ),
				'PLS' => __( 'Perlis', 'ovabookpro' ),
				'SBH' => __( 'Sabah', 'ovabookpro' ),
				'SWK' => __( 'Sarawak', 'ovabookpro' ),
				'SGR' => __( 'Selangor', 'ovabookpro' ),
				'TRG' => __( 'Terengganu', 'ovabookpro' ),
				'PJY' => __( 'Putrajaya', 'ovabookpro' ),
				'KUL' => __( 'Kuala Lumpur', 'ovabookpro' ),
			),
			'MZ' => array( // Mozambican provinces.
				'MZP'   => __( 'Cabo Delgado', 'ovabookpro' ),
				'MZG'   => __( 'Gaza', 'ovabookpro' ),
				'MZI'   => __( 'Inhambane', 'ovabookpro' ),
				'MZB'   => __( 'Manica', 'ovabookpro' ),
				'MZL'   => __( 'Maputo Province', 'ovabookpro' ),
				'MZMPM' => __( 'Maputo', 'ovabookpro' ),
				'MZN'   => __( 'Nampula', 'ovabookpro' ),
				'MZA'   => __( 'Niassa', 'ovabookpro' ),
				'MZS'   => __( 'Sofala', 'ovabookpro' ),
				'MZT'   => __( 'Tete', 'ovabookpro' ),
				'MZQ'   => __( 'Zambézia', 'ovabookpro' ),
			),
			'NA' => array( // Namibian regions.
				'ER' => __( 'Erongo', 'ovabookpro' ),
				'HA' => __( 'Hardap', 'ovabookpro' ),
				'KA' => __( 'Karas', 'ovabookpro' ),
				'KE' => __( 'Kavango East', 'ovabookpro' ),
				'KW' => __( 'Kavango West', 'ovabookpro' ),
				'KH' => __( 'Khomas', 'ovabookpro' ),
				'KU' => __( 'Kunene', 'ovabookpro' ),
				'OW' => __( 'Ohangwena', 'ovabookpro' ),
				'OH' => __( 'Omaheke', 'ovabookpro' ),
				'OS' => __( 'Omusati', 'ovabookpro' ),
				'ON' => __( 'Oshana', 'ovabookpro' ),
				'OT' => __( 'Oshikoto', 'ovabookpro' ),
				'OD' => __( 'Otjozondjupa', 'ovabookpro' ),
				'CA' => __( 'Zambezi', 'ovabookpro' ),
			),
			'NG' => array( // Nigerian provinces.
				'AB' => __( 'Abia', 'ovabookpro' ),
				'FC' => __( 'Abuja', 'ovabookpro' ),
				'AD' => __( 'Adamawa', 'ovabookpro' ),
				'AK' => __( 'Akwa Ibom', 'ovabookpro' ),
				'AN' => __( 'Anambra', 'ovabookpro' ),
				'BA' => __( 'Bauchi', 'ovabookpro' ),
				'BY' => __( 'Bayelsa', 'ovabookpro' ),
				'BE' => __( 'Benue', 'ovabookpro' ),
				'BO' => __( 'Borno', 'ovabookpro' ),
				'CR' => __( 'Cross River', 'ovabookpro' ),
				'DE' => __( 'Delta', 'ovabookpro' ),
				'EB' => __( 'Ebonyi', 'ovabookpro' ),
				'ED' => __( 'Edo', 'ovabookpro' ),
				'EK' => __( 'Ekiti', 'ovabookpro' ),
				'EN' => __( 'Enugu', 'ovabookpro' ),
				'GO' => __( 'Gombe', 'ovabookpro' ),
				'IM' => __( 'Imo', 'ovabookpro' ),
				'JI' => __( 'Jigawa', 'ovabookpro' ),
				'KD' => __( 'Kaduna', 'ovabookpro' ),
				'KN' => __( 'Kano', 'ovabookpro' ),
				'KT' => __( 'Katsina', 'ovabookpro' ),
				'KE' => __( 'Kebbi', 'ovabookpro' ),
				'KO' => __( 'Kogi', 'ovabookpro' ),
				'KW' => __( 'Kwara', 'ovabookpro' ),
				'LA' => __( 'Lagos', 'ovabookpro' ),
				'NA' => __( 'Nasarawa', 'ovabookpro' ),
				'NI' => __( 'Niger', 'ovabookpro' ),
				'OG' => __( 'Ogun', 'ovabookpro' ),
				'ON' => __( 'Ondo', 'ovabookpro' ),
				'OS' => __( 'Osun', 'ovabookpro' ),
				'OY' => __( 'Oyo', 'ovabookpro' ),
				'PL' => __( 'Plateau', 'ovabookpro' ),
				'RI' => __( 'Rivers', 'ovabookpro' ),
				'SO' => __( 'Sokoto', 'ovabookpro' ),
				'TA' => __( 'Taraba', 'ovabookpro' ),
				'YO' => __( 'Yobe', 'ovabookpro' ),
				'ZA' => __( 'Zamfara', 'ovabookpro' ),
			),
			'NL' => array(),
			'NO' => array(),
			'NP' => array( // Nepalese zones.
				'BAG' => __( 'Bagmati', 'ovabookpro' ),
				'BHE' => __( 'Bheri', 'ovabookpro' ),
				'DHA' => __( 'Dhaulagiri', 'ovabookpro' ),
				'GAN' => __( 'Gandaki', 'ovabookpro' ),
				'JAN' => __( 'Janakpur', 'ovabookpro' ),
				'KAR' => __( 'Karnali', 'ovabookpro' ),
				'KOS' => __( 'Koshi', 'ovabookpro' ),
				'LUM' => __( 'Lumbini', 'ovabookpro' ),
				'MAH' => __( 'Mahakali', 'ovabookpro' ),
				'MEC' => __( 'Mechi', 'ovabookpro' ),
				'NAR' => __( 'Narayani', 'ovabookpro' ),
				'RAP' => __( 'Rapti', 'ovabookpro' ),
				'SAG' => __( 'Sagarmatha', 'ovabookpro' ),
				'SET' => __( 'Seti', 'ovabookpro' ),
			),
			'NI' => array( // Nicaraguan states.
				'NI-AN' => __( 'Atlántico Norte', 'ovabookpro' ),
				'NI-AS' => __( 'Atlántico Sur', 'ovabookpro' ),
				'NI-BO' => __( 'Boaco', 'ovabookpro' ),
				'NI-CA' => __( 'Carazo', 'ovabookpro' ),
				'NI-CI' => __( 'Chinandega', 'ovabookpro' ),
				'NI-CO' => __( 'Chontales', 'ovabookpro' ),
				'NI-ES' => __( 'Estelí', 'ovabookpro' ),
				'NI-GR' => __( 'Granada', 'ovabookpro' ),
				'NI-JI' => __( 'Jinotega', 'ovabookpro' ),
				'NI-LE' => __( 'León', 'ovabookpro' ),
				'NI-MD' => __( 'Madriz', 'ovabookpro' ),
				'NI-MN' => __( 'Managua', 'ovabookpro' ),
				'NI-MS' => __( 'Masaya', 'ovabookpro' ),
				'NI-MT' => __( 'Matagalpa', 'ovabookpro' ),
				'NI-NS' => __( 'Nueva Segovia', 'ovabookpro' ),
				'NI-RI' => __( 'Rivas', 'ovabookpro' ),
				'NI-SJ' => __( 'Río San Juan', 'ovabookpro' ),
			),
			'NZ' => array( // New Zealand states.
				'NTL' => __( 'Northland', 'ovabookpro' ),
				'AUK' => __( 'Auckland', 'ovabookpro' ),
				'WKO' => __( 'Waikato', 'ovabookpro' ),
				'BOP' => __( 'Bay of Plenty', 'ovabookpro' ),
				'TKI' => __( 'Taranaki', 'ovabookpro' ),
				'GIS' => __( 'Gisborne', 'ovabookpro' ),
				'HKB' => __( 'Hawke’s Bay', 'ovabookpro' ),
				'MWT' => __( 'Manawatu-Wanganui', 'ovabookpro' ),
				'WGN' => __( 'Wellington', 'ovabookpro' ),
				'NSN' => __( 'Nelson', 'ovabookpro' ),
				'MBH' => __( 'Marlborough', 'ovabookpro' ),
				'TAS' => __( 'Tasman', 'ovabookpro' ),
				'WTC' => __( 'West Coast', 'ovabookpro' ),
				'CAN' => __( 'Canterbury', 'ovabookpro' ),
				'OTA' => __( 'Otago', 'ovabookpro' ),
				'STL' => __( 'Southland', 'ovabookpro' ),
			),
			'PA' => array( // Panamanian states.
				'PA-1' => __( 'Bocas del Toro', 'ovabookpro' ),
				'PA-2' => __( 'Coclé', 'ovabookpro' ),
				'PA-3' => __( 'Colón', 'ovabookpro' ),
				'PA-4' => __( 'Chiriquí', 'ovabookpro' ),
				'PA-5' => __( 'Darién', 'ovabookpro' ),
				'PA-6' => __( 'Herrera', 'ovabookpro' ),
				'PA-7' => __( 'Los Santos', 'ovabookpro' ),
				'PA-8' => __( 'Panamá', 'ovabookpro' ),
				'PA-9' => __( 'Veraguas', 'ovabookpro' ),
				'PA-10' => __( 'West Panamá', 'ovabookpro' ),
				'PA-EM' => __( 'Emberá', 'ovabookpro' ),
				'PA-KY' => __( 'Guna Yala', 'ovabookpro' ),
				'PA-NB' => __( 'Ngöbe-Buglé', 'ovabookpro' ),
			),
			'PE' => array( // Peruvian states.
				'CAL' => __( 'El Callao', 'ovabookpro' ),
				'LMA' => __( 'Municipalidad Metropolitana de Lima', 'ovabookpro' ),
				'AMA' => __( 'Amazonas', 'ovabookpro' ),
				'ANC' => __( 'Ancash', 'ovabookpro' ),
				'APU' => __( 'Apurímac', 'ovabookpro' ),
				'ARE' => __( 'Arequipa', 'ovabookpro' ),
				'AYA' => __( 'Ayacucho', 'ovabookpro' ),
				'CAJ' => __( 'Cajamarca', 'ovabookpro' ),
				'CUS' => __( 'Cusco', 'ovabookpro' ),
				'HUV' => __( 'Huancavelica', 'ovabookpro' ),
				'HUC' => __( 'Huánuco', 'ovabookpro' ),
				'ICA' => __( 'Ica', 'ovabookpro' ),
				'JUN' => __( 'Junín', 'ovabookpro' ),
				'LAL' => __( 'La Libertad', 'ovabookpro' ),
				'LAM' => __( 'Lambayeque', 'ovabookpro' ),
				'LIM' => __( 'Lima', 'ovabookpro' ),
				'LOR' => __( 'Loreto', 'ovabookpro' ),
				'MDD' => __( 'Madre de Dios', 'ovabookpro' ),
				'MOQ' => __( 'Moquegua', 'ovabookpro' ),
				'PAS' => __( 'Pasco', 'ovabookpro' ),
				'PIU' => __( 'Piura', 'ovabookpro' ),
				'PUN' => __( 'Puno', 'ovabookpro' ),
				'SAM' => __( 'San Martín', 'ovabookpro' ),
				'TAC' => __( 'Tacna', 'ovabookpro' ),
				'TUM' => __( 'Tumbes', 'ovabookpro' ),
				'UCA' => __( 'Ucayali', 'ovabookpro' ),
			),
			'PH' => array( // Philippine provinces.
				'ABR' => __( 'Abra', 'ovabookpro' ),
				'AGN' => __( 'Agusan del Norte', 'ovabookpro' ),
				'AGS' => __( 'Agusan del Sur', 'ovabookpro' ),
				'AKL' => __( 'Aklan', 'ovabookpro' ),
				'ALB' => __( 'Albay', 'ovabookpro' ),
				'ANT' => __( 'Antique', 'ovabookpro' ),
				'APA' => __( 'Apayao', 'ovabookpro' ),
				'AUR' => __( 'Aurora', 'ovabookpro' ),
				'BAS' => __( 'Basilan', 'ovabookpro' ),
				'BAN' => __( 'Bataan', 'ovabookpro' ),
				'BTN' => __( 'Batanes', 'ovabookpro' ),
				'BTG' => __( 'Batangas', 'ovabookpro' ),
				'BEN' => __( 'Benguet', 'ovabookpro' ),
				'BIL' => __( 'Biliran', 'ovabookpro' ),
				'BOH' => __( 'Bohol', 'ovabookpro' ),
				'BUK' => __( 'Bukidnon', 'ovabookpro' ),
				'BUL' => __( 'Bulacan', 'ovabookpro' ),
				'CAG' => __( 'Cagayan', 'ovabookpro' ),
				'CAN' => __( 'Camarines Norte', 'ovabookpro' ),
				'CAS' => __( 'Camarines Sur', 'ovabookpro' ),
				'CAM' => __( 'Camiguin', 'ovabookpro' ),
				'CAP' => __( 'Capiz', 'ovabookpro' ),
				'CAT' => __( 'Catanduanes', 'ovabookpro' ),
				'CAV' => __( 'Cavite', 'ovabookpro' ),
				'CEB' => __( 'Cebu', 'ovabookpro' ),
				'COM' => __( 'Compostela Valley', 'ovabookpro' ),
				'NCO' => __( 'Cotabato', 'ovabookpro' ),
				'DAV' => __( 'Davao del Norte', 'ovabookpro' ),
				'DAS' => __( 'Davao del Sur', 'ovabookpro' ),
				'DAC' => __( 'Davao Occidental', 'ovabookpro' ),
				'DAO' => __( 'Davao Oriental', 'ovabookpro' ),
				'DIN' => __( 'Dinagat Islands', 'ovabookpro' ),
				'EAS' => __( 'Eastern Samar', 'ovabookpro' ),
				'GUI' => __( 'Guimaras', 'ovabookpro' ),
				'IFU' => __( 'Ifugao', 'ovabookpro' ),
				'ILN' => __( 'Ilocos Norte', 'ovabookpro' ),
				'ILS' => __( 'Ilocos Sur', 'ovabookpro' ),
				'ILI' => __( 'Iloilo', 'ovabookpro' ),
				'ISA' => __( 'Isabela', 'ovabookpro' ),
				'KAL' => __( 'Kalinga', 'ovabookpro' ),
				'LUN' => __( 'La Union', 'ovabookpro' ),
				'LAG' => __( 'Laguna', 'ovabookpro' ),
				'LAN' => __( 'Lanao del Norte', 'ovabookpro' ),
				'LAS' => __( 'Lanao del Sur', 'ovabookpro' ),
				'LEY' => __( 'Leyte', 'ovabookpro' ),
				'MAG' => __( 'Maguindanao', 'ovabookpro' ),
				'MAD' => __( 'Marinduque', 'ovabookpro' ),
				'MAS' => __( 'Masbate', 'ovabookpro' ),
				'MSC' => __( 'Misamis Occidental', 'ovabookpro' ),
				'MSR' => __( 'Misamis Oriental', 'ovabookpro' ),
				'MOU' => __( 'Mountain Province', 'ovabookpro' ),
				'NEC' => __( 'Negros Occidental', 'ovabookpro' ),
				'NER' => __( 'Negros Oriental', 'ovabookpro' ),
				'NSA' => __( 'Northern Samar', 'ovabookpro' ),
				'NUE' => __( 'Nueva Ecija', 'ovabookpro' ),
				'NUV' => __( 'Nueva Vizcaya', 'ovabookpro' ),
				'MDC' => __( 'Occidental Mindoro', 'ovabookpro' ),
				'MDR' => __( 'Oriental Mindoro', 'ovabookpro' ),
				'PLW' => __( 'Palawan', 'ovabookpro' ),
				'PAM' => __( 'Pampanga', 'ovabookpro' ),
				'PAN' => __( 'Pangasinan', 'ovabookpro' ),
				'QUE' => __( 'Quezon', 'ovabookpro' ),
				'QUI' => __( 'Quirino', 'ovabookpro' ),
				'RIZ' => __( 'Rizal', 'ovabookpro' ),
				'ROM' => __( 'Romblon', 'ovabookpro' ),
				'WSA' => __( 'Samar', 'ovabookpro' ),
				'SAR' => __( 'Sarangani', 'ovabookpro' ),
				'SIQ' => __( 'Siquijor', 'ovabookpro' ),
				'SOR' => __( 'Sorsogon', 'ovabookpro' ),
				'SCO' => __( 'South Cotabato', 'ovabookpro' ),
				'SLE' => __( 'Southern Leyte', 'ovabookpro' ),
				'SUK' => __( 'Sultan Kudarat', 'ovabookpro' ),
				'SLU' => __( 'Sulu', 'ovabookpro' ),
				'SUN' => __( 'Surigao del Norte', 'ovabookpro' ),
				'SUR' => __( 'Surigao del Sur', 'ovabookpro' ),
				'TAR' => __( 'Tarlac', 'ovabookpro' ),
				'TAW' => __( 'Tawi-Tawi', 'ovabookpro' ),
				'ZMB' => __( 'Zambales', 'ovabookpro' ),
				'ZAN' => __( 'Zamboanga del Norte', 'ovabookpro' ),
				'ZAS' => __( 'Zamboanga del Sur', 'ovabookpro' ),
				'ZSI' => __( 'Zamboanga Sibugay', 'ovabookpro' ),
				'00'  => __( 'Metro Manila', 'ovabookpro' ),
			),
			'PK' => array( // Pakistani states.
				'JK' => __( 'Azad Kashmir', 'ovabookpro' ),
				'BA' => __( 'Balochistan', 'ovabookpro' ),
				'TA' => __( 'FATA', 'ovabookpro' ),
				'GB' => __( 'Gilgit Baltistan', 'ovabookpro' ),
				'IS' => __( 'Islamabad Capital Territory', 'ovabookpro' ),
				'KP' => __( 'Khyber Pakhtunkhwa', 'ovabookpro' ),
				'PB' => __( 'Punjab', 'ovabookpro' ),
				'SD' => __( 'Sindh', 'ovabookpro' ),
			),
			'PL' => array(),
			'PR' => array(),
			'PT' => array(),
			'PY' => array( // Paraguayan states.
				'PY-ASU' => __( 'Asunción', 'ovabookpro' ),
				'PY-1'   => __( 'Concepción', 'ovabookpro' ),
				'PY-2'   => __( 'San Pedro', 'ovabookpro' ),
				'PY-3'   => __( 'Cordillera', 'ovabookpro' ),
				'PY-4'   => __( 'Guairá', 'ovabookpro' ),
				'PY-5'   => __( 'Caaguazú', 'ovabookpro' ),
				'PY-6'   => __( 'Caazapá', 'ovabookpro' ),
				'PY-7'   => __( 'Itapúa', 'ovabookpro' ),
				'PY-8'   => __( 'Misiones', 'ovabookpro' ),
				'PY-9'   => __( 'Paraguarí', 'ovabookpro' ),
				'PY-10'  => __( 'Alto Paraná', 'ovabookpro' ),
				'PY-11'  => __( 'Central', 'ovabookpro' ),
				'PY-12'  => __( 'Ñeembucú', 'ovabookpro' ),
				'PY-13'  => __( 'Amambay', 'ovabookpro' ),
				'PY-14'  => __( 'Canindeyú', 'ovabookpro' ),
				'PY-15'  => __( 'Presidente Hayes', 'ovabookpro' ),
				'PY-16'  => __( 'Alto Paraguay', 'ovabookpro' ),
				'PY-17'  => __( 'Boquerón', 'ovabookpro' ),
			),
			'RE' => array(),
			'RO' => array( // Romanian states.
				'AB' => __( 'Alba', 'ovabookpro' ),
				'AR' => __( 'Arad', 'ovabookpro' ),
				'AG' => __( 'Argeș', 'ovabookpro' ),
				'BC' => __( 'Bacău', 'ovabookpro' ),
				'BH' => __( 'Bihor', 'ovabookpro' ),
				'BN' => __( 'Bistrița-Năsăud', 'ovabookpro' ),
				'BT' => __( 'Botoșani', 'ovabookpro' ),
				'BR' => __( 'Brăila', 'ovabookpro' ),
				'BV' => __( 'Brașov', 'ovabookpro' ),
				'B'  => __( 'București', 'ovabookpro' ),
				'BZ' => __( 'Buzău', 'ovabookpro' ),
				'CL' => __( 'Călărași', 'ovabookpro' ),
				'CS' => __( 'Caraș-Severin', 'ovabookpro' ),
				'CJ' => __( 'Cluj', 'ovabookpro' ),
				'CT' => __( 'Constanța', 'ovabookpro' ),
				'CV' => __( 'Covasna', 'ovabookpro' ),
				'DB' => __( 'Dâmbovița', 'ovabookpro' ),
				'DJ' => __( 'Dolj', 'ovabookpro' ),
				'GL' => __( 'Galați', 'ovabookpro' ),
				'GR' => __( 'Giurgiu', 'ovabookpro' ),
				'GJ' => __( 'Gorj', 'ovabookpro' ),
				'HR' => __( 'Harghita', 'ovabookpro' ),
				'HD' => __( 'Hunedoara', 'ovabookpro' ),
				'IL' => __( 'Ialomița', 'ovabookpro' ),
				'IS' => __( 'Iași', 'ovabookpro' ),
				'IF' => __( 'Ilfov', 'ovabookpro' ),
				'MM' => __( 'Maramureș', 'ovabookpro' ),
				'MH' => __( 'Mehedinți', 'ovabookpro' ),
				'MS' => __( 'Mureș', 'ovabookpro' ),
				'NT' => __( 'Neamț', 'ovabookpro' ),
				'OT' => __( 'Olt', 'ovabookpro' ),
				'PH' => __( 'Prahova', 'ovabookpro' ),
				'SJ' => __( 'Sălaj', 'ovabookpro' ),
				'SM' => __( 'Satu Mare', 'ovabookpro' ),
				'SB' => __( 'Sibiu', 'ovabookpro' ),
				'SV' => __( 'Suceava', 'ovabookpro' ),
				'TR' => __( 'Teleorman', 'ovabookpro' ),
				'TM' => __( 'Timiș', 'ovabookpro' ),
				'TL' => __( 'Tulcea', 'ovabookpro' ),
				'VL' => __( 'Vâlcea', 'ovabookpro' ),
				'VS' => __( 'Vaslui', 'ovabookpro' ),
				'VN' => __( 'Vrancea', 'ovabookpro' ),
			),
			'SN' => array( // Regions of Senegal. Ref: https://github.com/unicode-org/cldr/blob/release-42/common/subdivisions/en.xml#L4801.
				'SNDB' => __( 'Diourbel', 'ovabookpro' ),
				'SNDK' => __( 'Dakar', 'ovabookpro' ),
				'SNFK' => __( 'Fatick', 'ovabookpro' ),
				'SNKA' => __( 'Kaffrine', 'ovabookpro' ),
				'SNKD' => __( 'Kolda', 'ovabookpro' ),
				'SNKE' => __( 'Kédougou', 'ovabookpro' ),
				'SNKL' => __( 'Kaolack', 'ovabookpro' ),
				'SNLG' => __( 'Louga', 'ovabookpro' ),
				'SNMT' => __( 'Matam', 'ovabookpro' ),
				'SNSE' => __( 'Sédhiou', 'ovabookpro' ),
				'SNSL' => __( 'Saint-Louis', 'ovabookpro' ),
				'SNTC' => __( 'Tambacounda', 'ovabookpro' ),
				'SNTH' => __( 'Thiès', 'ovabookpro' ),
				'SNZG' => __( 'Ziguinchor', 'ovabookpro' ),
			),
			'SG' => array(),
			'SK' => array(),
			'SI' => array(),
			'SV' => array( // Salvadoran states.
				'SV-AH' => __( 'Ahuachapán', 'ovabookpro' ),
				'SV-CA' => __( 'Cabañas', 'ovabookpro' ),
				'SV-CH' => __( 'Chalatenango', 'ovabookpro' ),
				'SV-CU' => __( 'Cuscatlán', 'ovabookpro' ),
				'SV-LI' => __( 'La Libertad', 'ovabookpro' ),
				'SV-MO' => __( 'Morazán', 'ovabookpro' ),
				'SV-PA' => __( 'La Paz', 'ovabookpro' ),
				'SV-SA' => __( 'Santa Ana', 'ovabookpro' ),
				'SV-SM' => __( 'San Miguel', 'ovabookpro' ),
				'SV-SO' => __( 'Sonsonate', 'ovabookpro' ),
				'SV-SS' => __( 'San Salvador', 'ovabookpro' ),
				'SV-SV' => __( 'San Vicente', 'ovabookpro' ),
				'SV-UN' => __( 'La Unión', 'ovabookpro' ),
				'SV-US' => __( 'Usulután', 'ovabookpro' ),
			),
			'TH' => array( // Thai states.
				'TH-37' => __( 'Amnat Charoen', 'ovabookpro' ),
				'TH-15' => __( 'Ang Thong', 'ovabookpro' ),
				'TH-14' => __( 'Ayutthaya', 'ovabookpro' ),
				'TH-10' => __( 'Bangkok', 'ovabookpro' ),
				'TH-38' => __( 'Bueng Kan', 'ovabookpro' ),
				'TH-31' => __( 'Buri Ram', 'ovabookpro' ),
				'TH-24' => __( 'Chachoengsao', 'ovabookpro' ),
				'TH-18' => __( 'Chai Nat', 'ovabookpro' ),
				'TH-36' => __( 'Chaiyaphum', 'ovabookpro' ),
				'TH-22' => __( 'Chanthaburi', 'ovabookpro' ),
				'TH-50' => __( 'Chiang Mai', 'ovabookpro' ),
				'TH-57' => __( 'Chiang Rai', 'ovabookpro' ),
				'TH-20' => __( 'Chonburi', 'ovabookpro' ),
				'TH-86' => __( 'Chumphon', 'ovabookpro' ),
				'TH-46' => __( 'Kalasin', 'ovabookpro' ),
				'TH-62' => __( 'Kamphaeng Phet', 'ovabookpro' ),
				'TH-71' => __( 'Kanchanaburi', 'ovabookpro' ),
				'TH-40' => __( 'Khon Kaen', 'ovabookpro' ),
				'TH-81' => __( 'Krabi', 'ovabookpro' ),
				'TH-52' => __( 'Lampang', 'ovabookpro' ),
				'TH-51' => __( 'Lamphun', 'ovabookpro' ),
				'TH-42' => __( 'Loei', 'ovabookpro' ),
				'TH-16' => __( 'Lopburi', 'ovabookpro' ),
				'TH-58' => __( 'Mae Hong Son', 'ovabookpro' ),
				'TH-44' => __( 'Maha Sarakham', 'ovabookpro' ),
				'TH-49' => __( 'Mukdahan', 'ovabookpro' ),
				'TH-26' => __( 'Nakhon Nayok', 'ovabookpro' ),
				'TH-73' => __( 'Nakhon Pathom', 'ovabookpro' ),
				'TH-48' => __( 'Nakhon Phanom', 'ovabookpro' ),
				'TH-30' => __( 'Nakhon Ratchasima', 'ovabookpro' ),
				'TH-60' => __( 'Nakhon Sawan', 'ovabookpro' ),
				'TH-80' => __( 'Nakhon Si Thammarat', 'ovabookpro' ),
				'TH-55' => __( 'Nan', 'ovabookpro' ),
				'TH-96' => __( 'Narathiwat', 'ovabookpro' ),
				'TH-39' => __( 'Nong Bua Lam Phu', 'ovabookpro' ),
				'TH-43' => __( 'Nong Khai', 'ovabookpro' ),
				'TH-12' => __( 'Nonthaburi', 'ovabookpro' ),
				'TH-13' => __( 'Pathum Thani', 'ovabookpro' ),
				'TH-94' => __( 'Pattani', 'ovabookpro' ),
				'TH-82' => __( 'Phang Nga', 'ovabookpro' ),
				'TH-93' => __( 'Phatthalung', 'ovabookpro' ),
				'TH-56' => __( 'Phayao', 'ovabookpro' ),
				'TH-67' => __( 'Phetchabun', 'ovabookpro' ),
				'TH-76' => __( 'Phetchaburi', 'ovabookpro' ),
				'TH-66' => __( 'Phichit', 'ovabookpro' ),
				'TH-65' => __( 'Phitsanulok', 'ovabookpro' ),
				'TH-54' => __( 'Phrae', 'ovabookpro' ),
				'TH-83' => __( 'Phuket', 'ovabookpro' ),
				'TH-25' => __( 'Prachin Buri', 'ovabookpro' ),
				'TH-77' => __( 'Prachuap Khiri Khan', 'ovabookpro' ),
				'TH-85' => __( 'Ranong', 'ovabookpro' ),
				'TH-70' => __( 'Ratchaburi', 'ovabookpro' ),
				'TH-21' => __( 'Rayong', 'ovabookpro' ),
				'TH-45' => __( 'Roi Et', 'ovabookpro' ),
				'TH-27' => __( 'Sa Kaeo', 'ovabookpro' ),
				'TH-47' => __( 'Sakon Nakhon', 'ovabookpro' ),
				'TH-11' => __( 'Samut Prakan', 'ovabookpro' ),
				'TH-74' => __( 'Samut Sakhon', 'ovabookpro' ),
				'TH-75' => __( 'Samut Songkhram', 'ovabookpro' ),
				'TH-19' => __( 'Saraburi', 'ovabookpro' ),
				'TH-91' => __( 'Satun', 'ovabookpro' ),
				'TH-17' => __( 'Sing Buri', 'ovabookpro' ),
				'TH-33' => __( 'Sisaket', 'ovabookpro' ),
				'TH-90' => __( 'Songkhla', 'ovabookpro' ),
				'TH-64' => __( 'Sukhothai', 'ovabookpro' ),
				'TH-72' => __( 'Suphan Buri', 'ovabookpro' ),
				'TH-84' => __( 'Surat Thani', 'ovabookpro' ),
				'TH-32' => __( 'Surin', 'ovabookpro' ),
				'TH-63' => __( 'Tak', 'ovabookpro' ),
				'TH-92' => __( 'Trang', 'ovabookpro' ),
				'TH-23' => __( 'Trat', 'ovabookpro' ),
				'TH-34' => __( 'Ubon Ratchathani', 'ovabookpro' ),
				'TH-41' => __( 'Udon Thani', 'ovabookpro' ),
				'TH-61' => __( 'Uthai Thani', 'ovabookpro' ),
				'TH-53' => __( 'Uttaradit', 'ovabookpro' ),
				'TH-95' => __( 'Yala', 'ovabookpro' ),
				'TH-35' => __( 'Yasothon', 'ovabookpro' ),
			),
			'TR' => array( // Turkish states.
				'TR01' => __( 'Adana', 'ovabookpro' ),
				'TR02' => __( 'Adıyaman', 'ovabookpro' ),
				'TR03' => __( 'Afyon', 'ovabookpro' ),
				'TR04' => __( 'Ağrı', 'ovabookpro' ),
				'TR05' => __( 'Amasya', 'ovabookpro' ),
				'TR06' => __( 'Ankara', 'ovabookpro' ),
				'TR07' => __( 'Antalya', 'ovabookpro' ),
				'TR08' => __( 'Artvin', 'ovabookpro' ),
				'TR09' => __( 'Aydın', 'ovabookpro' ),
				'TR10' => __( 'Balıkesir', 'ovabookpro' ),
				'TR11' => __( 'Bilecik', 'ovabookpro' ),
				'TR12' => __( 'Bingöl', 'ovabookpro' ),
				'TR13' => __( 'Bitlis', 'ovabookpro' ),
				'TR14' => __( 'Bolu', 'ovabookpro' ),
				'TR15' => __( 'Burdur', 'ovabookpro' ),
				'TR16' => __( 'Bursa', 'ovabookpro' ),
				'TR17' => __( 'Çanakkale', 'ovabookpro' ),
				'TR18' => __( 'Çankırı', 'ovabookpro' ),
				'TR19' => __( 'Çorum', 'ovabookpro' ),
				'TR20' => __( 'Denizli', 'ovabookpro' ),
				'TR21' => __( 'Diyarbakır', 'ovabookpro' ),
				'TR22' => __( 'Edirne', 'ovabookpro' ),
				'TR23' => __( 'Elazığ', 'ovabookpro' ),
				'TR24' => __( 'Erzincan', 'ovabookpro' ),
				'TR25' => __( 'Erzurum', 'ovabookpro' ),
				'TR26' => __( 'Eskişehir', 'ovabookpro' ),
				'TR27' => __( 'Gaziantep', 'ovabookpro' ),
				'TR28' => __( 'Giresun', 'ovabookpro' ),
				'TR29' => __( 'Gümüşhane', 'ovabookpro' ),
				'TR30' => __( 'Hakkari', 'ovabookpro' ),
				'TR31' => __( 'Hatay', 'ovabookpro' ),
				'TR32' => __( 'Isparta', 'ovabookpro' ),
				'TR33' => __( 'İçel', 'ovabookpro' ),
				'TR34' => __( 'İstanbul', 'ovabookpro' ),
				'TR35' => __( 'İzmir', 'ovabookpro' ),
				'TR36' => __( 'Kars', 'ovabookpro' ),
				'TR37' => __( 'Kastamonu', 'ovabookpro' ),
				'TR38' => __( 'Kayseri', 'ovabookpro' ),
				'TR39' => __( 'Kırklareli', 'ovabookpro' ),
				'TR40' => __( 'Kırşehir', 'ovabookpro' ),
				'TR41' => __( 'Kocaeli', 'ovabookpro' ),
				'TR42' => __( 'Konya', 'ovabookpro' ),
				'TR43' => __( 'Kütahya', 'ovabookpro' ),
				'TR44' => __( 'Malatya', 'ovabookpro' ),
				'TR45' => __( 'Manisa', 'ovabookpro' ),
				'TR46' => __( 'Kahramanmaraş', 'ovabookpro' ),
				'TR47' => __( 'Mardin', 'ovabookpro' ),
				'TR48' => __( 'Muğla', 'ovabookpro' ),
				'TR49' => __( 'Muş', 'ovabookpro' ),
				'TR50' => __( 'Nevşehir', 'ovabookpro' ),
				'TR51' => __( 'Niğde', 'ovabookpro' ),
				'TR52' => __( 'Ordu', 'ovabookpro' ),
				'TR53' => __( 'Rize', 'ovabookpro' ),
				'TR54' => __( 'Sakarya', 'ovabookpro' ),
				'TR55' => __( 'Samsun', 'ovabookpro' ),
				'TR56' => __( 'Siirt', 'ovabookpro' ),
				'TR57' => __( 'Sinop', 'ovabookpro' ),
				'TR58' => __( 'Sivas', 'ovabookpro' ),
				'TR59' => __( 'Tekirdağ', 'ovabookpro' ),
				'TR60' => __( 'Tokat', 'ovabookpro' ),
				'TR61' => __( 'Trabzon', 'ovabookpro' ),
				'TR62' => __( 'Tunceli', 'ovabookpro' ),
				'TR63' => __( 'Şanlıurfa', 'ovabookpro' ),
				'TR64' => __( 'Uşak', 'ovabookpro' ),
				'TR65' => __( 'Van', 'ovabookpro' ),
				'TR66' => __( 'Yozgat', 'ovabookpro' ),
				'TR67' => __( 'Zonguldak', 'ovabookpro' ),
				'TR68' => __( 'Aksaray', 'ovabookpro' ),
				'TR69' => __( 'Bayburt', 'ovabookpro' ),
				'TR70' => __( 'Karaman', 'ovabookpro' ),
				'TR71' => __( 'Kırıkkale', 'ovabookpro' ),
				'TR72' => __( 'Batman', 'ovabookpro' ),
				'TR73' => __( 'Şırnak', 'ovabookpro' ),
				'TR74' => __( 'Bartın', 'ovabookpro' ),
				'TR75' => __( 'Ardahan', 'ovabookpro' ),
				'TR76' => __( 'Iğdır', 'ovabookpro' ),
				'TR77' => __( 'Yalova', 'ovabookpro' ),
				'TR78' => __( 'Karabük', 'ovabookpro' ),
				'TR79' => __( 'Kilis', 'ovabookpro' ),
				'TR80' => __( 'Osmaniye', 'ovabookpro' ),
				'TR81' => __( 'Düzce', 'ovabookpro' ),
			),
			'TZ' => array( // Tanzanian states.
				'TZ01' => __( 'Arusha', 'ovabookpro' ),
				'TZ02' => __( 'Dar es Salaam', 'ovabookpro' ),
				'TZ03' => __( 'Dodoma', 'ovabookpro' ),
				'TZ04' => __( 'Iringa', 'ovabookpro' ),
				'TZ05' => __( 'Kagera', 'ovabookpro' ),
				'TZ06' => __( 'Pemba North', 'ovabookpro' ),
				'TZ07' => __( 'Zanzibar North', 'ovabookpro' ),
				'TZ08' => __( 'Kigoma', 'ovabookpro' ),
				'TZ09' => __( 'Kilimanjaro', 'ovabookpro' ),
				'TZ10' => __( 'Pemba South', 'ovabookpro' ),
				'TZ11' => __( 'Zanzibar South', 'ovabookpro' ),
				'TZ12' => __( 'Lindi', 'ovabookpro' ),
				'TZ13' => __( 'Mara', 'ovabookpro' ),
				'TZ14' => __( 'Mbeya', 'ovabookpro' ),
				'TZ15' => __( 'Zanzibar West', 'ovabookpro' ),
				'TZ16' => __( 'Morogoro', 'ovabookpro' ),
				'TZ17' => __( 'Mtwara', 'ovabookpro' ),
				'TZ18' => __( 'Mwanza', 'ovabookpro' ),
				'TZ19' => __( 'Coast', 'ovabookpro' ),
				'TZ20' => __( 'Rukwa', 'ovabookpro' ),
				'TZ21' => __( 'Ruvuma', 'ovabookpro' ),
				'TZ22' => __( 'Shinyanga', 'ovabookpro' ),
				'TZ23' => __( 'Singida', 'ovabookpro' ),
				'TZ24' => __( 'Tabora', 'ovabookpro' ),
				'TZ25' => __( 'Tanga', 'ovabookpro' ),
				'TZ26' => __( 'Manyara', 'ovabookpro' ),
				'TZ27' => __( 'Geita', 'ovabookpro' ),
				'TZ28' => __( 'Katavi', 'ovabookpro' ),
				'TZ29' => __( 'Njombe', 'ovabookpro' ),
				'TZ30' => __( 'Simiyu', 'ovabookpro' ),
			),
			'LK' => array(),
			'RS' => array( // Serbian districts.
				'RS00' => _x( 'Belgrade', 'district', 'ovabookpro' ),
				'RS14' => _x( 'Bor', 'district', 'ovabookpro' ),
				'RS11' => _x( 'Braničevo', 'district', 'ovabookpro' ),
				'RS02' => _x( 'Central Banat', 'district', 'ovabookpro' ),
				'RS10' => _x( 'Danube', 'district', 'ovabookpro' ),
				'RS23' => _x( 'Jablanica', 'district', 'ovabookpro' ),
				'RS09' => _x( 'Kolubara', 'district', 'ovabookpro' ),
				'RS08' => _x( 'Mačva', 'district', 'ovabookpro' ),
				'RS17' => _x( 'Morava', 'district', 'ovabookpro' ),
				'RS20' => _x( 'Nišava', 'district', 'ovabookpro' ),
				'RS01' => _x( 'North Bačka', 'district', 'ovabookpro' ),
				'RS03' => _x( 'North Banat', 'district', 'ovabookpro' ),
				'RS24' => _x( 'Pčinja', 'district', 'ovabookpro' ),
				'RS22' => _x( 'Pirot', 'district', 'ovabookpro' ),
				'RS13' => _x( 'Pomoravlje', 'district', 'ovabookpro' ),
				'RS19' => _x( 'Rasina', 'district', 'ovabookpro' ),
				'RS18' => _x( 'Raška', 'district', 'ovabookpro' ),
				'RS06' => _x( 'South Bačka', 'district', 'ovabookpro' ),
				'RS04' => _x( 'South Banat', 'district', 'ovabookpro' ),
				'RS07' => _x( 'Srem', 'district', 'ovabookpro' ),
				'RS12' => _x( 'Šumadija', 'district', 'ovabookpro' ),
				'RS21' => _x( 'Toplica', 'district', 'ovabookpro' ),
				'RS05' => _x( 'West Bačka', 'district', 'ovabookpro' ),
				'RS15' => _x( 'Zaječar', 'district', 'ovabookpro' ),
				'RS16' => _x( 'Zlatibor', 'district', 'ovabookpro' ),
				'RS25' => _x( 'Kosovo', 'district', 'ovabookpro' ),
				'RS26' => _x( 'Peć', 'district', 'ovabookpro' ),
				'RS27' => _x( 'Prizren', 'district', 'ovabookpro' ),
				'RS28' => _x( 'Kosovska Mitrovica', 'district', 'ovabookpro' ),
				'RS29' => _x( 'Kosovo-Pomoravlje', 'district', 'ovabookpro' ),
				'RSKM' => _x( 'Kosovo-Metohija', 'district', 'ovabookpro' ),
				'RSVO' => _x( 'Vojvodina', 'district', 'ovabookpro' ),
			),
			'RW' => array(),
			'SE' => array(),
			'UA' => array( // Ukrainian oblasts. https://github.com/unicode-org/cldr/blob/release-42/common/subdivisions/en.xml#L5243.
				'UA05' => __( 'Vinnychchyna', 'ovabookpro' ),
				'UA07' => __( 'Volyn', 'ovabookpro' ),
				'UA09' => __( 'Luhanshchyna', 'ovabookpro' ),
				'UA12' => __( 'Dnipropetrovshchyna', 'ovabookpro' ),
				'UA14' => __( 'Donechchyna', 'ovabookpro' ),
				'UA18' => __( 'Zhytomyrshchyna', 'ovabookpro' ),
				'UA21' => __( 'Zakarpattia', 'ovabookpro' ),
				'UA23' => __( 'Zaporizhzhya', 'ovabookpro' ),
				'UA26' => __( 'Prykarpattia', 'ovabookpro' ),
				'UA30' => __( 'Kyiv', 'ovabookpro' ),
				'UA32' => __( 'Kyivshchyna', 'ovabookpro' ),
				'UA35' => __( 'Kirovohradschyna', 'ovabookpro' ),
				'UA40' => __( 'Sevastopol', 'ovabookpro' ),
				'UA43' => __( 'Crimea', 'ovabookpro' ),
				'UA46' => __( 'Lvivshchyna', 'ovabookpro' ),
				'UA48' => __( 'Mykolayivschyna', 'ovabookpro' ),
				'UA51' => __( 'Odeshchyna', 'ovabookpro' ),
				'UA53' => __( 'Poltavshchyna', 'ovabookpro' ),
				'UA56' => __( 'Rivnenshchyna', 'ovabookpro' ),
				'UA59' => __( 'Sumshchyna', 'ovabookpro' ),
				'UA61' => __( 'Ternopilshchyna', 'ovabookpro' ),
				'UA63' => __( 'Kharkivshchyna', 'ovabookpro' ),
				'UA65' => __( 'Khersonshchyna', 'ovabookpro' ),
				'UA68' => __( 'Khmelnychchyna', 'ovabookpro' ),
				'UA71' => __( 'Cherkashchyna', 'ovabookpro' ),
				'UA74' => __( 'Chernihivshchyna', 'ovabookpro' ),
				'UA77' => __( 'Chernivtsi Oblast', 'ovabookpro' ),
			),
			'UG' => array( // Ugandan districts.
				'UG314' => __( 'Abim', 'ovabookpro' ),
				'UG301' => __( 'Adjumani', 'ovabookpro' ),
				'UG322' => __( 'Agago', 'ovabookpro' ),
				'UG323' => __( 'Alebtong', 'ovabookpro' ),
				'UG315' => __( 'Amolatar', 'ovabookpro' ),
				'UG324' => __( 'Amudat', 'ovabookpro' ),
				'UG216' => __( 'Amuria', 'ovabookpro' ),
				'UG316' => __( 'Amuru', 'ovabookpro' ),
				'UG302' => __( 'Apac', 'ovabookpro' ),
				'UG303' => __( 'Arua', 'ovabookpro' ),
				'UG217' => __( 'Budaka', 'ovabookpro' ),
				'UG218' => __( 'Bududa', 'ovabookpro' ),
				'UG201' => __( 'Bugiri', 'ovabookpro' ),
				'UG235' => __( 'Bugweri', 'ovabookpro' ),
				'UG420' => __( 'Buhweju', 'ovabookpro' ),
				'UG117' => __( 'Buikwe', 'ovabookpro' ),
				'UG219' => __( 'Bukedea', 'ovabookpro' ),
				'UG118' => __( 'Bukomansimbi', 'ovabookpro' ),
				'UG220' => __( 'Bukwa', 'ovabookpro' ),
				'UG225' => __( 'Bulambuli', 'ovabookpro' ),
				'UG416' => __( 'Buliisa', 'ovabookpro' ),
				'UG401' => __( 'Bundibugyo', 'ovabookpro' ),
				'UG430' => __( 'Bunyangabu', 'ovabookpro' ),
				'UG402' => __( 'Bushenyi', 'ovabookpro' ),
				'UG202' => __( 'Busia', 'ovabookpro' ),
				'UG221' => __( 'Butaleja', 'ovabookpro' ),
				'UG119' => __( 'Butambala', 'ovabookpro' ),
				'UG233' => __( 'Butebo', 'ovabookpro' ),
				'UG120' => __( 'Buvuma', 'ovabookpro' ),
				'UG226' => __( 'Buyende', 'ovabookpro' ),
				'UG317' => __( 'Dokolo', 'ovabookpro' ),
				'UG121' => __( 'Gomba', 'ovabookpro' ),
				'UG304' => __( 'Gulu', 'ovabookpro' ),
				'UG403' => __( 'Hoima', 'ovabookpro' ),
				'UG417' => __( 'Ibanda', 'ovabookpro' ),
				'UG203' => __( 'Iganga', 'ovabookpro' ),
				'UG418' => __( 'Isingiro', 'ovabookpro' ),
				'UG204' => __( 'Jinja', 'ovabookpro' ),
				'UG318' => __( 'Kaabong', 'ovabookpro' ),
				'UG404' => __( 'Kabale', 'ovabookpro' ),
				'UG405' => __( 'Kabarole', 'ovabookpro' ),
				'UG213' => __( 'Kaberamaido', 'ovabookpro' ),
				'UG427' => __( 'Kagadi', 'ovabookpro' ),
				'UG428' => __( 'Kakumiro', 'ovabookpro' ),
				'UG101' => __( 'Kalangala', 'ovabookpro' ),
				'UG222' => __( 'Kaliro', 'ovabookpro' ),
				'UG122' => __( 'Kalungu', 'ovabookpro' ),
				'UG102' => __( 'Kampala', 'ovabookpro' ),
				'UG205' => __( 'Kamuli', 'ovabookpro' ),
				'UG413' => __( 'Kamwenge', 'ovabookpro' ),
				'UG414' => __( 'Kanungu', 'ovabookpro' ),
				'UG206' => __( 'Kapchorwa', 'ovabookpro' ),
				'UG236' => __( 'Kapelebyong', 'ovabookpro' ),
				'UG126' => __( 'Kasanda', 'ovabookpro' ),
				'UG406' => __( 'Kasese', 'ovabookpro' ),
				'UG207' => __( 'Katakwi', 'ovabookpro' ),
				'UG112' => __( 'Kayunga', 'ovabookpro' ),
				'UG407' => __( 'Kibaale', 'ovabookpro' ),
				'UG103' => __( 'Kiboga', 'ovabookpro' ),
				'UG227' => __( 'Kibuku', 'ovabookpro' ),
				'UG432' => __( 'Kikuube', 'ovabookpro' ),
				'UG419' => __( 'Kiruhura', 'ovabookpro' ),
				'UG421' => __( 'Kiryandongo', 'ovabookpro' ),
				'UG408' => __( 'Kisoro', 'ovabookpro' ),
				'UG305' => __( 'Kitgum', 'ovabookpro' ),
				'UG319' => __( 'Koboko', 'ovabookpro' ),
				'UG325' => __( 'Kole', 'ovabookpro' ),
				'UG306' => __( 'Kotido', 'ovabookpro' ),
				'UG208' => __( 'Kumi', 'ovabookpro' ),
				'UG333' => __( 'Kwania', 'ovabookpro' ),
				'UG228' => __( 'Kween', 'ovabookpro' ),
				'UG123' => __( 'Kyankwanzi', 'ovabookpro' ),
				'UG422' => __( 'Kyegegwa', 'ovabookpro' ),
				'UG415' => __( 'Kyenjojo', 'ovabookpro' ),
				'UG125' => __( 'Kyotera', 'ovabookpro' ),
				'UG326' => __( 'Lamwo', 'ovabookpro' ),
				'UG307' => __( 'Lira', 'ovabookpro' ),
				'UG229' => __( 'Luuka', 'ovabookpro' ),
				'UG104' => __( 'Luwero', 'ovabookpro' ),
				'UG124' => __( 'Lwengo', 'ovabookpro' ),
				'UG114' => __( 'Lyantonde', 'ovabookpro' ),
				'UG223' => __( 'Manafwa', 'ovabookpro' ),
				'UG320' => __( 'Maracha', 'ovabookpro' ),
				'UG105' => __( 'Masaka', 'ovabookpro' ),
				'UG409' => __( 'Masindi', 'ovabookpro' ),
				'UG214' => __( 'Mayuge', 'ovabookpro' ),
				'UG209' => __( 'Mbale', 'ovabookpro' ),
				'UG410' => __( 'Mbarara', 'ovabookpro' ),
				'UG423' => __( 'Mitooma', 'ovabookpro' ),
				'UG115' => __( 'Mityana', 'ovabookpro' ),
				'UG308' => __( 'Moroto', 'ovabookpro' ),
				'UG309' => __( 'Moyo', 'ovabookpro' ),
				'UG106' => __( 'Mpigi', 'ovabookpro' ),
				'UG107' => __( 'Mubende', 'ovabookpro' ),
				'UG108' => __( 'Mukono', 'ovabookpro' ),
				'UG334' => __( 'Nabilatuk', 'ovabookpro' ),
				'UG311' => __( 'Nakapiripirit', 'ovabookpro' ),
				'UG116' => __( 'Nakaseke', 'ovabookpro' ),
				'UG109' => __( 'Nakasongola', 'ovabookpro' ),
				'UG230' => __( 'Namayingo', 'ovabookpro' ),
				'UG234' => __( 'Namisindwa', 'ovabookpro' ),
				'UG224' => __( 'Namutumba', 'ovabookpro' ),
				'UG327' => __( 'Napak', 'ovabookpro' ),
				'UG310' => __( 'Nebbi', 'ovabookpro' ),
				'UG231' => __( 'Ngora', 'ovabookpro' ),
				'UG424' => __( 'Ntoroko', 'ovabookpro' ),
				'UG411' => __( 'Ntungamo', 'ovabookpro' ),
				'UG328' => __( 'Nwoya', 'ovabookpro' ),
				'UG331' => __( 'Omoro', 'ovabookpro' ),
				'UG329' => __( 'Otuke', 'ovabookpro' ),
				'UG321' => __( 'Oyam', 'ovabookpro' ),
				'UG312' => __( 'Pader', 'ovabookpro' ),
				'UG332' => __( 'Pakwach', 'ovabookpro' ),
				'UG210' => __( 'Pallisa', 'ovabookpro' ),
				'UG110' => __( 'Rakai', 'ovabookpro' ),
				'UG429' => __( 'Rubanda', 'ovabookpro' ),
				'UG425' => __( 'Rubirizi', 'ovabookpro' ),
				'UG431' => __( 'Rukiga', 'ovabookpro' ),
				'UG412' => __( 'Rukungiri', 'ovabookpro' ),
				'UG111' => __( 'Sembabule', 'ovabookpro' ),
				'UG232' => __( 'Serere', 'ovabookpro' ),
				'UG426' => __( 'Sheema', 'ovabookpro' ),
				'UG215' => __( 'Sironko', 'ovabookpro' ),
				'UG211' => __( 'Soroti', 'ovabookpro' ),
				'UG212' => __( 'Tororo', 'ovabookpro' ),
				'UG113' => __( 'Wakiso', 'ovabookpro' ),
				'UG313' => __( 'Yumbe', 'ovabookpro' ),
				'UG330' => __( 'Zombo', 'ovabookpro' ),
			),
			'UM' => array(
				'81' => __( 'Baker Island', 'ovabookpro' ),
				'84' => __( 'Howland Island', 'ovabookpro' ),
				'86' => __( 'Jarvis Island', 'ovabookpro' ),
				'67' => __( 'Johnston Atoll', 'ovabookpro' ),
				'89' => __( 'Kingman Reef', 'ovabookpro' ),
				'71' => __( 'Midway Atoll', 'ovabookpro' ),
				'76' => __( 'Navassa Island', 'ovabookpro' ),
				'95' => __( 'Palmyra Atoll', 'ovabookpro' ),
				'79' => __( 'Wake Island', 'ovabookpro' ),
			),
			'US' => array( // U.S. states.
				'AL' => __( 'Alabama', 'ovabookpro' ),
				'AK' => __( 'Alaska', 'ovabookpro' ),
				'AZ' => __( 'Arizona', 'ovabookpro' ),
				'AR' => __( 'Arkansas', 'ovabookpro' ),
				'CA' => __( 'California', 'ovabookpro' ),
				'CO' => __( 'Colorado', 'ovabookpro' ),
				'CT' => __( 'Connecticut', 'ovabookpro' ),
				'DE' => __( 'Delaware', 'ovabookpro' ),
				'DC' => __( 'District Of Columbia', 'ovabookpro' ),
				'FL' => __( 'Florida', 'ovabookpro' ),
				'GA' => _x( 'Georgia', 'US state of Georgia', 'ovabookpro' ),
				'HI' => __( 'Hawaii', 'ovabookpro' ),
				'ID' => __( 'Idaho', 'ovabookpro' ),
				'IL' => __( 'Illinois', 'ovabookpro' ),
				'IN' => __( 'Indiana', 'ovabookpro' ),
				'IA' => __( 'Iowa', 'ovabookpro' ),
				'KS' => __( 'Kansas', 'ovabookpro' ),
				'KY' => __( 'Kentucky', 'ovabookpro' ),
				'LA' => __( 'Louisiana', 'ovabookpro' ),
				'ME' => __( 'Maine', 'ovabookpro' ),
				'MD' => __( 'Maryland', 'ovabookpro' ),
				'MA' => __( 'Massachusetts', 'ovabookpro' ),
				'MI' => __( 'Michigan', 'ovabookpro' ),
				'MN' => __( 'Minnesota', 'ovabookpro' ),
				'MS' => __( 'Mississippi', 'ovabookpro' ),
				'MO' => __( 'Missouri', 'ovabookpro' ),
				'MT' => __( 'Montana', 'ovabookpro' ),
				'NE' => __( 'Nebraska', 'ovabookpro' ),
				'NV' => __( 'Nevada', 'ovabookpro' ),
				'NH' => __( 'New Hampshire', 'ovabookpro' ),
				'NJ' => __( 'New Jersey', 'ovabookpro' ),
				'NM' => __( 'New Mexico', 'ovabookpro' ),
				'NY' => __( 'New York', 'ovabookpro' ),
				'NC' => __( 'North Carolina', 'ovabookpro' ),
				'ND' => __( 'North Dakota', 'ovabookpro' ),
				'OH' => __( 'Ohio', 'ovabookpro' ),
				'OK' => __( 'Oklahoma', 'ovabookpro' ),
				'OR' => __( 'Oregon', 'ovabookpro' ),
				'PA' => __( 'Pennsylvania', 'ovabookpro' ),
				'RI' => __( 'Rhode Island', 'ovabookpro' ),
				'SC' => __( 'South Carolina', 'ovabookpro' ),
				'SD' => __( 'South Dakota', 'ovabookpro' ),
				'TN' => __( 'Tennessee', 'ovabookpro' ),
				'TX' => __( 'Texas', 'ovabookpro' ),
				'UT' => __( 'Utah', 'ovabookpro' ),
				'VT' => __( 'Vermont', 'ovabookpro' ),
				'VA' => __( 'Virginia', 'ovabookpro' ),
				'WA' => __( 'Washington', 'ovabookpro' ),
				'WV' => __( 'West Virginia', 'ovabookpro' ),
				'WI' => __( 'Wisconsin', 'ovabookpro' ),
				'WY' => __( 'Wyoming', 'ovabookpro' ),
				'AA' => __( 'Armed Forces (AA)', 'ovabookpro' ),
				'AE' => __( 'Armed Forces (AE)', 'ovabookpro' ),
				'AP' => __( 'Armed Forces (AP)', 'ovabookpro' ),
			),
			'UY' => array( // Uruguayan states.
				'UY-AR' => __( 'Artigas', 'ovabookpro' ),
				'UY-CA' => __( 'Canelones', 'ovabookpro' ),
				'UY-CL' => __( 'Cerro Largo', 'ovabookpro' ),
				'UY-CO' => __( 'Colonia', 'ovabookpro' ),
				'UY-DU' => __( 'Durazno', 'ovabookpro' ),
				'UY-FS' => __( 'Flores', 'ovabookpro' ),
				'UY-FD' => __( 'Florida', 'ovabookpro' ),
				'UY-LA' => __( 'Lavalleja', 'ovabookpro' ),
				'UY-MA' => __( 'Maldonado', 'ovabookpro' ),
				'UY-MO' => __( 'Montevideo', 'ovabookpro' ),
				'UY-PA' => __( 'Paysandú', 'ovabookpro' ),
				'UY-RN' => __( 'Río Negro', 'ovabookpro' ),
				'UY-RV' => __( 'Rivera', 'ovabookpro' ),
				'UY-RO' => __( 'Rocha', 'ovabookpro' ),
				'UY-SA' => __( 'Salto', 'ovabookpro' ),
				'UY-SJ' => __( 'San José', 'ovabookpro' ),
				'UY-SO' => __( 'Soriano', 'ovabookpro' ),
				'UY-TA' => __( 'Tacuarembó', 'ovabookpro' ),
				'UY-TT' => __( 'Treinta y Tres', 'ovabookpro' ),
			),
			'VE' => array( // Venezuelan states.
				'VE-A' => __( 'Capital', 'ovabookpro' ),
				'VE-B' => __( 'Anzoátegui', 'ovabookpro' ),
				'VE-C' => __( 'Apure', 'ovabookpro' ),
				'VE-D' => __( 'Aragua', 'ovabookpro' ),
				'VE-E' => __( 'Barinas', 'ovabookpro' ),
				'VE-F' => __( 'Bolívar', 'ovabookpro' ),
				'VE-G' => __( 'Carabobo', 'ovabookpro' ),
				'VE-H' => __( 'Cojedes', 'ovabookpro' ),
				'VE-I' => __( 'Falcón', 'ovabookpro' ),
				'VE-J' => __( 'Guárico', 'ovabookpro' ),
				'VE-K' => __( 'Lara', 'ovabookpro' ),
				'VE-L' => __( 'Mérida', 'ovabookpro' ),
				'VE-M' => __( 'Miranda', 'ovabookpro' ),
				'VE-N' => __( 'Monagas', 'ovabookpro' ),
				'VE-O' => __( 'Nueva Esparta', 'ovabookpro' ),
				'VE-P' => __( 'Portuguesa', 'ovabookpro' ),
				'VE-R' => __( 'Sucre', 'ovabookpro' ),
				'VE-S' => __( 'Táchira', 'ovabookpro' ),
				'VE-T' => __( 'Trujillo', 'ovabookpro' ),
				'VE-U' => __( 'Yaracuy', 'ovabookpro' ),
				'VE-V' => __( 'Zulia', 'ovabookpro' ),
				'VE-W' => __( 'Federal Dependencies', 'ovabookpro' ),
				'VE-X' => __( 'La Guaira (Vargas)', 'ovabookpro' ),
				'VE-Y' => __( 'Delta Amacuro', 'ovabookpro' ),
				'VE-Z' => __( 'Amazonas', 'ovabookpro' ),
			),
			'VN' => array(),
			'YT' => array(),
			'ZA' => array( // South African states.
				'EC'  => __( 'Eastern Cape', 'ovabookpro' ),
				'FS'  => __( 'Free State', 'ovabookpro' ),
				'GP'  => __( 'Gauteng', 'ovabookpro' ),
				'KZN' => __( 'KwaZulu-Natal', 'ovabookpro' ),
				'LP'  => __( 'Limpopo', 'ovabookpro' ),
				'MP'  => __( 'Mpumalanga', 'ovabookpro' ),
				'NC'  => __( 'Northern Cape', 'ovabookpro' ),
				'NW'  => __( 'North West', 'ovabookpro' ),
				'WC'  => __( 'Western Cape', 'ovabookpro' ),
			),
			'ZM' => array( // Zambian provinces.
				'ZM-01' => __( 'Western', 'ovabookpro' ),
				'ZM-02' => __( 'Central', 'ovabookpro' ),
				'ZM-03' => __( 'Eastern', 'ovabookpro' ),
				'ZM-04' => __( 'Luapula', 'ovabookpro' ),
				'ZM-05' => __( 'Northern', 'ovabookpro' ),
				'ZM-06' => __( 'North-Western', 'ovabookpro' ),
				'ZM-07' => __( 'Southern', 'ovabookpro' ),
				'ZM-08' => __( 'Copperbelt', 'ovabookpro' ),
				'ZM-09' => __( 'Lusaka', 'ovabookpro' ),
				'ZM-10' => __( 'Muchinga', 'ovabookpro' ),
			),
		);
	}
}

function obp_download_send_headers($filename) {
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download 
    header('Content-type: text/csv; charset=UTF-8');
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

function obp_array2csv(array &$array){
	if (count($array) == 0) {
		return null;
	}
	ob_start();
	$df = fopen("php://output", 'w');
	foreach ($array as $row) {
		fputcsv( $df, array_map('html_entity_decode', $row) );
	}
	fclose($df); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	return ob_get_clean();
}

function obp_round_number( $number ){
	$decial = obp_get_price_num_decimals();
	return round($number, $decial);
}

if ( ! function_exists('obp_get_our_work_url') ) {
	function obp_get_our_work_url( $permalink = '' ){
		$endpoint = OBP()->settings->endpoint->get('our_work', 'our-work');
		$value = '';
		if ( ! $permalink ) {
			$permalink = get_permalink();
		}

		// Map endpoint to options.
		$query_vars = OBP()->endpoint->get_query_vars();
		$endpoint   = ! empty( $query_vars[ $endpoint ] ) ? $query_vars[ $endpoint ] : $endpoint;

		if ( get_option( 'permalink_structure' ) ) {
			if ( strstr( $permalink, '?' ) ) {
				$query_string 	= '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
				$permalink    	= current( explode( '?', $permalink ) );
			} else {
				$query_string = '';
			}
			$url = trailingslashit( $permalink );

			if ( $value ) {
				$url .= trailingslashit( $endpoint ) . user_trailingslashit( $value );
			} else {
				$url .= user_trailingslashit( $endpoint );
			}

			$url .= $query_string;
		} else {
			$url = add_query_arg( 'our_work', $endpoint, $permalink );
		}

		return apply_filters( 'obp_get_our_work_url', $url, $endpoint, $value, $permalink );
	}
}

if ( ! function_exists( 'obp_check_business_our_work' ) ) {
	function obp_check_business_our_work(){
		global $wp;
		$result = false;
		if ( isset( $_GET['our_work'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$result = true;
		} else {
			$matched_query = $wp->matched_query;
			parse_str($matched_query, $queryArray);
			if ( isset( $queryArray['our_work'] ) && ! empty( $queryArray['our_work'] ) ) {
				$result = true;
			}
		}
		return apply_filters( 'obp_check_business_our_work', $result );
	}
}

if ( ! function_exists('obp_get_datepicker_language') ) {
	function obp_get_datepicker_language(){
		$lang = OBP()->settings->general->get('datepicker_language','default');
		switch ( $lang ) {
			case 'ar-kw':
			case 'ar-ly':
			case 'ar-ma':
			case 'ar-sa':
			case 'ar-tn':
				$lang = 'ar';
				break;
			case 'ca':
				$lang = 'cat';
			break;
			case 'de-at':
				$lang = 'de';
			break;

			case 'en-au':
			case 'en-gb':
			case 'en-nz':
				$lang = 'default';
			break;
			case 'es-us':
				$lang = 'es';
			break;

			case 'fr-ca':
			case 'fr-ch':
				$lang = 'fr';
			break;

			default:
				break;
		}
		return apply_filters( 'obp_get_datepicker_language', $lang );
	}
}

if ( ! function_exists( 'obp_get_google_map_lang' ) ) {
	function obp_get_google_map_lang(){
		return apply_filters( 'obp_get_google_map_lang', 'en' );
	}
}

if ( ! function_exists( 'obp_get_my_business_endpoint' ) ) {
	function obp_get_my_business_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('my_business', 'my-business');
		return apply_filters( 'obp_get_my_business_endpoint', $endpoint );
	}
}

if ( ! function_exists( 'obp_get_my_wallet_endpoint' ) ) {
	function obp_get_my_wallet_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('my_wallet', 'my-wallet');
		return apply_filters( 'obp_get_my_wallet_endpoint', $endpoint );
	}
}

if ( ! function_exists( 'obp_get_manage_booking_endpoint' ) ) {
	function obp_get_manage_booking_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_booking', 'manage-booking');
		return apply_filters( 'obp_get_manage_booking_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_manage_service_endpoint') ) {
	function obp_get_manage_service_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_service', 'manage-service' );
		return apply_filters( 'obp_get_manage_service_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_edit_service_endpoint') ) {
	function obp_get_edit_service_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('edit_service', 'edit-service' );
		return apply_filters( 'obp_get_edit_service_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_manage_type_endpoint') ) {
	function obp_get_manage_type_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_type', 'manage-type' );
		return apply_filters( 'obp_get_manage_type_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_manage_plan_endpoint') ) {
	function obp_get_manage_plan_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_plan', 'manage-plan' );
		return apply_filters( 'obp_get_manage_plan_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_manage_staff_endpoint') ) {
	function obp_get_manage_staff_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_staff', 'manage-staff' );
		return apply_filters( 'obp_get_manage_staff_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_edit_staff_endpoint') ) {
	function obp_get_edit_staff_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('edit_staff', 'edit-staff' );
		return apply_filters( 'obp_get_edit_staff_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_manage_coupon_endpoint') ) {
	function obp_get_manage_coupon_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_coupon', 'manage-coupon' );
		return apply_filters( 'obp_get_manage_coupon_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_edit_coupon_endpoint') ) {
	function obp_get_edit_coupon_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('edit_coupon', 'edit-coupon' );
		return apply_filters( 'obp_get_edit_coupon_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_overall_schedule_endpoint') ) {
	function obp_get_overall_schedule_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('overall_schedule', 'overall-schedule' );
		return apply_filters( 'obp_get_overall_schedule_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_staff_schedule_endpoint') ) {
	function obp_get_staff_schedule_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('staff_schedule', 'staff-schedule' );
		return apply_filters( 'obp_get_staff_schedule_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_manage_role_endpoint') ) {
	function obp_get_manage_role_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('manage_role', 'manage-role' );
		return apply_filters( 'obp_get_manage_role_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_my_booking_endpoint') ) {
	function obp_get_my_booking_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('my_booking', 'my-booking' );
		return apply_filters( 'obp_get_my_booking_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_our_work_endpoint') ) {
	function obp_get_our_work_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('our_work', 'our-work' );
		return apply_filters( 'obp_get_our_work_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_my_wishlist_endpoint') ) {
	function obp_get_my_wishlist_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('my_wishlist', 'my-wishlist' );
		return apply_filters( 'obp_get_my_wishlist_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_my_profile_endpoint') ) {
	function obp_get_my_profile_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('my_profile', 'my-profile' );
		return apply_filters( 'obp_get_my_profile_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_get_logout_endpoint') ) {
	function obp_get_logout_endpoint(){
		$endpoint = OBP()->settings->endpoint->get('logout', 'logout' );
		return apply_filters( 'obp_get_logout_endpoint', $endpoint );
	}
}

if ( ! function_exists('obp_generate_order_key') ){
	function obp_generate_order_key( $length = 12, $special_chars = true, $extra_special_chars = false ){
		return apply_filters( 'obp_generate_order_key', wp_generate_password( $length, $special_chars, $extra_special_chars ) );
	}
}

if ( ! function_exists( 'obp_get_thank_url_with_key' ) ) {
	function obp_get_thank_url_with_key( $key = '' ){
		$url = add_query_arg( 'key', $key, obp_thank_you_url() );
		return apply_filters( 'obp_get_thank_url_with_key', $url );
	}
}

if ( ! function_exists('obp_translate_string') ) {
	function obp_translate_string( $string , $domain, $name, $language_code = null ){
		return apply_filters( 'obp_translate_string', $string , $domain, $name, $language_code = null );
	}
}

if ( ! function_exists('obp_get_current_language') ) {
	function obp_get_current_language(){
		$cur_lang = get_locale();
		return apply_filters( 'obp_get_current_language', $cur_lang );
	}
}

if ( ! function_exists('obp_get_default_language') ) {
	function obp_get_default_language(){
		$default_lang = get_locale();

		return apply_filters( 'obp_get_default_language', $default_lang );
	}
}

if ( ! function_exists( 'obp_get_type' ) ) {
	function obp_get_type( $type_id = null ){
		return new BookPro\Type\OBP_Type_Item( $type_id );
	}
}

/**
 * permission upload file or Admin
 * @return true, false
 */

if ( ! function_exists('obp_can_upload_files') ) {
	function obp_can_upload_files(){
		$upload_files = ( current_user_can( 'upload_files' ) || current_user_can( 'administrator' ) );
		return apply_filters( 'obp_can_upload_files', $upload_files );
	}
}

if ( ! function_exists('obp_get_delete_account_subject') ) {
	function obp_get_delete_account_subject(){
		$subject = OBP()->settings->mail->get( 'delete_account_subject', __( 'Delete Account', 'ovabookpro' ) );
		return apply_filters( 'obp_get_delete_account_subject', $subject );
	}
}

if ( ! function_exists('obp_get_delete_account_email_content') ) {
	function obp_get_delete_account_email_content(){
		$email_content = OBP()->settings->mail->get( 'delete_account_email_content', __( 'User information [user_info]<br />Reason [reason]', 'ovabookpro' ) );
		return apply_filters( 'obp_get_delete_account_email_content', $email_content );
	}
}

if ( ! function_exists('obp_get_new_order_subject') ) {
	function obp_get_new_order_subject(){
		$subject = OBP()->settings->mail->get('new_order_subject', esc_html__( 'Booking Success', 'ovabookpro' ) );
		return apply_filters( 'obp_get_new_order_subject', $subject );
	}
}

if ( ! function_exists('obp_get_withdraw_request_subject') ) {
	function obp_get_withdraw_request_subject(){
		$subject = OBP()->settings->mail->get('withdraw_request_subject', esc_html__( 'Withdrawal Request', 'ovabookpro' ) );
		return apply_filters( 'obp_get_withdraw_request_subject', $subject );
	}
}

if ( ! function_exists('obp_get_withdraw_request_email_content') ) {
	function obp_get_withdraw_request_email_content(){
		$email_content = OBP()->settings->mail->get('withdraw_request_email_content', __( "Name: [obp_name]\r\nAmount: [obp_amount]\r\nWithdraw Date: [obp_withdraw_date]\r\nPayout Method: [obp_payout_method]\r\nPayout Status: [obp_payout_status]", "ovabookpro" ) );
		return apply_filters( 'obp_get_withdraw_request_email_content', $email_content );
	}
}

if ( ! function_exists('obp_get_withdraw_success_email_content') ) {
	function obp_get_withdraw_success_email_content(){
		$email_content = OBP()->settings->mail->get('withdraw_success_email_content', __( "Name: [obp_name]\r\nAmount: [obp_amount]\r\nWithdraw Date: [obp_withdraw_date]\r\nPayout Method: [obp_payout_method]\r\nPayout Status: [obp_payout_status]", "ovabookpro" ) );
		return apply_filters( 'obp_get_withdraw_success_email_content', $email_content );
	}
}

if ( ! function_exists('obp_get_withdraw_success_subject') ) {
	function obp_get_withdraw_success_subject(){
		$subject = OBP()->settings->mail->get('withdraw_success_subject', esc_html__( 'Successful Withdrawal', 'ovabookpro' ) );
		return apply_filters( 'obp_get_withdraw_success_subject', $subject );
	}
}

if ( ! function_exists('obp_get_withdraw_cancelled_subject') ) {
	function obp_get_withdraw_cancelled_subject(){
		$subject = OBP()->settings->mail->get('withdraw_cancelled_subject', esc_html__( 'Withdrawal Cancelled', 'ovabookpro' ) );
		return apply_filters( 'obp_get_withdraw_cancelled_subject', $subject );
	}
}

if ( ! function_exists('obp_get_withdraw_cancelled_email_content') ) {
	function obp_get_withdraw_cancelled_email_content(){
		$subject = OBP()->settings->mail->get('withdraw_cancelled_email_content', __( "Name: [obp_name]\r\nAmount: [obp_amount]\r\nWithdraw Date: [obp_withdraw_date]\r\nPayout Method: [obp_payout_method]\r\nPayout Status: [obp_payout_status]", "ovabookpro" ) );
		return apply_filters( 'obp_get_withdraw_cancelled_email_content', $subject );
	}
}

if ( ! function_exists('obp_get_change_admin_subject') ) {
	function obp_get_change_admin_subject(){
		$subject = OBP()->settings->mail->get('change_admin_subject', esc_html__( 'Change Schedule', 'ovabookpro' ) );
		return apply_filters( 'obp_get_change_admin_subject', $subject );
	}
}

if ( ! function_exists('obp_get_change_admin_email_content') ) {
	function obp_get_change_admin_email_content(){
		$email_content = OBP()->settings->mail->get('change_admin_email_content', esc_html__( "The customer changed schedule of order #[booking_id].\r\nThis is new schedule\r\n[booking_schedule]", 'ovabookpro' ) );
		return apply_filters( 'obp_get_change_admin_email_content', $email_content );
	}
}

if ( ! function_exists('obp_get_change_customer_subject') ) {
	function obp_get_change_customer_subject(){
		$subject = OBP()->settings->mail->get('change_customer_subject', esc_html__( 'Change Schedule Successfully', 'ovabookpro' ) );
		return apply_filters( 'obp_get_change_customer_subject', $subject );
	}
}

if ( ! function_exists('obp_get_change_customer_email_content') ) {
	function obp_get_change_customer_email_content(){
		$email_content = OBP()->settings->mail->get('change_customer_email_content', esc_html__( "You changed schedule order #[booking_id] successfully.\r\nThis is new schedule\r\n[booking_schedule]", 'ovabookpro' ) );
		return apply_filters( 'obp_get_change_customer_email_content', $email_content );
	}
}

if ( ! function_exists('obp_get_cancel_admin_subject') ) {
	function obp_get_cancel_admin_subject(){
		$subject = OBP()->settings->mail->get('cancel_admin_subject', esc_html__( 'Cancel Booking', 'ovabookpro' ) );
		return apply_filters( 'obp_get_cancel_admin_subject', $subject );
	}
}

if ( ! function_exists('obp_get_cancel_admin_email_content') ) {
	function obp_get_cancel_admin_email_content(){
		$email_content = OBP()->settings->mail->get('cancel_admin_email_content', esc_html__( "The customer cancelled order #[booking_id]", 'ovabookpro' ) );
		return apply_filters('obp_get_cancel_admin_email_content', $email_content );
	}
}

if ( ! function_exists('obp_get_cancel_customer_subject') ) {
	function obp_get_cancel_customer_subject(){
		$subject = OBP()->settings->mail->get('cancel_customer_subject', esc_html__( 'Cancel Booking successfully', 'ovabookpro' ) );
		return apply_filters( 'obp_get_cancel_customer_subject', $subject );
	}
}

if ( ! function_exists('obp_get_cancel_customer_email_content') ) {
	function obp_get_cancel_customer_email_content(){
		$email_content = OBP()->settings->mail->get('cancel_customer_email_content', esc_html__( "You cancelled order #[booking_id] successfully. We will refund you soon.", 'ovabookpro' ) );
		return apply_filters( 'obp_get_cancel_customer_email_content', $email_content );
	}
}

if ( ! function_exists( 'obp_get_enable_calendar' ) ) {
	function obp_get_enable_calendar(){
		$enable = OBP()->settings->general->get('google_calendar_enable', 'no');
		return apply_filters( 'obp_get_enable_calendar', $enable );
	}
}

if ( ! function_exists('obp_google_calendar_enabled') ) {
	function obp_google_calendar_enabled(){
		$enable = obp_get_enable_calendar();
		$result = $enable == 'yes' ? true : false;
		return apply_filters( 'obp_google_calendar_enabled', $result );
	}
}

if ( ! function_exists('obp_get_google_client_id') ) {
	function obp_get_google_client_id(){
		$client_id = OBP()->settings->general->get('google_client_id');
		return apply_filters( 'obp_get_google_client_id', $client_id );
	}
}

if ( ! function_exists('obp_get_google_api_key') ) {
	function obp_get_google_api_key(){
		$api_key = OBP()->settings->general->get('google_api_key');
		return apply_filters( 'obp_get_google_api_key', $api_key );
	}
}

if ( ! function_exists('obp_google_calendar_is_setup_complete') ) {
	function obp_google_calendar_is_setup_complete(){
		$client_id 	= obp_get_google_client_id();
		$api_key 	= obp_get_google_api_key();
		$result 	= $client_id && $api_key;
		return apply_filters( 'obp_google_calendar_is_setup_complete', $result );
	}
}

if ( ! function_exists('obp_get_weekday') ) {
	function obp_get_weekday(){
		$weekday = array(
			'sunday',
			'monday',
			'tuesday',
			'wednesday',
			'thursday',
			'friday',
			'saturday',
		);
		return apply_filters( 'obp_get_weekday', $weekday );
	}
}
