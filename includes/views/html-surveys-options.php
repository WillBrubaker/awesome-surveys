<?php
	$options = get_option( 'wwm_awesome_surveys_options', self::$options );
	$include = ( isset( $options['general_options'] ) && isset( $options['general_options']['include_css'] ) ) ? absint( $options['general_options']['include_css'] ) : 1;
	?>
<h4>
	<?php __( 'Survey Styling Options', 'awesome-surveys' ); ?>
</h4>
<div id="general-surveys-options">
	<fieldset>
		<div class="overlay">
			<span class="preloader"></span>
		</div>
		<div class="control-group">
			<p>
				<?php _e( 'You can add a captcha to your surveys. For this to work, you must enter your site key and secret key. You can get those here: ', 'awesome-surveys' ); ?>
				<a href="https://www.google.com/recaptcha/admin"><?php _e( 'Google reCAPTCHA' ); ?></a>
			</p>
			<label class="control-label" for="enable_captcha"><?php _e( 'Enable captcha', 'awesome-surveys' ) ?></label>
			<div class="controls">
				<input type="checkbox" name="options[general_options][enable_captcha]" id="enable_captcha" <?php checked( $options['general_options']['enable_captcha'] ); ?> value="1">
			</div>
	</div>
		<div class="control-group">
			<label class="control-label" for="captcha_site_key"><?php _e( 'Your re-captcha Site Key', 'awesome-surveys' ) ?></label>
			<div class="controls">
				<input type="text" name="options[general_options][captcha_site_key]" id="captcha_site_key" value="<?php echo  sanitize_text_field( $options['general_options']['captcha_site_key'] ); ?>">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label" for="captcha_secret_key"><?php _e( 'Your re-captcha Secret', 'awesome-surveys' ) ?></label>
			<div class="controls"><input type="password" name="options[general_options][captcha_secret_key]" id="captcha_secret_key" value="<?php echo sanitize_text_field( $options['general_options']['captcha_secret_key'] ); ?>">
			</div>
		</div>
		<hr />
		<div class="control-group">
			<p>
				<?php _e( 'This plugin outputs some very basic structural css. You can enable/disable this by setting the option below', 'awesome-surveys' ); ?>
			</p>
			<label class="control-label" for="styling-options-element-1">
				<?php _e( 'Use included css?', 'awesome-surveys' ); ?></label>
			<div class="controls">
				<label class="radio inline">
					<input id="styling-options-element-1-0" name="options[general_options][include_css]" value="1" <?php checked( 1 == $include ); ?> type="radio"><?php _e( 'Yes', 'awesome-surveys' ); ?></label>
				<label class="radio inline">
					<input id="styling-options-element-1-1" name="options[general_options][include_css]" value="0" <?php checked( 0 == $include ); ?> type="radio"><?php _e( 'No', 'awesome-surveys' ); ?></label>
			</div>
		</div>
		<input name="action" value="update_styling_options" id="styling-options-element-2" type="hidden">
		<?php wp_nonce_field( 'awesome-surveys-update-options', '_nonce', false, true ); ?>
		<div class="form-actions">
			<p>
				<input value="<?php _e( 'Save', 'awesome-surveys' ); ?>
				" name="" class="button-primary btn btn-primary" id="styling-options-element-4" type="submit">
			</p>
		</div>
	</fieldset>
</div>
<h4>
	<?php _e( 'Translate', 'awesome-surveys' ); ?>
</h4>
<p>
	<?php _e( 'Press the button below to translate your surveys to the current site language', 'awesome-surveys' ); ?>
</p>
<div id="translate-surveys">
	<!--fieldset-->
			<div class="overlay">
				<span class="preloader"></span>
			</div>
			<a href="<?php echo $_SERVER['REQUEST_URI'] . '&translate-surveys=true'; ?>" class="button-primary btn btn-primary" id="translate-surveys"><?php _e( 'Translate', 'awesome-surveys' ); ?></a>
	<!--/fieldset-->
</div>
