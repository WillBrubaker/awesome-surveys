<?php

class Awesome_Surveys {

	protected $wwm_plugin_values = array(
		'name' => 'Awesome_Surveys',
		'dbversion' => '1.1',
		'version' => '2.0-pre',
	);

	protected $buttons;
	public $text_domain, $existing_elements, $plugin_version;
	public function __construct() {
		$this->plugin_version = $this->get_version();
		$this->text_domain = 'awesome-surveys';
		$this->buttons = $this->get_buttons();
		$actions = array(
			'init' => array( 'init', 10, 0 ),
			);
		foreach ( $actions as $action => $args ) {
			add_action( $action, array( $this, $args[0] ), $args[1], $args[2] );
		}
		add_filter( 'the_content', array( $this, 'the_content' ), 10, 1 );
	}

	public function init() {
		$this->register_post_type();
	}

	private function get_buttons() {
		return array(
			'text' => array(
				'type' => 'Element_Textbox',
				'label' => __( 'Text Input', 'awesome-surveys' )
					),
			'email' => array(
				'type' => 'Element_Email',
				'label' => __( 'Email', 'awesome-surveys' ),
					),
			'number' => array(
				'type' => 'Element_Number',
				'label' => __( 'Number', 'awesome-surveys' ),
					),
			'dropdown' => array(
				'type' => 'Element_Select',
				'label' => __( 'Dropdown Selector', 'awesome-surveys' ),
					),
			'radio' => array(
				'type' => 'Element_Radio',
				'label' => __( 'Radio Buttons', 'awesome-surveys' ),
					),
			'checkbox' => array(
				'type' => 'Element_Checkbox',
				'label' => __( 'Checkboxes', 'awesome-surveys' ),
					),
			'textarea' => array(
				'type' => 'Element_Textarea',
				'label' => __( 'Textarea', 'awesome-surveys' ),
					),
		);
	}

	public function register_post_type() {

		$args = array(
			'label' => _( 'Awesome Surveys', 'awesome-surveys' ),
			'labels' => array(
				'name' => __( 'Surveys', 'awesome-surveys' ),
				'singular_name' => __( 'Survey', 'awesome-surveys' ),
				'menu_name' => __( 'My Surveys', 'awesome-surveys' ),
				'name_admin_bar' => __( 'Survey', 'awesome-surveys' ),
				'add_new' => __( 'New Survey', 'awesome-surveys' ),
				'new_item' => __( 'New Survey', 'awesome-surveys' ),
				'add_new_item' => __( 'Add New Survey', 'awesome-surveys' ),
				'edit_item' => __( 'Edit Survey', 'awesome-surveys' ),
				),
			'description' => __( 'Surveys for your site', 'awesome-surveys' ),
			'public' => true,
			'capability_type' => 'post',
			'exclude_from_search' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'show_in_menu' => false,
			'show_in_admin_bar' => false,
			'supports' => array(
				'title',
				),
			'register_meta_box_cb' => array( $this, 'survey_editor' ),
			'rewrite' => true,
			);
		register_post_type( 'awesome-surveys', $args );
	}

	public function survey_editor() {

		add_meta_box( 'create_survey', __( 'Create Survey', 'awesome-surveys' ), array( $this, 'survey_builder' ), 'awesome-surveys', 'normal', 'core' );
		add_meta_box( 'general-survey-options-metabox', __( 'General Survey Options', 'awesome-surveys' ), array( $this, 'general_survey_options' ), 'awesome-surveys', 'normal', 'core' );
	}

	public function survey_builder() {
		wp_enqueue_script( 'awesome-surveys-admin-script' );
		wp_enqueue_style( 'awesome-surveys-admin-style' );
		include_once( 'views/html-survey-builder.php' );
	}

	public function general_survey_options() {
		include_once( 'views/html-survey-options-general.php' );
	}

