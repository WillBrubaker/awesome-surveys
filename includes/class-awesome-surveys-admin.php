<?php

class Awesome_Surveys_Admin extends Awesome_Surveys {

	protected $page_hook, $page_title, $menu_title, $menu_slug;
	public static $options;
	public function __construct() {
		parent::__construct();
		$this->page_title = __( 'Awesome Surveys Options', 'awesome-surveys' );
		$this->menu_title = __( 'Survey Options', 'awesome-surveys' );
		$this->menu_slug = 'awesome-surveys.php';
		$this->menu_link_text = __( 'Awesome Surveys', 'awesome-surveys' );
		$this->text_domain = 'awesome-surveys';
		self::$options = $this->get_options();
		$actions = array(
			'admin_menu' => array( 'admin_menu', 10, 0 ),
			'save_post' => array( 'save_post', 10, 2 ),
			'admin_enqueue_scripts' => array( 'admin_enqueue_scripts', 10, 0 ),
			'init' => array( 'init', 10, 0 ),
			'admin_init' => array( 'admin_init', 1, 0 ),
			'admin_notices' => array( 'admin_notices', 10, 0 ),
			'wp_insert_post_data' => array( 'insert_post_data', 10, 2 ),
			);

		foreach ( $actions as $action => $args ) {
			add_action( $action, array( $this, $args[0] ), $args[1], $args[2] );
		}

		$filters = array(
			'post_row_actions' => array( 'post_row_actions', 10, 2 ),
			);

		foreach ( $actions as $key => $action ) {
			add_action( $key, array( $this, $action[0] ), $action[1], $action[2] );
		}
		foreach ( $filters as $key => $filter ) {
			add_filter( $key, array( $this, $filter[0] ), $filter[1], $filter[2] );
		}
		$awesome_surveys_admin = $this;
	}

