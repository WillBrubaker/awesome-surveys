<?php
global $post;
$results = get_post_meta( $post->ID, '_response', false );
$elements = json_decode( get_post_meta( $post->ID, 'existing_elements', true ), true );
//echo 'your survey has received ' . count( $results ) . ' responses<br>';
//echo '<pre>';
//print_r( $results );
//echo '</pre>';
$response_counts = array();
foreach ( $results as $response_key => $response_array ) {
	foreach ( $response_array as $key => $array ) {
		foreach ( $array as $question_key => $responses ) {
			if ( isset( $elements[ $question_key ]['value'] ) ) {
				$response_counts[ $question_key ]['totalcount'] = ( isset( $response_counts[ $question_key ]['totalcount'] ) ) ? ( $response_counts[ $question_key ]['totalcount'] + 1 ) : 1;
				if ( is_array( $responses ) && ! empty( $responses ) ) {
					foreach ( $responses as $response ) {
						$response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $response ] ] = ( isset( $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $response ] ] ) ) ? $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $response ] ] + 1 : 1;
						echo 'this is debug output ' . $elements[ $question_key ]['label'][ $response ] . '<br>';
						echo 'this is value debug output ' . $elements[ $question_key ]['value'][ $response ] . '<br>';
					}
				} elseif ( isset( $responses ) ) {
				$response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $responses ] ] = ( isset( $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $responses ] ] ) ) ? $response_counts[ $question_key ]['count'][ $elements[ $question_key ]['value'][ $responses ] ] + 1 : 1;
					echo 'this is debug output ' . $elements[ $question_key ]['label'][ $responses ] . '<br>';
				}
				echo '<pre>';
				//print_r( $responses );
				echo '</pre>';
			}
		}
	}
}

foreach ( $response_counts as $question_key => $value ) {
	if ( isset( $elements[ $question_key ]['value'] ) ) {
		echo $elements[ $question_key ]['name'] . ' was answered ' . $value['totalcount'] . ' times<br>';
	}
}
echo '<pre>';
print_r( $response_counts );
echo '</pre>';

echo '<pre>';
//print_r( $elements );
echo '</pre>';
