<?php

class Awesome_Surveys_Admin extends Awesome_Surveys {

	protected $page_hook, $page_title, $menu_title, $menu_slug;
	public function __construct() {
		parent::__construct();
		$this->page_title = __( 'Awesome Surveys Options', 'awesome-surveys' );
		$this->menu_title = __( 'Survey Options', 'awesome-surveys' );
		$this->menu_slug = 'awesome-surveys.php';
		$this->menu_link_text = __( 'Awesome Surveys', 'awesome-surveys' );
		$this->text_domain = 'awesome-surveys';
		$actions = array(
			'admin_menu' => array( 'admin_menu', 10, 0 ),
			'save_post' => array( 'save_post', 10, 2 ),
			'admin_enqueue_scripts' => array( 'admin_enqueue_scripts', 10, 0 ),
			'admin_init' => array( 'init', 10, 0 ),
			);

		foreach ( $actions as $action => $args ) {
			add_action( $action, array( $this, $args[0] ), $args[1], $args[2] );
		}

		$filters = array(
			//debugi dont think so...'survey_auth_options' => array( 'default_auth_methods', 10, 1 ),
			'post_row_actions' => array( 'post_row_actions', 10, 2 ),
			);

		foreach ( $actions as $key => $action ) {
			add_action( $key, array( $this, $action[0] ), $action[1], $action[2] );
		}
		foreach ( $filters as $key => $filter ) {
			add_filter( $key, array( $this, $filter[0] ), $filter[1], $filter[2] );
		}
	}

	public function save_post( $post_id, $post ) {

		if (  ! isset( $_POST['create_survey_nonce'] ) || ! wp_verify_nonce( $_POST['create_survey_nonce'], 'create-survey' ) ) {
			return;
		}
		if ( isset( $_POST['existing_elements'] ) ) {
			$existing_elements = $_POST['existing_elements'];
			update_post_meta( $post_id, 'existing_elements', $existing_elements );
		}
		if ( isset( $_POST['meta']['survey_auth_method'] ) ) {
			update_post_meta( $post_id, 'survey_auth_method', absint( $_POST['meta']['survey_auth_method'] ) );
		}
	}

	/**
		* hooked into 'survey_auth_options' - provides the default array of authentication methods
		* @param  array  $options associative array of authentication method names
		* @return array  associative array of authentication method names
		*/
	/*debugpublic function default_auth_methods( $options = array() ) {

		$options = array( 'login' => __( 'User must be logged in', $this->text_domain ), 'cookie' => __( 'Cookie based', $this->text_domain ), 'none' => __( 'None' ) );
		return $options;
	}*/

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
		wp_register_style( 'jquery-ui-lightness', WWM_AWESOME_SURVEYS_URL . '/css/jquery-ui.min.css', array( 'wp-admin' ), '1.10.13', 'all' );
		wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css', array( 'normalize-css' ) );

