<?php
global $post;
global $awesome_surveys;
$existing_elements = get_post_meta( $post->ID, 'existing_elements', true );
$form_preview_html = $awesome_surveys->get_form_preview_html( $post->ID );
?>
<div id="awesome-survey">
 <input type="hidden" name="existing_elements" id="existing_elements" value='<?php echo $existing_elements; ?>'>
 <textarea id="content" name="content" style="display:none;"><?php echo $post->post_content; ?></textarea>
  <?php wp_nonce_field( 'create-survey', 'create_survey_nonce', false, true ); ?>
 <input type="hidden" name="survey_id" value="<?php echo $post->ID; ?>">
 <div id="survey-elements-buttons">
  <h4><?php _e( 'Add a form element to your survey by clicking a button', 'awesome-surveys' ); ?></h4>
  <?php
  foreach ( $awesome_surveys->buttons as $name => $value ) {
   echo '<button name="' . $name . '">' . $value['label'] . '</button>' . "\n";
  }
  ?>
 </div>
 <div id="current-element-wrapper">
 <hr>
 <h4><?php _e( 'Configure a survey question', 'awesome-surveys' ); ?></h4>
 <div id="current-element"></div>
 </div>
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