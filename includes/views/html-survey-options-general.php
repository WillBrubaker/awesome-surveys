<?php
global $post;
$auth_method = get_post_meta( $post->ID, 'survey_auth_method', true );
$thank_you_message = ( ! empty( $post->post_excerpt ) ) ? $post->post_excerpt : __( 'Thank you for completing this survey', 'awesome-surveys' );
?>

<div class="pure-form pure-form-stacked form-horizontal" id="general-survey-options">
 <fieldset>
  <div class="control-group">
   <label for="general-survey-options-element-0" class="control-label"><?php _e( 'A Thank You message', 'awesome-surveys' ); ?>:</label>
   <div class="controls">
    <textarea id="excerpt" name="excerpt" cols="40" rows="5"><?php echo $thank_you_message; ?></textarea>
   </div>
  </div>
  <div class="ui-widget-content ui-corner-all validation field-validation">
   <span class="label">
    <p>
     <?php _e( 'To prevent people from filling the survey out multiple times you may select one of the options below', 'awesome-surveys' ); ?>
    </p>
   </span>
   <div class="control-group">
    <label for="general-survey-options-element-2" class="control-label"><?php _e( 'Validation/authentication', 'awesome-surveys' ); ?></label>
    <div class="controls">
     <label class="radio">
      <input type="radio" value="0" name="meta[survey_auth_method]" id="general-survey-options-element-2-0" <?php checked( empty( $auth_method ) ); ?>><?php _e( 'None', 'awesome-surveys' ); ?></label>
     <label class="radio">
      <input type="radio" value="1" name="meta[survey_auth_method]" id="general-survey-options-element-2-1" <?php checked( "1" === $auth_method ); ?>><?php _e( 'Cookie based', 'awesome-surveys' ); ?></label>
     <label class="radio">
      <input type="radio" value="2" name="meta[survey_auth_method]" id="general-survey-options-element-2-2" <?php checked( "2" === $auth_method ); ?>><?php _e( 'User must be logged in', 'awesome-surveys' ); ?></label>
    </div>
   </div>
  </div>
 </fieldset>
</div>