	protected function get_form_preview_html( $post_id = 0 ) {

		$output = null;
		if ( ! class_exists( 'Form' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
		}

		if ( ! isset( $this->existing_elements ) ) {

			$this->existing_elements = json_decode( get_post_meta( $post_id, 'existing_elements', true ), true );
		}
		$required_is_option = array( 'Element_Textbox', 'Element_Textarea', 'Element_Email', 'Element_Number' );
		$elements_count = 0;
		if ( ! isset( $this->buttons ) || empty( $this->buttons ) ) {

			$this->buttons = $this->get_buttons();
		}
		$form = new FormOverrides();
		$form->configure( array( 'class' => 'pure-form pure-form-stacked' ) );

		if ( isset( $this->existing_elements ) && ! empty( $this->existing_elements ) ) {
			foreach ( $this->existing_elements as $element ) {
					$method = $this->buttons[ $element['type'] ]['type'];
					$options = $atts = $rules = array();
					if ( isset( $element['validation']['rules'] ) && is_array( $element['validation']['rules'] ) ) {
						foreach ( $element['validation']['rules'] as $key => $value ) {
							if ( '' != $value && ! is_null( $value ) ) {
								$rules['data-' . $key] = $value;
							}
						}
					}
					if ( in_array( $method, $required_is_option ) && ! empty( $rules ) ) {
							$options = array_merge( $options, $rules );
					} else {
						$atts = array_merge( $options, $rules );
					}
					if ( ! empty( $element['validation']['required'] ) && 'false' != $element['validation']['required'] ) {
						if ( in_array( $method, $required_is_option ) ) {
							$options['required'] = 1;
							$options['class'] = 'required';
						} else {
							$atts['required'] = 1;
							$atts['class'] = 'required';
						}
					}
					$max = ( isset( $element['label'] ) ) ? count( $element['label'] ) : 0;
					for ( $iterations = 0; $iterations < $max; $iterations++ ) {
						/**
							* Since the pfbc is being used, and it has some weird issue with values of '0', but
							* it will work if you append :pfbc to it...not well documented, but it works!
							*/
						$options[$element['value'][$iterations] . ':pfbc'] = htmlentities( stripslashes( $element['label'][$iterations] ) );
					}
					$atts['value'] = ( isset( $element['default'] ) ) ? $element['default']  : null;
					$form->addElement( new Element_HTML( '<div class="single-element-edit">' ) );
					$form->addElement( new $method( htmlentities( stripslashes( $element['name'] ) ), sanitize_title( $element['name'] ), $options, $atts ) );
					$form->addElement( new Element_HTML( '<div class="button-holder"><button class="element-edit" data-action="delete" data-index="' . $elements_count . '">' . __( 'Delete question', 'awesome-surveys' ) . '</button><button class="element-edit" data-action="edit" data-index="' . $elements_count . '">' . __( 'Edit question', 'awesome-surveys' ) . '</button></div><div class="clear"></div></div>' ) );
					$elements_count++;
			}
			$output = $form->render( true );
		}

		$pattern = '/<form action="[^"]+"/';
		$replacement = '<div';
		$output = preg_replace( $pattern, $replacement, $output );
		$pattern = '/method="post"/';
		$replacement = '';
		$output = preg_replace( $pattern, $replacement, $output );
		$pattern = '/<\/form/';
		$replacement = '</div';
		$output = preg_replace( $pattern, $replacement, $output );
		return $output;
	}

	/**
		* Builds the survey form from the stored options in the database.
		* @param  array $form an array of form elements - this array was stored in the db when the survey was created
		* @param  array $args an array of arguments, includes the survey id and the survey name
		* @return string an html form
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	function awesome_surveys_render_form( $args = array() ) {

		if ( ! isset( $this->buttons ) ) {
			$this->buttons = $this->get_buttons();
		}
		if ( ! class_exists( 'Form' ) ) {
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Form.php' );
			include_once( plugin_dir_path( __FILE__ ) . 'PFBC/Overrides.php' );
		}
		$nonce = 'answer_survey_nonce';
		$has_options = array( 'Element_Select', 'Element_Checkbox', 'Element_Radio' );
		$form_output = new FormOverrides();
		$form_output->configure( array( 'class' => 'answer-survey pure-form pure-form-stacked', 'action' => $_SERVER['REQUEST_URI'], ) );
		$form_output->addElement( new Element_HTML( '<div class="overlay"><span class="preloader"></span></div>') );
		$questions_count = 0;
		$existing_elements = $this->existing_elements;
		foreach ( $existing_elements as $element ) {
			$method = $this->buttons[ $element['type'] ]['type'];
			$atts = $rules = $options = array();
			if ( 'Element_Select' == $method ) {
				$options[''] = __( 'make a selection...', $this->text_domain );
			}
			if ( isset( $element['validation']['rules'] ) ) {
				foreach ( $element['validation']['rules'] as $key => $value ) {
					if ( '' != $value ) {
						$rules['data-rule-' . $key] = $value;
					}
				}
			}
			if ( in_array( $method, $has_options ) ) {
				$atts = array_merge( $atts, $rules );
				if ( isset( $element['default'] ) ) {
					$atts['value'] = $element['default'];
				}
				if ( isset( $element['validation']['required'] ) && false != $element['validation']['required'] ) {
					$atts['required'] = 'required';
				}
				foreach ( $element['value'] as $key => $value ) {
					/**
						* append :pfbc to the key so that pfbc doesn't freak out
						* about numerically keyed arrays.
						*/
					$options[$value . ':pfbc'] = stripslashes( $element['label'][$key] );
				}
			} else {
				$options = array_merge( $options, $rules );
				if ( isset( $element['default'] ) ) {
					$options['value'] = $element['default'];
				}
				if ( isset( $element['validation']['required'] ) && false != $element['validation']['required'] ) {
					$options['required'] = 'required';
				}
			}
			$form_output->addElement( new $method( stripslashes( $element['name'] ), 'question[' . $questions_count . ']', $options, $atts ) );
			$questions_count++;
		}
		$form_output->addElement( new Element_Hidden( 'answer_survey_nonce', $nonce ) );
		$form_output->addElement( new Element_Hidden( 'survey_id', '', array( 'value' => $args['survey_id'], ) ) );
		$form_output->addElement( new Element_Hidden( 'action', 'answer_survey' ) );
		$form_output->addElement( new Element_Button( __( 'Submit Response', 'awesome-surveys' ), 'submit', array( 'class' => 'button-primary', 'disabled' => 'disabled' ) ) );
		return $form_output->render( true );
	}

	/**
		* Removes some unneeded bits and pieces from
		* the survey form prior to displaying for preview &
		* prior to json_encoding the array of elements for storage in the db
		* @param  array $form_elements_array an array of form elements
		* @return array $form_elements_array the filtered form elements
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function awesome_surveys_form_preview( $form_elements_array ) {

		$defaults = array(
			'required' => false,
			'rules' => array(),
		);
		$form_elements_array['validation'] = wp_parse_args( ( isset( $form_elements_array['validation'] ) ) ? $form_elements_array['validation'] : array(), $defaults );
		if ( isset( $form_elements_array['validation']['rules'] ) ) {
			unset( $form_elements_array['validation']['rules']['number_validation_type'] );
			foreach ( $form_elements_array['validation']['rules'] as $key => $value ) {
				if ( is_null( $value ) || '' == $value && 'required' != $value ) {
					unset( $form_elements_array['validation']['rules'][$key] );
				}
			}
		}
		return $form_elements_array;
	}

	public function the_content( $content ) {
		if ( is_singular( 'awesome-survey' ) ) {
			$nonce = wp_create_nonce( 'answer-survey' );
			$content = str_replace( 'value="answer_survey_nonce"', 'value="' . $nonce . '"', $content );
		}
		return $content;
	}

	protected function get_version() {
		return '2.0-pre';
	}
}