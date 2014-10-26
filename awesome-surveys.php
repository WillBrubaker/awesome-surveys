<?php
/*
Plugin Name: Awesome Surveys
Plugin URI: http://www.willthewebmechanic.com/awesome-surveys
Description: Easily create surveys for your WordPress website and publish them with a simple shortcode
Version: 1.5
Author: Will Brubaker
Author URI: http://www.willthewebmechanic.com
License: GPLv3.0
Text Domain: awesome-surveys
Domain Path: /languages/
*/

/**
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  Other software packaged with this plugin is subject to other licenses:
 *
 *  pure css (forms.css) is licensed under the Yahoo! BSD License. See css/purecss-license.txt
 *  The jQuery validate plugin is licensed under the MIT license. See js/jquery-validate-license.txt
 *  normalize.css is licensed under the MIT license see css/normalize-license.txt
 *  the PHP Form Builder class is licensed under the GPL v3. See LICENSE
 *
 */

/**
 * @package Awesome_Surveys
 *
 */
class Awesome_Surveys {

 static private $wwm_plugin_values = array(
  'name' => 'Awesome_Surveys',
  'dbversion' => '1.1',
  'version' => '1.5',
  'supplementary' => array(
   'hire_me_html' => '<a href="http://www.willthewebmechanic.com">Hire Me</a>',
  )
 );
 public $wwm_page_link, $page_title, $menu_title, $menu_slug, $menu_link_text, $text_domain, $frontend, $page_hook, $option_updated;

 /**
 * The construct runs every time plugins are loaded.  The bulk of the action and filter hooks go here
 * @since 1.0
 *
 */
 public function __construct()
 {

  if ( ! is_admin() && ! class_exists( 'Awesome_Surveys_Frontend' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/class.awesome-surveys-frontend.php' );
   $this->frontend = new Awesome_Surveys_Frontend( self::$wwm_plugin_values['version'] );
  }
  $this->page_title = 'Awesome Surveys';
  $this->menu_title = 'Awesome Surveys';
  $this->menu_slug = 'awesome-surveys.php';
  $this->menu_link_text = 'Awesome Surveys';
  $this->text_domain = 'awesome-surveys';

  if ( ! defined( 'WWM_AWESOME_SURVEYS_URL' ) ) {
   define( 'WWM_AWESOME_SURVEYS_URL', plugins_url( '', __FILE__ ) );
  }
  if ( ! defined( 'WWM_AWESOME_SURVEYS_PATH' ) ) {
   define( 'WWM_AWESOME_SURVEYS_PATH', plugin_dir_path( __FILE__ ) );

  }
  register_activation_hook( __FILE__ , array( $this, 'init_plugin' ) );
  add_action( 'admin_menu', array( &$this, 'plugin_menu' ) );
  if ( is_admin() ) {
   add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'plugin_manage_link' ), 10, 4 );
   add_action( 'after_wwm_plugin_links', array( &$this, 'output_links' ) );
  }

