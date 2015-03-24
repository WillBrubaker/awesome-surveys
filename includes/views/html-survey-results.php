<?php
/*
todo:
this belongs in a function - not here
 */
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

_e( sprintf( '%sThis survey has received a total of %s responses%s', '<p>', count( $results ), '</p>' ), 'awesome-surveys' );
foreach ( $response_counts as $question_key => $value ) {
	echo '<div class="answers">';
	if ( isset( $elements[ $question_key ]['value'] ) ) {
		echo '<p class="answers">';
		_e( sprintf( '%s received %s responses', $elements[ $question_key ]['name'], $value['totalcount'] ), 'awesome-surveys' );
		echo '</p>';
		foreach( $value['count'] as $answer_key => $count ) {

			$percentage = number_format( ( $count / $value['totalcount'] ) * 100, 2 );
			$totalCount = $value['totalcount'];
			
			if (intval($percentage) == 100) {
				echo '<div class="options-container">'
						.'<div class="options" style="width:' . $percentage . '%;">' 
							. $elements[ $question_key ]['label'][ $answer_key ] 
							. '<p class="percentage">' . $percentage . '% (' . $count . '/' . $totalCount . ')</span>'
						. '</div>'
						. '<p>&nbsp;</p>'
					. '</div>';
			}
			else {
				echo '<div class="options-container">'
					.'<span class="options" style="width:' . $percentage . '%;">' 
						. $elements[ $question_key ]['label'][ $answer_key ] 
					. '</span>'
					. '<p class="percentage">' . $percentage . '% (' . $count . '/' . $totalCount . ')</p>'
				. '</div>';
			}

			
		}
	} else {
		_e( sprintf( '<p class="totalcount survey">%s was answered %s times</p>', $elements[ $question_key ]['name'], $value['totalcount'] ), 'awesome-surveys' );
	}
	echo '<div class="clear"></div></div>';
}
