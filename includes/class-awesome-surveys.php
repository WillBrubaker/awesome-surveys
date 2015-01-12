<?php

class Awesome_Surveys {

 protected $wwm_plugin_values = array(
  'name' => 'Awesome_Surveys',
  'dbversion' => '1.1',
  'version' => '1.6.3',
 );

 public function __construct() {

  $actions = array(
   'init' => array( 10, 1 ),
   );
  foreach ( $actions as $action => $args ) {
   add_action( $action, array( $this, $action ), $args[0], $args[1] );
  }
 }


 public function init() {
  if ( class_exists( 'Awesome_Surveys_Admin' ) ) {
   flush_rewrite_rules();
   $this->register_post_type();
  }
 }

 private function register_post_type() {
  $awesome_surveys_admin = new Awesome_Surveys_Admin;
  $args = array(
   'label' => __( 'Awesome Surveys', 'awesome-surveys' ),
   'labels' => array(
    'name' => __( 'Surveys', 'awesome-surveys' ),
    'singular_name' => __( 'Survey', 'awesome-surveys' ),
    'menu_name' => __( 'My Surveys', 'awesome-surveys' ),
    'name_admin_bar' => __( 'Survey', 'awesome-surveys' ),
    'add_new' => __( 'New Survey', 'awesome-surveys' ),
    'new_item' => __( 'New Survey', 'awesome-surveys' ),
    'add_new_item' => __( 'Add New Survey', 'awesome-surveys' ),
    'edit_item' => __( 'Build Survey', 'awesome-surveys' ),
    ),
   'description' => __( 'Surveys for your site', 'awesome-surveys' ),
   'public' => true,
   'exclude_from_search' => true,
   'publicly_queryable' => true,
   'show_ui' => true,
   'show_in_nav_menus' => false,
   'show_in_menu' => true,
   'show_in_admin_bar' => false,
   'supports' => array(
    'title',
    ),
   'register_meta_box_cb' => array( $awesome_surveys_admin, 'survey_editor' ),
   'rewrite' => true,
   );
  register_post_type( 'awesome-surveys', $args );
 }
}