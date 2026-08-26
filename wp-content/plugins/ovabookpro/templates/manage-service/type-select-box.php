<?php defined( 'ABSPATH' ) || exit; ?>

<option value=""><?php esc_html_e( 'Choose a type', 'ovabookpro' ); ?></option>

<?php if ( ! empty( $list_type ) ):
	
	if ( $list_type->have_posts() ) {

	while ( $list_type->have_posts() ) {
		$list_type->the_post();
		$type = obp_get_type( get_the_ID() );
		echo '<option value="'. esc_attr( $type->get_id() ) .'" '.selected( $type_id, $type->get_id(), false ).'>' . esc_html( $type->get_name() ) . '</option>';
	}
}
endif; ?>