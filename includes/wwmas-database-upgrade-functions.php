<?php

function wwmas_do_database_upgrade() {
	add_option( 'wwm_as_survey_id_map', array(), '', 'no' );
	global $awesome_surveys;

	$type_map = array(
		'Element_Textbox' => 'text',
		'Element_Email' => 'email',
		'Element_Number' => 'number',
		'Element_Select' => 'dropdown',
		'Element_Radio' => 'radio',
		'Element_Checkbox' => 'checkbox',
		'Element_Textarea' => 'textarea',
	);


	$old_surveys = get_option( 'wwm_awesome_surveys', array() );
	$old_survey_ids = array_keys( $old_surveys['surveys'] );
	if ( ! empty( $old_surveys ) ) {
		for ( $num_surveys = 0; $num_surveys < count( $old_surveys['surveys'] ); $num_surveys++ ) {
			if ( empty( $old_surveys['surveys'][ $num_surveys ] ) ) {
				continue;
			}
			$existing_elements = $elements_to_render = json_decode( $old_surveys['surveys'][ $num_surveys ]['form'], true );
				//need to map the old type to the new type
				foreach ( $existing_elements as $element_key => $element_value ) {
					$existing_elements[ $element_key ]['type'] = $type_map[ $element_value['type'] ];
					if ( in_array( $type_map[ $element_value['type'] ], array( 'radio', 'dropdown', 'checkbox' ) ) ) {
						$atts = ( isset( $element_value['atts'] ) ) ? $element_value['atts'] : array();
						$atts['can_add_options'] = 'yes';
						$existing_elements[ $element_key ]['atts'] = $atts;
					}
				}
			$elements = json_encode( $existing_elements );
			$post = array(
				'post_content' => '',
				'post_excerpt' => $old_surveys['surveys'][ $num_surveys ]['thank_you'],
				'post_type' => 'awesome-surveys',
				'post_title' => $old_surveys['surveys'][ $num_surveys ]['name'],
				'post_status' => 'publish',
				);
			$survey_id = wp_insert_post( $post );
			$old_survey_id = $old_survey_ids[ $num_surveys ];
			$id_map = get_option( 'wwm_as_survey_id_map', array() );
			$id_map[ $old_survey_id ] = $survey_id;
			update_option( 'wwm_as_survey_id_map', $id_map );
			if ( ! empty( $survey_id ) ) {
				$args = array( 'survey_id' => $survey_id );
				$post_content = wwmas_post_content_generator( $args, $elements_to_render );
				$post = array(
					'ID' => $survey_id,
					'post_content' => $post_content,
					);
				wp_update_post( $post );
				$old_auth_method = $old_surveys['surveys'][ $num_surveys ]['auth'];
				$auth_method = 0;
				foreach ( $awesome_surveys->auth_methods as $auth_key => $value ) {
					if ( $old_auth_method == $value['name'] ) {
						$auth_method = $auth_key;
						break;
					}
				}
				$auth_type = $awesome_surveys->auth_methods[ $auth_method ]['name'];
				$num_responses = ( isset( $old_surveys['surveys'][ $num_surveys ]['num_responses'] ) ) ? $old_surveys['surveys'][ $num_surveys ]['num_responses'] + 1 : 0;
				$respondent_ids = isset( $old_surveys['surveys'][ $num_surveys ]['respondents'] ) ? $old_surveys['surveys'][ $num_surveys ]['respondents'] : array();
				if ( empty( $respondent_ids ) && $num_responses > 0 ) {
					$respondent_ids = array_fill( 0, $num_responses, null );
				}
				$post_metas = array(
					'existing_elements' => $elements,
					'survey_auth_method' => $auth_method,
					);
				if ( $num_responses ) {
					$post_metas['num_responses'] = $num_responses;
				}
				if ( 'login' == $auth_type ) {
					$post_metas['_respondents'] = $respondent_ids;
				}
				foreach ( $post_metas as $meta_key => $meta_value ) {
					update_post_meta( $survey_id, $meta_key, $meta_value );
				}
				if ( ! isset( $old_surveys['surveys'][ $num_surveys ]['num_responses'] ) ) {
					continue;
				}
				$args = array(
					'survey_id' => $survey_id,
					'answers' => wp_list_pluck( $old_surveys['surveys'][ $num_surveys ]['responses'], 'answers' ),
					'questions' => $existing_elements,
					'auth_type' => $auth_type,
					'num_responses' => $num_responses + 1,
					'respondent_ids' => $respondent_ids,
					);
				if ( $num_responses > 0 ) {
					wwmas_build_response_array( $args );
				}
			}
		}
	}
	update_option( 'wwm_as_dbversion', $awesome_surveys->dbversion );
}

