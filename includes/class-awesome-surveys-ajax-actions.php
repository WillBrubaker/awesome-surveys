<?php

class Awesome_Surveys_Ajax extends Awesome_Surveys {

	public function __construct() {
		parent::__construct();

		$filters = array(
			'survey_validation_elements' => array( 10, 2 ),
			'get_validation_elements_number' => array( 10, 1 ),
			'get_validation_elements_text' => array( 10, 1 ),
			'get_validation_elements_textarea' => array( 10, 1 ),
			);
		foreach ( $filters as $filter => $args ) {
			add_filter( $filter, array( $this, $filter ), $args[0], $args[1] );
		}

		$actions = array(
			'awesome_surveys_update_login' => array( 'update_logged_in_respondents', 10, 1 ),
			'awesome_surveys_update_cookie' => array( 'set_cookie', 10, 1 ),
			'wwm_as_response_saved' => array( 'send_survey_emails', 10, 1 ),
			);
		foreach ( $actions as $action => $args ) {
			add_action( $action, array( $this, $args[0] ), $args[1], $args[2] );
		}
	}

	public function add_form_element() {
		if ( ! current_user_can( 'edit_others_posts' ) || ! wp_verify_nonce( $_POST['_as_nonce'], 'wwm-as-add-element' ) ) {
			status_header( 403 );
			exit;
		}
		/*
		custom buttons can be added - if they have been,
		they should have been handled already, but just in case
		they haven't, exit now
		 */
		if ( ! array_key_exists( $_POST['element'], $this->buttons ) ) {
			_doing_it_wrong( __METHOD__, 'your custom button should have halted execution, but it didn\'t.', '2.0' );
			status_header( 400 );
			exit;
		}
		$filters = array(
			'wwm_survey_validation_elements' => array( 10, 2),
			);

		foreach ( $filters as $filter => $args ) {
			add_filter( $filter, array( $this, $filter ), $args[0], $args[1] );
		}

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
		$html .= '<input type="hidden" name="action" value="generate-preview">';
		$html .= '<input type="hidden" name="options[type]" value="' . $form_element . '">';
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
				if ( 'textarea' == $form_element ) {
					$html .= '<label>' . __( 'Display number of characters remaining?', 'awesome-surveys' ) . '<br><input id="add-countdown" type="checkbox" name="options[add_countdown]"></label>';
				}
			$html .= '</div>';
		}
		$needs_options = array( 'radio', 'checkbox', 'dropdown' );
		if ( in_array( $form_element, $needs_options ) ) {
			$html .= '<input type="hidden" name="options[atts][can_add_options]" value="yes">';
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
	public function wwm_survey_validation_elements( $elements = array(), $type = '' ) {

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
	public function get_validation_elements_number( $elements ) {

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
	public function get_validation_elements_text( $elements ) {

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
	public function get_validation_elements_textarea( $elements ) {

		return $this->get_validation_elements_text( $elements );
	}

	/**
		* AJAX handler to generate the form preview
		* @since 1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function generate_preview() {

		$form_args = array( 'survey_id' => $_POST['survey_id'] );
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
		$form_elements_array['options'] = ( isset( $form_elements_array['options'] ) ) ? apply_filters( 'awesome_surveys_form_preview', $form_elements_array['options'] ) : array();
		if ( ! class_exists( 'Form' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
		}

		$saved_elements = ( ! empty( $_POST['existing_elements'] ) && 'null' != $_POST['existing_elements'] ) ? stripslashes( $_POST['existing_elements'] ) : get_post_meta( $_POST['survey_id'], 'existing_elements', true );
		$this->existing_elements = ( ! empty( $saved_elements ) && 'null' != $saved_elements ) ? array_merge( json_decode( $saved_elements, true ), array( $form_elements_array['options'], ) ) : array( $form_elements_array['options'] );
		$form = new FormOverrides();
		$form->configure( array( 'class' => 'pure-form pure-form-stacked' ) );
		$preview_form = $this->get_form_preview_html( $_POST['survey_id'] );
		$post_content = $this->awesome_surveys_render_form( $form_args );
		$form = new FormOverrides( 'save-survey' );
		$form->configure( array( 'class' => 'save' ) );
		$form->addElement( new Element_HTML( '<hr>' ) );
		$form->addElement( new Element_Button( __( 'Reset', 'awesome-surveys' ), 'button', array( 'class' => 'button-secondary reset-button', 'name' => 'reset' ) ) );
		$save_form = $form->render( true );
		$pattern = '/<form action="[^"]+"/';
		$replacement = '<div';
		$save_form = preg_replace( $pattern, $replacement, $save_form );
		$pattern = '/<\/form>/';
		$replacement = '</div>';
		$save_form = preg_replace( $pattern, $replacement, $save_form );
		$preview_form = preg_replace( $pattern, $replacement, $preview_form );
		$pattern = '/method="post"/';
		$replacement = '';
		$save_form = preg_replace( $pattern, $replacement, $save_form );
		$data = array( array( $preview_form . $save_form ), array( esc_html( $post_content ) ), array( json_encode( $this->existing_elements ) ) );
		wp_send_json_success( $data );
	}

	/**
		* hooked into 'wp_ajax_wwm_as_get_json' used by dynamic dialog function in admin-script.js
		*
		*/
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
		if ( isset( $arr['label'] ) ) {
			foreach ( $arr['label'] as $key => $value ) {
				$arr['label'][ $key ] = stripslashes( sanitize_text_field( $value ) );
			}
		}
		wp_send_json_success( json_encode( $arr ) );
	}

	public function update_post_content() {
		$form_args = array( 'survey_id' => $_POST['survey_id'] );
		$this->existing_elements = json_decode( stripslashes( $_POST['existing_elements'] ), true );
		if ( ! $this->existing_elements ) {
			wp_send_json_error( array( sprintf( '%s %s of %s', __( 'There was an error on line ', 'awesome-surveys' ), __LINE__, __FILE__ ) ) );
		}
		$post_content = $this->awesome_surveys_render_form( $form_args );
		wp_send_json_success( array( $post_content ) );
	}

	/**
		* Ajax handler to process the survey form
		* @since 1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function process_response() {

		if ( ! wp_verify_nonce( $_POST['answer_survey_nonce'], 'answer-survey' ) || is_null( $_POST['survey_id'] ) ) {
			status_header( 403 );
			exit;
		}
		$survey_id = absint( $_POST['survey_id'] );
		$post = get_post( $survey_id, 'OBJECT', 'display' );
		if ( 'publish' != $post->post_status ) {
			$data = array( __( 'Answers not saved. Survey in draft status.', 'awesome-surveys' ) );
			wp_send_json_error( $data );
		}
		$saved_answers = get_post_meta( $survey_id, '_response', false );
		$existing_elements = json_decode( get_post_meta( $survey_id, 'existing_elements', true ), true );
		$responses = array();
		$auth_type = get_post_meta( $survey_id, 'survey_auth_method', true );
		$auth_method = $this->auth_methods[ $auth_type ]['name'];
		$auth_args = array(
			'survey_id' => $survey_id,
			);
		$filters = array(
			'awesome_surveys_auth_method_login' => array( 'awesome_surveys_auth_method_login', 10, 1 ),
			'awesome_surveys_auth_method_cookie' => array( 'awesome_surveys_auth_method_cookie', 10, 1 ),
			);
		foreach ( $filters as $filter => $args ) {
			add_filter( $filter, array( $this, $filter ), $args[0], $args[1] );
		}
		if ( false === apply_filters( 'awesome_surveys_auth_method_' . $auth_method, $auth_args ) ) {
			$data = array( apply_filters( 'wwm_survey_no_auth_message', sprintf( '<p>%s</p>', __( 'Your response to this survey has already been recorded. Thank you!', 'awesome-surveys' ) ) ) );
			wp_send_json_error( $data );
		}

		if ( empty( $existing_elements ) || is_null( $existing_elements ) ) {
			$data = array( 'There was a problem in ' . __FILE__ . ' on line ' . ( __LINE__ - 1 ) . ' (bad array?) at ' . date( 'Y-m-d H:i:s' ) );
			wp_send_json_error( $data );
			exit;
		}
		do_action( 'wwm_as_before_save_responses', $survey_id );
		$num_responses = absint( get_post_meta( $survey_id, 'num_responses', true ) ) + 1;
		if ( 'login' === $auth_method ) {
			$respondent_key = get_current_user_id();
		} else {
			$respondent_key = $num_responses;
		}

		$multi_responses = array();
		foreach ( $existing_elements as $key => $question ) {
			$type = $question['type'];
			if ( 'checkbox' === $type && isset( $_POST['question'][ $key ] ) ) {//the answers are an array
				$radio_answers = array();
				foreach ( $question['value'] as $multi_response_key => $response ) {
					if ( isset( $_POST['question'][ $key ][ $multi_response_key ] ) ) {
						$radio_answers[] = absint( $_POST['question'][ $key ][ $multi_response_key ] );
					}
				}
				$responses[ $respondent_key ][ $key ] = $radio_answers;
			} elseif( isset( $_POST['question'][ $key ] ) && '' !== $_POST['question'][ $key ] ) {
				$responses[ $respondent_key ][ $key ] = $this->answer_sanitizer( $_POST['question'][ $key ], $this->buttons[ $question['type'] ]['type'] );
			}
		}
		if ( ! empty( $responses ) ) {
			add_post_meta( $survey_id, '_response', $responses, false );
			update_post_meta( $survey_id, 'num_responses', $num_responses );
		}

		if ( ! empty( $multi_responses ) ) {
			foreach ( $multi_responses as $key => $value ) {
				foreach ( $value as $answer_key => $answer_value ) {
					$count = get_post_meta( $survey_id, '_response_' . $key . '_' . $answer_key, true ) + 1;
					update_post_meta( $survey_id, '_response_' . $key . '_' . $answer_key, $count );
				}
			}
		}
		$action_args = array(
			'survey_id' => $survey_id,
			'responses' => $responses,
			'respondent_key' => $respondent_key,
			);
		do_action( 'wwm_as_response_saved', array( $survey_id, $responses, $existing_elements, $respondent_key ) );
		do_action( 'awesome_surveys_update_' . $auth_method, $action_args );
		$data = $post->post_excerpt;
		wp_send_json_error( array( $data ) );
	}
}