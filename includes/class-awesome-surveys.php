<?php

class Awesome_Surveys {

	protected $existing_elements;
	public $text_domain, $buttons, $plugin_version, $dbversion;

	public function __construct() {
		$this->plugin_version = '2.0.4';
		$this->text_domain = 'awesome-surveys';
		$this->dbversion = '1.2';
		$this->buttons = $this->get_buttons();
		$this->auth_methods = $this->auth_methods();
	}

	public function init() {
		$this->register_post_type();
	}

	/**
		* creates an array of buttons for use in the editor
		* as well as mapping values
		* @return array an array of button types w/labels
		*/
	public function get_buttons() {
		return array(
			'text' => array(
				'type' => 'Element_Textbox',
				'label' => __( 'Text Input', 'awesome-surveys' )
					),
			'email' => array(
				'type' => 'Element_Email',
				'label' => __( 'Email', 'awesome-surveys' ),
					),
			'number' => array(
				'type' => 'Element_Number',
				'label' => __( 'Number', 'awesome-surveys' ),
					),
			'dropdown' => array(
				'type' => 'Element_Select',
				'label' => __( 'Dropdown Selector', 'awesome-surveys' ),
					),
			'radio' => array(
				'type' => 'Element_Radio',
				'label' => __( 'Radio Buttons', 'awesome-surveys' ),
					),
			'checkbox' => array(
				'type' => 'Element_Checkbox',
				'label' => __( 'Checkboxes', 'awesome-surveys' ),
					),
			'textarea' => array(
				'type' => 'Element_Textarea',
				'label' => __( 'Textarea', 'awesome-surveys' ),
					),
		);
	}