function wwmas_post_content_generator( $args = array(), $elements = array() ) {
		if ( ! class_exists( 'Form' ) ) {
			include_once( WWM_AWESOME_SURVEYS_PATH . 'includes/PFBC/Form.php' );
			include_once( WWM_AWESOME_SURVEYS_PATH . 'includes/PFBC/Overrides.php' );
		}
		$nonce = 'answer_survey_nonce';
		$has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
		$form_output = new FormOverrides();
		$form_output->configure( array( 'class' => 'answer-survey pure-form pure-form-stacked', 'action' => $_SERVER['REQUEST_URI'], ) );
		$form_output->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
		$questions_count = 0;
		foreach ( $elements as $element ) {
			$method = $element['type'];
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
			$form_output->addElement( new $method( stripslashes( $element['name'] ), 'question[' . $questions_count . ']', $options, $atts ) );
			$questions_count++;
		}
		$form_output->addElement( new Element_Hidden( 'answer_survey_nonce', $nonce ) );
		$form_output->addElement( new Element_Hidden( 'survey_id', '', array( 'value' => $args['survey_id'], ) ) );
		$form_output->addElement( new Element_Hidden( 'action', 'answer-survey' ) );
		$form_output->addElement( new Element_Button( __( 'Submit Response', 'awesome-surveys' ), 'submit', array( 'class' => 'button-primary', 'disabled' => 'disabled' ) ) );
		return $form_output->render( true );
}

function wwmas_build_response_array( $args ) {
	/*
	$args = array(
						'survey_id' => $survey_id,
						'answers' => wp_list_pluck( $old_surveys['surveys'][ $num_surveys ]['responses'], 'answers' ),
						'questions' => $existing_elements,
						'auth_type' => $auth_type,
						'num_responses' => $num_responses + 1,
						'respondent_ids' => $respondent_ids,
						);
	 */
	extract( $args );
	$num_questions = count( $questions );
	$default_responses = array_fill( 0, $num_questions, null );
	$responses = array();
	foreach ( $respondent_ids as $respondent_id => $response ) {
		$responses[ $respondent_id ] = $default_responses;
	}

	$has_options = array( 'dropdown', 'radio' );
	foreach ( $responses as $respondent_id => $response ) {
		foreach ( $response as $question_key => $answer ) {
			$checked_answers = array();
			if ( in_array( $questions[ $question_key ]['type'], $has_options ) ) {
				foreach ( $answers[ $question_key ] as $answer_key => $possible_answer ) {
					if ( in_array( $respondent_id, $possible_answer ) ) {
						$responses[ $respondent_id ][ $question_key ] = $answer_key;
						break;
					}
				}
			} elseif ( 'checkbox' == $questions[ $question_key ]['type'] ) {
				foreach ( $answers[ $question_key ] as $answer_key => $possible_answer ) {
					if ( in_array( $respondent_id, $possible_answer ) ) {
						$checked_answers[] = $answer_key;
					}
				}
				$responses[ $respondent_id ][ $question_key ] = $checked_answers;
			} else {
				$responses[ $respondent_id ][ $question_key ] = ( isset( $answers[ $question_key ][ $respondent_id ] ) ) ? $answers[ $question_key ][ $respondent_id ] : null;
			}
		}
	}

	foreach ( $respondent_ids as $respondent_id => $value ) {
		$user_id = ( 'login' == $auth_type ) ? $value : $respondent_id + 1;
		$response = array( $user_id => array_filter( $responses[ $respondent_id ], 'wwmas_remove_unset_responses' ) );
		add_post_meta( $survey_id, '_response', $response, false );
	}
	return;
}

function wwmas_remove_unset_responses( $value ) {
	if ( is_array( $value ) ) {
		return ( ! empty( $value ) );
	}
	if ( $value === 0 ) {
		return true;
	}
	return ( ! empty( $value ) );
}

function wwmas_translate_post_content() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		die( 'not authorized' );
	}
	$query_args = array(
		'post_type' => 'awesome-surveys',
		'post_status' => 'publish',
		);
	$surveys = new WP_Query( $query_args );
	if ( $surveys->have_posts() ) {
		while ( $surveys->have_posts() ) {
			$surveys->the_post();
			$survey_id = get_the_ID();
			$args = array(
			'survey_id' => $survey_id,
			);
			$elements = wwmas_convert_elements( json_decode( get_post_meta( $survey_id, 'existing_elements', true ), true ) );
			$content = wwmas_post_content_generator( $args, $elements );
			$postarr = array(
		'ID' => $survey_id,
		'post_content' => $content,
		);
		wp_update_post( $postarr );
		}
	}
}

function wwmas_convert_elements( $elements = array() ) {
	global $awesome_surveys;
	foreach ( $elements as $element_key => $element ) {
		$elements[ $element_key ]['type'] = $awesome_surveys->buttons[ $element['type'] ]['type'];
	}
	return $elements;
}