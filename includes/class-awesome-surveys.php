<?php

class Awesome_Surveys {

 public function __construct() {

  if ( class_exists( 'Awesome_Surveys_Admin' ) ) {
   $this->register_post_type();
  }
 }

 private function register_post_type() {
  $awesome_surveys_admin = new Awesome_Surveys_Admin;
  $args = array(
   'label' => _( 'Awesome Surveys', 'awesome-surveys' ),
   'labels' => array(
    'name' => _( 'Surveys', 'awesome-surveys' ),
    'singular_name' => _( 'Survey', 'awesome-surveys' ),
    'menu_name' => _( 'My Surveys', 'awesome-surveys' ),
    'name_admin_bar' => _( 'Survey', 'awesome-surveys' ),
    'add_new' => _( 'New Survey', 'awesome-surveys' ),
    'new_item' => _( 'New Survey', 'awesome-surveys' ),
    'add_new_item' => _( 'Add New Survey', 'awesome-surveys' ),
    ),
   'description' => _( 'Surveys for your site', 'awesome-surveys' ),
   'public' => true,
   'exclude_from_search' => true,
   'publicly_queryable' => false,
   'show_ui' => true,
   'show_in_nav_menus' => false,
   'show_in_menu' => true,
   'show_in_admin_bar' => false,
   'supports' => array(
    'title',
    ),
   'register_meta_box_cb' => array( $awesome_surveys_admin, 'survey_editor' ),
   'rewrite' => false,
   );
  register_post_type( 'awesome-surveys', $args );
 }
}

new Awesome_Surveys;