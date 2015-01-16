<?php
if ( ! defined( 'ABSPATH' ) ) {
								exit; // Exit if accessed directly
}


if ( isset( $_POST ) && ! empty( $_POST ) ) {
	error_log( print_r( $_POST, true ) );
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['_nonce'], 'awesome-surveys-update-options' ) ) {
	wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
}
}