	/**
		* regsiters the 'awesome-surveys' post type
		*/
	public function register_post_type() {

		$args = array(
			'label' => __( 'Awesome Surveys', 'awesome-surveys' ),
			'labels' => array(
				'name' => __( 'Surveys', 'awesome-surveys' ),
				'singular_name' => __( 'Survey', 'awesome-surveys' ),
				'menu_name' => __( 'My Surveys', 'awesome-surveys' ),
				'name_admin_bar' => __( 'Survey', 'awesome-surveys' ),
				'add_new' => __( 'New Survey', 'awesome-surveys' ),
				'new_item' => __( 'New Survey', 'awesome-surveys' ),
				'add_new_item' => __( 'Add New Survey', 'awesome-surveys' ),
				'edit_item' => __( 'Edit Survey', 'awesome-surveys' ),
				),
			'description' => __( 'Surveys for your site', 'awesome-surveys' ),
			'public' => true,
			'capability_type' => 'post',
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'supports' => array(
				'title',
				),
			'register_meta_box_cb' => array( $this, 'survey_editor' ),
			'rewrite' => true,
			);
		if ( is_admin() ) {
			$args['register_meta_box_cb'] = array( $this, 'survey_editor' );
		}
		register_post_type( 'awesome-surveys', $args );
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
	function awesome_surveys_render_form( $args = array() ) {

		if ( ! isset( $this->buttons ) ) {
			$this->buttons = $this->get_buttons();
		}
		if ( ! class_exists( 'Form' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
		}
		$nonce = 'answer_survey_nonce';
		$has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
		$form_output = new FormOverrides();
		$form_output->configure( array( 'class' => 'answer-survey pure-form pure-form-stacked', 'action' => $_SERVER['REQUEST_URI'], ) );
		$form_output->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
		$questions_count = 0;
		$existing_elements = ( is_array( $this->existing_elements ) ) ? $this->existing_elements : array();
		foreach ( $existing_elements as $element ) {
			$method = $this->buttons[ $element['type'] ]['type'];
			$atts = $rules = $options = array();
			if ( 'Element_Select' == $method ) {
				$options[''] = __( 'make a selection...', 'awesome-surveys' );
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
			if ( isset( $element['add_countdown'] ) ) {
				$options['data-add_countdown'] = true;
			}
			$form_output->addElement( new $method( stripslashes( $element['name'] ), 'question[' . $questions_count . ']', $options, $atts ) );
			$questions_count++;
		}
		$form_output->addElement( new Element_Hidden( 'answer_survey_nonce', $nonce ) );
		$form_output->addElement( new Element_Hidden( 'survey_id', '', array( 'value' => $args['survey_id'], ) ) );
		$form_output->addElement( new Element_Hidden( 'action', 'answer-survey' ) );
		$form_output->addElement( new Element_Button( __( 'Submit Response', 'awesome-surveys' ), 'submit', array( 'class' => 'button-primary', 'disabled' => 'disabled' ) ) );
		return $form_output->render( true );
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
	public function awesome_surveys_form_preview( $form_elements_array ) {

		$defaults = array(
			'required' => false,
			'rules' => array(),
		);
		$form_elements_array['validation'] = wp_parse_args( ( isset( $form_elements_array['validation'] ) ) ? $form_elements_array['validation'] : array(), $defaults );
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
	 * hooked into WordPress filter 'the_content'
	 * replaces the nonce placeholder with an actual nonce
	 * as well as conditionally checking the auth method to see
	 * if the current viewer is allowed to take this particular survey.
	 * If not, outputs a message
	 * @param   $content string - the WordPress post content
	 * @return string  the filtered content
	 */
	public function the_content( $content ) {
		global $post;
		if ( is_singular( 'awesome-surveys' ) ) {
			$nonce = wp_create_nonce( 'answer-survey' );
			$auth_method = get_post_meta( $post->ID, 'survey_auth_method', true );
			$auth_type = $this->auth_methods[ $auth_method ]['name'];
			$auth_args = array(
				'survey_id' => $post->ID,
			);
			if ( false !== apply_filters( 'awesome_surveys_auth_method_' . $auth_type, $auth_args ) ) {
				$content = str_replace( 'value="answer_survey_nonce"', 'value="' . $nonce . '"', $content );
			} else {
				return apply_filters( 'wwm_survey_no_auth_message', sprintf( '<p>%s</p>', __( 'Your response to this survey has already been recorded. Thank you!', 'awesome-surveys' ) ) );
			}
		}
		return $content;
	}

	/**
		* provides the default array of survey authentication methods
		* @return array  indexed array of authentication methods, each of which is an array
		* with a name and a label.
		*/
	public function auth_methods() {

		/*
		survey_auth_options filter
		add your own auth method but also know that you will need to
		add a handler for your auth method as well.
			*/
		return apply_filters( 'survey_auth_options', array(
			array(
				'name' => 'none',
				'label' => __( 'None', 'awesome-surveys' ),
				),
			array(
				'name' => 'login',
				'label' => __( 'User must be logged in', 'awesome-surveys' ),
				),
			array(
				'name' => 'cookie',
				'label' => __( 'Cookie based', 'awesome-surveys' ),
				),
			)
		);
	}

	/**
		* Sanitizes survey form inputs before storing in the database
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		* @param  mixed $input_value the value that was input into the form field
		* @param  string $type a descriptor of what type data the form field is expecting (uses PFBC element types)
		* @return mixed  $input_value sanitized value that aims to be safe for db storage.
		*/
	public function answer_sanitizer( $input_value, $type ) {

		$input_value = ( '' == $input_value ) ? null : $input_value;
		$has_options = array( 'Element_Checkbox', 'Element_Radio', 'Element_Select' );
		if ( 'Element_Textbox' == $type || 'Element_Textarea' == $type && ! is_null( $input_value ) ) {
				$input_value = sanitize_text_field( $input_value );
		} elseif ( 'Element_Number' == $type && ! is_null( $input_value ) ) {
			$input_value = intval( $input_value );
		} elseif ( 'Element_Email' == $type && ! is_null( $input_value ) ) {
			$input_value = sanitize_email( $input_value );
		} elseif ( in_array( $type,  $has_options ) ) {//This should cover radio/checkbox & select
			$input_value = absint( $input_value );
		}
		return $input_value;
	}

	/**
		* if the login method is 'login' add the logged in user's
		* id to the post meta key '_respondents'
		* @param  array $action_args an array of arguments
		*/
	public function update_logged_in_respondents( $action_args ) {

		extract( $action_args );
		$respondents_array = get_post_meta( $survey_id, '_respondents', true );
		$respondents = ( is_array( $respondents_array ) && ( ! empty( $respondents_array ) ) ) ? $respondents_array : array();
		$respondents[] = $respondent_key;//came from extract
		if ( ! empty( $respondents ) ) {
			update_post_meta( $survey_id, '_respondents', $respondents );
		}
	}

	/**
		* Handles the auth type 'login' to determine whether the
		* survey form should be output or not
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		* @param  array $args an array of function arguments - most
		* notably ['survey_id']
		* @return bool       whether or not the user is authorized to take this survey.
		*/
	public function awesome_surveys_auth_method_login( $args = array() ) {

		if ( ! is_user_logged_in() ) {
			add_filter( 'wwm_survey_no_auth_message', array( $this, 'not_logged_in_message' ), 10, 1 );
			return false;
		}
		extract( $args );
		$respondents_array = get_post_meta( $survey_id, '_respondents', true );
		$respondents = ( is_array( $respondents_array ) && ( ! empty( $respondents_array ) ) ) ? $respondents_array : array();
		if ( in_array( get_current_user_id(), $respondents ) ) {
			return false;
		}
		$old_survey_ids = get_option( 'wwm_as_survey_id_map', array() );
		if ( array_key_exists( $survey_id, $old_survey_ids ) ) {
			$old_surveys = get_option( 'wwm_awesome_surveys', array() );
			$respondents_array = $old_survey_ids['surveys'][ $survey_id ]['respondents'];
			if ( in_array( get_current_user_id(), $respondents_array ) ) {
				return false;
			}
		}
		return true;
	}

	/**
		* Handles the auth type 'cookie' to determine whether the
		* survey form should be output or not
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		* @param  array $args an array of function arguments - most
		* notably ['survey_id']
		* @return bool       whether or not the user is authorized to take this survey.
		*/
	public function awesome_surveys_auth_method_cookie( $args = array() ) {
		extract( $args );
		$old_survey_ids = get_option( 'wwm_as_survey_id_map', array() );
		if ( $array_key = array_search( $survey_id, $old_survey_ids ) ) {

			if ( isset( $_COOKIE['responded_to_survey_' . $array_key ] ) ) {
				return false;
			}
		}
		return ( ! isset( $_COOKIE['responded_to_survey_' . $survey_id ] ) );
	}

	public function set_cookie( $args ) {
		extract( $args );
		setcookie( 'responded_to_survey_' . $survey_id, 'true', time() + YEAR_IN_SECONDS, '/' );
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
		* Hooked into wwm_as_response_saved to send email if set
		* @param  array $args @see process_response in class-awesome-surveys-ajax-actions.php
		* @since 1.6
		*/
	public function send_survey_emails( $args ) {
		/*
		$args = array( $survey_id, $responses, $existing_elements, $respondent_id )
			*/
		$form = $args[2];
		$answers = $args[1][ $args[3] ];
		$options = get_option( 'wwm_awesome_surveys_options', array() );
		if ( isset( $options['email_options'] ) && $options['email_options']['enable_emails'] ) {
			$subject = apply_filters( 'wwm_as_admin_email_subject', __( 'Survey Completed', 'awesome-surveys' ) );
			$to = $options['email_options']['mail_to'];
			$message = sprintf( __( 'A survey on your site named %s has been completed', 'awesome-surveys' ), html_entity_decode( get_the_title( $args[0] ) ) );
			$has_options = array( 'radio', 'dropdown' );
			foreach ( $args[2] as $question_key => $question ) {
				$answer = null;
				$message .= "\n\nReply to " . stripslashes( $question['name'] . ":\n" );
				if ( 'checkbox' == $question['type'] ) {
					if ( ! empty( $answers[ $question_key ] ) ) {
						foreach ( $answers[ $question_key ] as $answer_key => $answer_value ) {
							$answer .= $question['label'][ $answer_value ] . "\n";
						}
					}
				} elseif ( in_array( $question['type'], $has_options ) ) {
					$answer = ( isset( $answers[ $question_key] ) && ! is_null( $answers[ $question_key] ) ) ? $question['label'][ $answers[ $question_key] ] : null;
				} else {
					$answer = ( isset( $answers[ $question_key ] ) ) ? $answers[ $question_key ] : null;
				}
				$message .= ( ! is_null( $answer ) ) ? $answer : sprintf( __( 'No Answer Given', 'awesome-surveys' ) );
			}

			$message = apply_filters( 'wwm_as_admin_email', $message );
			wp_mail( $to, $subject, $message );
		}
		if ( isset( $options['email_options'] ) && $options['email_options']['enable_respondent_email'] ) {
			foreach ( $form as $key => $value ) {
				if ( 'email' == $value['type'] && isset( $answers[ $key ] ) && is_email( $answers[ $key ] ) ) {
					$to = $answers[ $key ];
					$subject = sanitize_text_field( $options['email_options']['email_subject'] );
					$message = $options['email_options']['respondent_email_message'];
					$replacements = apply_filters( 'wwm_as_template_replacements', array(
						'(\{blogname\})' => get_option( 'blogname' ),
						'(\{siteurl\})' => get_option( 'siteurl' ),
						'(\{surveyname\})' => stripslashes( get_the_title( $args[0] ) ),
							)
					);
					$message = html_entity_decode( preg_replace( array_keys( $replacements ), array_values( $replacements ),  $message ) );
					wp_mail( $to, $subject, $message );
					break;
				}
			}
		}
	}

		protected function get_form_preview_html( $post_id = 0 ) {

		$output = null;
		if ( ! class_exists( 'Form' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
		}

		if ( ! isset( $this->existing_elements ) ) {

			$this->existing_elements = json_decode( get_post_meta( $post_id, 'existing_elements', true ), true );
		}
		$required_is_option = array( 'Element_Textbox', 'Element_Textarea', 'Element_Email', 'Element_Number' );
		$elements_count = 0;
		if ( ! isset( $this->buttons ) || empty( $this->buttons ) ) {

			$this->buttons = $this->get_buttons();
		}
		$form = new FormOverrides();
		$form->configure( array( 'class' => 'pure-form pure-form-stacked' ) );

		if ( isset( $this->existing_elements ) && ! empty( $this->existing_elements ) ) {
			foreach ( $this->existing_elements as $element ) {
					$method = $this->buttons[ $element['type'] ]['type'];
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
					$has_responses = get_post_meta( $post_id, '_response', true );
					$class = ( empty( $has_responses ) ) ? 'single-element-edit' : 'label-edit';
					$form->addElement( new Element_HTML( '<div class="' . $class . '">' ) );
					$form->addElement( new $method( htmlentities( stripslashes( $element['name'] ) ), sanitize_title( $element['name'] ), $options, $atts ) );
						$form->addElement( new Element_HTML( '<div class="button-holder">' ) );
						if ( empty( $has_responses ) ) {
							$form->addElement( new Element_HTML( '<button class="element-edit" data-action="delete" data-index="' . $elements_count . '">' . __( 'Delete question', 'awesome-surveys' ) . '</button><button class="element-edit" data-action="edit" data-index="' . $elements_count . '">' . __( 'Edit question', 'awesome-surveys' ) . '</button>' ) );
						} else {
							$form->addElement( new Element_HTML( '<button class="element-label-edit" data-action="edit" data-index="' . $elements_count . '">' . __( 'Edit question', 'awesome-surveys' ) . '</button>' ) );
						}
						$form->addElement( new Element_HTML( '</div><div class="clear"></div></div>' ) );
					$elements_count++;
			}
			$output = $form->render( true );
		}

		$pattern = '/<form action="[^"]+"/';
		$replacement = '<div';
		$output = preg_replace( $pattern, $replacement, $output );
		$pattern = '/method="post"/';
		$replacement = '';
		$output = preg_replace( $pattern, $replacement, $output );
		$pattern = '/<\/form/';
		$replacement = '</div';
		$output = preg_replace( $pattern, $replacement, $output );
		return $output;
	}
}