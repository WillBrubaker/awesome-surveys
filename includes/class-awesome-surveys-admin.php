<?php

class Awesome_Surveys_Admin {

 public function __construct() {
  $actions = array(
   'admin_menu' => array( 'admin_menu', 10, 1 ),
   'save_post' => array( 'save_post', 10, 2 ),
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
  add_meta_box( 'thank-you-message', __( 'General Survey Options', 'awesome-surveys' ), array( $this, 'general_survey_options' ), 'awesome-surveys', 'normal', 'core' );
 }

 public function survey_builder() {
  echo $this->render_element_selector();
  echo $this->create_survey_form();
 }

 private function create_survey_form() {

  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
  }

  //$form = new FormOverrides( 'survey-manager' );
  //$form->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
  //$form->addElement( new Element_Textbox( __( 'Survey Name:', 'awesome-surveys' ), 'survey_name', array( 'required' => 1 ) ) );
  //$form->addElement( new Element_Hidden( 'action', 'create_survey' ) );

  //$form->addElement( new Element_HTML( '<div class="create_holder">') );
  //$form->addElement( new Element_Button( __( 'Start Building', 'awesome-surveys' ), 'submit', array( 'class' => 'button-primary' ) ) );
  //$form->addElement( new Element_HTML( '</div>') );

  //$output = '
  //    <div class="overlay"><span class="preloader"></span></div>
  //    <div class="create half">';
  //$output = $form->render( true );
     $nonce = wp_create_nonce( 'create-survey' );
     $form = new FormOverrides( 'new-elements' );
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
     $form->addElement( new Element_HTML( '<div>' ) );
     $form->addElement( new Element_Hidden( 'create_survey_nonce', $nonce ) );
     $form->addElement( new Element_Button( __( 'Add Question', 'awesome-surveys' ), 'submit', array( 'class' => 'button-primary' ) ) );
     $form->addElement( new Element_HTML( '</div>' ) );
     $output = $form->render( true );

  //$output .= '<input type="hidden" name="post_content" value="this is some post content that I put here because fuck">';
  return $output;
 }

 /**
  * Renders a dropdown select element with options
  * that coincide with the pfbc form builder class
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 private function render_element_selector() {
  return null;
  $types = array(
   'select...' => '',
   'text' => 'Element_Textbox',
   'email' => 'Element_Email',
   'number' => 'Element_Number',
   'dropdown selection' => 'Element_Select',
   'radio' => 'Element_Radio',
   'checkbox' => 'Element_Checkbox',
   'textarea' => 'Element_Textarea'
  );
  $survey_id = ( isset( $_POST['survey_id'] ) ) ? intval( $_POST['survey_id'] ) : -1;
  $html = '<input type="hidden" name="survey_name" value="' . esc_html( stripslashes( $_POST['survey_name'] ) ) . '" data-id="' . sanitize_title( stripslashes( $_POST['survey_name'] ) ) . '">';
  $html .= '<input type="hidden" name="survey_id" value="' . $survey_id . '" data-id="' . sanitize_title( stripslashes( $_POST['survey_name'] ) ) . '">';
  $html .= '<div id="new-element-selector"><span>' . __( 'Add a question to your survey:', 'awesome-surveys' ) . '</span><label>' . __( 'Select Field Type:', 'awesome-surveys' ) . '<br><select name="options[type]" class="type-selector">';
  foreach ( $types as $type => $pfbc_method ) {
   $html .= '<option value="' . $pfbc_method . '">' . $type . '</option>';
  }
  $html .= '</select></label></div>';
  return $html;
 }

 public function general_survey_options() {
  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
  }
  global $post;
  $form = new FormOverrides( 'general-survey-options' );
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
     $form->render();
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
}