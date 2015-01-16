<?php
	$options = get_option( 'wwm_awesome_surveys_options', array() );
	$enable = ( isset( $options['email_options'] ) ) ? absint( $options['email_options']['enable_emails'] ) : 0;
	$enable_respondent_email = ( isset( $options['email_options'] ) ) ? absint( $options['email_options']['enable_respondent_email'] ) : 0;
	$email_subject = ( isset( $options['email_options'] ) && isset( $options['email_options']['email_subject'] ) ) ? sanitize_text_field( $options['email_options']['email_subject'] ) : __( 'Thank you for your response', 'awesome-surveys' );
?>
<h4><?php _e( 'Notification Emails', 'awesome-surveys' ); ?></h4>
<div id="surveys-email-options">
 <fieldset>
  <div class="overlay">
   <span class="preloader"></span>
  </div>
  <div class="control-group">
   <label class="control-label" for="email-options-element-1"><?php _e( 'Enable emails on survey completion?', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <label class="radio inline">
     <input id="email-options-element-1-0" name="email_options[enable_emails]" value="1" type="radio" <?php checked( $enable ); ?>><?php _e( 'Yes', 'awesome-surveys' ); ?></label>
    <label class="radio inline">
     <input id="email-options-element-1-1" name="email_options[enable_emails]" value="0" type="radio" <?php checked( ! $enable ); ?>><?php _e( 'No', 'awesome-surveys' ); ?></label>
   </div>
  </div>
  <div class="control-group">
   <label class="control-label" for="email-options-element-2"><?php _e( 'Send Notifications for all survey completions to', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <input name="email_options[mail_to]" value="will@Sheridan.localhost" id="email-options-element-2" type="email"></div>
  </div>
  <div class="control-group">
   <label class="control-label" for="email-options-element-3"><?php _e( 'Send email to survey respondent?', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <label class="radio inline">
     <input id="email-options-element-3-0" name="email_options[enable_respondent_email]" value="1" <?php checked( $enable_respondent_email ); ?> type="radio"><?php _e( 'Yes', 'awesome-surveys' ); ?></label>
    <label class="radio inline">
     <input id="email-options-element-3-1" name="email_options[enable_respondent_email]" value="0" <?php checked( ! $enable_respondent_email ); ?> type="radio"><?php _e( 'No', 'awesome-surveys' ); ?></label>
   </div>
  </div>
  <p class="italics">
  <?php _e( 'For this to work, the survey must have an element of type "email"', 'awesome-surveys' ); ?>
  </p>
  <div class="control-group">
   <label class="control-label" for="email-options-element-5"><?php _e( 'Respondent email subject', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <input name="email_options[respondent_email_subject]" value="Thank you for your response" id="email-options-element-5" type="text"></div>
  </div>
  <div class="control-group">
   <label class="control-label" for="email-options-element-6"><?php _e( 'Respondent email message', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <textarea rows="5" name="email_options[respondent_email_message]" id="email-options-element-6"><?php _e( 'Thank you for your response to a survey', 'awesome-surveys' ); ?></textarea>
   </div>
  </div>
  <div class="control-group">
   <p class="template-tags">
   <?php printf( '%s', __( 'The following template tags are available', 'awesome-surveys' ) . ': {siteurl}, {blogname}, {surveyname}' ); ?>
   </p>
   <p><?php _e( 'HTML is not supported', 'awesome-surveys' ); ?></p>
  <div class="form-actions">
   <input value="<?php _e( 'Save', 'awesome-surveys' ); ?>" class="button-primary btn btn-primary" id="email-options-element-10" type="submit"></div>
 </fieldset>
</div>