<?php

$type_map = array(
	'Element_Textbox' => 'text',
	'Element_Email' => 'email',
	'Element_Number' => 'number',
	'Element_Select' => 'dropdown',
	'Element_Radio' => 'radio',
	'Element_Checkbox' => 'checkbox',
	'Element_Textarea' => 'textarea',
	);

echo 'I am ur database upgrader<br>';
$old_surveys = get_option( 'wwm_awesome_surveys', array() );
echo '<pre>';
//print_r( json_decode( $old_surveys['surveys'][2]['form'], true ) );
//print_r( $old_surveys['surveys'][2]['responses'] );
//print_r( $old_surveys['surveys'][2]['num_responses'] );
echo '</pre>';
//return;
if ( is_array( $old_surveys ) ) {
	$existing_elements = $elements_to_render = json_decode( $old_surveys['surveys'][2]['form'], true );
		//need to map the old type to the new type
		foreach ( $existing_elements as $element_key => $element_value ) {
			$existing_elements[ $element_key ]['type'] = $type_map[ $element_value['type'] ];
		}
	$elements = json_encode( $existing_elements );
	$post = array(
		'post_content' => '',
		'post_excerpt' => $old_surveys['surveys'][2]['thank_you'],
		'post_type' => 'awesome-surveys',
		'post_title' => $old_surveys['surveys'][2]['name'],
		'post_status' => 'publish',
		);
	$survey_id = wp_insert_post( $post );
	if ( ! empty( $survey_id ) ) {
		echo 'updating post ' . $survey_id . '<br>';
		$args = array( 'survey_id' => $survey_id );
		$post_content = wwmas_post_content_generator( $args, $elements_to_render );
		$post = array(
			'ID' => $survey_id,
			'post_content' => $post_content,
			);
		wp_update_post( $post );
		$post_metas = array(
			'existing_elements' => $elements,
			'num_responses' => $old_surveys['surveys'][2]['num_responses'],
			);
		foreach ( $post_metas as $meta_key => $meta_value ) {
			update_post_meta( $survey_id, $meta_key, $meta_value );
		}
		$example = get_post_meta( 1249, '_response', false );
		error_log( '=========EXAMPLE==========');
		error_log( print_r( $example, true ) );
		//error_log( print_r( $old_surveys['surveys'][2]['responses'], true ) );
		error_log( '=========END EXAMPLE==========');
		$responses = array();
		//error_log( 'responses ' . print_r( $old_surveys['surveys'][2]['responses'], true ) );
		$answers = wp_list_pluck( $old_surveys['surveys'][2]['responses'], 'answers' );
		//error_log( print_r( $answers, true ) );
		$responses = array();
		foreach ( $answers as $question_key => $array ) {
			foreach ( $array as $respondent_key => $answer ) {
				if ( is_array( $answer ) ) {
					//error_log( 'looking for ' . $respondent_key . "\n" . print_r( $old_surveys['surveys'][2]['responses'][ $question_key ]['answers'], true ) );
				if ( 'checkbox' == $existing_elements[ $question_key ]['type'] ) {
					//error_log( "checkbox answers for respondent " . $respondent_key . "\n" . print_r( $answer, true ) );
					$responses[ $respondent_key ][ $question_key ] = $answer;
				} else {
						$possible_answers = $old_surveys['surveys'][2]['responses'][ $question_key ]['answers'];
						//error_log( "possible answers\n" . print_r( $possible_answers, true ) );
						foreach ( $possible_answers as $possible_answer_key => $possible_answer ) {
							//error_log( 'looking for ' . $respondent_key );
							//error_log( print_r( $possible_answer, true ) );
							if ( in_array( $respondent_key, $possible_answer ) ) {
								//error_log( 'respondent ' . $respondent_key . ' answered ' . $possible_answer_key . ' to ' . $question_key );
								$responses[ $respondent_key ][ $question_key ] = $possible_answer_key;
								continue;
							}
						}
				}
			} else {
					//error_log( 'respondent ' . $respondent_key . ' answered ' . $answer );
					$responses[ $respondent_key ][ $question_key ] = $answer;
				}
			}
		}
	}
	error_log( print_r( $responses, true ) );
	wp_delete_post( $survey_id, true );
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

function wwmas_database_update_process_response( $args = array() ) {

	extract( $args );
	//error_log( print_r( $existing_elements, true ) );
		//$survey_id = absint( $_POST['survey_id'] );
		//$post = get_post( $survey_id, 'OBJECT', 'display' );
		//$saved_answers = get_post_meta( $survey_id, '_response', false );
		//$existing_elements = json_decode( get_post_meta( $survey_id, 'existing_elements', true ), true );
		$responses = array();
		//$auth_type = get_post_meta( $survey_id, 'survey_auth_method', true );
		if ( empty( $existing_elements ) || is_null( $existing_elements ) ) {
			error_log( 'bailing out ' . __LINE__ );
			return false;
		}
		//$num_responses = absint( get_post_meta( $survey_id, 'num_responses', true ) ) + 1;
		//if ( 'login' === $this->auth_methods[ $auth_type ]['name'] ) {
		//	$respondent_key = get_current_user_id();
		//} else {
		//	$respondent_key = $num_responses;
		//}

		$multi_responses = array();
		//error_log( print_r( $answers, true ) );
		//return;
		//error_log( print_r( $existing_elements, true ) );
		foreach ( $answers as $answer_key => $value ) {
			$type = $existing_elements[ $answer_key ]['type'];
			error_log( $type );
				if ( 'checkbox' === $type ) {//the answers are an array
					//error_log( 'suck a cock!!' );
					$checkbox_answers = array();
					//error_log( 'checkboxes ' . print_r( $value, true ) );
				} elseif ( ! is_array( $value['answers'][ $respondent_key ] ) ) {
					//error_log( 'the answer? ' . print_r( $value['answers'][ $respondent_key ], true ) );
					$responses[ $respondent_key ][ $answer_key ] = $value['answers'][ $respondent_key ];
				}
		}
		if ( ! empty( $responses ) ) {
			add_post_meta( $survey_id, '_response', $responses, false );
			//update_post_meta( $survey_id, 'num_responses', $num_responses );
		}
		if ( ! empty( $multi_responses ) ) {
			foreach ( $multi_responses as $key => $value ) {
				foreach ( $value as $answer_key => $answer_value ) {
					$count = get_post_meta( $survey_id, '_response_' . $key . '_' . $answer_key, true ) + 1;
					update_post_meta( $survey_id, '_response_' . $key . '_' . $answer_key, $count );
				}
			}
		}
		return;
		foreach ( $existing_elements as $key => $question ) {
			//error_log( print_r( $question, true ) );
			$type = $question['type'];
			if ( 'checkbox' === $type ) {//the answers are an array
				$radio_answers = array();
				foreach ( $question['value'] as $multi_response_key => $response ) {
					//if ( isset( $_POST['question'][ $key ][ $multi_response_key ] ) ) {
						$radio_answers[] = absint( $response );
					//}
				}
				$responses[ $respondent_key ][ $key ] = $radio_answers;
			} else {
				$responses[ $respondent_key ][ $key ] = $answers[ $respondent_key ]['answers'][ $key ];
			}
		}
		//error_log( 'responses ' . print_r( $responses, true ) );
		//error_log( 'multi responses ' . print_r( $multi_responses, true ) );
		//return;
		if ( ! empty( $responses ) ) {
			add_post_meta( $survey_id, '_response', $responses, false );
			//update_post_meta( $survey_id, 'num_responses', $num_responses );
		}

		if ( ! empty( $multi_responses ) ) {
			foreach ( $multi_responses as $key => $value ) {
				foreach ( $value as $answer_key => $answer_value ) {
					$count = get_post_meta( $survey_id, '_response_' . $key . '_' . $answer_key, true ) + 1;
					update_post_meta( $survey_id, '_response_' . $key . '_' . $answer_key, $count );
				}
			}
		}
		//$data = 'this is a debug success completion notice';
		//wp_send_json_error( array( $data ) );

		//		if ( isset( $_POST['question'][$key] ) && is_array( $_POST['question'][$key] ) ) {
					/**
						* A quirk of PFBC is that checkbox arrays are unkeyed
						* php doesn't like that so give 'em keys I say
						*/
		//			$arr = array_values( $_POST['question'][$key] );
		//			foreach ( $arr as $answerkey ) {
		//				$response['answers'][$answerkey][] = $iterations;
		//			}
		//			if ( ! array_key_exists( $_POST['question'][ $key ], $form[ $key ]['value'] ) ) {
		//				status_header( 400 );
		//				exit;
		//			}
		//			$response['answers'][$_POST['question'][$key]][] = $iterations;
//
		//		$data = array( 'There was a problem in ' . __FILE__ . ' on line ' . ( __LINE__ - 1 ) . ' (response array empty?) at ' . date( 'Y-m-d H:i:s' ) );
		//		wp_send_json_error( $data );
		/*
			Feature request - 'Can I redirect to some page after survey submission?'
			@see https://gist.github.com/WillBrubaker/57157ee587a9d580ddef
			*/
	}