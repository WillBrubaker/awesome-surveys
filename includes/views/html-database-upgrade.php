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
//print_r( json_decode( $old_surveys['surveys'][1]['form'], true ) );
//print_r( $old_surveys['surveys'][1] );
print_r( $old_surveys['surveys'][1]['num_responses'] );
echo '</pre>';
if ( is_array( $old_surveys ) ) {
	$existing_elements = $elements_to_render = json_decode( $old_surveys['surveys'][0]['form'], true );
		//need to map the old type to the new type
		foreach ( $existing_elements as $element_key => $element_value ) {
			$existing_elements[ $element_key ]['type'] = $type_map[ $element_value['type'] ];
		}
	$elements = json_encode( $existing_elements );
	$post = array(
		'post_content' => '',
		'post_excerpt' => $old_surveys['surveys'][0]['thank_you'],
		'post_type' => 'awesome-surveys',
		'post_title' => $old_surveys['surveys'][0]['name'],
		'post_status' => 'publish',
		);
	//post insert stuff - works$survey_id = wp_insert_post( $post );
	//post insert stuff - worksif ( ! empty( $survey_id ) ) {
	//post insert stuff - works	echo 'updating post ' . $survey_id . '<br>';
	//post insert stuff - works	$args = array( 'survey_id' => $survey_id );
	//post insert stuff - works	$post_content = wwmas_post_content_generator( $args, $elements_to_render );
	//post insert stuff - works	$post = array(
	//post insert stuff - works		'ID' => $survey_id,
	//post insert stuff - works		'post_content' => $post_content,
	//post insert stuff - works		);
	//post insert stuff - works	wp_update_post( $post );
	//post insert stuff - works	$post_metas = array(
	//post insert stuff - works		'existing_elements' => $elements,
	//post insert stuff - works		'num_responses' => $old_surveys['surveys'][0]['num_responses'],
	//post insert stuff - works		);
	//post insert stuff - works	foreach ( $post_metas as $meta_key => $meta_value ) {
	//post insert stuff - works		update_post_meta( $survey_id, $meta_key, $meta_value );
	//post insert stuff - works	}
	//post insert stuff - works}
	//update the respones post meta:
	$response_args = array(
		'survey_id' => $survey_id,
		'existing_elements' => $existing_elements,
		'respondent_key' => $some_array_key,
		);
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

function wwmas_database_update_process_response() {

	extract( $args );
		//$survey_id = absint( $_POST['survey_id'] );
		$post = get_post( $survey_id, 'OBJECT', 'display' );
		$saved_answers = get_post_meta( $survey_id, '_response', false );
		$existing_elements = json_decode( get_post_meta( $survey_id, 'existing_elements', true ), true );
		$responses = array();
		//$auth_type = get_post_meta( $survey_id, 'survey_auth_method', true );
		if ( empty( $existing_elements ) || is_null( $existing_elements ) ) {
			return false;
		}
		$num_responses = absint( get_post_meta( $survey_id, 'num_responses', true ) ) + 1;
		if ( 'login' === $this->auth_methods[ $auth_type ]['name'] ) {
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
						$radio_answers[] = absint( $response );
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
		$data = 'this is a debug success completion notice';
		wp_send_json_error( array( $data ) );

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
					if ( ! array_key_exists( $_POST['question'][ $key ], $form[ $key ]['value'] ) ) {
						status_header( 400 );
						exit;
					}
					$response['answers'][$_POST['question'][$key]][] = $num_responses;

				$data = array( 'There was a problem in ' . __FILE__ . ' on line ' . ( __LINE__ - 1 ) . ' (response array empty?) at ' . date( 'Y-m-d H:i:s' ) );
				wp_send_json_error( $data );
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