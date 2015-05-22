<?php
/*
todo:
this belongs in a function - not here
make the strings translatable the right way
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

	echo '<p>';
	printf( __( 'This survey has received a total of %s%d%s responses', 'awesome-surveys' ), '<b>', count( $results ), '</b>' );
	echo '</p>';
	foreach ( $response_counts as $question_key => $value ) {
		echo '<div class="answers">';
		if ( isset( $elements[ $question_key ]['value'] ) ) {
			echo '<p class="answers">';
			printf( __( '%s received %s%d%s responses', 'awesome-surveys' ), $elements[ $question_key ]['name'], '<b>', $value['totalcount'], '</b>' );
			echo '</p>';
			foreach( $value['count'] as $answer_key => $count ) {

				$percentage = number_format( ( $count / $value['totalcount'] ) * 100, 2 );
				$total_count = $value['totalcount'];

				if ( 100 == intval( $percentage ) ) {
					echo '<div class="options-container">'
							.'<div class="options" style="width:' . $percentage . '%;">'
								. $elements[ $question_key ]['label'][ $answer_key ] . ' (<b>' . $count . '</b>)'
								. '<p class="percentage">' . $percentage . '% (' . $count . '/' . $total_count . ')</span>'
							. '</div>'
							. '<p>&nbsp;</p>'
						. '</div>';
				} else {
					echo '<div class="options-container">'
						.'<span class="options" style="width:' . $percentage . '%;">'
							. $elements[ $question_key ]['label'][ $answer_key ] . ' (<b>' . $count . '</b>)'
						. '</span>'
						. '<p class="percentage">' . $percentage . '% (' . $count . '/' . $total_count . ')</p>'
					. '</div>';
				}
			}
		} else {
			//future todo echo '<a href="#" data-question-key="' . $question_key . '">';
			echo '<p class="totalcount survey">';
			echo $elements[ $question_key ]['name'] . ' ';
			printf( __( 'was answered %d times', 'awesome-surveys' ), $value['totalcount'] );
			echo '</p>';
		//future todo echo '</a>';
		}
		echo '<div class="clear"></div></div>';
}
