<?php
if ( ! defined( 'ABSPATH' ) ) {
								exit; // Exit if accessed directly
}

if ( isset( $_POST ) && ! empty( $_POST ) ) {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $_POST['_nonce'], 'awesome-surveys-update-options' ) ) {
		wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
	}
	global $awesome_surveys;
	$plugin_options = $awesome_surveys->options;
	error_log( "gettign plugin options\n" . print_r( $plugin_options, true ) );
	$posted_options = $_POST['options'];
	$options_to_save = wp_parse_args( $_POST['options'], $plugin_options );
	/*
	TO DO: There's got to be a better way to handle all of this
	 */
	$options_to_save['general_options']['include_css'] = absint( $options_to_save['general_options']['include_css'] );
	$options_to_save['email_options']['enable_emails'] = absint( $options_to_save['email_options']['enable_emails'] );
	$options_to_save['email_options']['enable_respondent_email'] = absint( $options_to_save['email_options']['enable_respondent_email'] );
	$options_to_save['email_options']['email_subject'] = sanitize_text_field( $options_to_save['email_options']['email_subject'] );
	$options_to_save['email_options']['mail_to'] = sanitize_email( $options_to_save['email_options']['mail_to'] );
	$options_to_save['email_options']['respondent_email_message'] = wp_filter_kses( $options_to_save['email_options']['respondent_email_message'] );
	error_log( print_r( $options_to_save, true ) );
	update_option( 'wwm_awesome_surveys_options', $options_to_save );
}