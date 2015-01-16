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
     <input id="email-options-element-1-0" name="email_options[enable_wwm_as_emails]" value="1" type="radio"><?php _e( 'Yes', 'awesome-surveys' ); ?></label>
    <label class="radio inline">
     <input id="email-options-element-1-1" name="email_options[enable_wwm_as_emails]" value="0" type="radio"><?php _e( 'No', 'awesome-surveys' ); ?></label>
   </div>
  </div>
  <div class="control-group">
   <label class="control-label toggle hidden" for="email-options-element-2"><?php _e( 'Send Notifications for all survey completions to', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <input name="email_options[mail_to]" value="will@Sheridan.localhost" class="toggle hidden" id="email-options-element-2" type="email"></div>
  </div>
  <div class="control-group">
   <label class="control-label toggle hidden" for="email-options-element-3"><?php _e( 'Send email to survey respondent?', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <label class="radio inline toggle hidden">
     <input id="email-options-element-3-0" name="email_options[enable_wwm_as_respondent_email]" class="toggle hidden" value="1" type="radio">Yes</label>
    <label class="radio inline toggle hidden">
     <input id="email-options-element-3-1" name="email_options[enable_wwm_as_respondent_email]" class="toggle hidden" value="0" checked="checked" type="radio">No</label>
   </div>
  </div>
  <p class="italics toggle hidden">
  <?php _e( 'For this to work, the survey must have an element of type "email"', 'awesome-surveys' ); ?>
  </p>
  <div class="control-group">
   <label class="control-label toggle hidden" for="email-options-element-5"><?php _e( 'Respondent email subject', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <input name="email_options[respondent_email_subject]" class="toggle hidden" value="Thank you for your response" id="email-options-element-5" type="text"></div>
  </div>
  <div class="control-group">
   <label class="control-label toggle hidden" for="email-options-element-6"><?php _e( 'Respondent email message', 'awesome-surveys' ); ?></label>
   <div class="controls">
    <textarea rows="5" name="email_options[respondent_email_message]" class="toggle hidden" id="email-options-element-6"><?php _e( 'Thank you for your response to a survey', 'awesome-surveys' ); ?></textarea>
   </div>
  </div>
  <div class="control-group">
   <p class="template-tags toggle hidden">
   <?php printf( '%s', __( 'The following template tags are available', 'awesome-surveys' ) . ': {siteurl}, {blogname}, {surveyname}' ); ?>
   </p>
   <p class="toggle hidden"><?php _e( 'HTML is not supported', 'awesome-surveys' ); ?></p>
  </div>
  <input name="action" value="update_email_options" id="email-options-element-8" type="hidden">
  <div class="form-actions">
   <input value="<?php _e( 'Save', 'awesome-surveys' ); ?>" class="button-primary btn btn-primary" id="email-options-element-10" type="submit"></div>
 </fieldset>
</div>