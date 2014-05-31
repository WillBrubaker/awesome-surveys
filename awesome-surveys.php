<?php
/*
Plugin Name: Awesome Surveys
Plugin URI: http://www.willthewebmechanic.com/awesome-surveys
Description:
Version: 1.0
Author: Will Brubaker
Author URI: http://www.willthewebmechanic.com
License: GPLv3.0
Text Domain: awesome-surveys
Domain Path: /languages/
*/

/**
 * @package Awesome_Surveys
 *
 */
class Awesome_Surveys {

 static private $wwm_plugin_values = array(
  'name' => 'Awesome_Surveys',
  'dbversion' => '1.0',
  'supplementary' => array(
   'hire_me_html' => '<a href="http://www.willthewebmechanic.com">Hire Me</a>',
  )
 );
 public $wwm_page_link, $page_title, $menu_title, $menu_slug, $menu_link_text, $text_domain;

 /**
 * The construct runs every time plugins are loaded.  The bulk of the action and filter hooks go here
 * @since 1.0
 *
 */
 public function __construct()
 {

  $this->page_title = 'Awesome Surveys';
  $this->menu_title = 'Awesome Surveys';
  $this->menu_slug = 'awesome-surveys.php';
  $this->menu_link_text = 'Awesome Surveys';
  $this->text_domain = 'awesome-surveys';
  register_activation_hook( __FILE__ , array( $this, 'init_plugin' ) );
  add_action( 'admin_menu', array( &$this, 'plugin_menu' ) );
  if ( is_admin() ) {
   add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'plugin_manage_link' ), 10, 4 );
  }

  add_action( 'init', array( &$this, 'init' ) );
  add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
  add_action( 'wp_ajax_create_survey', array( &$this, 'create_survey' ) );
  add_action( 'wp_ajax_get_element_form', array( &$this, 'element_info_inputs' ) );
  add_action( 'wp_ajax_options_fields', array( &$this, 'options_fields' ) );
  add_action( 'wp_ajax_generate_preview', array( &$this, 'generate_preview' ) );
  add_action( 'wp_ajax_wwm_save_survey', array( &$this, 'save_survey' ) );
  add_filter( 'wwm_survey_validation_elements', array( &$this, 'wwm_survey_validation_elements' ), 10, 2 );
  add_filter( 'get_validation_elements_number', array( &$this, 'get_validation_elements_number' ) );
  add_action( 'contextual_help', array( &$this, 'contextual_help' ) );
 }

 /**
 * stuff to do on plugin initialization
 * @return none
 * @since 1.0
 */
 public function init_plugin()
 {

 }

 /**
  * Hooked into the init action, does things required on that action
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function init()
 {

  /**
   * This plugin uses PFBC (the php form builder class, which requires an active session)
   */
  if ( ! isset( $_SESSION ) ) {
   session_start();
  }
 }

 /**
  * enqueues the necessary css/js for the admin area
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function admin_enqueue_scripts()
 {

  if ( strpos( $_SERVER['REQUEST_URI'], $this->menu_slug ) > 1 ) {
   wp_enqueue_script( $this->text_domain . '-admin-script', plugins_url( 'js/admin-script.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-tooltip', 'jquery-ui-accordion' ), self::$wwm_plugin_values['version'] );
   wp_register_style( 'jquery-ui-lightness', plugins_url( 'css/jquery-ui.min.css', __FILE__ ), array(), '1.10.13', 'all' );
   wp_enqueue_style( $this->text_domain . '-admin-style', plugins_url( 'css/admin-style.css', __FILE__ ), array( 'jquery-ui-lightness' ), self::$wwm_plugin_values['version'], 'all' );
  }
 }

 /**
  * Adds the WtWM menu item to the admin menu & adds a submenu link to this plugin to that menu item.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function plugin_menu()
 {

  global $_wwm_plugins_page;
  /**
   * If, in the future, there is an enhancement or improvement,
   * allow other plugins to overwrite the panel by using
   * a higher number version.
   */
  $plugin_panel_version = 1;
  add_filter( 'wwm_plugin_links', array( &$this, 'this_plugin_link' ) );
  if ( empty( $_wwm_plugins_page ) || ( is_array( $_wwm_plugins_page ) && $plugin_panel_version > $_wwm_plugins_page[1] ) ) {
   $_wwm_plugins_page[0] = add_menu_page( 'WtWM Plugins', 'WtWM Plugins', 'manage_options', 'wwm_plugins', array( &$this, 'wwm_plugin_links' ), plugins_url( 'images/wwm_wp_menu.png', __FILE__ ), '60.9' );
   $_wwm_plugins_page[1] = $plugin_panel_version;
  }
  add_submenu_page( 'wwm_plugins', $this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array( &$this, 'plugin_options' ) );
 }

 /**
  * adds the link to this plugin's management page
  * to the $links array to be displayed on the WWM
  * plugins page:
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  * @param  array $links the array of links
  * @return array $links the filtered array of links
  */
 public function this_plugin_link( $links )
 {

  $this->wwm_page_link = $menu_page_url = menu_page_url( $this->menu_slug, 0 );
  $links[] = '<a href="' . $this->wwm_page_link . '">' . $this->menu_link_text . '</a>' . "\n";
  return $links;
 }

 /**
  * outputs an admin panel and displays links to all
  * admin pages that have been added to the $wwm_plugin_links array
  * via apply_filters
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function wwm_plugin_links()
 {

  $wwm_plugin_links = apply_filters( 'wwm_plugin_links', $wwm_plugin_links );
  //set a version here so that everything can be overwritten by future plugins.
  //and pass it via the do_action calls
  $plugin_links_version = 1;
  echo '<div class="wrap">' . "\n";
  echo '<div id="icon-plugins" class="icon32"><br></div>' . "\n";
  echo '<h2>Will the Web Mechanic Plugins</h2>' . "\n";
  do_action( 'before_wwm_plugin_links', $plugin_links_version, $wwm_plugin_links );
  if ( ! empty( $wwm_plugin_links ) ) {
   echo '<ul>' . "\n";
   foreach ( $wwm_plugin_links as $link ) {
    echo '<li>' . $link . '</li>' . "\n";
   }
   echo '</ul>';
  }
  do_action( 'after_wwm_plugin_links', $plugin_links_version );
  echo '</div>' . "\n";
 }

 /**
  * Outputs the plugin options panel for this plugin
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function plugin_options()
 {

  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'create-survey' );
  $form = new FormOverrides( 'survey-manager' );
  $form->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
  $form->addElement( new Element_Textbox( __( 'Survey Name:', $this->text_domain ), 'survey_name' ) );
  $form->addElement( new Element_Hidden( 'action', 'create_survey' ) );
  $form->addElement( new Element_Hidden( 'create_survey_nonce', $nonce ) );
  $form->addElement( new Element_HTML( '<div class="create_holder">') );
  $form->addElement( new Element_Button( __( 'Start Building', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
  $form->addElement( new Element_HTML( '</div>') );
  ?>
  <div class="wrap">
   <div class="updated">
    <p>
     <?php _e( 'Need help? There are handy tips for some of the options in the help menu. Click the help tab in the upper right corner of your screen', $this->text_domain ); ?>
    </p>
   </div>
   <div id="tabs">
    <ul>
     <li><a href="#create"><?php _e( 'Build Survey Form', $this->text_domain ); ?></a></li>
     <li><a href="#surveys">surveys - translate this</a></li>
    </ul>
    <div id="create">
     <div class="overlay"><span class="preloader"></span></div>
     <div class="create half">
     <?php
      $form->render();
      $form = new FormOverrides( 'new-elements' );
      $form->addElement( new Element_HTML( '<div class="submit_holder"><div id="add-element"></div><div class="ui-widget-content ui-corner-all validation accordion"><h5>' . __( 'General Survey Options:', $this->text_domain ) . '</h5><div>' ) );
      $form->addElement( new Element_Textarea( __( 'A Thank You message:', $this->text_domain ), 'thank_you' ) );
      $options = array( 'login' => __( 'User must be logged in', $this->text_domain ), 'cookie' => __( 'Cookie based', $this->text_domain ), 'none' => __( 'None' ) );
      /**
       * Implementation of survey_auth_options filter is incomplete and should not be used.
       * todo: complete implementation of this filter - it needs to be processed
       * in other places.
       */
      $options = apply_filters( 'survey_auth_options', $options );
      $form->addElement( new Element_HTML( '<div class="ui-widget-content ui-corner-all validation"><span class="label"><p>' . __( 'To prevent people from filling the survey out multiple times you may select one of the options below', $this->text_domain ) . '</p></span>' ) );
      $form->addElement( new Element_Radio( 'Validation/authentication', 'auth', $options, array( 'value' => 'none' ) ) );
      $form->addElement( new Element_HTML( '</div></div></div>' ) );
      $form->addElement( new Element_Hidden( 'action', 'generate_preview' ) );
      $form->addElement( new Element_Button( __( 'Add Element', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
      $form->addElement( new Element_HTML( '</div>' ) );
      $form->render();
     ?>
     </div><!--.create-->
     <div id="preview" class="half">
      <h4 class="survey-name"></h4>
      <div class="survey-preview">
      </div><!--.survey-preview-->
     </div><!--#preview-->
     <div class="clear"></div>
    </div><!--#create-->
    <div id="surveys">
     surveys
     <pre>
     <?php
     $data = get_option( 'wwm_awesome_surveys', array() );
     var_dump( $data );
     ?>
    </pre>
    </div>
   </div><!--#tabs-->
  </div>
  <?php
 }

 /**
  * Outputs contextual help for the plugin options panel
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function contextual_help()
 {

  if ( strpos( $_SERVER['REQUEST_URI'], $this->menu_slug ) > 0 ) {
   $screen = get_current_screen();
   $args = array(
    'id' => 'survey_name',
    'title' => __( 'Survey Name', $this->text_domain ),
    'content' => __( 'This field is a unique name for your survey and will be displayed before your survey form', $this->text_domain ),
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'field_type',
    'title' => __( 'Field Type', $this->text_domain ),
    'content' => __( 'This is the type of form field you would like to add to your form, i.e.: checkbox, radio, text, email, textarea, etc.', $this->text_domain ),
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'survey_options',
    'title' => __( 'General Survey Options', $this->text_domain ),
    'content' => __( 'These are options for your survey, they are not built in to the survey form, but rather determine behavior of how your survey will work.', $this->text_domain ),
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'survey_authentication',
    'title' => __( 'Authentication', $this->text_domain ),
    'content' => __( 'Choose a method to prevent users from filling out the survey multiple times. User login is much more reliable, but may not be the best option for your site if you don\'t allow registrations. Cookie based authentication is very easy to circumvent, but will prevent most users from taking the survey multiple times. Of course, you may prefer to allow users to take the survey an unlimited number of times in which case you should leave both options unchecked. Note to developers: methods can be added via the survey_auth_options filter.', $this->text_domain ),
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'survey_thank_you',
    'title' => __( 'Thank You Message', $this->text_domain ),
    'content' => __( 'If this field is filled out, the message will be displayed to a user who completes the survey.', $this->text_domain ),
   );
   $screen->add_help_tab( $args );
  }
 }

 /**
  * Ajax handler to output a 'type' selector to the survey form builder
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  * @uses render_element_selector
  */
 public function create_survey()
 {

  if ( ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) || ! current_user_can( 'manage_options' ) ) {
   exit;
  }
  $data = get_option( 'wwm_awesome_surveys', array() );
  if ( isset( $data['surveys'] ) && ! empty( $data['surveys'] ) ) {
   $names = wp_list_pluck( $data['surveys'], 'name' );
   if ( in_array( $_POST['survey_name'], $names ) ) {
    echo json_encode( array( 'error' => __( 'A survey already exists named ', $this->text_domain ) .  $_POST['survey_name'] ) );
    exit;
   }
  } elseif ( '' == $_POST['survey_name'] ) {
   echo json_encode( array( 'error' => __( 'Survey Name cannot be blank', $this->text_domain ) ) );
   exit;
  }
  $form = $this->render_element_selector();
  echo json_encode( array( 'form' => $form ) );
  exit;
 }

 /**
  * Renders a dropdown select element with options
  * that coincide with the pfbc form builder class
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 private function render_element_selector()
 {

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
  $html = '<input type="hidden" name="survey_name" value="' . stripslashes( $_POST['survey_name'] ) . '" data-id="' . sanitize_title( stripslashes( $_POST['survey_name'] ) ) . '">';
  $html .= '<div id="new-element-selector"><span>' . __( 'Add a field to your survey.', $this->text_domain ) . '</span><label>' . __( 'Select Field Type:', $this->text_domain ) . '<br><select name="options[type]" class="type-selector">';
  foreach ( $types as $type => $pfbc_method ) {
   $html .= '<option value="' . $pfbc_method . '">' . $type . '</option>';
  }
  $html .= '</select></label></div>';
  return $html;
 }

 /**
  * Ajax handler which will output
  * some form elements so that information can be gathered
  * about the element that a user is adding to their survey
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function element_info_inputs()
 {

  if ( ! current_user_can( 'manage_options' ) ) {
   exit;
  }
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
  $validation_elements = apply_filters( 'wwm_survey_validation_elements', $elements, $_POST['text'] );
  $html = '';
  $html .= '<label>' . __( 'Label this', $this->text_domain ) . ' ' . $_POST['text']  . ' ' . __( 'field', $this->text_domain ) . '<br><input title="' . __( 'The text that will appear with this form field, i.e. the question you are asking', $this->text_domain ) . '" type="text" name="options[name]"></label>';
  if ( ! empty( $validation_elements ) ) {
   $html .= '<div class="ui-widget-content validation ui-corner-all"><h5>'. __( 'Field Validation Options', $this->text_domain ) . '</h5>';
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
     error_log( print_r( $element, true ) );
     if ( ! is_null( $element['tag'] ) ) {
      $html .= '<label>' . $element['label_text'] . '<br><' . $element['tag'] . ' ' . ' type="' . $element['type'] . '"  value="' . $element['value'] . '" name="options[validation][' . $element['name'] . ']" ' . $element['atts'] . '></label>';
     }
     $rule_count = 0;
     if ( ! empty( $element['data'] ) && is_array( $element['data'] ) ) {
      $html .= '<span class="label">' . __( 'Advanced Validation Rules:', $this->text_domain ) . '</span>';
      foreach ( $element['data'] as $rule ) {
       $defaults = array(
        'label_text' => null,
        'tag' => null,
        'type' => 'text',
        'name' => 'default',
        'value' => null,
        'atts' => '',
        'text' => '',
       );
      $rule = wp_parse_args( $rule, $defaults );
      $html .= '<label>' . $rule['label_text'] . '<br></label>';
       $can_have_options = array( 'radio', 'checkbox' );
       if ( in_array( $rule['type'], $can_have_options ) && is_array( $rule['value'] ) ) {
        foreach ( $rule['value'] as $key => $value ) {
         $html .= '<' . $rule['tag'] . ' ' . ' type="' . $rule['type'] . '"  value="' . $key . '" name="options[validation][rules][' . $rule['name'] . ']" ' . $rule['atts'] . '> ' . $value . '<br>';
        }
       } else {
        $html .= '<' . $rule['tag'] . ' ' . ' type="' . $rule['type'] . '"  value="' . $rule['value'] . '" name="options[validation][rules][' . $rule['name'] . ']" ' . $rule['atts'] . '><br>';
       }
       $rule_count++;
      }
     }
    }
   $html .= '</div>';
  }
  $needs_options = array( 'radio', 'checkbox', 'dropdown selection' );
  if ( in_array( $_POST['text'], $needs_options ) ) {
   $html .= '<span class="label">' . __( 'Number of options required?', $this->text_domain ) . '</span><div class="slider-wrapper"><div id="slider"></div><div class="slider-legend"></div></div><div id="options-holder"></div>';
  }
  echo json_encode( array( 'form' => $html ) );
  exit;
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
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function wwm_survey_validation_elements( $elements = array(), $type = '' )
 {

  $simple_elements = array( 'text', 'email', 'textarea' );
  $simple_elements = apply_filters( 'wwm_survey_simple_validation_elements', $simple_elements );
  $elements[] = array(
   'label_text' => __( 'required?', $this->text_domain ),
   'tag' => 'input',
   'type' => 'checkbox',
   'value' => 1,
   'name' => 'required',
  );
  $func = 'get_validation_elements_' . $type;
  return apply_filters( 'get_validation_elements_' . $type, $elements );
 }

 /**
  * provides some additional, advanced validation elements for input type="number"
  * anything put inside the 'data' array will eventually be output as data-rule-*
  * attributes in the element shown on the survey. The intended use is for the jquery validation
  * plugin.
  * @return array an array of validation element data
  * @see  element_info_inputs
  * @see  wwm_survey_validation_elements
  */
 public function get_validation_elements_number( $elements )
 {

  $radios = array(
   'label_text' => 'Type of Number Validation:',
   'tag' => 'input',
   'type' => 'radio',
   'name' => 'range',
   'value' => array( 'range' => 'range', 'min-max' => 'min-max', ),
  );
  $min_max_element_one = array(
   'label_text' => __( 'Min number allowed', $this->text_domain ),
   'tag' => 'input',
   'type' => 'number',
   'name' => 'min',
   'atts' => 'data-related="min-max" disabled',
  );
  $min_max_element_two = array(
   'label_text' => __( 'Max number allowed', $this->text_domain ),
   'tag' => 'input',
   'type' => 'number',
   'name' => 'max',
   'atts' => 'data-related="min-max" disabled',
  );
  $range_element = array(
   'label_text' => __( 'Range', $this->text_domain ),
   'tag' => 'input',
   'type' => 'text',
   'name' => 'range',
   'atts' => 'data-related="range" disabled',
  );
  $elements[]['data'] = array( $radios, $min_max_element_one, $min_max_element_two, $range_element );
  return $elements;
 }

 /**
  * Ajax handler to generate some fields
  * for survey option inputs
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function options_fields()
 {

  $html = '';
  for ( $iterations = 0; $iterations < absint( $_POST['num_options'] ); $iterations++ ) {
   $label = $iterations + 1;
   $html .= '<label>' . __( 'option label', $this->text_domain ) . ' ' . $label . '<br><input type="text" name="options[label][' . $iterations . ']"></label><label>' . __( 'option value', $this->text_domain ) . ' ' . $label . '<br><input type="text" name="options[value][' . $iterations . ']" value="' . $iterations . '"></label><label>' . __( 'default?', $this->text_domain ) . '<br><input type="radio" name="options[default]" value="' . $iterations . '"></label>';
  }
  echo $html;
  exit;
 }

 /**
  * Ajax handler to generate the form preview
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function generate_preview()
 {

  $form_elements_array = $_POST;
  /**
   * If someone has added pieces to the form elements array, allow them to filter
   * those before processing.
   *
   */
  $form_elements_array = apply_filters( 'awesome_surveys_form_preview', $form_elements_array );
  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'create-survey' );
  $form = new FormOverrides( sanitize_title( $form_elements_array['survey_name'] ) );
  if ( isset( $form_elements_array['existing_elements'] ) ) {
   $element_json = json_decode( stripslashes( $form_elements_array['existing_elements'] ), true );
  }
  $required_is_option = array( 'Element_Textbox', 'Element_Textarea', 'Element_Email', 'Element_Number' );
  $existing_elements = ( isset( $element_json ) ) ? array_merge( $element_json, array( $form_elements_array['options'] ) ) : array( $form_elements_array['options'] );
  foreach ( $existing_elements as $element ) {
   $method = $element['type'];
   $options = $atts = array();
   if ( isset( $element['validation']['required'] ) ) {
    if ( in_array( $method, $required_is_option ) ) {
     $options['required'] = 1;
     $options['class'] = 'required';
     $options['data-debug'] = 'debug';
    } else {
     $atts['required'] = 1;
     $atts['class'] = 'required';
     $atts['data-debug'] = 'debug';
    }
   }
   for ( $iterations = 0; $iterations < count( $element['label'] ); $iterations++ ) {
    /**
     * Since the pfbc is being used, and it has some weird issue with values of '0', but
     * it will work if you append :pfbc to it...not well documented, but it works!
     */
    $options[$element['value'][$iterations] . ':pfbc'] = stripslashes( $element['label'][$iterations] );
   }
   $atts['value'] = ( isset( $element['default'] ) ) ? $element['default']  : null;
   $form->addElement( new $method( stripslashes( $element['name'] ), sanitize_title( $element['name'] ), $options, $atts ) );
  }
  $preview_form = $form->render(true);
  $form = new FormOverrides( 'save-survey' );
  $form->configure( array( 'class' => 'save' ) );
  $auth_messages = array( 'none' => __( 'None', $this->text_domain ), 'cookie' => __( 'Cookie Based', $this->text_domain ), 'login' => __( 'User must be logged in', $this->text_domain ) );
  $auth_type = esc_attr( $_POST['auth'] );
  $form->addElement( new Element_HTML( '<span class="label">' . __( 'Type of authentication: ', $this->text_domain ) . $auth_messages[ $auth_type ] . '</span>' ) );
  $form->addElement( new Element_Hidden( 'auth', $auth_type ) );
  if ( isset( $_POST['thank_you'] ) && ! empty( $_POST['thank_you'] ) ) {
   $thank_you_message = esc_html( $_POST['thank_you'] );
   $form->addElement( new Element_Hidden( 'thank_you', $thank_you_message ) );
   $form->addElement( new Element_HTML( '<span class="label">' . __( 'Thank you message:', $this->text_domain ) . '</span><div>' . $thank_you_message . '</div>' ) );
  }
  $form->addElement( new Element_Hidden( 'create_survey_nonce', $nonce ) );
  $form->addElement( new Element_Hidden( 'action', 'wwm_save_survey' ) );
  $form->addElement( new Element_Hidden( 'existing_elements', json_encode( $existing_elements ) ) );
  $form->addElement( new Element_Hidden( 'survey_name', $form_elements_array['survey_name'] ) );
  $form->addElement( new Element_Button( __( 'Reset', $this->text_domain ), 'submit', array( 'class' => 'button-secondary reset-button', 'name' => 'reset' ) ) );
  $form->addElement( new Element_Button( __( 'Save Survey', $this->text_domain ), 'submit', array( 'class' => 'button-primary', 'name' => 'save' ) ) );
  $save_form = $form->render(true);
  echo $preview_form . $save_form;
  exit;
 }

 /**
  * Ajax handler to save the survey form details to the db.
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic>
  * @link http://willthewebmechanic.com
  */
 public function save_survey()
 {

  if ( ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) || ! current_user_can( 'manage_options' ) ) {
   exit;
  }
  /**
   * Build an empty array to hold responses.
   * This needs to be able to hold individual responses to elements that
   * that are free-form input (text, email, number)
   * and count of responses that are selected/checked options.
   */
  $has_options( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
  $data = get_option( 'wwm_awesome_surveys', array() );
  $surveys = ( isset( $data['surveys'] ) ) ? $data['surveys'] : array();
  $form = serialize( json_decode( stripslashes( $_POST['existing_elements'] ), true ) );
  $surveys[] = array( 'name' => sanitize_text_field( $_POST['survey_name'] ), 'form' => $form, 'thank_you' => ( isset( $_POST['thank_you'] ) ) ? esc_html( $_POST['thank_you'] ) : null, 'auth' => esc_attr( $_POST['auth'] ), 'responses' => $responses );
  $data['surveys'] = $surveys;
  update_option( 'wwm_awesome_surveys', $data );
  exit;
 }

 /**
  * Adds a link on the plugins page. Nothing more than
  * shameless self-promotion, really.
  * @param  array $actions     the actions array
  * @param  string $plugin_file this plugin file
  * @param  array $plugin_data plugin data
  * @param  string $context
  * @return array  $actions the action links array
  * @since 1.0
  */
 public function plugin_manage_link( $actions, $plugin_file, $plugin_data, $context )
 {

  //add a link to the front of the actions list for this plugin
  return array_merge(
   array(
   'Hire Me' => self::$wwm_plugin_values['supplementary']['hire_me_html']
   ),
   $actions
  );
 }
}
$var = new Awesome_Surveys;