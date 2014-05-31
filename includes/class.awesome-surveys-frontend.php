<?php
/**
 * @package Awesome_Surveys
 *
 */
class Awesome_Surveys_Frontend {

 public $text_domain;

 public function __construct()
 {

  $this->text_domain = 'awesome-surveys';
  add_shortcode( 'wwm_survey', array( &$this, 'wwm_survey' ) );
  add_action( 'wp_enqueue_scripts', array( &$this, 'register_scripts' ) );
 }

 public function wwm_survey( $atts )
 {

  if ( ! isset( $atts['id'] ) ) {
   return;
  }
  wp_enqueue_script( 'awesome-surveys-frontend' );
  wp_localize_script( 'awesome-surveys-frontend', 'wwm_awesome_surveys', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), ) );
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  if ( empty( $surveys ) || empty( $surveys['surveys'][$atts['id']] ) ) {
   return;
  }
  $form = $this->render_form( unserialize( $surveys['surveys'][$atts['id']]['form'] ), $surveys['surveys'][$atts['id']]['name']   );
  return $form;
 }

 private function render_form( $form, $name )
 {

  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'answer-survey' );
  $has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
  $form_output = new FormOverrides( stripslashes( $name ) );
  $form_output->configure( array( 'class' => 'answer-survey' ) );
  $form_output->addElement( new Element_HTML( '<p>' . $name . '</p>' ) );
  foreach ( $form as $element ) {
   $method = $element['type'];
   $options = array();
   $atts = array();
   if ( in_array( $method, $has_options ) ) {
    if ( isset( $element['default'] ) ) {
     $atts['value'] = $element['default'];
    }
    if ( isset( $element['validation']['required'] ) ) {
     $atts['required'] = 'required';
    }
    foreach ( $element['value'] as $key => $value ) {
     /**
      * append :pfbc to the key so that pfbc doesn't freak out
      * about numerically keyed arrays.
      */
     $options[$value . ':pfbc'] = $element['label'][$key];
    }
   } else {
    if ( isset( $element['default'] ) ) {
     $options['value'] = $element['default'];
    }
    if ( isset( $element['validation']['required'] ) ) {
     $options['required'] = 'required';
    }
   }
   $form_output->addElement( new $method( stripslashes( $element['name'] ), str_replace( '-', '_', sanitize_title( $element['name'] ) ), $options, $atts ) );
  }
  $form_output->addElement( new Element_Hidden( 'answer_survey_nonce', $nonce ) );
  $form_output->addElement( new Element_Hidden( 'action', 'answer_survey' ) );
  $form_output->addElement( new Element_Button( __( 'Submit Response', $this->text_domain ), 'submit', array( 'class' => 'button-primary', ) ) );
  return $form_output->render( true );
 }

 public function register_scripts()
 {

  wp_register_script( 'awesome-surveys-frontend', WWM_AWESOME_SURVEYS_URL .'/js/script.js', array( 'jquery' ) );
 }

 public function process_response()
 {

  if ( ! wp_verify_nonce( $_POST['answer_survey_nonce'], 'answer-survey' ) ) {
   exit;
  }
  var_dump( $_POST );
  exit;
 }
}