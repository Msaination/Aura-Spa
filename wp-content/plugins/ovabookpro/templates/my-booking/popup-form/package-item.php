<?php defined( 'ABSPATH' ) || exit;
$package = obp_get_package( $package_id );
?>
<div class="package-item"><?php echo esc_html( $package->get_name() ) ?></div>