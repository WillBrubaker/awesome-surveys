<?php
global $post;
$results = get_post_meta( $post->ID, '_response', false );
$elements = json_decode( get_post_meta( $post->ID, 'existing_elements', true ), true );
/*
go through the elements array, get the array key for each one that has a type which has options
get the number of answers for each option
	*/
$has_options = array( 'dropdown', 'radio', 'checkbox' );
$get_totals = array();
foreach ( $elements as $key => $value ) {
	if ( in_array( $value['type'], $has_options ) ) {
		$get_totals[] = $key;
	}
}
$responses = $answer_counts = array();
foreach ( $results as $result ) {
	$responses[] = $result;
}
foreach ( $elements as $answer_key => $answer_array ) {
	if ( in_array( $answer_array['type'], $has_options ) ) {
		$answer_counts[ $answer_key ][] = $responses[ $answer_key ];
	}
}
?>
<pre class="brush: php; gutter: true">
	responses<br>
	<?php print_r( $responses ); ?>
	<hr>
	<?php print_r( $answer_counts ); ?>
	<hr>
</pre>