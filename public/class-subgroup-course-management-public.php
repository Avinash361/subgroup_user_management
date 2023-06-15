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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subgroup_Course_Management_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subgroup_Course_Management_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/subgroup-course-management-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subgroup_Course_Management_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subgroup_Course_Management_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/subgroup-course-management-public.js', array('jquery'), $this->version, false);
	}

	public function register_shortcodes()
	{
		add_shortcode('wsdm_subgroup_dashboard', array($this, 'wsdm_subgroup_dashboard_callback'));
	}
	public function wsdm_subgroup_dashboard_callback($atts = [], $content = null)
	{
		$current_user = get_current_user_id();
		$group_id = $this->get_the_group_id($current_user);
		$users = learndash_get_groups_users($group_id);

		if (learndash_is_group_leader_user($current_user)) {
			foreach ($users as $user) {
				$group_user_ids = learndash_get_groups_user_ids($group_id);
				if (!empty($group_user_ids) && in_array($user->ID, $group_user_ids, true)) {
					$atts = array(
						'user_id' => $user->ID,
						'group_id' => $group_id,
					);
					$atts = apply_filters('learndash_group_administration_course_info_atts', $atts, get_user_by('id', $user->ID));

					$content .= '<h3>' . $user->user_login . '</h3>';
					$courses = learndash_user_get_enrolled_courses($user->ID, $course_query_args = array(), false);

					foreach ($courses as $course_id) {
						$content .= $this->display_course_lessons_and_topics($course_id, $user->ID);
					}
					// Handle progression updates
					$this->handle_update_progression($user->ID, $course_id);
					$content .= '<br>';
				}
			}
		}

		return $content;
	}
	private function get_the_group_id($cuurent_user)
	{
		// Code to generate shortcode output...
		$group_ids = learndash_get_administrators_group_ids($cuurent_user);
		foreach ($group_ids as $id) {
			return $id;
		}
	}
	private function display_course_lessons_and_topics($course_id, $user_id)
	{
		$content = '';

		$lessons_args = array(
			'order' => 'ASC', // Sort the lessons in ascending order
			'posts_per_page' => -1, // Retrieve all lessons
		);

		// Retrieve the lessons for the course
		$lessons_list = learndash_get_course_lessons_list($course_id, $user_id, $lessons_args);

		// Loop through the lessons and display their titles
		if ($lessons_list) {
			$content .= '<form method="post">';
			foreach ($lessons_list as $lesson) {
				$content .= $this->display_lesson_and_topics($lesson, $user_id, $course_id);
			}
			$content .= '<button type="submit" name="update_progression" value="' . $user_id . '">Update</button>';
			$content .= '</form>';
		}

		return $content;
	}

	private function display_lesson_and_topics($lesson, $user_id, $course_id)
	{
		$content = '';

		$content .= '<span class="list-arrow"></span>';
		$checkbox_html = '<input type="checkbox" name="lesson_checkbox[]" value="' . $lesson['id'] . '"';
		if (learndash_is_lesson_complete($user_id, $lesson['id'], $course_id)) {
			$checkbox_html .= ' checked';
		}
		$checkbox_html .= '>';
		$content .= $checkbox_html . $lesson['post']->post_title . '<br>';

		$topic_list = learndash_get_topic_list($lesson['id']);
		if ($topic_list) {
			foreach ($topic_list as $topic) {
				$content .= $this->display_topic($topic, $user_id, $course_id);
			}
		}

		return $content;
	}

	private function display_topic($topic, $user_id, $course_id)
	{
		$content = '';

		$content .= "&nbsp&nbsp";
		$checkbox_html = '<input type="checkbox" name="lesson_checkbox[]" value="' . $topic->ID . '"';
		if (learndash_is_topic_complete($user_id, $topic->ID, $course_id)) {
			$checkbox_html .= ' checked';
		}
		$checkbox_html .= '>';
		$content .= $checkbox_html . $topic->post_title . '<br>';

		return $content;
	}

	public function handle_update_progression($user_id, $course_id)
	{
		
		if (isset($_POST['update_progression'])) {
			$selected_topics = isset($_POST['lesson_checkbox']) ? $_POST['lesson_checkbox'] : array();
		
			foreach ($selected_topics as $topic_id) {
				learndash_process_mark_complete($_POST['update_progression'], $topic_id, false, $course_id);
			}
		}
	}
}
