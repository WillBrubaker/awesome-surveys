<?php
/**
	* @package Awesome_Surveys
	*
	*/
class Awesome_Surveys_Frontend extends Awesome_Surveys {

	public function __construct() {
		$filters = array(
			'wwm_awesome_survey_response' => array( 'wwm_awesome_survey_response_filter', 10, 2 ),
			'awesome_surveys_auth_method_login' => array( 'awesome_surveys_auth_method_login', 10, 1 ),
			'awesome_surveys_auth_method_cookie' => array( 'awesome_surveys_auth_method_cookie', 10, 1 ),
			'the_content' => array( 'the_content', 10, 1 ),
			);
		foreach ( $filters as $filter => $args ) {
				add_filter( $filter, array( $this, $args[0] ), $args[1], $args[2] );
		}
		parent::__construct();
		add_shortcode( 'wwm_survey', array( $this, 'wwm_survey' ) );
		add_filter( 'awesome_surveys_auth_method_none', '__return_true' );
		$actions = array(
			'wp_enqueue_scripts' => array( 'register_scripts', 10, 0 ),
			'init' => array( 'init', 10, 0 ),
			);
		foreach ( $actions as $action => $args ) {
			add_action( $action, array( $this, $args[0] ), $args[1], $args[2] );
		}
	}

