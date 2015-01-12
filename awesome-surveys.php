<?php
/*
Plugin Name: Awesome Surveys
Plugin URI: http://www.willthewebmechanic.com/awesome-surveys
Description: Easily create surveys for your WordPress website and publish them with a simple shortcode
Version: 1.6.3
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

	$admin_includes = array(
		'awesome-surveys-admin'
		);
	$frontend_includes = array(
		'awesome-surveys-frontend',
		);
	$includes = array(
		'awesome-surveys',
		'awesome-surveys-ajax-actions',
		);

		foreach ( $includes as $include_file ) {
			include_once( plugin_dir_path( __FILE__ ) . 'includes/class-' . $include_file . '.php' );
		}

		if ( is_admin() ) {
		foreach ( $admin_includes as $include_file ) {
			include_once( plugin_dir_path( __FILE__ ) . 'includes/class-' . $include_file . '.php' );
		}
	} else {
		foreach ( $frontend_includes as $include_file ) {
			include_once( plugin_dir_path( __FILE__ ) . 'includes/class-' . $include_file . '.php' );
		}
	}

		if ( ! defined( 'WWM_AWESOME_SURVEYS_URL' ) ) {
			define( 'WWM_AWESOME_SURVEYS_URL', plugins_url( '', __FILE__ ) );
		}
		if ( ! defined( 'WWM_AWESOME_SURVEYS_PATH' ) ) {
			define( 'WWM_AWESOME_SURVEYS_PATH', plugin_dir_path( __FILE__ ) );
		}

		$awesome_surveys_ajax = new Awesome_Surveys_Ajax;
		$awesome_surveys_nopriv_ajax_actions = array(

			);
		$awesome_surveys_ajax_actions = array(
			'add-form-element' => 'add_form_element',
   'options-fields' => 'echo_options_fields',
   'generate-preview' => 'generate_preview',
   'get-preview' => 'get_preview',
   'wwm-as-get-json' => 'wwm_as_get_json',
			);

		foreach ( $awesome_surveys_nopriv_ajax_actions as $action => $function ) {
			add_action( 'wp_ajax_nopriv_' . $action, array( $awesome_surveys_ajax, $function ) );
			add_action( 'wp_ajax_' . $action, array( $awesome_surveys_ajax, $function ) );
		}
		foreach ( $awesome_surveys_ajax_actions as $action => $function ) {
			add_action( 'wp_ajax_' . $action, array( $awesome_surveys_ajax, $function ) );
		}

   /**
  * Builds the survey form from the stored options in the database.
  * @param  array $form an array of form elements - this array was stored in the db when the survey was created
  * @param  array $args an array of arguments, includes the survey id and the survey name
  * @return string an html form
  * @since  1.0
  * @author Will the Web Mechanic <will@willthewebmechanic.com>
  * @link http://willthewebmechanic.com
  */
 function awesome_surveys_render_form( $form = array(), $args = array() ) {

  error_log( print_r( $form, true ) );
  $buttons = array(
   'text' => 'Element_Textbox',
   'email' => 'Element_Email',
   'number' => 'Element_Number',
   'dropdown' => 'Element_Select',
   'radio' => 'Element_Radio',
   'checkbox' => 'Element_Checkbox',
   'textarea' => 'Element_Textarea',
  );
  if ( ! class_exists( 'Form' ) ) {
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Form.php' );
   include_once( plugin_dir_path( __FILE__ ) . 'includes/PFBC/Overrides.php' );
  }
  $nonce = wp_create_nonce( 'answer-survey' );
  $has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
  $form_output = new FormOverrides();
  $form_output->configure( array( 'class' => 'answer-survey pure-form pure-form-stacked', 'action' => $_SERVER['REQUEST_URI'], ) );
  $form_output->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
  $questions_count = 0;
  foreach ( $form as $element ) {
   $method = $buttons[ $element['type'] ];
   $atts = $rules = $options = array();
   if ( 'Element_Select' == $method ) {
    $options[''] = __( 'make a selection...', $this->text_domain );
   }
   if ( isset( $element['validation']['rules'] ) ) {
    foreach ( $element['validation']['rules'] as $key => $value ) {
     if ( '' != $value ) {
      $rules['data-rule-' . $key] = $value;
     }
    }
   }
   if ( in_array( $method, $has_options ) ) {
    $atts = array_merge( $atts, $rules );
    if ( isset( $element['default'] ) ) {
     $atts['value'] = $element['default'];
    }
    if ( isset( $element['validation']['required'] ) && false != $element['validation']['required'] ) {
     $atts['required'] = 'required';
    }
    foreach ( $element['value'] as $key => $value ) {
     /**
      * append :pfbc to the key so that pfbc doesn't freak out
      * about numerically keyed arrays.
      */
     $options[$value . ':pfbc'] = stripslashes( $element['label'][$key] );
    }
   } else {
    $options = array_merge( $options, $rules );
    if ( isset( $element['default'] ) ) {
     $options['value'] = $element['default'];
    }
    if ( isset( $element['validation']['required'] ) && false != $element['validation']['required'] ) {
     $options['required'] = 'required';
    }
   }
   $form_output->addElement( new $method( stripslashes( $element['name'] ), 'question[' . $questions_count . ']', $options, $atts ) );
   $questions_count++;
  }
  $form_output->addElement( new Element_Hidden( 'answer_survey_nonce', $nonce ) );
  $form_output->addElement( new Element_Hidden( 'survey_id', '', array( 'value' => $args['survey_id'], ) ) );
  $form_output->addElement( new Element_Hidden( 'action', 'answer_survey' ) );
  $form_output->addElement( new Element_Hidden( 'auth_method', $args['auth_method'] ) );
  $form_output->addElement( new Element_Button( __( 'Submit Response', 'awesome-surveys' ), 'submit', array( 'class' => 'button-primary', 'disabled' => 'disabled' ) ) );
  return $form_output->render( true );
 }
