<?php
/*
Plugin Name: Awesome Surveys
Plugin URI: http://www.willthewebmechanic.com/awesome-surveys
Description: Easily create surveys for your WordPress website and publish them with a simple shortcode
Version: 1.6.3
Author: Will Brubaker
Author URI: http://www.willthewebmechanic.com
License: GPLv3.0
Text Domain: awesome-surveys
Domain Path: /languages/
*/

/**
	* This program is free software: you can redistribute it and/or modify
	*  it under the terms of the GNU General Public License as published by
	*  the Free Software Foundation, either version 3 of the License, or
	*  (at your option) any later version.
	*
	*  This program is distributed in the hope that it will be useful,
	*  but WITHOUT ANY WARRANTY; without even the implied warranty of
	*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	*  GNU General Public License for more details.
	*
	*  You should have received a copy of the GNU General Public License
	*  along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*
	*  Other software packaged with this plugin is subject to other licenses:
	*
	*  pure css (forms.css) is licensed under the Yahoo! BSD License. See css/purecss-license.txt
	*  The jQuery validate plugin is licensed under the MIT license. See js/jquery-validate-license.txt
	*  normalize.css is licensed under the MIT license see css/normalize-license.txt
	*  the PHP Form Builder class is licensed under the GPL v3. See LICENSE
	*
	*/

/**
	* @package Awesome_Surveys
	*
	*/

	$admin_includes = array(
		'awesome-surveys-admin'
		);
	$frontend_includes = array(
		'awesome-surveys-frontend',
		);
	$includes = array(
		'awesome-surveys',
		'awesome-surveys-ajax-actions',
		);

		foreach ( $includes as $include_file ) {
			include_once( plugin_dir_path( __FILE__ ) . 'includes/class-' . $include_file . '.php' );
		}

		if ( is_admin() ) {
		foreach ( $admin_includes as $include_file ) {
			include_once( plugin_dir_path( __FILE__ ) . 'includes/class-' . $include_file . '.php' );
		}
	} else {
		foreach ( $frontend_includes as $include_file ) {
			include_once( plugin_dir_path( __FILE__ ) . 'includes/class-' . $include_file . '.php' );
		}
	}

		if ( ! defined( 'WWM_AWESOME_SURVEYS_URL' ) ) {
			define( 'WWM_AWESOME_SURVEYS_URL', plugins_url( '', __FILE__ ) );
		}
		if ( ! defined( 'WWM_AWESOME_SURVEYS_PATH' ) ) {
			define( 'WWM_AWESOME_SURVEYS_PATH', plugin_dir_path( __FILE__ ) );
		}

		$awesome_surveys_ajax = new Awesome_Surveys_Ajax;
		$awesome_surveys_nopriv_ajax_actions = array(

			);
		$awesome_surveys_ajax_actions = array(
			'add-form-element' => 'add_form_element',
			);

		foreach ( $awesome_surveys_nopriv_ajax_actions as $action => $function ) {
			add_action( 'wp_ajax_nopriv_' . $action, array( $awesome_surveys_ajax, $function ) );
			add_action( 'wp_ajax_' . $action, array( $awesome_surveys_ajax, $function ) );
		}
		foreach ( $awesome_surveys_ajax_actions as $action => $function ) {
			add_action( 'wp_ajax_' . $action, array( $awesome_surveys_ajax, $function ) );
		}
