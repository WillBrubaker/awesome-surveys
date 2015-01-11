<?php

class Awesome_Surveys_Ajax {

 public function __construct() {

  $filters = array(
   'survey_validation_elements' => array( 10, 2 ),
   'get_validation_elements_number' => array( 10, 1 ),
   'get_validation_elements_text' => array( 10, 1 ),
   'get_validation_elements_textarea' => array( 10, 1 ),
   );
  foreach ( $filters as $filter => $args ) {
   add_filter( $filter, array( $this, $filter ), $args[0], $args[1] );
  }
 }

 public function add_form_element() {
  if ( ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   exit;
  }
  $buttons = array(
  'text' => 'Element_Textbox',
  'email' => 'Element_Email',
  'number' => 'Element_Number',
  'dropdown' => 'Element_Select',
  'radio' => 'Element_Radio',
  'checkbox' => 'Element_Checkbox',
  'textarea' => 'Element_Textarea',
  );

  $html = $this->element_info_inputs( $_POST['element'] );
  echo $html;
  exit;
 }

  /**
  * generate output for some form elements so that information can be gathered
  * about the element that a user is adding to their survey
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 private function element_info_inputs( $form_element = 'Element_Textbox' ) {
  $elements = array();
  /**
   * Filter hook wwm_survey_validation_elements adds elements to the validation elements array
   * $elements is an array with keys that hope to be self-explanatory (see the $defaults array below). The 'tag' key may be
   * a bit ambiguous but should be thought of as the type of element i.e. 'input', 'select', etc...
   * 'data' aims to be a key which will add data-rule-* attributes directly to the element for use by
   * the jquery validation plugin e.g. data-rule-minlength="3", so the if the 'data' array has
   * an element with the key minlength, and that element's value is 3, the validation element
   * will have the attribute data-rule-minlength="3" appended to it (on the form output side of things, they will each be added as text inputs). Care should be taken to
   * keep the correct rules with the types of form elements where they make sense. When using this
   * filter, ensure that you specify that it takes two arguments so that type of element is passed
   * on to your filter e.g.: add_filter( 'wwm_survey_validation_elements', 'your_filter_hook', 10, 2 );
   * @see  wwm_survey_validation_elements
   * @see  https://github.com/jzaefferer/jquery-validation/blob/master/test/index.html
   */
  $validation_elements = apply_filters( 'wwm_survey_validation_elements', $elements, $form_element );
  $html = '<div class="pure-form pure-form-stacked">';
  $html .= '<label>' . __( 'The question you are asking:', 'awesome-surveys' ) . '<br><input type="text" name="options[name]" required></label>';
  if ( ! empty( $validation_elements ) ) {
   $html .= '<div class="ui-widget-content field-validation validation ui-corner-all"><h5>'. __( 'Field Validation Options', 'awesome-surveys' ) . '</h5>';
    foreach ( $validation_elements as $element ) {
     $defaults = array(
      'label_text' => null,
      'tag' => null,
      'type' => 'text',
      'name' => 'default',
      'value' => null,
      'data' => array(),
      'atts' => '',
     );
     $element = wp_parse_args( $element, $defaults );
     $atts = apply_filters( 'wwm_survey_element_atts', $element['atts'], $element );
     if ( ! is_null( $element['tag'] ) ) {
      $html .= '<label>' . $element['label_text'] . '<br><' . $element['tag'] . ' ' . ' type="' . $element['type'] . '"  value="' . $element['value'] . '" name="options[validation][' . $element['name'] . ']" ' . $atts . '></label>';
     }
     $rule_count = 0;
     if ( ! empty( $element['data'] ) && is_array( $element['data'] ) ) {
      $html .= '<span class="label">' . __( 'Advanced Validation Rules:', 'awesome-surveys' ) . '</span>';
      foreach ( $element['data'] as $rule ) {
       $defaults = array(
        'label_text' => null,
        'tag' => null,
        'type' => 'text',
        'name' => 'default',
        'value' => null,
        'atts' => '',
        'text' => '',
        'label_atts' => null,
       );
      $rule = wp_parse_args( $rule, $defaults );
      $label_atts = ( $rule['label_atts'] ) ? ' ' . $rule['label_atts'] : null;
      $html .= '<label' . $label_atts . '>' . $rule['label_text'] . '<br>';
       $can_have_options = array( 'radio', 'checkbox' );
       if ( in_array( $rule['type'], $can_have_options ) && is_array( $rule['value'] ) ) {
        foreach ( $rule['value'] as $key => $value ) {
         $html .= ( ! is_null( $value ) ) ? '<' . $rule['tag'] . ' ' . ' type="' . $rule['type'] . '"  value="' . $key . '" name="options[validation][rules][' . $rule['name'] . ']" ' . $rule['atts'] . '> ' . $value . '<br></label>' : null;
        }
       } else {
        $html .= '<' . $rule['tag'] . ' ' . ' type="' . $rule['type'] . '"  value="' . $rule['value'] . '" name="options[validation][rules][' . $rule['name'] . ']" ' . $rule['atts'] . '><br></label>';
       }
       $rule_count++;
      }
     }
    }
   $html .= '</div>';
  }
  $needs_options = array( 'radio', 'checkbox', 'dropdown' );
  if ( in_array( $form_element, $needs_options ) ) {
   $html .= '<span class="label">' . __( 'Number of answers required?', 'awesome-surveys' ) . '</span><div class="slider-wrapper"><div id="slider"></div><div class="slider-legend"></div></div><div id="options-holder">';
   $html .= $this->options_fields( array( 'num_options' => 1, 'ajax' => false ) );
   $html .= '</div>';
  }