	/**
		* This is the callback from the shortcode 'wwm_survey'. It takes a survey id ($atts['id'])
		* and gets the options for that survey from the db, then passes some of that data off to render_form
		* to eventually output the survey to the frontend. Also enqueues necessary js and css for the form.
		* @param  array $atts an array of shortcode attributes
		* @return mixed string|null  if there is a survey form to output will return an html form, else returns null
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function wwm_survey( $atts ) {

		if ( ! isset( $atts['id'] ) ) {
			return null;
		}
		$atts['id'] = absint( $atts['id'] );
		$survey = get_post( $atts['id'], 'OBJECT', 'display' );
		if ( is_null( $survey ) ) {
			$old_surveys = get_option( 'wwm_as_survey_id_map', array() );
			if ( array_key_exists( $atts['id'], $old_surveys ) ) {
				$survey = get_post( $old_surveys[ $atts['id'] ], 'OBJECT', 'display' );
				if ( 'awesome-surveys' != $survey->post_type ) {//well, we tried...
					return null;
				} else {
					$atts['id'] = $old_surveys[ $atts['id'] ];
				}
			}
		}
		if ( is_null( $survey ) ) {
			return null;
		}
		$auth_args = array(
			'survey_id' => $atts['id'],
			);
		$auth_key = get_post_meta( $atts['id'], 'survey_auth_method', true );
		$auth_method = ( $auth_key ) ? $this->auth_methods[ $auth_key ]['name'] : 'none';
		if ( false !== apply_filters( 'awesome_surveys_auth_method_' . $auth_method, $auth_args ) ) {
			wp_enqueue_script( 'awesome-surveys-frontend' );
			if ( defined( 'WPLANG' ) || false != get_option( 'WPLANG', false ) ) {
				add_action( 'wp_footer', array( $this, 'validation_messages' ), 90, 0 );
			}
			$options = get_option( 'wwm_awesome_surveys_options', array() );
			$include_css = ( isset( $options['general_options']['include_css'] ) ) ? absint( $options['general_options']['include_css'] ) : 1;
			if ( $include_css ) {
				wp_enqueue_style( 'awesome-surveys-frontend-styles' );
			}
			/**
				* wwm_survey action hook added in v1.4
				* a hook so that any js/css needed by extensions can be enqueued
				*/
			do_action( 'wwm_survey' );
		} else {
			/**
			* If the user fails the authentication method, the failure message can be customized via
			* add_filter( 'wwm_survey_no_auth_message' )
			* @var string
			* @see awesome_surveys_auth_method_login() which adds a filter if the user is not logged in
			* @see not_logged_in_message() which is the filter used to customize the message if the user is not logged in.
			*/
			return apply_filters( 'wwm_survey_no_auth_message', sprintf( '<p>%s</p>', __( 'Your response to this survey has already been recorded. Thank you!', 'awesome-surveys' ) ) );
		}
		$nonce = wp_create_nonce( 'answer-survey' );
		$survey_form = sprintf( '<%1$s %3$s>%2$s</%1$s>', apply_filters( 'wwm_survey_title_tag', 'h4' ), $survey->post_title, apply_filters( 'wwm_survey_title_atts', '' ) ) . str_replace( 'value="answer_survey_nonce"', 'value="' . $nonce . '"', $survey->post_content );
		return $survey_form;
	}


	/**
		* registers necessary styles & scripts for later use
		* @since 1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function register_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_style( 'normalize-css', WWM_AWESOME_SURVEYS_URL . '/css/normalize.min.css' );
		wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css' );
		wp_register_script( 'jquery-validation-plugin', WWM_AWESOME_SURVEYS_URL . '/js/jquery.validate.min.js', array( 'jquery' ), '1.13.1' );
		wp_register_script( 'awesome-surveys-frontend', WWM_AWESOME_SURVEYS_URL .'/js/script' . $suffix . '.js', array( 'jquery', 'jquery-validation-plugin' ), $this->plugin_version, true );
		wp_register_style( 'awesome-surveys-frontend-styles', WWM_AWESOME_SURVEYS_URL . '/css/style' . $suffix . '.css', array( 'normalize-css', 'pure-forms-css' ), $this->plugin_version, 'all' );
		wp_localize_script( 'awesome-surveys-frontend', 'wwm_awesome_surveys', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'countDownMessage' => apply_filters( 'wwm_as_countdown_message', __( 'Characters remaining', 'awesome-surveys' ) ) ) );
		if ( is_singular( 'awesome-surveys' ) ) {
			$options = get_option( 'wwm_awesome_surveys_options', array() );
			$include_css = ( isset( $options['general_options']['include_css'] ) ) ? absint( $options['general_options']['include_css'] ) : 1;
			if ( $include_css ) {
				wp_enqueue_style( 'awesome-surveys-frontend-styles' );
			}
			wp_enqueue_script( 'awesome-surveys-frontend' );
			if ( defined( 'WPLANG' ) || false != get_option( 'WPLANG', false ) ) {
				add_action( 'wp_footer', array( $this, 'validation_messages' ), 90, 0 );
			}
		}
	}

	/**
		* This filter is conditionally added if the auth method
		* is login and the user is not logged in.
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		* @param  string $message a message to display to the user
		* @return string          the filtered message.
		*/
	public function not_logged_in_message( $message ) {

		return sprintf( '<p>%s</p>', __( 'You must be logged in to participate in this survey', 'awesome-surveys' ) );
	}

	/**
		* This filter is applied if the auth type is 'login'. It adds the
		* current user id to the survey['respondents'] array so that
		* the auth method 'login' can check if the current user has already
		* filled out the survey.
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		* @param  array $survey    an array of survey responses
		* @param  string $auth_type an authorization type
		* @return array  $survey the filtered array of survey responses.
		*/
	public function wwm_awesome_survey_response_filter( $survey, $auth_type ) {

		if ( 'login' == $auth_type ) {
			$survey['respondents'][] = get_current_user_id();
		}
		return $survey;
	}


	/**
		* Attempts to output language localized validation messages if the localized
		* messages file exists. Wraps those messages up in a jQuery noconflict wrapper.
		* @since 1.3
		*/
	public function validation_messages() {

		if ( ! $lang = get_option( 'WPLANG', false ) ) {
			$lang = WPLANG;
		}

		$path = WWM_AWESOME_SURVEYS_PATH . '/js/localization/';
		$file = $path . 'messages_' . $lang . '.js';
		//There are some language files which are regionally specific
		//if that one exists, use it, if not, look for the general one
		if ( ! file_exists( $file ) ) {
			$lang = substr( $lang, 0, 2 );
			$file = $path . 'messages_' . $lang . '.js';
		}
		if ( file_exists( $file ) && $messages_file = fopen( $file, 'r' ) ) {
			$messages = fread( $messages_file, filesize( $path . 'messages_' . $lang . '.js' ) );
			echo '<script>';
			echo 'jQuery(document).ready(function($){';
			echo $messages;
			echo '});';
			echo '</script>';
		}
	}
}