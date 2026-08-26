<?php
defined( 'ABSPATH' ) || exit;

if ( $field['html'] ) {
	echo $field['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}