  add_action( 'init', array( &$this, 'init' ) );
  add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
  add_action( 'admin_init', array( &$this, 'add_meta_boxes' ) );
  add_action( 'wp_ajax_create_survey', array( &$this, 'create_survey' ) );
  add_action( 'wp_ajax_get_element_form', array( &$this, 'element_info_inputs' ) );
  add_action( 'wp_ajax_options_fields', array( &$this, 'options_fields' ) );
  add_action( 'wp_ajax_generate_preview', array( &$this, 'generate_preview' ) );
  add_action( 'wp_ajax_get_survey_results', array( &$this, 'get_survey_results' ) );
  add_action( 'wp_ajax_wwm_save_survey', array( &$this, 'save_survey' ) );
  add_action( 'wp_ajax_wwm_delete_survey', array( &$this, 'delete_survey' ) );
  add_action( 'wp_ajax_wwm_edit_question', array( &$this, 'edit_question' ) );
  add_action( 'wp_ajax_wwm_edit_answer', array( &$this, 'edit_answer' ) );
  add_action( 'wp_ajax_wwm_edit_survey_name', array( &$this, 'edit_survey_name' ) );
  add_action( 'wp_ajax_wwm_edit_survey_thanks', array( &$this, 'edit_survey_thanks' ) );
  add_action( 'wp_ajax_wwm_get_auth_method_edit_form', array( &$this, 'get_auth_method_edit_form' ) );
  add_action( 'wp_ajax_wwm_edit_survey_auth', array( &$this, 'edit_survey_auth' ) );
  add_action( 'wp_ajax_update_styling_options', array( &$this, 'update_styling_options' ) );
  add_action( 'wp_ajax_answer_survey', array( &$this, 'process_response' ) );
  add_action( 'wp_ajax_nopriv_answer_survey', array( &$this, 'process_response' ) );
  add_action( 'wp_ajax_wwm_as_get_json', array( &$this, 'get_json' ) );
  add_action( 'wp_ajax_get_element_selector', array( &$this, 'get_element_selector' ) );
  add_action( 'wp_ajax_survey_edit_name_inline', array( &$this, 'survey_edit_name_inline' ) );
  add_filter( 'wwm_survey_validation_elements', array( &$this, 'wwm_survey_validation_elements' ), 10, 2 );
  add_filter( 'get_validation_elements_number', array( &$this, 'get_validation_elements_number' ) );
  add_filter( 'get_validation_elements_text', array( &$this, 'get_validation_elements_text' ) );
  add_filter( 'get_validation_elements_textarea', array( &$this, 'get_validation_elements_textarea' ) );
  add_action( 'contextual_help', array( &$this, 'contextual_help' ) );
  add_filter( 'awesome_surveys_form_preview', array( &$this, 'awesome_surveys_form_preview' ) );
  add_filter( 'survey_auth_options', array( &$this, 'default_auth_methods' ) );
  add_action( 'plugins_loaded', array( &$this, 'load_translations' ) );
 }

 /**
  * stuff to do on plugin initialization
  * @return none
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function init_plugin()
 {

 }

 /**
  * Hooked into the init action, does things required on that action
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function init()
 {
 }

 /**
  * enqueues the necessary css/js for the admin area
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function admin_enqueue_scripts()
 {

  wp_register_script( 'jquery-validation-plugin', WWM_AWESOME_SURVEYS_URL . '/js/jquery.validate.min.js', array( 'jquery' ), '1.13.0' );
  wp_register_script( $this->text_domain . '-admin-script', plugins_url( 'js/admin-script.min.js', __FILE__ ), array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-validation-plugin', 'jquery-ui-dialog', 'jquery-ui-button', 'postbox' ), self::$wwm_plugin_values['version'] );

  wp_register_style( 'normalize-css', WWM_AWESOME_SURVEYS_URL . '/css/normalize.min.css' );
  wp_register_style( 'jquery-ui-lightness', plugins_url( 'css/jquery-ui.min.css', __FILE__ ), array(), '1.10.13', 'all' );
  wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css', array( 'normalize-css' ) );
  wp_register_style( $this->text_domain . '-admin-style', plugins_url( 'css/admin-style.min.css', __FILE__ ), array( 'jquery-ui-lightness', 'pure-forms-css' ), self::$wwm_plugin_values['version'], 'all' );
 }

 /**
  * Hooked into admin_print_scripts-{$page-hook}
  * to output required javascript for this plugin only
  * on its page hook
  * @since 1.1
  */
 public function admin_print_scripts()
 {

  wp_enqueue_script( $this->text_domain . '-admin-script' );
 }

 /**
  * Hooked into admin_print_styles-{$page-hook}
  * to output the css on a conditional basis when
  * adimin pages for this plugin are loaded
  * @since 1.1
  *
  */
 public function admin_print_styles()
 {

  wp_enqueue_style( $this->text_domain . '-admin-style' );
 }

 /**
  * Adds the WtWM menu item to the admin menu & adds a submenu link to this plugin to that menu item.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
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
  $this->page_hook = add_submenu_page( 'wwm_plugins', $this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array( &$this, 'plugin_options' ) );
  add_action( 'admin_print_scripts-' . $this->page_hook, array( &$this, 'admin_print_scripts' ) );
  add_action( 'admin_print_styles-' . $this->page_hook, array( &$this, 'admin_print_styles' ) );
 }

 /**
  * adds the link to this plugin's management page
  * to the $links array to be displayed on the WWM
  * plugins page:
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
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
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function wwm_plugin_links()
 {

  $wwm_plugin_links = array();
  $wwm_plugin_links = apply_filters( 'wwm_plugin_links', $wwm_plugin_links );
  //set a version here so that everything can be overwritten by future plugins.
  //and pass it via the do_action calls
  $plugin_links_version = 1;
  do_action( 'before_wwm_plugin_links', $plugin_links_version, $wwm_plugin_links );
  if ( ! empty( $wwm_plugin_links ) ) {
   echo '<div class="wrap">' . "\n";
   echo '<div id="icon-plugins" class="icon32"><br></div>' . "\n";
   echo '<h2>Will the Web Mechanic Plugins</h2>' . "\n";
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
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function plugin_options()
 {

  $user_id = get_current_user_id();
  if ( isset( $_GET['wwm_dismiss'] ) ) {
   if ( wp_verify_nonce( $_GET['wwm_dismiss'], 'wwm_dismiss' ) && current_user_can( 'manage_options' ) ) {
    update_user_meta( $user_id, 'wwm_as_notice_dismissed', true );
   }
  }

  $dismissed = get_user_meta( $user_id, 'wwm_as_notice_dismissed', true );
  if ( empty( $dismissed ) ) {
   add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
  }
  ?>
  <h2>Awesome Surveys</h2>
  <div class="wrap">
   <?php do_action( 'admin_notices' ); ?>
   <p>
    <ul><?php _e( 'Donate to the future development of this plugin:', $this->text_domain ); ?>
     <li>
      <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
       <input name="cmd" type="hidden" value="_s-xclick" />
       <input name="hosted_button_id" type="hidden" value="634DZTUWQA2ZU" />
       <input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" type="image" />
       <img src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="Donate" width="1" height="1" border="0" />
      </form>
     </li>
    </ul>
   </p>
   <div id="tabs">
    <ul>
    <?php
    $tabs = array(
     array(
     'create',
     __( 'Build Survey Form', $this->text_domain ),
     array( &$this, 'create_survey_form' ),
     ),
     array(
     'styles',
     __( 'Survey Styling Options', $this->text_domain ),
     array( &$this, 'get_styling_options' ),
     ),
     array(
      'surveys',
      __( 'Your Survey Results', $this->text_domain ),
      array( &$this, 'output_survey_results' ),
     ),
     array(
      'results',
      __( 'Survey Results by User', $this->text_domain ),
      array( &$this, 'output_results_by_user' ),
     ),
     array(
      'video',
      __( 'How To Video', $this->text_domain ),
      array( &$this, 'output_howto_video' ),
     )
    );
    $tabs = apply_filters( 'awesome_surveys_options_panel', $tabs );
    foreach ( $tabs as $tab ) {
     echo '<li><a href="#' . $tab[0] . '">' . $tab[1] . '</a></li>' . "\n";
    }

    if ( isset( $_GET['debug'] ) ) : ?>
     <li><a href="#debug"><?php _e( 'Debug', $this->text_domain ); ?></a></li>
     <?php endif; ?>
    </ul>

    <?php
    foreach ( $tabs as $tab ) {
     echo '<div id="' . $tab[0] . '">' . "\n";
     echo call_user_func( $tab[2] );
     echo '</div><!--#' . $tab[0] . '-->';
    }
    if ( isset( $_GET['debug'] ) ) : ?>
    <div id="debug">
     <pre>
      <?php
      $surveys = get_option( 'wwm_awesome_surveys', array() );
      if ( isset( $_GET['survey_id'] ) ) {
       var_dump( $_GET['survey_id'] );
       print_r( $surveys['surveys'][$_GET['survey_id']] );
       echo '<p><h4>Form:</h4></p>';
       print_r( json_decode( $surveys['surveys'][$_GET['survey_id']]['form'], true ) );
      } else {
       echo '<p>Browser: ' . $_SERVER['HTTP_USER_AGENT'] . '</p>';
       echo '<p>Plugins:</p>';
       $plugins = get_option( 'active_plugins', array() );
       foreach ( $plugins as $plugin ) {
        echo '<p>' . $plugin . '</p>';
       }
       echo '<p>Debugging Output</p>';
       global $wp_version;
       echo '<p>WordPress version: ' . $wp_version . '</p>';
       echo '<p>Awesome Surveys Version: ' . self::$wwm_plugin_values['version'] . '</p>';
       $arr = array( 'key_zero' => 'this is a string', 'key_one' => 'this is another string' );
       print_r( $arr );
       echo '<br>';
       print_r( json_encode( $arr, true ) );
       echo '<br>php version: ' . phpversion() . '<br>json version: ' . phpversion( 'json' );
      }
      ?>
     </pre>
    </div><!--#debug-->
   <?php endif; ?>
   </div><!--#tabs-->
   <?php
   do_meta_boxes( 'awesome-surveys.php', 'normal', null );
   ?>
  </div>
  <?php
 }

 /**
  * Generates html output with survey results
  * @param  array $args an array of function arguments
  * @return string       html markup with survey results.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 private function display_survey_results( $args = array() )
 {

  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $html = '<div id="survey-responses">' . "\n";
  if ( ! empty( $surveys['surveys'] ) ) {
   foreach ( $surveys['surveys'] as $key => $survey ) {
    if ( ! empty( $surveys['surveys'][$key] ) ) {
     $form = json_decode( $survey['form'], true );
     $survey_name = stripslashes( stripslashes( $survey['name'] ) );
     $edit_name_nonce = wp_create_nonce( 'edit-survey-name_' . $key );
     $edit_auth_nonce = wp_create_nonce( 'edit-survey-auth_' . $key );
     $edit_thanks_nonce = wp_create_nonce( 'edit-survey-thanks_' . $key );
     $existing_elements_value = htmlentities( $survey['form'] );
     $thanks_message = sanitize_text_field( $survey['thank_you'] );
     $auth_method = sanitize_text_field( $survey['auth'] );
     $edit_survey_name_link = '<p><a href="#" title="' . __( 'Edit Survey Name', $this->text_domain ) . '" data-nonce="' . $edit_name_nonce . '" data-survey_id="' . $key . '" class="edit-survey-name">' . $survey_name . '</a> (click to edit)</p><div class="clear"></div>';
     $num_responses = ( isset( $survey['num_responses'] ) ) ? intval( $survey['num_responses'] + 1)  : 0;
     $html .= "\t\t\t" . '<h5>' . $survey_name . '</h5>' . "\n\t\t\t" . '<div class="survey">' . "\n";
     $html .= apply_filters( 'before_individual_survey_result', null, $survey );
     if ( 0 == $num_responses ) {
      $html .= "\t\t\t" . '<p><a class="dup-edit" data-survey_name="' . htmlentities( $survey_name ) . '" data-auth_method="' . $auth_method . '" data-thank_you="' . $thanks_message . '" data-existing_elements="' . $existing_elements_value . '" data-survey_id="' . $key . '" href="#">' . __( 'Edit this survey', $this->text_domain ) . '</a></p><div class="clear"></div>' . "\n";
     }
     $html .= "\t\t\t" . '<p><a class="dup-edit" data-survey_name="' . htmlentities( $survey_name ) . '" data-auth_method="' . $auth_method . '" data-thank_you="' . $thanks_message . '" data-existing_elements="' . $existing_elements_value . '" data-survey_id="-1" href="#">' . __( 'Clone this survey', $this->text_domain ) . '</a></p><div class="clear"></div>' . "\n";
     $html .= "\t\t\t" . $edit_survey_name_link . "\n";
     $html .= "\t\t\t" . '<form class="delete-survey" method="post" action="' . $_SERVER['PHP_SELF'] . '">' . "\n";
     $html .= "\t\t\t\t" . '<input type="hidden" name="action" value="wwm_delete_survey">' . "\n";
     $html .= "\t\t\t\t" . '<input type="hidden" name="survey_id" value="' . intval( $key ) . '">' . "\n";
     $html .= "\t\t\t\t" . wp_nonce_field( 'delete-survey_' . $key, 'delete_survey', false, false );
     $html .= "\t\t\t\t" . '<input type="submit" value="' . __( 'Delete', $this->text_domain ) . '" class="button-secondary">' . "\n";
     $html .= "\t\t\t" . '</form>' . "\n";
     $html .= "\t\t\t\t" . '<ul><br>' . "\n";
     $html .= "\t\t\t\t" . '<li>' .  __( 'You can insert this survey with shortcode: ', $this->text_domain ) . '[wwm_survey id="' . $key . '"]</li>' . "\n";
     $html .= "\t\t\t\t" . '<li>' . sprintf( __( 'This survey has received %d responses', $this->text_domain ), $num_responses ) . '</li>' . "\n";
     $html .= "\t\t\t\t" . '</ul>' . "\n";
     $html .= "\n\t\t\t" . '<div class="answers">' . "\n";
      foreach ( $survey['responses'] as $response_key => $response ) {
       $question_edit_nonce = wp_create_nonce( 'edit-question_' . $key . '_' . $response_key );
       $question_edit_link = '<a title="' . __( 'edit this question', $this->text_domain ) . '" class="edit-question" data-question_id="' . $response_key . '" data-survey_id="' . $key . '" data-nonce="' . $question_edit_nonce . '" href="#">' . sanitize_text_field( stripslashes( $response['question'] ) ) . '</a> (' . __( 'click to edit', $this->text_domain ) . ')';
       if ( $response['has_options'] ) {
        $html .= "\n\t\t\t\t" . '<div class="question-container ui-widget-content ui-corner-all"><span class="question">' . $question_edit_link . '</span>' . "\n";
        foreach ( $response['answers'] as $answer_key => $arr ) {
         $num_answers = count( $response['answers'] );
         $ttl_count = count( $response['answers'], COUNT_RECURSIVE );
         $ttl_responses = $ttl_count - $num_answers;
         $this_answer = count( $arr );
         $percent = ( $ttl_responses > 0 ) ? sprintf( '%.1f', ( $this_answer / $ttl_responses ) * 100 ) : 0;
         $edit_answer_nonce = wp_create_nonce( 'edit-answer_' . $response_key . '_' . $answer_key );
         $edit_answer_text = stripslashes( $form[$response_key]['label'][$answer_key] );
         $edit_answer_link = '<a href="#" class="edit-answer-option" data-survey_id="' . $key . '" data-question_id="' . $response_key . '" data-answer_id="' . $answer_key . '" data-nonce="' . $edit_answer_nonce . '">' . $edit_answer_text . '</a>';
         $html .= "\t\t\t\t" . '<div class="options-container"><span class="options" style="width: ' . $percent . '%;"></span><div class="data">' . $edit_answer_link . ' ' . $percent . '% (' . $this_answer . ' of ' . $ttl_responses . ')</div></div><!--.options-container-->' . "\n";
        }
        $html .= '</div><!--.question-container-->';
       } else {
        $html .= "\n\t\t\t\t" . '<div class="answer-accordion">' . "\n";
        $html .= "\t\t\t\t\t" . '<h4 class="answers">' . sanitize_text_field( stripslashes( $response['question'] ) ) . '</h4>' . "\n";
        $html .= "\t\t\t\t\t\t" . '<div>' . "\n";
        $html .= "\t\t\t\t\t\t" . $question_edit_link . "\n";
        foreach ( $response['answers'] as $answer ) {
         $html .= "\t\t\t\t\t\t\t" . '<p>' . "\n";
         $html .= "\t\t\t\t\t\t\t" . stripslashes( $answer ) . "\n";
         $html .= "\t\t\t\t\t\t\t" . '</p>' . "\n";
        }
        $html .= "\t\t\t\t\t\t" . '</div>' . "\n";
        $html .= "\t\t\t\t" . '</div><!--.accordion-->';
       }
      }
     $html .= "\n\t\t\t" . '</div><!--.answers-->' . "\n";
     $html .=  "\t\t\t" . '<p>' . __( 'Thank You Message:', $this->text_domain ) . ' <a href="#" data-survey_id="' . $key . '" data-nonce="' . $edit_thanks_nonce . '" class="edit-thanks">' . $thanks_message . '</a> (' . __( 'click to edit', $this->text_domain ) . ')</p>';
     $html .=  "\t\t\t" . '<p>' . __( 'Survey Authentication Method:', $this->text_domain ) . ' <a href="#"  data-survey_id="' . $key . '" data-nonce="' . $edit_auth_nonce . '" class="edit-auth-method">' . $auth_method . '</a> (' . __( 'click to edit', $this->text_domain ) . ')</p>';
     $html .= apply_filters( 'after_individual_survey_result', null, $survey );
     $html .= "\n\t\t" . '</div><!--.survey-->' . "\n";
    }
   }
   $html .= '<div id="question-dialog">
              <form id="edit-question" method="post" action="' . $_SERVER['PHP_SELF'] . '">
               <input type="text" name="question" value="" required>
               <input type="hidden" name="question_id" value="">
               <input type="hidden" name="survey_id" value="">
               <input type="hidden" name="_nonce" value="">
               <input type="hidden" name="action" value="wwm_edit_question">
              </form>
             </div><!--#question-dialog-->';
   $html .= '<div id="answer-dialog">
              <form id="edit-answer" method="post" action="' . $_SERVER['PHP_SELF'] . '">
               <input type="text" name="answer" value="" required>
               <input type="hidden" name="question_id" value="">
               <input type="hidden" name="answer_id" value="">
               <input type="hidden" name="survey_id" value="">
               <input type="hidden" name="_nonce" value="">
               <input type="hidden" name="action" value="wwm_edit_answer">
              </form>
             </div><!--#answer-dialog-->';
   $html .= '<div id="survey-name-dialog">
              <form id="edit-survey-name" method="post" action="' . $_SERVER['PHP_SELF'] . '">
               <input type="text" name="name" value="" required>
               <input type="hidden" name="survey_id" value="">
               <input type="hidden" name="_nonce" value="">
               <input type="hidden" name="action" value="wwm_edit_survey_name">
              </form>
             </div><!--survey-name-dialog-->';
   $html .= '<div id="survey-thanks-dialog">
              <form id="edit-survey-thanks" method="post" action="' . $_SERVER['PHP_SELF'] . '">
               <textarea name="thank_you" value="" required></textarea>
               <input type="hidden" name="survey_id" value="">
               <input type="hidden" name="_nonce" value="">
               <input type="hidden" name="action" value="wwm_edit_survey_thanks">
              </form>
             </div><!--survey-thanks-dialog-->';

  }
  $html .= '</div><!--#survey-responses-->';
  return $html;
 }

  /**
  * Generates html output with survey results by user
  * @param  array $args an array of function arguments
  * @return string       html markup with survey results.
  * @since  1.5
  * @author Toby Hawkins <toby@genobi.net> based on above function by
  *   Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://www.genobi.net
  */
  function display_results_by_user( $args = array() )
  {

   $surveys = get_option( 'wwm_awesome_surveys', array() );
   // Need to rearrange the surveys array to make it per user
   $surveys_new = array();
   $html = '<div id="survey-results">' . "\n";

   if ( ! empty( $surveys['surveys'] ) )
   {
    foreach( $surveys['surveys'] as $survey_key => $survey )
    {
     if( 'login' == $survey['auth'] )
     {
      $form = json_decode( $survey['form'], true );

      // First, recreate the survey in the new array with the same key
      $surveys_new[$survey_key] = array();
      $surveys_new[$survey_key]['name'] = $survey['name'];
      // and then questions and respondents sections
      $surveys_new[$survey_key]['questions'] = array();
      $surveys_new[$survey_key]['respondents'] = array();

      // Then reconstruct the questions for this survey
      foreach( $survey['responses'] as $response_key => $response )
      {
       $surveys_new[$survey_key]['questions'][$response_key] = $response['question'];
      }

      // Now create the respondents sections
      if( ! empty( $survey['respondents'] ) )
      {
       foreach( $survey['respondents'] as $respondent_key => $user_id )
       {
        $user_info = get_userdata( $user_id );
        $user_name = $user_info->display_name;

        $surveys_new[$survey_key]['respondents'][$respondent_key]['user_id'] = $user_id;
        $surveys_new[$survey_key]['respondents'][$respondent_key]['user_name'] = $user_name;
        $surveys_new[$survey_key]['respondents'][$respondent_key]['answers'] = array();
       }
      }

      // Finally, populate the respondents section with the relevant responses
      foreach( $surveys_new[$survey_key]['respondents'] as $respondents_key => $arr )
      {
       foreach( $survey['responses'] as $response_key => $response_arr )
       {
        // Responses with options are handled slightly differently
        if( 0 == $response_arr['has_options'] )
        {
         if( array_key_exists( $respondents_key, $response_arr['answers'] ) )
          $surveys_new[$survey_key]['respondents'][$respondents_key]['answers'][$response_key] = $response_arr['answers'][$respondents_key];
        }
        else
        {
         foreach( $response_arr['answers'] as $answer_key => $answer_arr )
         {
          // Get the answer from the form label
          $answer = stripslashes( $form[$response_key]['label'][$answer_key] );
          // Options questions may have multiple answers for the same question. Store in an array.
          if( in_array( $respondents_key, $answer_arr ) )
           $surveys_new[$survey_key]['respondents'][$respondents_key]['answers'][$response_key]['multi'][] = $answer;
         }
        }
       }
      }
     }
    }
    // Now set up the HTML and out put the array for display
    if( ! empty( $surveys_new ) )
    {
     foreach ( $surveys_new as $key => $survey )
     {
      if ( ! empty( $surveys_new[$key]['respondents'] ) ) {
       $survey_name = stripslashes( stripslashes( $survey['name'] ) );
       $html .= "\t\t\t" . '<h5>' . $survey_name . '</h5>' . "\n\t\t\t" . '<div class="survey">' . "\n";
       $html .= apply_filters( 'before_individual_survey_result', null, $survey );
       $html .= "\t\t\t\t" . '<ul>' . "\n";
       foreach( $survey['questions'] as $question_key => $question )
       {
        $question_text = absint( $question_key ) + 1 . ": " . stripslashes( stripslashes( $question ) );
        $html.= "\t\t\t\t\t" . "<li>$question_text</li>" . "\n";
       }
       $html .= "\t\t\t\t" . '</ul>' . "\n";
       foreach( $survey['respondents'] as $respondent_key => $respondent_arr )
       {
        $html .= "\t\t\t\t\t" . '<div class="answer-accordion">' . "\n";
        $html .= "\t\t\t\t\t\t" . '<h4 class="answers">' . sanitize_text_field( stripslashes( $respondent_arr['user_name'] ) ) . '</h4>' . "\n";
        $html .= "\t\t\t\t\t\t" . '<div>' . "\n";
        foreach ( $respondent_arr['answers'] as $answer_key => $answer ) {
         // Check for multiple response answers
         if( is_array($answer) ) {
          foreach( $answer['multi'] as $multi )
          {
           $answer_text = absint( $answer_key ) + 1 . ": " . stripslashes( $multi );
           $html .= "\t\t\t\t\t\t\t" . "<li>$answer_text</li>" . "\n";
          }
         }
         else
         {
          $answer_text = absint( $answer_key ) + 1 . ": " . stripslashes( $answer );
          $html .= "\t\t\t\t\t\t\t" . "<li>$answer_text</li>" . "\n";
         }
        }
        $html .= "\t\t\t\t\t\t" . '</div>' . "\n";
        $html .= "\t\t\t\t\t" . '</div><!--.answer-accordion-->' . "\n";
       }
       $html .= "\t\t\t" . '</div><!-- .survey -->' . "\n";
      }
     }
    }
   }
   $html .= '</div><!--#survey-results-->';
   return $html;
  }

 /**
  * AJAX handler for get_survey_results
  * @return string html string with survey results.
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function get_survey_results()
 {

  echo $this->display_survey_results();
  exit;
 }

 /**
  * Echos the styling options form
  * @since 1.1
  */
 public function styling_options()
 {

  echo $this->get_styling_options();
  exit;
 }

 /**
  * Outputs the content for the styling options tab
  * @return string html form for the styling options
  * @since  1.1
  */
 private function get_styling_options()
 {

  $html = '<p>' . __( 'This plugin outputs some very basic structural css. You can enable/disable this by setting the option below', $this->text_domain ) . '</p>';
  if ( ! class_exists( 'Form') ) {
   include_once( 'includes/PFBC/Form.php' );
   include_once( 'includes/PFBC/Overrides.php' );
  }
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $nonce = wp_create_nonce( 'update-styling-options' );
  $include_css = ( isset( $surveys['include_css'] ) ) ? absint( $surveys['include_css'] ) : 1;
  $form = new FormOverrides( 'styling-options' );
  $form->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
  $form->addElement( new Element_YesNo( __( 'Use included css?', $this->text_domain ), 'options[include_css]', array( 'value' => $include_css, ) ) );
  $form->addElement( new Element_Hidden( 'action', 'update_styling_options' ) );
  $form->addElement( new Element_Hidden( '_nonce', $nonce ) );
  $form->addElement( new Element_Button( __( 'Save', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
  return $html . $form->render( true );
 }

 /**
  * Outputs contextual help for the plugin options panel
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function contextual_help()
 {

  if ( strpos( $_SERVER['REQUEST_URI'], $this->menu_slug ) > 0 ) {
   $screen = get_current_screen();
   $args = array(
    'id' => 'survey_name',
    'title' => __( 'Survey Name', $this->text_domain ),
    'content' => '<p>' . __( 'This field is a name for your survey and will be displayed before your survey form', $this->text_domain ) . '</p>',
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'field_type',
    'title' => __( 'Field Type', $this->text_domain ),
    'content' => '<p>' . sprintf( '%s<br><ul><li>text: <input type="text"></li><li>email: <input type="email"></li><li>number: <input type="number"></li><li>dropdown selector: <select><option>Make a selection</option></select></li><li>radio: <input type="radio"></li><li>checkbox: <input type="checkbox"></li><li>textarea: <textarea></textarea></li></ul>', __( 'This is the type of form field you would like to add to your form, i.e.: checkbox, radio, text, email, textarea, etc. Examples:', $this->text_domain ) ) . '</p>',
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'field_validation',
    'title' => __( 'Field Validation Options', $this->text_domain ),
    'content' => '<p>' . __( 'Based upon the type of field option you have selected, you may see different options for validation. Selecting \'required\' will make this part of your survey a required field. Some fields have a \'Maximum Length\' Validation option where you can specify a character limit. Field type "number" can have a maximum and minimum value added to it. As an example, if your survey asked a user their age, and you wanted to limit the survey to respondents who claim that they are 18 or older, you can enter 18 in the \'Min number allowed\' field.', $this->text_domain ) . '</p>',
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'survey_options',
    'title' => __( 'General Survey Options', $this->text_domain ),
    'content' => '<p>' . __( 'These are options for your survey, they are not built in to the survey form, but rather determine behavior of how your survey will work.', $this->text_domain ),
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'survey_authentication',
    'title' => __( 'Authentication', $this->text_domain ),
    'content' => '<p>' . __( 'Choose a method to prevent users from filling out the survey multiple times. User login is much more reliable, but may not be the best option for your site if you don\'t allow registrations. Cookie based authentication is very easy to circumvent, but will prevent most users from taking the survey multiple times. Of course, you may prefer to allow users to take the survey an unlimited number of times in which case you should choose \'None\'. Note to developers: methods can be added via the survey_auth_options filter.', $this->text_domain ) . '</p>',
   );
   $screen->add_help_tab( $args );
   $args = array(
    'id' => 'survey_thank_you',
    'title' => __( 'Thank You Message', $this->text_domain ),
    'content' => '<p>' . __( 'The message will be displayed to a user who completes the survey.', $this->text_domain ) . '</p>',
   );
   $screen->add_help_tab( $args );
  }
 }

 /**
  * AJAX handler to update styling options
  * @since 1.1
  */
 public function update_styling_options()
 {

   if ( ! wp_verify_nonce( $_POST['_nonce'], 'update-styling-options' ) || ! current_user_can( 'manage_options' ) ) {
    status_header( 403 );
    die();
   }
   $surveys = get_option( 'wwm_awesome_surveys', array() );
   $surveys['include_css'] = absint( $_POST['options']['include_css'] );
   update_option( 'wwm_awesome_surveys', $surveys );
   wp_send_json_success();
   exit;
 }

 public function create_survey_form()
 {

  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'create-survey' );
  $form = new FormOverrides( 'survey-manager' );
  $form->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
  $form->addElement( new Element_Textbox( __( 'Survey Name:', $this->text_domain ), 'survey_name', array( 'required' => 1 ) ) );
  $form->addElement( new Element_Hidden( 'action', 'create_survey' ) );
  $form->addElement( new Element_Hidden( 'create_survey_nonce', $nonce ) );
  $form->addElement( new Element_HTML( '<div class="create_holder">') );
  $form->addElement( new Element_Button( __( 'Start Building', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
  $form->addElement( new Element_HTML( '</div>') );

  $output = '
      <div class="overlay"><span class="preloader"></span></div>
      <div class="create half">';
  $output .= $form->render( true );
     $form = new FormOverrides( 'new-elements' );
     $form->addElement( new Element_HTML( '<div class="submit_holder"><div id="add-element"></div><div class="validation accordion"><h5>' . __( 'General Survey Options:', $this->text_domain ) . '</h5><div>' ) );
     $form->addElement( new Element_Textarea( __( 'A Thank You message:', $this->text_domain ), 'thank_you', array( 'value' => __( 'Thank you for completing this survey', $this->text_domain ), 'required' => 1 ) ) );
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
     $form->addElement( new Element_HTML( '<div class="ui-widget-content ui-corner-all validation field-validation"><span class="label"><p>' . __( 'To prevent people from filling the survey out multiple times you may select one of the options below', $this->text_domain ) . '</p></span>' ) );
     $form->addElement( new Element_Radio( 'Validation/authentication', 'auth', $options, array( 'value' => 'none' ) ) );
     $form->addElement( new Element_HTML( '</div></div></div>' ) );
     $form->addElement( new Element_Hidden( 'action', 'generate_preview' ) );
     $form->addElement( new Element_Button( __( 'Add Question', $this->text_domain ), 'submit', array( 'class' => 'button-primary' ) ) );
     $form->addElement( new Element_HTML( '</div>' ) );
     $output .= $form->render( true );

  $output .= '</div><!--.create-->
    <div id="preview" class="half">
     <div class="single-element-edit"><h4 class="survey-name"></h4><div class="button-holder"><button class="survey-name-edit">Edit Survey Title</button></div><div class="clear"></div></div>
     <div class="survey-preview">
     </div><!--.survey-preview-->
    </div><!--#preview-->
    <div class="clear"></div>';
  return $output;
 }

 public function output_survey_results()
 {

  $output = '
   <div class="your-surveys">
    <h4>' . __( 'Your Surveys', $this->text_domain ) . '</h4>
   </div>
   <div id="existing-surveys" class="existing-surveys">';
  $args = array();
  $output .= $this->display_survey_results( $args );
  $output .= '</div>';
  return $output;
 }

 public function output_results_by_user()
 {

  $output = '
   <div class="your-surveys">
    <h4>' . __( 'Results by User', $this->text_domain ) . '</h4>
   </div>
   <div id="survey-results" class="existing-surveys">';
  $args = array();
  $output .= $this->display_results_by_user( $args );
  $output .= '</div>';
  return $output;
 }

 public function output_howto_video()
 {

  return '<iframe width="420" height="315" src="//www.youtube.com/embed/YIta2rDE-QU" frameborder="0" allowfullscreen></iframe>';
 }

 public function admin_notices()
 {

  ?>
  <div class="updated">
    <p>
     <?php
      _e( 'Need help? There are handy tips for some of the options in the help menu. Click the help tab in the upper right corner of your screen', $this->text_domain );
      ?>
    </p>
    <?php
     printf( '<p><a href="%s">%s</a></p>', esc_url( add_query_arg( 'wwm_dismiss', wp_create_nonce( 'wwm_dismiss' ) ) ), __( 'Dismiss', $this->text_domain ) );
    ?>
   </div>
   <?php
 }

 /**
  * AJAX handler to output a 'type' selector to the survey form builder
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  * @uses render_element_selector
  */
 public function create_survey()
 {

  if ( ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   exit;
  }
  $data = get_option( 'wwm_awesome_surveys', array() );
  if ( isset( $data['surveys'] ) && ! empty( $data['surveys'] ) ) {
   if ( '' == $_POST['survey_name'] ) {
    wp_send_json_error( __( 'Survey Name cannot be blank', $this->text_domain ) );
   }
  }
  $form = $this->render_element_selector();
  $json = json_encode( array( 'form' => $form ) );
  if ( is_null( $json ) || false == $json ) {
   wp_send_json_error( 'json failure' );
  }
  echo $json;
  exit;
 }

 /**
  * Renders a dropdown select element with options
  * that coincide with the pfbc form builder class
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
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
  $survey_id = ( isset( $_POST['survey_id'] ) ) ? intval( $_POST['survey_id'] ) : -1;
  $html = '<input type="hidden" name="survey_name" value="' . esc_html( stripslashes( $_POST['survey_name'] ) ) . '" data-id="' . sanitize_title( stripslashes( $_POST['survey_name'] ) ) . '">';
  $html .= '<input type="hidden" name="survey_id" value="' . $survey_id . '" data-id="' . sanitize_title( stripslashes( $_POST['survey_name'] ) ) . '">';
  $html .= '<div id="new-element-selector"><span>' . __( 'Add a question to your survey:', $this->text_domain ) . '</span><label>' . __( 'Select Field Type:', $this->text_domain ) . '<br><select name="options[type]" class="type-selector">';
  foreach ( $types as $type => $pfbc_method ) {
   $html .= '<option value="' . $pfbc_method . '">' . $type . '</option>';
  }
  $html .= '</select></label></div>';
  return $html;
 }

 /**
  * AJAX handler which will output
  * some form elements so that information can be gathered
  * about the element that a user is adding to their survey
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function element_info_inputs()
 {

  if ( ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
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
  $html .= '<label>' . __( 'The question you are asking:', $this->text_domain ) . '<br><input type="text" name="options[name]" required></label>';
  if ( ! empty( $validation_elements ) ) {
   $html .= '<div class="ui-widget-content field-validation validation ui-corner-all"><h5>'. __( 'Field Validation Options', $this->text_domain ) . '</h5>';
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
  $needs_options = array( 'radio', 'checkbox', 'dropdown selection' );
  if ( in_array( $_POST['text'], $needs_options ) ) {
   $html .= '<span class="label">' . __( 'Number of answers required?', $this->text_domain ) . '</span><div class="slider-wrapper"><div id="slider"></div><div class="slider-legend"></div></div><div id="options-holder">';
   $html .= $this->options_fields( array( 'num_options' => 1, 'ajax' => false ) );
   $html .= '</div>';
  }
  echo json_encode( array( 'form' => $html ) );
  exit;
 }

 /**
  * AJAX handler to get the element selector html
  * @since 1.3
  */
 public function get_element_selector() {
  echo $this->render_element_selector();
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
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
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
   'label_text' => __( 'Min number allowed', $this->text_domain ),
   'tag' => 'input',
   'type' => 'number',
   'name' => 'min',
  );
  $max = array(
   'label_text' => __( 'Max number allowed', $this->text_domain ),
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
   'label_text' => __( 'Maximum Length (in number of characters)', $this->text_domain ),
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

 /**
  * AJAX handler to generate some fields
  * for survey option inputs
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function options_fields( $args = array() )
 {

  $defaults = array(
   'num_options' => ( isset( $_POST['num_options'] ) ) ? $_POST['num_options'] : 1,
   'ajax' => true,
  );
  $args = wp_parse_args( $args, $defaults );
  $html = '';
  for ( $iterations = 0; $iterations < absint( $args['num_options'] ); $iterations++ ) {
   $label = $iterations + 1;
   $html .= '<label>' . __( 'Answer', $this->text_domain ) . ' ' . $label . '<br><input type="text" name="options[label][' . $iterations . ']" required></label><input type="hidden" name="options[value][' . $iterations . ']" value="' . $iterations . '"><label>' . __( 'default?', $this->text_domain ) . '<br><input type="radio" name="options[default]" value="' . $iterations . '"></label>';
  }
  if ( $args['ajax'] ) {
   echo $html;
   exit;
  }
  else return $html;
 }

 /**
  * Removes some unneeded bits and pieces from
  * the survey form prior to displaying for preview &
  * prior to json_encoding the array of elements for storage in the db
  * @param  array $form_elements_array an array of form elements
  * @return array $form_elements_array the filtered form elements
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function awesome_surveys_form_preview( $form_elements_array )
 {

  $defaults = array(
   'required' => false,
   'rules' => array(),
  );

  $form_elements_array['validation'] = wp_parse_args( $form_elements_array['validation'], $defaults );
  if ( isset( $form_elements_array['validation']['rules'] ) ) {
   unset( $form_elements_array['validation']['rules']['number_validation_type'] );
   foreach ( $form_elements_array['validation']['rules'] as $key => $value ) {
    if ( is_null( $value ) || '' == $value && 'required' != $value ) {
     unset( $form_elements_array['validation']['rules'][$key] );
    }
   }
  }
  return $form_elements_array;
 }

 /**
  * AJAX handler to generate the form preview
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function generate_preview()
 {

  $form_elements_array = $_POST;
  /**
   * This filter facilitates the modification of form elements
   * prior to the form being output to preview, and prior to the
   * form elements being json_encoded for db storage. The intended use is
   * to allow for elements to exist within the form builder that do not have
   * a purpose in the survey form. As an example, if an radio element were
   * added to the form builder to choose a type of advanced validation, and
   * that radio was used to add/enable a rule field, the rule field is the
   * one that wants to be saved to the db, but the radio doesn't. Get rid of
   * the radio via apply_filters( 'awesome_surveys_form_preview' ).
   */
  $form_elements_array['options'] = apply_filters( 'awesome_surveys_form_preview', $form_elements_array['options'] );
  $survey_name = $form_elements_array['survey_name'];
  $survey_id = ( intval( $_POST['survey_id'] ) > -1 ) ? $_POST['survey_id'] : '-1';
  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'create-survey' );
  $form = new FormOverrides( sanitize_title( $form_elements_array['survey_name'] ) );
  $form->configure( array( 'class' => 'pure-form pure-form-stacked' ) );
  if ( isset( $form_elements_array['existing_elements'] ) ) {
   $element_json = json_decode( stripslashes( $form_elements_array['existing_elements'] ), true );
  }
  $required_is_option = array( 'Element_Textbox', 'Element_Textarea', 'Element_Email', 'Element_Number' );
  $existing_elements = ( isset( $element_json ) ) ? array_merge( $element_json, array( $form_elements_array['options'] ) ) : array( $form_elements_array['options'] );
  $elements_count = 0;
  foreach ( $existing_elements as $element ) {
   $method = $element['type'];
   $options = $atts = $rules = array();
   if ( isset( $element['validation']['rules'] ) && is_array( $element['validation']['rules'] ) ) {
    foreach ( $element['validation']['rules'] as $key => $value ) {
     if ( '' != $value && ! is_null( $value ) ) {
      $rules['data-' . $key] = $value;
     }
    }
   }
   if ( in_array( $method, $required_is_option ) && ! empty( $rules ) ) {
     $options = array_merge( $options, $rules );
   } else {
    $atts = array_merge( $options, $rules );
   }
   if ( ! empty( $element['validation']['required'] ) && 'false' != $element['validation']['required'] ) {
    if ( in_array( $method, $required_is_option ) ) {
     $options['required'] = 1;
     $options['class'] = 'required';
    } else {
     $atts['required'] = 1;
     $atts['class'] = 'required';
    }
   }
   $max = ( isset( $element['label'] ) ) ? count( $element['label'] ) : 0;
   for ( $iterations = 0; $iterations < $max; $iterations++ ) {
    /**
     * Since the pfbc is being used, and it has some weird issue with values of '0', but
     * it will work if you append :pfbc to it...not well documented, but it works!
     */
    $options[$element['value'][$iterations] . ':pfbc'] = htmlentities( stripslashes( $element['label'][$iterations] ) );
   }
   $atts['value'] = ( isset( $element['default'] ) ) ? $element['default']  : null;
   $form->addElement( new Element_HTML( '<div class="single-element-edit">' ) );
   $form->addElement( new $method( htmlentities( stripslashes( $element['name'] ) ), sanitize_title( $element['name'] ), $options, $atts ) );
   $form->addElement( new Element_HTML( '<div class="button-holder"><button class="element-edit" data-action="delete" data-index="' . $elements_count . '">' . __( 'Delete question', $this->text_domain ) . '</button><button class="element-edit" data-action="edit" data-index="' . $elements_count . '">' . __( 'Edit question', $this->text_domain ) . '</button></div><div class="clear"></div></div>' ) );
   $elements_count++;
  }
  $preview_form = $form->render( true );
  $form = new FormOverrides( 'save-survey' );
  $form->configure( array( 'class' => 'save' ) );
  $auth_messages = array( 'none' => __( 'None', $this->text_domain ), 'cookie' => __( 'Cookie Based', $this->text_domain ), 'login' => __( 'User must be logged in', $this->text_domain ) );
  $auth_type = esc_attr( $_POST['auth'] );
  $form->addElement( new Element_HTML( '<span class="label">' . __( 'Type of authentication: ', $this->text_domain ) . $auth_messages[ $auth_type ] . '</span>' ) );
  $form->addElement( new Element_Hidden( 'auth', $auth_type ) );
  $thank_you_message = ( '' != $_POST['thank_you'] ) ? sanitize_text_field( $_POST['thank_you'] ) : __( 'Thank you for completing this survey', $this->text_domain );
  $form->addElement( new Element_Hidden( 'thank_you', $thank_you_message ) );
  $form->addElement( new Element_HTML( '<span class="label">' . __( 'Thank you message:', $this->text_domain ) . '</span><div>' . $thank_you_message . '</div>' ) );
  $form->addElement( new Element_Hidden( 'create_survey_nonce', $nonce ) );
  $form->addElement( new Element_Hidden( 'action', 'wwm_save_survey' ) );
  $form->addElement( new Element_Hidden( 'existing_elements', json_encode( $existing_elements ) ) );
  $form->addElement( new Element_Hidden( 'survey_id', $survey_id . ':pfbc' ) );
  $form->addElement( new Element_Hidden( 'survey_name', $survey_name ) );
  $form->addElement( new Element_Button( __( 'Reset', $this->text_domain ), 'submit', array( 'class' => 'button-secondary reset-button', 'name' => 'reset' ) ) );
  $form->addElement( new Element_Button( __( 'Save Survey', $this->text_domain ), 'submit', array( 'class' => 'button-primary', 'name' => 'save' ) ) );
  $save_form = $form->render(true);
  echo $preview_form . $save_form;
  exit;
 }

 /**
  * AJAX handler to save the survey form details to the db.
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function save_survey()
 {

  if ( ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }
  /**
   * Build an empty array to hold responses.
   * This needs to be able to hold individual responses to elements that
   * that are free-form input (text, email, number)
   * and count of responses that are selected/checked options.
   */
  $has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
  $form_elements = json_decode( stripslashes( $_POST['existing_elements'] ), true );
  if ( is_null( $form_elements ) || false == $form_elements ) {
   wp_send_json_error( 'json failure' );
  }
  $responses = array();
  $question_count = 0;
  foreach ( $form_elements as $survey_question ) {
   $responses[$question_count]['has_options'] = 0;
   $responses[$question_count]['question'] = $survey_question['name'];
   $responses[$question_count]['answers'] = array();
   if ( in_array( $survey_question['type'],  $has_options ) ) {
    $responses[$question_count]['has_options'] = 1;
    foreach ( $survey_question['value'] as $key => $value ) {
     $responses[$question_count]['answers'][] = array();
    }
   }
   $question_count++;
  }
  $data = get_option( 'wwm_awesome_surveys', array() );
  $surveys = ( isset( $data['surveys'] ) ) ? $data['surveys'] : array();
  $end = strpos( $_POST['survey_id'], ':' );
  $survey_id = substr( $_POST['survey_id'], 0, $end );
  $survey_id = ( isset( $_POST['survey_id'] ) && is_numeric( $survey_id ) && intval( $survey_id ) > -1 ) ? intval( $survey_id ) : count( $surveys );
  $form = json_encode( $form_elements );
  $surveys[$survey_id] = array( 'name' => sanitize_text_field( $_POST['survey_name'] ), 'form' => $form, 'thank_you' => ( isset( $_POST['thank_you'] ) ) ? sanitize_text_field( $_POST['thank_you'] ) : null, 'auth' => esc_attr( $_POST['auth'] ), 'responses' => $responses, );
  $data['surveys'] = $surveys;
  add_action( 'update_option', array( &$this, 'did_option_update' ), 10, 1 );
  $success = update_option( 'wwm_awesome_surveys', $data );
  if ( $success || ( isset( $this->option_updated ) && true === $this->option_updated ) ) {
   wp_send_json_success();
  } else {
   wp_send_json_error( 'The update_option function returned false. Survey not saved?' );
  }
 }

 public function did_option_update( $option = '' ) {
  if ( 'wwm_awesome_surveys' == $option ) {
   $this->option_updated = true;
  }
 }

 /**
  * AJAX handler for question editing
  * @since 1.1
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  */
 public function edit_question()
 {

  if ( ! wp_verify_nonce( $_POST['_nonce'], 'edit-question_' . $_POST['survey_id'] . '_' . $_POST['question_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }

  $updated = false;
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  if ( isset( $surveys['surveys'][$_POST['survey_id']] ) ) {
   $survey = $surveys['surveys'][$_POST['survey_id']];
   $form = json_decode( $survey['form'], true );
   $question = sanitize_text_field( $_POST['question'] );
   $form[$_POST['question_id']]['name'] = $question;
   $survey['form'] = json_encode( $form );
   $survey['responses'][$_POST['question_id']]['question'] = $question;
   $surveys['surveys'][$_POST['survey_id']] = $survey;
   $updated = update_option( 'wwm_awesome_surveys', $surveys );
  }

  if ( $updated ) {
   wp_send_json_success();
  } else {
   wp_send_json_error();
  }
  exit;
 }

 /**
  * AJAX handler for answer editing
  * @since 1.1
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  */
 public function edit_answer()
 {

  if ( ! wp_verify_nonce( $_POST['_nonce'], 'edit-answer_' . $_POST['question_id'] . '_' . $_POST['answer_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }
  $updated = false;
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  if ( isset( $surveys['surveys'][$_POST['survey_id']] ) ) {
   $survey = $surveys['surveys'][$_POST['survey_id']];
   $form = json_decode( $survey['form'], true );
   $answer = sanitize_text_field( $_POST['answer'] );
   $form[$_POST['question_id']]['label'][$_POST['answer_id']] = $answer;
   $survey['form'] = json_encode( $form );
   $surveys['surveys'][$_POST['survey_id']] = $survey;
   $updated = update_option( 'wwm_awesome_surveys', $surveys );
  }

  if ( $updated ) {
   wp_send_json_success();
  } else {
   wp_send_json_error();
  }
  exit;
 }

 public function edit_survey_name()
 {

  if ( ! wp_verify_nonce( $_POST['_nonce'], 'edit-survey-name_' . $_POST['survey_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  if ( $this->is_existing_name( esc_html( stripslashes( $_POST['name'] ) ), $surveys ) ) {
   wp_send_json_error( array( 'message' => __( 'The name already exists', $this->text_domain ) ) );
   exit;
  }

  $survey = $surveys['surveys'][$_POST['survey_id']];
  $survey['name'] = esc_html( stripslashes( $_POST['name'] ) );
  $surveys['surveys'][$_POST['survey_id']] = $survey;
  update_option( 'wwm_awesome_surveys', $surveys );
  wp_send_json_success();
  exit;
 }

 public function edit_survey_thanks()
 {

  if ( ! wp_verify_nonce( $_POST['_nonce'], 'edit-survey-thanks_' . $_POST['survey_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $survey = $surveys['surveys'][$_POST['survey_id']];
  $survey['thank_you'] = sanitize_text_field( $_POST['thank_you'] );
  $surveys['surveys'][$_POST['survey_id']] = $survey;
  update_option( 'wwm_awesome_surveys', $surveys );
  wp_send_json_success( $survey['thank_you'] );
  exit;
 }

 public function get_auth_method_edit_form()
 {

  if ( ! wp_verify_nonce( $_POST['_nonce'], 'edit-survey-auth_' . $_POST['survey_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $survey = $surveys['surveys'][$_POST['survey_id']];
  $options = apply_filters( 'survey_auth_options', array() );
  $html = '<form id="edit-survey-auth-method" class="pure-form pure-form-stacked">';
  $html .= '<p>' . __( 'A survey that has had responses can not be changed to the "Logged in" auth method', $this->text_domain ) . '</p>';
  foreach ( $options as $key => $value ) {
   $html .= '<p><input type="radio" name="auth" value="' . $key . '"' . checked( $key == $survey['auth'], true, false ) . disabled(  'login' == $key && isset( $survey['num_responses'] ), true, false ) . '> ' . $value . '</p>';
  }
  $html .= '<input type="hidden" name="original_auth" value="' . $survey['auth'] . '">
            <input type="hidden" name="_nonce" value="' . $_POST['_nonce'] . '">
            <input type="hidden" name="survey_id" value="' . $_POST['survey_id'] . '">
            <input type="hidden" name="action" value="wwm_edit_survey_auth">
           </form>';
   wp_send_json_success( $html );
 }

 public function edit_survey_auth()
 {

  if ( ! wp_verify_nonce( $_POST['_nonce'], 'edit-survey-auth_' . $_POST['survey_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }

  if ( $_POST['original_auth'] == $_POST['auth'] ) {
   wp_send_json_success();//nothing to do.
  }

  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $survey = $surveys['surveys'][$_POST['survey_id']];
  $survey['auth'] = sanitize_text_field( $_POST['auth'] );
  if ( 'login' != $survey['auth'] && 'login' == $_POST['original_auth'] && isset( $survey['respondents'] ) ) {
   unset( $survey['respondents'] );
  }
  $surveys['surveys'][$_POST['survey_id']] = $survey;
  update_option( 'wwm_awesome_surveys', $surveys );
  wp_send_json_success();
 }

 public function survey_edit_name_inline() {
  echo sanitize_text_field( $_POST['edit_survey_name'] );
  exit;
 }

 /**
  * AJAX handler for survey removal
  * @since 1.1
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  */
 public function delete_survey()
 {

  if ( ! wp_verify_nonce( $_POST['delete_survey'], 'delete-survey_' . $_POST['survey_id'] ) || ! current_user_can( 'manage_options' ) ) {
   status_header( 403 );
   die();
  }
  $updated = false;
  $surveys = get_option( 'wwm_awesome_surveys', array() );
  $surveys['surveys'][$_POST['survey_id']] = array();
  $updated = update_option( 'wwm_awesome_surveys', $surveys );
  if ( $updated ) {
   wp_send_json_success();
  } else {
   wp_send_json_error();
  }
  exit;
 }

 /**
  * Alias for Awesome_Surveys_Frontend::process_response
  * Here because of the way wp_ajax_$action works
  * @since  1.0
  */
 public function process_response()
 {

  if ( ! class_exists( 'Awesome_Surveys_Frontend' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/class.awesome-surveys-frontend.php' );
   $frontend = new Awesome_Surveys_Frontend;
  }
  $frontend->process_response();
 }

 public function default_auth_methods( $options = array() )
 {

  $options = array( 'login' => __( 'User must be logged in', $this->text_domain ), 'cookie' => __( 'Cookie based', $this->text_domain ), 'none' => __( 'None' ) );
  return $options;
 }

 private function is_existing_name( $name = '', $surveys = array() )
 {

  if ( empty( $surveys ) ) {
   return false;
  }
  foreach ( $surveys['surveys'] as $key => $survey ) {
   if ( empty( $survey ) ) {
    unset( $surveys['surveys'][$key] );
   }
  }
  if ( isset( $surveys['surveys'] ) && ! empty( $surveys['surveys'] ) ) {
   $names = wp_list_pluck( $surveys['surveys'], 'name' );
   if ( in_array( $name, $names ) ) {
    return true;
   }
  }
  return false;
 }

 public function get_json() {
  $defaults = array(
   'name' => null,
   'validation' => array(
    'required' => false,
    'rules' => array(),
   ),
  );
  $arr = wp_parse_args( $_POST['options'], $defaults );
  $max = ( isset( $_POST['options']['label'] ) ) ? count( $_POST['options']['label'] ) : 0;
  for ( $iterations = 0; $iterations < $max; $iterations++ ) {
   $arr['value'][$iterations] = $iterations;
  }
  $arr['name'] = html_entity_decode( stripslashes( sanitize_text_field( htmlentities( $arr['name'] ) ) ) );
  if ( $arr['label'] ) {
   foreach ( $arr['label'] as $key => $value ) {
    $arr['label'][$key] = stripslashes( sanitize_text_field( $value ) );
   }
  }
  wp_send_json_success( json_encode( $arr ) );
 }

 /**
  * outputs some links to the WtWM main admin plugin page.
  * @param  int $plugin_links_version the plugin links version.
  * @since 1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 public function output_links( $plugin_links_version )
 {

  if ( 1 == $plugin_links_version ) {
   echo '<ul>
          <li><a href="https://github.com/WillBrubaker/awesome-surveys" title="' . __( 'Fork Me on GitHub', $this->text_domain ) . '">' . __( 'Awesome Surveys on github', $this->text_domain ) . '</a></li>
          <li><a href="http://wordpress.org/support/plugin/awesome-surveys" title="Get Support">' . __( 'Support for Awesome Surveys', $this->text_domain ) . '</a></li>
          <li><a href="http://wordpress.org/support/view/plugin-reviews/awesome-surveys" title="' . __( 'Review the Awesome Surveys Plugin', $this->text_domain ) . '">' . __( 'Rate Awesome Surveys', $this->text_domain ) . '</a></li>
          <li><a href="http://ctt.ec/qNg6L" title="' . __( 'Shout it From the Rooftops!' , $this->text_domain ) . '">' . __( 'Tweet this plugin', $this->text_domain ) . '</a></li>
          <li>' . __( 'Donate to the development of the Awesome Surveys plugin', $this->text_domain ) . '
           <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input name="cmd" type="hidden" value="_s-xclick" />
            <input name="hosted_button_id" type="hidden" value="634DZTUWQA2ZU" />
            <input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" type="image" />
            <img src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="Donate" width="1" height="1" border="0" />
           </form>
          </li>
         </ul>';
  }
 }

 /**
  * hooked into admin_init and simply adds a couple of meta boxes to the admin interface for this plugin
  * @since  1.4
  */
 public function add_meta_boxes()
 {

  add_meta_box( 'wwm-awesome-surveys-ratings', __( 'Rate Awesome Surveys', $this->text_domain ), array( &$this, 'rating_box' ), $this->menu_slug, 'normal' );
  add_meta_box( 'wwm-ratings-awesome-surveys-news', __( 'Awesome Surveys News', $this->text_domain ), array( &$this, 'news_box' ), $this->menu_slug, 'normal' );
 }

 /**
  * outputs the content of the ratings meta box
  * @since  1.4
  */
 public function rating_box() {

  echo '<p>This otter would love it if you <a href="http://wordpress.org/support/view/plugin-reviews/awesome-surveys?filter=5" title="give this plugin a 5-star rating">give this plugin 5 stars</a></p><p><a href="http://wordpress.org/support/view/plugin-reviews/awesome-surveys?filter=5" title="give this plugin a 5-star rating"><img src="' . WWM_AWESOME_SURVEYS_URL . '/images/otter.jpg" alt="begging otter"></a></p>';
 }

 /**
  * outputs the contents of the news meta box
  * @since 1.4
  */
 public function news_box() {
  echo '<h3>Call for beta testers</h3><p>Did you know that an extension for Awesome Surveys that will allow the exporting of survey results in CSV format is being actively developed? I need feedback from YOU!. Get started by <a href="http://plugins.willthewebmechanic.com/repo/awesome-surveys-export-csv.zip" title="get beta version of plugin extension">downloading the extension</a> today!</p>';
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

 /**
  * loads translation files as applicable
  * @since 1.5
  */
 public function load_translations()
 {

  load_plugin_textdomain( $this->text_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
 }
}
$var = new Awesome_Surveys;
