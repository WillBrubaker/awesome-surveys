<?php
/**
 * Cleans up the database on plugin deletion
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! defined( 'ABSPATH' ) ) {
 exit();
}

$options = array( 'wwm_awesome_surveys', );

if ( ! is_multisite() ) {
 foreach ( $options as $option ) {
  delete_option( $option );
 }
} else {

 global $wpdb;
 $blog_ids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
 $original_blog_id = get_current_blog_id();
 foreach ( $blog_ids as $blog_id ) {
  switch_to_blog( $blog_id );
  foreach ( $options as $option ) {
   delete_option( $option );
  }
 }
 switch_to_blog( $original_blog_id );
}