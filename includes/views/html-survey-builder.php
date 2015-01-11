<?php
global $post;
$buttons = array(
 'text' => __( 'Text Input', 'awesome-surveys' ),
 'email' => __( 'Email', 'awesome-surveys' ),
 'number' => __( 'Number', 'awesome-surveys' ),
 'dropdown' => __( 'Dropdown Selector', 'awesome-surveys' ),
 'radio' => __( 'Radio Buttons', 'awesome-surveys' ),
 'checkbox' => __( 'Checkboxes', 'awesome-surveys' ),
 'textarea' => __( 'Textarea', 'awesome-surveys' ),
 );
?>
<div id="survey-elements-buttons">
 <input type="hidden" id="post_id" value="<?php $post->ID; ?>">
 <h4><?php _e( 'Add a form element to your survey by clicking a button', 'awesome-surveys' ); ?></h4>
 <?php
 foreach ( $buttons as $name => $value ) {
  echo '<button name="' . $name . '">' . $value . '</button>' . "\n";
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
<div id="form-preview"></div>
</div>