<?php
global $post;
global $awesome_surveys;
$auth_method = get_post_meta( $post->ID, 'survey_auth_method', true );
if ( empty( $auth_method ) ) {
	$auth_method = 0;
}
$auth_type = $awesome_surveys->auth_methods[ $auth_method ]['name'];
$responses = get_post_meta( $post->ID, '_response', true );
$has_responses = ( empty( $responses ) ) ? false : true;
$auth_locked = ( ( $has_responses ) && ( 'login' == $auth_type ) );
$thank_you_message = ( ! empty( $post->post_excerpt ) ) ? $post->post_excerpt : __( 'Thank you for completing this survey', 'awesome-surveys' );
?>

<div class="pure-form pure-form-stacked form-horizontal" id="general-survey-options">
	<fieldset>
		<div class="control-group">
			<label for="general-survey-options-element-0" class="control-label"><?php _e( 'Thank You message', 'awesome-surveys' ); ?>:</label>
			<div class="controls">
				<textarea id="excerpt" name="excerpt" cols="40" rows="5"><?php echo $thank_you_message; ?></textarea>
			</div>
		</div>
		<?php if ( ! $auth_locked ) { ?>
		<div class="ui-widget-content ui-corner-all validation field-validation">
			<span class="label">
				<p>
					<?php _e( 'To prevent people from filling the survey out multiple times you may select one of the options below', 'awesome-surveys' ); ?>
				</p>
			</span>
			<div class="control-group">
				<label for="general-survey-options-element-2" class="control-label"><?php _e( 'Survey Authentication Method:', 'awesome-surveys' ); ?></label>
				<div class="controls">
					<?php
					foreach ( $awesome_surveys->auth_methods as $key => $method ) {
						echo '<label class="radio">' ."\n";
						echo ' <input type="radio" value="' . $key . '" name="meta[survey_auth_method]" id="general-survey-options-element-2-' . $key . '" ' . checked( $key == $auth_method, true, false );
							if ( $has_responses && 'login' == $method['name'] ) {
								echo 'disabled="disabled" ';
							}
						echo '>';
						echo $method['label'] . "\n";
						echo '</label>' . "\n";
					}
					?>
				</div>
			</div>
		</div>
	<?php
		} else {
			echo '<div style="margin-top: 10px; float: left;"><h3>' . __( 'Auth method can not be edited', 'awesome-surveys' ) . '</h3></div>';
	?>
	<?php } ?>
	</fieldset>
</div>