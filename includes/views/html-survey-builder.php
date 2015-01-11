<div id="survey-elements-buttons">
 <h4><?php _e( 'Add a form element to your survey by clicking a button', 'awesome-surveys' ); ?></h4>
 <?php
 $buttons = array(
  'text' => __( 'Text Input', 'awesome-surveys' ),
  'email' => __( 'Email', 'awesome-surveys' ),
  'number' => __( 'Number', 'awesome-surveys' ),
  'dropdown' => __( 'Dropdown Selector', 'awesome-surveys' ),
  'radio' => __( 'Radio Buttons', 'awesome-surveys' ),
  'checkbox' => __( 'Checkboxes', 'awesome-surveys' ),
  'textarea' => __( 'Textarea', 'awesome-surveys' ),
  );
 foreach ( $buttons as $name => $value ) {
  echo '<button name="' . $name . '">' . $value . '</button>' . "\n";
 }
 ?>
</div>
<hr>
<div id="current-element"></div>
<hr>
<div id="form-preview"></div>