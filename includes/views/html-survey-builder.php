<?php
global $post;
global $awesome_surveys;
$nonce = wp_create_nonce( 'wwm-as-add-element' );
$existing_elements = get_post_meta( $post->ID, 'existing_elements', true );
$form_preview_html = $awesome_surveys->get_form_preview_html( $post->ID );
?>
<div id="awesome-survey">
	<input type="hidden" name="existing_elements" id="existing_elements" value='<?php echo $existing_elements; ?>'>
	<input type="hidden" id="content" name="content" value="<?php echo esc_html( $post->post_content ); ?>">
		<?php wp_nonce_field( 'create-survey', 'create_survey_nonce', false, true ); ?>
	<input type="hidden" name="survey_id" value="<?php echo $post->ID; ?>">
	<?php
		$has_responses = get_post_meta( $post->ID, '_response', true );
		if ( empty( $has_responses ) ) :
	?>
	<div id="survey-elements-buttons">
		<h4><?php _e( 'Add a form element to your survey by clicking a button', 'awesome-surveys' ); ?></h4>
		<?php
		foreach ( $awesome_surveys->buttons as $name => $value ) {
			echo '<button name="' . $name . '" data-nonce="' . $nonce . '">' . $value['label'] . '</button>' . "\n";
		}
		/*

		The idea here is that custom buttons can be added e.g. a pre-configured "states"
		dropdown. Ensure that their name isn't one of the array keys
		in $awesome_surveys->buttons. You also need to add a custom ajax action to
		handle your button type and it needs to happen earlier than default, check for
		your custom button name, output what it needs to output and then exit.

		for example:
		add_action( 'wp_ajax_add-form-element', 'my_handler_function', 5 );

		function my_handler_function() {
			//just an example for the default handler - do as you please
			if ( ! current_user_can( 'edit_others_posts' ) || ! wp_verify_nonce( $_POST['_as_nonce'], 'wwm-as-add-element' ) ) {
				status_header( 403 );
				exit;
			}
			if ( 'my-custom-button-name' == $_POST['element'] ) {
				//generate some html
				echo $my_html;
				exit;//halt the ajax action execution
			}//element wasn't my custom one - do nothing and let the default handler take care of it
		}

		Whatever is added, however, needs to work with the PFBC methods
		 */
		do_action( 'after_wwm_as_output_buttons' );
		?>
	</div>
	<div id="current-element-wrapper">
	<hr>
	<h4><?php _e( 'Configure a survey question', 'awesome-surveys' ); ?></h4>
	<div id="current-element"></div>
	</div>
<?php else : ?>
	<h3><?php _e( 'This survey has responses. Questions can not be edited', 'awesome-surveys' ); ?></h3>
<?php endif; ?>
	<div id="form-preview-wrapper">
	<hr>
	<h4><?php _e( 'Survey Preview', 'awesome-surveys' ); ?></h4>
	<p>
		<?php
		_e( 'You can insert this survey with shortcode: ', 'awesome-surveys' );
		echo '[wwm_survey id="' . $post->ID . '"]';
		?>
	</p>
	<div id="form-preview"><?php echo $form_preview_html; ?></div>
	</div>
</div>