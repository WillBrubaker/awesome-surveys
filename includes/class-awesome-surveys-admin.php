<?php

class Awesome_Surveys_Admin extends Awesome_Surveys {

 public $text_domain;

 public function __construct() {
  $this->text_domain = 'awesome-surveys';
  $actions = array(
   'admin_menu' => array( 'admin_menu', 10, 1 ),
   'save_post' => array( 'save_post', 10, 2 ),
   'admin_enqueue_scripts' => array( 'admin_enqueue_scripts', 10, 1 ),
   );

  $filters = array(
   'survey_auth_options' => array( 'default_auth_methods', 10, 1 ),
   );

  foreach ( $actions as $key => $action ) {
   add_action( $key, array( $this, $action[0] ), $action[1], $action[2] );
  }
  foreach ( $filters as $key => $filter ) {
   add_filter( $key, array( $this, $filter[0] ), $filter[1], $filter[2] );
  }
 }

 public function admin_menu() {
 }

 public function survey_editor( $post ) {
  $args = array(
   'id' => 'create_survey',
   'title' => _( 'Create Survey' ),
   'callback' => array( $this, 'survey_builder' ),
   );
  add_meta_box( 'create_survey', __( 'Create Survey', 'awesome-surveys' ), array( $this, 'survey_builder' ), 'awesome-surveys', 'normal', 'core' );
  add_meta_box( 'general-survey-options', __( 'General Survey Options', 'awesome-surveys' ), array( $this, 'general_survey_options' ), 'awesome-surveys', 'normal', 'core' );
 }

 public function survey_builder() {

  wp_enqueue_script( 'awesome-surveys-admin-script' );
  wp_enqueue_style( 'awesome-surveys-admin-style' );
  include_once( 'views/html-survey-builder.php' );
 }

 public function general_survey_options() {
  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
  }
  global $post;
  $form = new FormOverrides( 'general-survey-options' );
  $form->configure( array( 'class' => 'pure-form pure-form-stacked' ) );
  $thank_you_message = get_post_meta( $post->ID, 'thank_you_message', true );
  $message = ( empty( $thank_you_message ) ) ? __( 'Thank you for completing this survey', 'awesome-surveys' ) : $thank_you_message;
  //printf( '<textarea name="meta[thank_you_message]" style="width: %s;">%s</textarea>', '100%', $message );
  $form->addElement( new Element_Textarea( __( 'A Thank You message:', 'awesome-surveys' ), 'meta[thank_you_message]', array( 'value' => $message ) ) );
  $options = array();
     /**
      * *!!!IMPORTANT!!!*
      * If an auth method is added via the survey_auth_options, a filter must also be added
      * to return a boolean based on whether the auth method passed or not.
      * The function that outputs the survey form will check for valid authentication via
      * apply_filters( 'awesome_surveys_auth_method_{$your_method}', false )
      * If a filter does not exist for your auth method then obviously the return value is false
      * and the survey form output function will generate a null output.
      * @see  class.awesome-surveys-frontend.php.
      * When the survey is submitted, you can use do_action( 'awesome_surveys_update_' . $auth_method );
      * to do whatever needs to be done i.e. set a cookie, update some database option, etc.
      */
     $options = apply_filters( 'survey_auth_options', $options );
     $form->addElement( new Element_HTML( '<div class="ui-widget-content ui-corner-all validation field-validation"><span class="label"><p>' . __( 'To prevent people from filling the survey out multiple times you may select one of the options below', 'awesome-surveys' ) . '</p></span>' ) );
     $form->addElement( new Element_Radio( 'Validation/authentication', 'auth', $options, array( 'value' => 'none' ) ) );
     $form->addElement( new Element_HTML( '</div>' ) );
     echo $form->render( true );
 }

 public function save_post( $post_id, $post ) {
  if ( ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) ) {
   die( 'not authorized' );
  }
  if ( isset( $_POST['meta']['thank_you_message'] ) ) {
   update_post_meta( $post_id, 'thank_you_message', sanitize_text_field( $_POST['meta']['thank_you_message'] ) );
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
  wp_register_script( $this->text_domain . '-admin-script', WWM_AWESOME_SURVEYS_URL . '/js/admin-script' . $suffix . '.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-validation-plugin', 'jquery-ui-dialog', 'jquery-ui-button', 'postbox' ), $this->wwm_plugin_values['version'] );
  wp_localize_script( $this->text_domain . '-admin-script', 'wwm_as_admin_script', $args );

  wp_register_style( 'normalize-css', WWM_AWESOME_SURVEYS_URL . '/css/normalize.min.css' );
  wp_register_style( 'jquery-ui-lightness', WWM_AWESOME_SURVEYS_URL . '/css/jquery-ui.min.css', array(), '1.10.13', 'all' );
  wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css', array( 'normalize-css' ) );
  wp_register_style( $this->text_domain . '-admin-style', WWM_AWESOME_SURVEYS_URL . '/css/admin-style' . $suffix . '.css', array( 'jquery-ui-lightness', 'pure-forms-css' ), $this->wwm_plugin_values['version'], 'all' );
 }
}

$awesome_surveys = new Awesome_Surveys;