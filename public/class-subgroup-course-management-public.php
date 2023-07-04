<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://avinash.wisdmlabs.net/
 * @since      1.0.0
 *
 * @package    Subgroup_Course_Management
 * @subpackage Subgroup_Course_Management/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Subgroup_Course_Management
 * @subpackage Subgroup_Course_Management/public
 * @author     Avinash Jha <avinash.jha@wisdmlabs.com>
 */
require_once 'ld-helper.php';

class Subgroup_Course_Management_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	private $helper;

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->helper = new Subgroup_Course_Management_Helper_Function();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/subgroup-course-management-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		// Font Awesome
		wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');
		// Material Icons
		wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons');

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/subgroup-course-management-public.js', array('jquery'), $this->version, false);
	}
	function add_custom_group_registration_tab($tab_headers, $group_id)
	{
		// Add a new tab
		$tab_headers[] = array(
			'title' => __('Manage Users', 'wdm_ld_group'),
			'slug' => 'wdm_manage_users_label',
			'id' => 3,
		);

		return $tab_headers;
	}
	function add_custom_group_registration_tab_content($tab_contents, $group_id)
	{
		// Add content to the custom tab
		$tab_contents[] = array(
			'id' => 3,
			'active' => false,
			'template' => plugin_dir_path(dirname(__FILE__)) . 'templates/course-tab.template.php',
		);

		return $tab_contents;
	}

	public function register_shortcodes()
	{
		add_shortcode('wsdm_subgroup_dashboard', array($this, 'wsdm_subgroup_dashboard_callback'));
	}

	public function wsdm_subgroup_dashboard_callback($atts = [], $content = null)
	{
		$current_user = get_current_user_id();
		$group_id =  $this->helper->get_the_group_id($current_user);

		if (learndash_is_group_leader_user($current_user)) {

			$content .= '<div id="table-top">';
			$content .= '<div>' . $this->helper->selectCourse($group_id) . '</div>';
			$content .= '<div>' . $this->helper->selectEntries($group_id) . '</div>';
			$content .= '</div>';
			$content .=  '<div id="report_container"><div>';
		}

		return $content;
	}

	function ld_fetch_student_report()
	{

		if (isset($_POST['course_id']) && isset($_POST['entries'])) {
			$course_id = $_POST['course_id'];
			$entries = $_POST['entries'];

			echo  $this->helper->get_users_from_course($course_id, $entries);
		} else {
			echo '<p>Invalid course ID or No of Entries.</p>';
		}
		wp_die(); // Terminate the script
	}
	function ld_fetch_student_course_report()
	{

		if (isset($_POST['course_id']) && isset($_POST['user_id'])) {
			$course_id = $_POST['course_id'];
			$user_id = $_POST['user_id'];

			echo   $this->helper->display_course_lessons_and_topics($course_id, $user_id);
		} else {
			wp_send_json_error('Invalid course ID or user ID.');
		}
		wp_die();
	}
	function ld_update_student_course_report()
	{
		if (isset($_POST['course_id']) && isset($_POST['user_id'])) {
			$course_id = $_POST['course_id'];
			$user_id = $_POST['user_id'];
			$checked_values = $_POST['checked_values'];
			$unchecked_values = $_POST['unchecked_values'];
			$entries = $_POST['entries'];
			
			foreach ($unchecked_values as $topic_id) {
				learndash_process_mark_incomplete($user_id, $course_id, $topic_id, false);
				$this->mark_quiz_incomplete($topic_id, $user_id);
			}

			foreach ($checked_values as $topic_id) {
				learndash_process_mark_complete($user_id, $topic_id, false, $course_id);
				$this->mark_quiz_complete($topic_id, $user_id);
			}

			echo  $this->helper->get_users_from_course($course_id, $entries);
		} else {
			wp_send_json_error('Invalid course ID or user ID.');
		}
		wp_die();
	}
	function mark_quiz_complete($quiz_id, $user_id)
	{
		// Get the user meta for quiz progress
		$quiz_progress = get_user_meta($user_id, '_sfwd-quizzes', true);

		if (empty($quiz_progress)) {
			$quiz_progress = [];
		}

		// Check if the quiz is already marked as completed
		$is_completed = false;
		foreach ($quiz_progress as &$quiz_data) {
			if ($quiz_data['quiz'] == $quiz_id) {
				$quiz_data['pass'] = true; // Mark the quiz as completed
				$is_completed = true;
				break;
			}
		}

		// If the quiz is not already marked as completed, add it to the user meta
		if (!$is_completed) {
			$new_quiz_data = [
				'quiz'       => $quiz_id,
				'score'      => 0,
				'count'      => 0,
				'pass'       => true, // Mark the quiz as completed
				'time'       => time(),
				'percentage' => 0,
			];

			$quiz_progress[] = $new_quiz_data;
		}

		// Update the user meta with the updated quiz progress
		update_user_meta($user_id, '_sfwd-quizzes', $quiz_progress);
	}
	function mark_quiz_incomplete($quiz_id, $user_id)
	{
		// Get the user meta for quiz progress
		$quiz_progress = get_user_meta($user_id, '_sfwd-quizzes', true);

		// Check if the quiz is marked as completed and update the pass status
		foreach ($quiz_progress as &$quiz_data) {
			if ($quiz_data['quiz'] == $quiz_id) {
				$quiz_data['pass'] = false; // Mark the quiz as uncompleted
				break;
			}
		}

		// Update the user meta with the updated quiz progress
		update_user_meta($user_id, '_sfwd-quizzes', $quiz_progress);
	}
}