	public function save_post( $post_id, $post ) {
		if (  ! isset( $_POST['create_survey_nonce'] ) || ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) ) {
			return;
		}
		if ( isset( $_POST['existing_elements'] ) ) {
			$existing_elements = $_POST['existing_elements'];
			$this->existing_elements = $existing_elements;
			update_post_meta( $post_id, 'existing_elements', $existing_elements );
		}
		if ( isset( $_POST['meta']['survey_auth_method'] ) ) {
			update_post_meta( $post_id, 'survey_auth_method', absint( $_POST['meta']['survey_auth_method'] ) );
		}
	}

	/**
		* enqueues the necessary css/js for the admin area
		* @since 1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function admin_enqueue_scripts() {
		$defaults = array(
			'num_answers' => 10,
			);
		$args = apply_filters( 'wwm_as_admin_script_vars', $defaults );
		$args = wp_parse_args( $args, $defaults );
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'jquery-validation-plugin', WWM_AWESOME_SURVEYS_URL . '/js/jquery.validate.min.js', array( 'jquery' ), '1.13.0' );

		wp_register_style( 'normalize-css', WWM_AWESOME_SURVEYS_URL . '/css/normalize.min.css' );
		wp_register_style( 'jquery-ui-smoothness', WWM_AWESOME_SURVEYS_URL . '/css/jquery-ui.min.css', array( 'wp-admin' ), '1.10.13', 'all' );
		wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css', array( 'normalize-css' ) );

		wp_register_script( $this->text_domain . '-options-script', WWM_AWESOME_SURVEYS_URL . '/js/options' . $suffix . '.js', array( 'jquery', 'jquery-ui-accordion', 'postbox' ), $this->plugin_version, true );
		wp_register_script( $this->text_domain . '-view-results', WWM_AWESOME_SURVEYS_URL . '/js/results' . $suffix . '.js', array( 'jquery', 'postbox', 'jquery-ui-accordion' ), $this->plugin_version, true );
		wp_register_style( $this->text_domain . '-options-style', WWM_AWESOME_SURVEYS_URL . '/css/options' . $suffix . '.css', array( 'pure-forms-css' ), $this->plugin_version, 'all' );
		wp_register_style( $this->text_domain . '-results-style', WWM_AWESOME_SURVEYS_URL . '/css/results' . $suffix . '.css', array( 'pure-forms-css' ), $this->plugin_version, 'all' );
		$screen = get_current_screen();
		if ( 'awesome-surveys' === $screen->id ) {
			wp_enqueue_script( $this->text_domain . '-admin-script', WWM_AWESOME_SURVEYS_URL . '/js/admin-script' . $suffix . '.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-validation-plugin', 'jquery-ui-dialog', 'jquery-ui-button', 'postbox' ), $this->plugin_version, true );
			wp_localize_script( $this->text_domain . '-admin-script', 'wwm_as_admin_script', $args );
			wp_enqueue_style( $this->text_domain . '-admin-style', WWM_AWESOME_SURVEYS_URL . '/css/admin-style' . $suffix . '.css', array( 'jquery-ui-smoothness', 'pure-forms-css' ), $this->plugin_version, 'all' );
			if ( isset( $_GET['view'] ) && 'results' === $_GET['view'] ) {
				wp_enqueue_script( $this->text_domain . '-view-results' );
				wp_enqueue_style( $this->text_domain . '-results-style' );
			}
		}
	}

	public function admin_print_scripts() {
		wp_enqueue_script( $this->text_domain . '-options-script' );
	}

	public function admin_print_styles() {
		wp_enqueue_style( $this->text_domain . '-options-style' );
	}

	/**
		* Adds the WtWM menu item to the admin menu & adds a submenu link to this plugin to that menu item.
		* @since  1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function admin_menu() {
		global $_wwm_plugins_page;
		/**
			* If, in the future, there is an enhancement or improvement,
			* allow other plugins to overwrite the panel by using
			* a higher number version.
			*/
		$plugin_panel_version = 2;
		add_filter( 'wwm_plugin_links', array( $this, 'this_plugin_link' ) );
		if ( empty( $_wwm_plugins_page ) || ( is_array( $_wwm_plugins_page ) && $plugin_panel_version > $_wwm_plugins_page[1] ) ) {
			$_wwm_plugins_page[0] = add_menu_page( 'WtWM Plugins', 'WtWM Plugins', 'edit_others_posts', 'wwm_plugins', array( $this, 'wwm_plugin_links' ), WWM_AWESOME_SURVEYS_URL . '/images/wwm_wp_menu.png', '90' );
			$_wwm_plugins_page[1] = $plugin_panel_version;
		}
		$this->page_hook = add_submenu_page( 'wwm_plugins', $this->page_title, $this->menu_title, 'edit_others_posts', $this->menu_slug, array( &$this, 'plugin_options' ) );
		add_submenu_page( 'wwm_plugins', '', __( 'My Surveys', 'awesome-surveys' ), 'edit_others_posts', 'edit.php?post_type=awesome-surveys' );
		add_submenu_page( 'wwm_plugins', '', __( 'New Survey', 'awesome-surveys' ), 'edit_others_posts', 'post-new.php?post_type=awesome-surveys' );
		add_action( 'admin_print_scripts-' . $this->page_hook, array( $this, 'admin_print_scripts' ) );
		add_action( 'admin_print_styles-' . $this->page_hook, array( $this, 'admin_print_styles' ) );
	}

	/**
		* adds the link to this plugin's management page
		* to the $links array to be displayed on the WWM
		* plugins page:
		* @since 1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		* @param  array $links the array of links
		* @return array $links the filtered array of links
		*/
	public function this_plugin_link( $links ) {

		$this->wwm_page_link = $menu_page_url = menu_page_url( $this->menu_slug, 0 );
		$links[] = '<a href="' . $this->wwm_page_link . '">' . $this->menu_link_text . '</a>' . "\n";
		return $links;
	}

	/**
		* outputs an admin panel and displays links to all
		* admin pages that have been added to the $wwm_plugin_links array
		* via apply_filters
		* @since 1.0
		* @author Will the Web Mechanic <will@willthewebmechanic.com>
		* @link http://willthewebmechanic.com
		*/
	public function wwm_plugin_links() {

		$wwm_plugin_links = array();
		$wwm_plugin_links = apply_filters( 'wwm_plugin_links', $wwm_plugin_links );
		//set a version here so that everything can be overwritten by future plugins.
		//and pass it via the do_action calls
		$plugin_links_version = 1;
		do_action( 'before_wwm_plugin_links', $plugin_links_version, $wwm_plugin_links );
		if ( ! empty( $wwm_plugin_links ) ) {
			echo '<div class="wrap">' . "\n";
			echo '<div id="icon-plugins" class="icon32"><br></div>' . "\n";
			echo '<h2>Will the Web Mechanic Plugins</h2>' . "\n";
			echo '<ul>' . "\n";
			foreach ( $wwm_plugin_links as $link ) {
				echo '<li>' . $link . '</li>' . "\n";
			}
			echo '</ul>';
		}
		do_action( 'after_wwm_plugin_links', $plugin_links_version );
		echo '</div>' . "\n";
	}

	/**
	 * outputs the plugin option metaboxes
	 */
	public function plugin_options() {
		include_once( WWM_AWESOME_SURVEYS_PATH . '/options.php' );
		add_meta_box( 'awesome-surveys-options', __( 'Awesome Surveys Options', 'awesome-surveys' ), array( $this, 'surveys_options' ), $this->page_hook, 'normal', 'core' );
		add_meta_box( 'awesome-surveys-email-options', __( 'Email Options', 'awesome-surveys' ), array( $this, 'email_options' ), $this->page_hook, 'normal', 'core' );
		echo '<div id="poststuff" class="wrap">';
		echo '<form action="' . $_SERVER['REQUEST_URI'] . '" id="surveys-options" method="post" class="form-horizontal">';
		do_meta_boxes( $this->page_hook, 'normal', $this );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * gets the surveys options html
	 */
	public function surveys_options() {
		include_once( 'views/html-surveys-options.php' );
	}

	/**
	 * gets the email options html
	 */
	public function email_options() {
		include_once( 'views/html-surveys-options-emails.php' );
	}

	/**
	 * conditinally outputs a 'view results' link in the
	 * 'all posts' screen
	 * @param  array $actions
	 * @param  oject $post    the wp post object
	 * @return array          the filtered array of links
	 */
	public function post_row_actions( $actions, $post ) {
		if ( 'awesome-surveys' === $post->post_type ) {
			$nonce = wp_create_nonce( 'wwm-duplicate-survey' );
			$edit_post_link = get_edit_post_link( $post->ID, true );
			$duplicate_url = admin_url( 'post.php?post=' . $post->ID . '&action=duplicate&duplicate_survey_nonce=' . $nonce );
			$show_link = get_post_meta( $post->ID, '_response', true );
			if ( ! empty( $show_link ) ) {
				$actions['results'] = '<a href="' . $edit_post_link . '&amp;view=results' . '" title="' . __( 'View Survey Results', 'awesome-surveys' ) . '">' . __( 'Results', 'awesome-surveys' ) . '</a>';
			}
			$actions['duplicate'] = '<a href="' . $duplicate_url . '" title="' . __( 'Create a copy of this survey', 'awesome-surveys' ) . '">' . __( 'Duplicate', 'awesome-surveys' ) . '</a>';
		}
		return $actions;
	}

	public function admin_notices() {
		$screen = get_current_screen();
		if ( strpos( $screen->id, 'awesome-surveys' ) > 0 || 'toplevel_page_wwm_plugins' == $screen->id ) {
			$old_surveys = get_option( 'wwm_awesome_surveys', false );
			if ( ! $old_surveys ) {
				return;
			}
			if ( isset( $_GET['database_upgraded'] ) && 'true' == $_GET['database_upgraded'] ) {
				echo '<div class="updated"><p>' . __( 'Surveys updated', 'awesome-surveys' ) . '</p></div>';
				return;
			}
			$dbversion = get_option( 'wwm_as_dbversion', '1.1' );
				if ( version_compare( $this->dbversion, $dbversion, '==' ) && ! isset( $_GET['force_upgrade'] ) ) {
					return;
				}
			include_once( 'views/html-database-upgrade.php' );
		}
	}

	public function admin_init() {
		if ( isset( $_POST['wwm_do_db_upgrade'] ) && wp_verify_nonce( $_POST['wwm_as_db_upgrade'], 'wwm-as-database-upgrade' ) ) {
			require_once( 'wwmas-database-upgrade-functions.php' );
			wwmas_do_database_upgrade();
			ob_start();
			$url = admin_url( 'edit.php?post_type=awesome-surveys&database_upgraded=true' );
			wp_redirect( $url );
			exit;
		}
		if ( isset( $_GET['action'] ) && 'duplicate' == $_GET['action'] && wp_verify_nonce( $_GET['duplicate_survey_nonce'], 'wwm-duplicate-survey' ) ) {
			$new_survey = $this->duplicate_survey( $_GET['post'] );
			if ( $new_survey > 0 ) {
				$url = admin_url( 'post.php?post=' . $new_survey . '&action=edit' );
				wp_redirect( $url );
				exit;
			}
		}
		if ( isset( $_GET['translate-surveys'] ) && 'true' == $_GET['translate-surveys'] ) {
			ob_start();
			require_once( WWM_AWESOME_SURVEYS_PATH . '/includes/wwmas-database-upgrade-functions.php' );
			wwmas_translate_post_content();
			$url = admin_url( 'admin.php?page=awesome-surveys.php' );
			wp_redirect( $url );
			exit;
		}
	}

	public function insert_post_data( $data, $postarr ) {
		if ( 'awesome-surveys' == $data['post_type'] && isset( $data['post_content'] ) ) {
			$data['post_content'] = htmlspecialchars_decode( $data['post_content'], ENT_QUOTES );
		}
		return $data;
	}

	/**
	 * duplicates a survey
	 * @param  int $post_id the post id that is being duplicated
	 */
	private function duplicate_survey( $post_id ) {
		$post = get_post( $post_id );
		$post_metas = array(
					'existing_elements',
					'survey_auth_method',
					);
		$new_post_data = array(
			'post_content' => $post->post_content,
			'post_excerpt' => $post->post_excerpt,
			'post_type' => 'awesome-surveys',
			'post_status' => 'draft',
			'post_title' => $post->post_title . ' (' . __( 'Copy', 'awesome-surveys' ) . ')',
			);
		$new_survey = wp_insert_post( $new_post_data );
		if ( $new_survey ) {
			foreach( $post_metas as $meta_key ) {
				$meta_value = get_post_meta( $post_id, $meta_key, true );
				update_post_meta( $new_survey, $meta_key, $meta_value );
			}
		}
		return $new_survey;
	}

	/**
	 * outputs pagination links on the results screen
	 *
	 */
	public function results_pagination_links() {
		$post_id = absint( $_GET['post'] );
		$num_results = count( get_post_meta( $post_id, '_response', false ) );
		$limit = ( isset( $_GET['results'] ) ) ? intval( $_GET['results'] ) : 10;
		$offset = 0;
		$cur_page_num = ( isset( $_GET['offset'] ) ) ? intval( $_GET['offset'] ) : 0;
		if ( $num_results > $limit ) {
			$num_pages = ceil( $num_results / $limit );
			for ( $iterations = 0; $iterations < $num_pages; $iterations++ ) {
				if ( 0 == $iterations ) {
					if ( $cur_page_num > ( ( $iterations + 1 ) * $limit ) ) {
						echo '&nbsp;<a href="post.php?post=' . $post_id . '&action=edit&view=results&results=' . $limit . '&offset=0">&nbsp;&laquo;&nbsp;</a>&nbsp;';
					}
					if ( $cur_page_num > 0 ) {
						echo '&nbsp;<a href="post.php?post=' . $post_id . '&action=edit&view=results&results=' . $limit . '&offset=' . ( $cur_page_num - $limit ) . '">&nbsp;&lsaquo;&nbsp;</a>&nbsp;';
					}

				}
				echo '&nbsp;';
				if ( $offset != $cur_page_num ) {
						echo '<a href="post.php?post=' . $post_id . '&action=edit&view=results&results=' . $limit . '&offset=' . $offset . '">';
					}
					echo ( $iterations + 1 ) . '&nbsp;';
					if ( $offset != $cur_page_num ) {
						echo '</a>';
					}
					if ( $iterations == $num_pages - 1 ) {
						if ( $cur_page_num < ( ( $num_pages - 1 ) * $limit ) ) {
							echo '&nbsp;<a href="post.php?post=' . $post_id . '&action=edit&view=results&results=' . $limit . '&offset=' . ( $cur_page_num + $limit ) . '">&nbsp;&rsaquo;&nbsp;</a>&nbsp;';
						}
						if ( $cur_page_num < ( ( $num_pages - 2 ) * $limit ) ) {
							echo '&nbsp;<a href="post.php?post=' . $post_id . '&action=edit&view=results&results=' . $limit . '&offset=' . ( ( $num_pages - 1 ) * $limit ) . '">&nbsp;&raquo;&nbsp;</a>';
						}
					}
				$offset += $limit;
			}
		}
	}

		/**
	 * outputs appropriate content for each of the screens
	 *
	 */
	public function survey_editor() {
		if ( isset( $_GET['view'] ) && 'results' === $_GET['view'] ) {
			$post_id = absint( $_GET['post'] );
			add_action( 'edit_form_after_title', array( $this, 'results_pagination_links' ) );
			add_action( 'edit_form_advanced', array( $this, 'results_pagination_links' ) );
			$auth_method = get_post_meta( $post_id, 'survey_auth_method', true );
			$auth_type = $this->auth_methods[ $auth_method ]['name'];
			remove_post_type_support( 'awesome-surveys', 'title' );
			remove_meta_box( 'submitdiv', 'awesome-surveys', 'side' );
			add_meta_box( 'survey-results', __( 'Survey Results For:', 'awesome-surveys' ) . ' ' . get_the_title( $post_id ), array( $this, 'survey_results' ), 'awesome-surveys', 'normal', 'core' );
			$results = $this->get_results( $post_id );
			$offset = ( isset( $_GET['offset'] ) ) ? absint( $_GET['offset'] ) : 0;
			$results_keys = array();
			foreach ( $results as $key => $value ) {
				$results_keys[] = array_keys( $value );
			}
			$elements = json_decode( get_post_meta( $post_id, 'existing_elements', true ), true );
			foreach ( $results as $respondent_key => $answers ) {
				$auth_method = get_post_meta( $post_id, 'survey_auth_method', true );
				$auth_type = $this->auth_methods[ $auth_method ]['name'];
				if ( 'login' == $auth_type ) {
					$user_data = get_userdata( $results_keys[ $respondent_key ][0] );
					$meta_box_title = __( 'Results for ', 'awesome-surveys' ) . $user_data->display_name;
				} else {
					$meta_box_title = __( 'Results for respondent ', 'awesome-surveys' ) . ( $respondent_key + 1 + $offset );
					$number = ( isset( $_GET['offset'] ) ) ? absint( $_GET['offset'] ) : 0;
				}
				add_filter( 'postbox_classes_awesome-surveys_respondent-' . ( $respondent_key + 1 ), array( $this, 'postbox_class' ) );
				add_meta_box( 'respondent-' . $respondent_key, $meta_box_title, array( $this, 'answers_by_respondent' ), 'awesome-surveys', 'normal', 'core', array( $results, $elements, $respondent_key ) );
			}
		} else {
			add_meta_box( 'create_survey', __( 'Create Survey', 'awesome-surveys' ), array( $this, 'survey_builder' ), 'awesome-surveys', 'normal', 'core' );
			add_meta_box( 'general-survey-options-metabox', __( 'General Survey Options', 'awesome-surveys' ), array( $this, 'general_survey_options' ), 'awesome-surveys', 'normal', 'core' );
		}
	}


	private function get_results( $post_id ) {
		global $wpdb;
		$screen = get_current_screen();
		$limit = ( isset( $_GET['results'] ) ) ? intval( $_GET['results'] ) : 10;
		$offset = ( isset( $_GET['offset'] ) ) ? intval( $_GET['offset'] ) : 0;
		$my_query = $wpdb->prepare( "SELECT `meta_value` FROM `" . $wpdb->postmeta . "` WHERE `post_id` = %d AND `meta_key` = '_response'  ORDER BY `meta_id` ASC LIMIT %d OFFSET %d", $post_id, $limit, $offset );
		$responses = $wpdb->get_results( $my_query );
		$return = array();
		foreach ( $responses as $response ) {
			$answers = unserialize( $response->meta_value );
			$return[] = $answers;
 		}
		return $return;
	}

		/**
	 * populates the meta boxes with individual survey respondents
	 * @param  object $post the wp $post object
	 * @param  array  $args questions and answers
	 */
	public function answers_by_respondent( $post, $args = array() ) {
		$questions = $args['args'][1];
		$answers = reset( $args['args'][0][ $args['args'][2] ] );
		foreach ( $questions as $key => $question ) {
			$response = null;
			$has_options = array( 'dropdown', 'radio', 'checkbox' );
			$label = $question['name'];
			if ( in_array( $question['type'], $has_options ) ) {
				if ( isset( $answers[ $key ] ) && is_array( $answers[ $key ] ) ) {
					$response = '<ul class="answers">' . __( 'Answers', 'awesome-surveys' ) . "\n";
					foreach ( $answers[ $key ] as $answer_key => $answer_value ) {
						$response .= '<li>' . $question['label'][ $answer_value ] . '</li>' . "\n";
					}
					$response .= '</ul>' . "\n";
				} else {
					$response = ( isset( $answers[ $key ] ) && isset( $question['label'][ $answers[ $key ] ] ) ) ? '<span class="answer">' . __( 'Answer', 'awesome-surveys' ) . ': ' . $question['label'][ $answers[ $key ] ] . '</span>' : null;
				}
			} else {
				$response = ( isset( $answers[ $key ] ) && ! empty( $answers[ $key ] ) ) ? '<span class="answer">' . __( 'Answer', 'awesome-surveys' ) . ': ' . $answers[ $key ] . '</span>' : null;
			}
			$response = ( ! is_null( $response ) ) ? $response : '<span class="answer italics">' . __( 'No response given', 'awesome-surveys' ) . '</span>';
			echo '<p><span class="italics">' . __( 'Question', 'awesome-surveys' ). ': ' . $label . '</span><br>' . $response . "</p>\n";
		}
	}

	/**
	 * loads scripts and html for the survey builder
	 */
	public function survey_builder() {
		wp_enqueue_script( 'awesome-surveys-admin-script' );
		wp_enqueue_style( 'awesome-surveys-admin-style' );
		include_once( 'views/html-survey-builder.php' );
	}

	/**
	 * gets the html for the options form
	 */
	public function general_survey_options() {
		include_once( 'views/html-survey-options-general.php' );
	}

	public function survey_results() {
		include_once( 'views/html-survey-results.php' );
	}

	/**
	* adds the closed class to all survey responses postboxes
	* @param  array $classes the array to filter
	* @return array          the filtered array
	*/
	public function postbox_class( $classes ) {
		if ( ! in_array( 'closed', $classes ) ) {
			$classes[] = 'closed';
		}
		return $classes;
	}

		private function get_options() {
			return array(
			'general_options' => array(
				'include_css' => 1,
				),
			'email_options' => array(
				'enable_emails' => 0,
				'enable_respondent_email' => 0,
				'email_subject' => __( 'Thank you for your response', 'awesome-surveys' ),
				'mail_to' => get_option( 'admin_email', '' ),
				'respondent_email_message' => __( 'Thank you for your response to a survey', 'awesome-surveys' ),
				)
			);
	}
}