  $html .= '<p><button class="button-primary">' . __( 'Add Question', 'awesome-surveys' ) . '</button></p>';
  $html .= '</div>';
  return $html;
 }

  /**
  * AJAX handler to generate some fields
  * for survey option inputs
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function options_fields( $args = array() ) {

  $defaults = array(
   'num_options' => ( isset( $_POST['num_options'] ) ) ? $_POST['num_options'] : 1,
   'ajax' => false,
  );
  $args = wp_parse_args( $args, $defaults );
  $html = '';
  for ( $iterations = 0; $iterations < absint( $args['num_options'] ); $iterations++ ) {
   $label = $iterations + 1;
   $html .= '<label>' . __( 'Answer', 'awesome-surveys' ) . ' ' . $label . '<br><input type="text" name="options[label][' . $iterations . ']" required></label><input type="hidden" name="options[value][' . $iterations . ']" value="' . $iterations . '"><label>' . __( 'default?', 'awesome-surveys' ) . '<br><input type="radio" name="options[default]" value="' . $iterations . '"></label>';
  }
  return $html;
 }

 public function echo_options_fields() {
  $data = array( $this->options_fields() );
  wp_send_json_success( $data );
 }

 /**
  * Outputs some elements related to data validation for the element being added to the survey form.
  * A dynamic filter hook is provided to enable the addition of validation elements based on the type
  * of element being added to the survey form. get_validation_elements_{$type}. See get_validation_elements_number
  * for an example.
  * @param  array  $elements an array of elements
  * @param  string $type     the type of element that will be validated
  * @return array           the filtered elements
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function wwm_survey_validation_elements( $elements = array(), $type = '' )
 {

  $simple_elements = array( 'text', 'email', 'textarea' );
  $simple_elements = apply_filters( 'wwm_survey_simple_validation_elements', $simple_elements );
  $elements[] = array(
   'label_text' => __( 'required?', 'awesome-surveys' ),
   'tag' => 'input',
   'type' => 'checkbox',
   'value' => 1,
   'name' => 'required',
  );
  $func = 'get_validation_elements_' . $type;
  return apply_filters( 'get_validation_elements_' . $type, $elements );
 }

  /**
  * Provides some additional, advanced validation elements for input type="number"
  * anything put inside the 'data' array will eventually be output as data-rule-*
  * attributes in the element shown on the survey. The intended use is for the jquery validation
  * plugin.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  * @return array an array of validation element data
  * @see  element_info_inputs
  * @see  wwm_survey_validation_elements
  */
 public function get_validation_elements_number( $elements )
 {

  $min = array(
   'label_text' => __( 'Min number allowed', 'awesome-surveys' ),
   'tag' => 'input',
   'type' => 'number',
   'name' => 'min',
  );
  $max = array(
   'label_text' => __( 'Max number allowed', 'awesome-surveys' ),
   'tag' => 'input',
   'type' => 'number',
   'name' => 'max',
  );

  $elements[]['data'] = array( $min, $max, );
  return $elements;
 }

  /**
  * Provides advanced validation for element type text
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  * @param  array $elements an array of form elements
  * @return array $elements the filtered array of elements
  */
 public function get_validation_elements_text( $elements )
 {

  $maxlength_element = array(
   'label_text' => __( 'Maximum Length (in number of characters)', 'awesome-surveys' ),
   'tag' => 'input',
   'type' => 'number',
   'name' => 'maxlength',
  );
  $elements[]['data'] = array( $maxlength_element );
  return $elements;
 }

  /**
  * An alias of get_validation_elements_text
  * to apply the same advanced validation option to
  * a textarea element.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  * @param  array $elements an array of form elements
  * @return array $elements the filtered array of elements
  */
 public function get_validation_elements_textarea( $elements )
 {

  return $this->get_validation_elements_text( $elements );
 }
}