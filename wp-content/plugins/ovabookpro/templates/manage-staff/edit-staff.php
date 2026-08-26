<?php defined( 'ABSPATH' ) || exit;

use BookPro\User\OBP_User;
use BookPro\OBP_Permission;

$vendor_id 	= OBP_User::get_vendor_id();

if ( ! OBP_Permission::is_administrator() && obp_get_vendor_id() != $vendor_id ) {
	return;
}

?>

<h1 class="obp-title">
	<?php echo esc_html( $obp_title ); ?>	
</h1>

<div class="obp-content obp-content-staff">
	<?php obp_get_template( 'manage-staff/edit-staff-form.php', $args ); ?>
</div>