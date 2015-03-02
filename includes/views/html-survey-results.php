<?php
global $post;
$results = get_post_meta( $post->ID, '_response', false );
$elements = json_decode( get_post_meta( $post->ID, 'existing_elements', true ), true );
$response_counts = array();
foreach ( $results as $response_key => $response_array ) {
	foreach ( $response_array as $key => $array ) {
		foreach ( $array as $question_key => $responses ) {
			$response_counts[ $question_key ]['totalcount'] = ( isset( $response_counts[ $question_key ]['totalcount'] ) ) ? ( $response_counts[ $question_key ]['totalcount'] + 1 ) : 1;
			if ( isset( $elements[ $question_key ]['value'] ) ) {
				if ( is_array( $responses ) && ! empty( $responses ) ) {
					foreach ( $responses as $response ) {
						$response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $response ] ] = ( isset( $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $response ] ] ) ) ? $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $response ] ] + 1 : 1;
					}
				} elseif ( isset( $responses ) ) {
				$response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $responses ] ] = ( isset( $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $responses ] ] ) ) ? $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $responses ] ] + 1 : 1;
				}
			}
		}
	}
}

_e( sprintf( '<p>Your survey has received a total of %s responses</p>', count( $results ) ), 'awesome-surveys' );
foreach ( $response_counts as $question_key => $value ) {
	if ( isset( $elements[ $question_key ]['value'] ) ) {
		_e( '<ul class="answers">' . sprintf( '%s was answered %s times', $elements[ $question_key ]['name'], $value['totalcount'] ), 'awesome-surveys' );
		foreach( $value['count'] as $answer_key => $count ) {
			_e( sprintf( '<li>%s was selected times</li>', $elements[ $question_key ]['label'][ $answer_key ], $count), 'awesome-surveys' );
		}
		echo '</ul>';
	} else {
		_e( sprintf( '<p class="totalcount survey">%s was answered %s times</p>', $elements[ $question_key ]['name'], $value['totalcount'] ), 'awesome-surveys' );
	}
}

//echo '<pre>';
//print_r( $response_counts );
//echo '</pre>';
//echo '<pre>';
//print_r( $elements );
//echo '</pre>';
