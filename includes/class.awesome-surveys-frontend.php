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
  $args = array(
   'survey_id' => $atts['id'],
   'name' => $surveys['surveys'][$atts['id']]['name'],
  );
  $form = $this->render_form( unserialize( $surveys['surveys'][$atts['id']]['form'] ), $args );
  return $form;
 }

 private function render_form( $form, $args )
 {

  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'answer-survey' );
  $has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
  $form_output = new FormOverrides( stripslashes( $args['name'] ) );
  $form_output->configure( array( 'class' => 'answer-survey' ) );
  $form_output->addElement( new Element_HTML( '<p>' . $args['name'] . '</p>' ) );
  $questions_count = 0;
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
   $form_output->addElement( new $method( stripslashes( $element['name'] ), 'question[' . $questions_count . ']', $options, $atts ) );
   $questions_count++;
  }
  $form_output->addElement( new Element_Hidden( 'answer_survey_nonce', $nonce ) );
  $form_output->addElement( new Element_Hidden( 'survey_id', '', array( 'value' => $args['survey_id'], ) ) );
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

  if ( ! wp_verify_nonce( $_POST['answer_survey_nonce'], 'answer-survey' ) || is_null( $_POST['survey_id'] ) ) {
   exit;
  }
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $survey = $surveys['surveys'][$_POST['survey_id']];
  if ( empty( $survey ) ) {
   exit;
  }
  $form = unserialize( $survey['form'] );
  $has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
  foreach ( $survey['responses'] as $key => $value ) {
   if ( in_array( $form[$key]['type'], $has_options ) ) {
    $count = $value['answers'][$_POST['question'][$key]] + 1;
    $survey['responses'][$key]['answers'][$_POST['question'][$key]] = $count;
   } else {
    $survey['responses'][$key]['answers'][$key][] = $_POST['question'][$key];
   }
  }
  var_dump($survey);
  $surveys['surveys'][$_POST['survey_id']] = $survey;
  update_option( 'wwm_awesome_surveys', $surveys );
  exit;
 }
}