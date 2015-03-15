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
$meta = get_post_meta( 1249, '_response' );
//echo '<pre>';
//print_r( $meta );
//echo '</pre>';
//echo '=====================================';
//$meta = get_post_meta( 1398, '_response' );
//echo '<pre>';
//print_r( $meta );
//echo '</pre>';
//exit;
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
		//error_log( print_r( $example, true ) );
		//error_log( print_r( $old_surveys['surveys'][2]['responses'], true ) );
		error_log( '=========END EXAMPLE==========');
		$responses = array();
		error_log( 'responses ' . print_r( $old_surveys['surveys'][2]['responses'], true ) );
		$answers = wp_list_pluck( $old_surveys['surveys'][2]['responses'], 'answers' );
		//error_log( print_r( $answers, true ) );
		$responses = array(
			'survey_id' => $survey_id,
			);
		foreach ( $answers as $question_key => $array ) {
			foreach ( $array as $respondent_key => $answer ) {
				$responses[ $respondent_key ]['mykey'] = $respondent_key;
				if ( is_array( $answer ) ) {
					//error_log( 'looking for ' . $respondent_key . "\n" . print_r( $old_surveys['surveys'][2]['responses'][ $question_key ]['answers'], true ) );
				if ( 'checkbox' == $existing_elements[ $question_key ]['type'] ) {
					$possible_answers = $old_surveys['surveys'][2]['responses'][ $question_key ]['answers'];
					//error_log( "these are the possible answers\n" . print_r( $possible_answers, true ) );
					//error_log( "checkbox answers for respondent " . $respondent_key . "\n" . print_r( $answer, true ) );
					$checkbox_answers = array();
					foreach ( $possible_answers as $checkbox_answer_key => $possible_answer ) {
						if ( in_array( $respondent_key, $possible_answer ) ) {
							$checkbox_answers[] = $checkbox_answer_key;
						}
					}
					error_log( "here is what goes in the array\n" . print_r( $checkbox_answers, true ) );
					$responses[ $respondent_key ][ $question_key ] = $checkbox_answers;
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
		//error_log( print_r( $responses, true ) );
	}
	//error_log( print_r( $responses, true ) );
	foreach ( $responses as $response ) {
		$respondent_key = $response['mykey'];
		unset( $response['mykey'] );
		process_response( $survey_id, $response, $respondent_key );
	}
	//wp_delete_post( $survey_id, true );
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

	function process_response( $survey_id, $response, $respondent_key ) {

		//$survey_id = absint( $_POST['survey_id'] );
		$post = get_post( $survey_id, 'OBJECT', 'display' );
		$saved_answers = get_post_meta( $survey_id, '_response', false );
		$existing_elements = json_decode( get_post_meta( $survey_id, 'existing_elements', true ), true );
		$responses = array();
		if ( empty( $existing_elements ) || is_null( $existing_elements ) ) {
			return false;
		}

		$multi_responses = array();
		foreach ( $existing_elements as $key => $question ) {
			$type = $question['type'];
			if ( 'checkbox' === $type && isset( $response[ $key ] ) ) {//the answers are an array
				$radio_answers = array();
				foreach ( $question['value'] as $multi_response_key => $otter_response ) {
					if ( isset( $response[ $key ][ $multi_response_key ] ) ) {
						$radio_answers[] = absint( $otter_response );
						//error_log( print_r( $radio_answers, true ) );
					}
				}
				$responses[ $respondent_key ][ $key ] = $radio_answers;
			} elseif( isset( $response[ $key ] ) && '' !== $response[ $key ] ) {
				$responses[ $respondent_key ][ $key ] = $response[ $key ];
			}
		}
		if ( ! empty( $responses ) ) {
			error_log( "setting post meta _response\n" . print_r( $responses, true ) );
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
return true;

		//		if ( isset( $_POST['question'][$key] ) && is_array( $_POST['question'][$key] ) ) {
					/**
						* A quirk of PFBC is that checkbox arrays are unkeyed
						* php doesn't like that so give 'em keys I say
						*/
					$arr = array_values( $_POST['question'][$key] );
					foreach ( $arr as $answerkey ) {
						if ( ! array_key_exists( $answerkey, $form[ $key ]['value'] ) ) {
							status_header( 400 );
							exit;
						}
						$response['answers'][$answerkey][] = $num_responses;
					}
		//		} elseif ( isset( $_POST['question'][$key] ) ) {
					if ( ! array_key_exists( $response[ $key ], $form[ $key ]['value'] ) ) {
						status_header( 400 );
						exit;
					}
					$response['answers'][$_POST['question'][$key]][] = $num_responses;
		//		}
		//	} else {
		//		$response['answers'][] = ( isset( $_POST['question'][$key] ) ) ? $this->wwm_filter_survey_answer_filter( $_POST['question'][$key], $form[$key]['type'] ) : null;
		//	}
		//	$responses[$key] = $response;
		//}
		//if ( ! empty( $responses ) ) {
		//	$survey['responses'] = $responses;
		//	$survey = apply_filters( 'wwm_awesome_survey_response', $survey, $_POST['auth_method'] );
		//	$surveys['surveys'][$_POST['survey_id']] = $survey;
		//	$action_args = array(
		//		'survey_id' => $_POST['survey_id'],
		//		'survey' => $survey,
		//	);
		//	do_action( 'awesome_surveys_update_' . $_POST['auth_method'], $action_args );
		//} else {
				$data = array( 'There was a problem in ' . __FILE__ . ' on line ' . ( __LINE__ - 1 ) . ' (response array empty?) at ' . date( 'Y-m-d H:i:s' ) );
				wp_send_json_error( $data );
		//}
		//if ( ! empty( $surveys ) && ! empty( $survey ) ) {
		//	update_option( 'wwm_awesome_surveys', $surveys );
		//	do_action( 'wwm_as_response_saved', array( $_POST['survey_id'], $survey, $responses, $original_responses ) );
		//	$form_id = sanitize_title( stripslashes( $survey['name'] ) );
		//	$thank_you = stripslashes( $survey['thank_you'] );
		/*
			Feature request - 'Can I redirect to some page after survey submission?'
			@see https://gist.github.com/WillBrubaker/57157ee587a9d580ddef
			*/
		$url = esc_url( apply_filters( 'after_awesome_survey_response_processed', null, array( 'survey_id' => $_POST['survey_id'], 'survey' => $survey, 'responses' => $_POST['question'], ) ) );
		wp_send_json_success( array( 'form_id' => $form_id, 'thank_you' => $thank_you, 'url' => $url ) );
		exit;
		//} else {
			$data = array( 'There was a problem in ' . __FILE__ . ' on line ' . ( __LINE__ - 1 ) . ' (bad array?) at ' . date( 'Y-m-d H:i:s' ) );
			wp_send_json_error( $data );
	//	}
	}