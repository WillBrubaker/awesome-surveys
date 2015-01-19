<?php
global $post;
$results = get_post_meta( $post->ID, '_response', false );
$elements = json_decode( get_post_meta( $post->ID, 'existing_elements', true ), true );
$has_options = array( 'dropdown', 'radio', 'checkbox' );
$responses = $answer_counts = array();
foreach ( $results as $result ) {
	foreach ( $result as $answer_array ) {
		foreach ( $answer_array as $key => $value ) {
			//echo '<pre>key<br>';
			//var_dump( $key );
			//echo '<br>value<br>';
			//var_dump( $value );
			//echo '<br>elements<br>';
				//echo 'found ' . $elements[ $key ]['type'] . '<br>';
			if ( isset( $elements[ $key ]['value'] ) && is_array( $elements[ $key ]['value'] ) ) {
				//echo '<ul>answers';
				foreach ( $elements[ $key ]['value'] as $answerkey ) {
					var_dump( $answerkey );
					if ( $answerkey == $answer_array ) {
						$count = ( isset( $answer_counts[ $key ][ $answerkey ] ) ) ? $answer_counts[ $key ][ $answerkey ] + 1 : 1;
						$answer_counts[ $key ][ $answerkey ] = $count;
					}
					//echo '<li>' . $answerkey . '</li>';
				}
				//echo '</ul>';
			} else {
				//echo 'answer is ' . $value . '<br>';
			}
		}
	}
}

echo '<pre>';
			var_dump( $answer_counts );
			echo '</pre>';