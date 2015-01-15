<?php

class Awesome_Surveys_Admin extends Awesome_Surveys {

	protected $page_hook, $page_title, $menu_title, $menu_slug;

	public function __construct() {

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
			'survey_auth_options' => array( 'default_auth_methods', 10, 1 ),
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
	public function default_auth_methods( $options = array() ) {

		$options = array( 'login' => __( 'User must be logged in', $this->text_domain ), 'cookie' => __( 'Cookie based', $this->text_domain ), 'none' => __( 'None' ) );
		return $options;
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
		wp_register_style( 'jquery-ui-lightness', WWM_AWESOME_SURVEYS_URL . '/css/jquery-ui.min.css', array(), '1.10.13', 'all' );
		wp_register_style( 'pure-forms-css', WWM_AWESOME_SURVEYS_URL . '/css/forms.min.css', array( 'normalize-css' ) );

		$screen = get_current_screen();
		if ( 'awesome-surveys' === $screen->id ) {
			wp_enqueue_script( $this->text_domain . '-admin-script', WWM_AWESOME_SURVEYS_URL . '/js/admin-script' . $suffix . '.js', array( 'jquery', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-sortable', 'jquery-ui-accordion', 'jquery-validation-plugin', 'jquery-ui-dialog', 'jquery-ui-button', 'postbox' ), $this->wwm_plugin_values['version'], true );
			wp_localize_script( $this->text_domain . '-admin-script', 'wwm_as_admin_script', $args );
			wp_enqueue_style( $this->text_domain . '-admin-style', WWM_AWESOME_SURVEYS_URL . '/css/admin-style' . $suffix . '.css', array( 'jquery-ui-lightness', 'pure-forms-css' ), $this->wwm_plugin_values['version'], 'all' );
		}
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
}