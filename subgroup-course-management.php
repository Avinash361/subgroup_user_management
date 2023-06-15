<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://avinash.wisdmlabs.net/
 * @since             1.0.0
 * @package           Subgroup_Course_Management
 *
 * @wordpress-plugin
 * Plugin Name:       learndash-subgroup-course-management
 * Plugin URI:        https://https://avinash.wisdmlabs.net/
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Avinash Jha
 * Author URI:        https://https://avinash.wisdmlabs.net/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       subgroup-course-management
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SUBGROUP_COURSE_MANAGEMENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-subgroup-course-management-activator.php
 */
function activate_subgroup_course_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subgroup-course-management-activator.php';
	Subgroup_Course_Management_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-subgroup-course-management-deactivator.php
 */
function deactivate_subgroup_course_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-subgroup-course-management-deactivator.php';
	Subgroup_Course_Management_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_subgroup_course_management' );
register_deactivation_hook( __FILE__, 'deactivate_subgroup_course_management' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-subgroup-course-management.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_subgroup_course_management() {

	$plugin = new Subgroup_Course_Management();
	$plugin->run();

}
run_subgroup_course_management();