		wp_register_script( $this->text_domain . '-options-script', WWM_AWESOME_SURVEYS_URL . '/js/options' . $suffix . '.js', array( 'jquery', 'jquery-ui-accordion', 'postbox' ), $this->plugin_version, true );
		wp_register_script( $this->text_domain . '-view-results', WWM_AWESOME_SURVEYS_URL . '/js/results' . $suffix . '.js', array( 'jquery', 'postbox', 'jquery-ui-accordion' ), $this->plugin_version, true );
		wp_register_style( $this->text_domain . '-options-style', WWM_AWESOME_SURVEYS_URL . '/css/options' . $suffix . '.css', array( 'pure-forms-css' ), $this->plugin_version, 'all' );
		wp_register_style( $this->text_domain . '-results-style', WWM_AWESOME_SURVEYS_URL . '/css/results' . $suffix . '.css', array( 'pure-forms-css' ), $this->plugin_version, 'all' );
		$screen = get_current_screen();
		if ( 'awesome-surveys' === $screen->id ) {
			wp_enqueue_script( $this->text_domain . '-admin-script', WWM_AWESOME_SURVEYS_URL . '/js/admin-script' . $suffix . '.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-validation-plugin', 'jquery-ui-dialog', 'jquery-ui-button', 'postbox' ), $this->plugin_version, true );
			wp_localize_script( $this->text_domain . '-admin-script', 'wwm_as_admin_script', $args );
			wp_enqueue_style( $this->text_domain . '-admin-style', WWM_AWESOME_SURVEYS_URL . '/css/admin-style' . $suffix . '.css', array( 'jquery-ui-lightness', 'pure-forms-css' ), $this->plugin_version, 'all' );
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
		$plugin_panel_version = 1;
		add_filter( 'wwm_plugin_links', array( &$this, 'this_plugin_link' ) );
		if ( empty( $_wwm_plugins_page ) || ( is_array( $_wwm_plugins_page ) && $plugin_panel_version > $_wwm_plugins_page[1] ) ) {
			$_wwm_plugins_page[0] = add_menu_page( 'WtWM Plugins', 'WtWM Plugins', 'manage_options', 'wwm_plugins', array( &$this, 'wwm_plugin_links' ), WWM_AWESOME_SURVEYS_URL . '/images/wwm_wp_menu.png', '60.9' );
			$_wwm_plugins_page[1] = $plugin_panel_version;
		}
		$this->page_hook = add_submenu_page( 'wwm_plugins', $this->page_title, $this->menu_title, 'manage_options', $this->menu_slug, array( &$this, 'plugin_options' ) );
		add_submenu_page( 'wwm_plugins', '', __( 'My Surveys', 'awesome-surveys' ), 'manage_options', 'edit.php?post_type=awesome-surveys' );
		add_submenu_page( 'wwm_plugins', '', __( 'New Survey', 'awesome-surveys' ), 'manage_options', 'post-new.php?post_type=awesome-surveys' );
		add_action( 'admin_print_scripts-' . $this->page_hook, array( &$this, 'admin_print_scripts' ) );
		add_action( 'admin_print_styles-' . $this->page_hook, array( &$this, 'admin_print_styles' ) );
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

	public function plugin_options() {
		include_once( WWM_AWESOME_SURVEYS_PATH . '/options.php' );
		add_meta_box( 'awesome-surveys-options', __( 'Awesome Surveys Options', 'awesome-surveys' ), array( $this, 'surveys_options' ), $this->page_hook, 'normal', 'core' );
		add_meta_box( 'awesome-surveys-email-options', __( 'Email Options', 'awesome-surveys' ), array( $this, 'email_options' ), $this->page_hook, 'normal', 'core' );
		if ( get_option( 'wwm_awesome_surveys' ) && ( 0 == 0 /*debug - do the database version check too*/ ) ) {
			add_meta_box( 'awesome-surveys-database-upgrade', __( 'Database Upgrade', 'awesome-surveys' ), array( $this, 'database_upgrade' ), $this->page_hook, 'normal', 'core' );
		}
		echo '<div id="poststuff" class="wrap">';
		echo '<form action="' . $_SERVER['REQUEST_URI'] . '" id="surveys-options" method="post" class="form-horizontal">';
		do_meta_boxes( $this->page_hook, 'normal', $this );
		echo '</form>';
		echo '</div>';
	}

	public function surveys_options() {
		include_once( 'views/html-surveys-options.php' );
	}

	public function email_options() {
		include_once( 'views/html-surveys-options-emails.php' );
	}

	public function database_upgrade() {
		include_once( 'wwmas-database-upgrade-functions.php' );
		include_once( 'views/html-database-upgrade.php' );
	}

	public function post_row_actions( $actions, $post ) {
		if ( 'awesome-surveys' === $post->post_type ) {
			$edit_post_link = get_edit_post_link( $post->ID, true );
			$actions['results'] = '<a href="' . $edit_post_link . '&amp;view=results' . '" title="' . __( 'View Survey Results', 'awesome-surveys' ) . '">' . __( 'Results', 'awesome-surveys' ) . '</a>';
		}
		return $actions;
	}
}