<?php

class Awesome_Surveys_Admin extends Awesome_Surveys {



 public function __construct() {

  $actions = array(
   'admin_menu' => array( 'admin_menu', 10, 1 ),
   'save_post' => array( 'save_post', 10, 2 ),
   'admin_enqueue_scripts' => array( 'admin_enqueue_scripts', 10, 1 ),
   'admin_init' => array( 'init', 10, 0 ),
   );

  foreach ( $actions as $action => $args ) {
   add_action( $action, array( $this, $args[0] ), $args[1], $args[2] );
  }

  $filters = array(
   'survey_auth_options' => array( 'default_auth_methods', 10, 1 ),
   );

  foreach ( $actions as $key => $action ) {
   add_action( $key, array( $this, $action[0] ), $action[1], $action[2] );
  }
  foreach ( $filters as $key => $filter ) {
   add_filter( $key, array( $this, $filter[0] ), $filter[1], $filter[2] );
  }
  $this->register_post_type();
 }

 public function admin_menu() {
 }

 public function survey_editor() {

  add_meta_box( 'create_survey', __( 'Create Survey', 'awesome-surveys' ), array( $this, 'survey_builder' ), 'awesome-surveys', 'normal', 'core' );
  add_meta_box( 'general-survey-options-metabox', __( 'General Survey Options', 'awesome-surveys' ), array( $this, 'general_survey_options' ), 'awesome-surveys', 'normal', 'core' );
 }

 public function survey_builder() {
  wp_enqueue_script( 'awesome-surveys-admin-script' );
  wp_enqueue_style( 'awesome-surveys-admin-style' );
  include_once( 'views/html-survey-builder.php' );
 }

 public function general_survey_options() {
  include_once( 'views/html-survey-options-general.php' );
 }

 public function save_post( $post_id, $post ) {

  if (  ! isset( $_POST['create_survey_nonce'] ) || ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) ) {
   return;
  }
  if ( isset( $_POST['existing_elements'] ) ) {
   $existing_elements = $_POST['existing_elements'];
   update_post_meta( $post_id, 'existing_elements', $existing_elements );
  }
  if ( isset( $_POST['meta']['survey_auth_method'] ) ) {
   update_post_meta( $post_id, 'survey_auth_method', absint( $_POST['meta']['survey_auth_method'] ) );
  }
 }

 /**
  * hooked into 'survey_auth_options' - provides the default array of authentication methods
  * @param  array  $options associative array of authentication method names
  * @return array  associative array of authentication method names
  */
 public function default_auth_methods( $options = array() ) {

  $options = array( 'login' => __( 'User must be logged in', $this->text_domain ), 'cookie' => __( 'Cookie based', $this->text_domain ), 'none' => __( 'None' ) );
  return $options;
 }

 /**
  * enqueues the necessary css/js for the admin area
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function admin_enqueue_scripts() {
  $defaults = array(
   'num_answers' => 10,
   );
  $args = apply_filters( 'wwm_as_admin_script_vars', $defaults );
  $args = wp_parse_args( $args, $defaults );
  $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
  wp_register_script( 'jquery-validation-plugin', WWM_AWESOME_SURVEYS_URL . '/js/jquery.validate.min.js', array( 'jquery' ), '1.13.0' );
  wp_enqueue_script( $this->text_domain . '-admin-script', WWM_AWESOME_SURVEYS_URL . '/js/admin-script' . $suffix . '.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-validation-plugin', 'jquery-ui-dialog', 'jquery-ui-button', 'postbox' ), $this->wwm_plugin_values['version'], true );
  wp_localize_script( $this->text_domain . '-admin-script', 'wwm_as_admin_script', $args );
  _doing_it_wrong( __METHOD__ . ' ' . __LINE__, 'dont load this on every single admin page', '4.1' );
  wp_register_style( 'normalize-css', WWM_AWESOME_SURVEYS_URL . '/css/normalize.min.css' );
  wp_register_style( 'jquery-ui-lightness', WWM_AWESOME_SURVEYS_URL . '/css/jquery-ui.min.css', array(), '1.10.13', 'all' );
  wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css', array( 'normalize-css' ) );
  wp_enqueue_style( $this->text_domain . '-admin-style', WWM_AWESOME_SURVEYS_URL . '/css/admin-style' . $suffix . '.css', array( 'jquery-ui-lightness', 'pure-forms-css' ), $this->wwm_plugin_values['version'], 'all' );

 }
}