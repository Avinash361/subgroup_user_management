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
	public function register_shortcodes()
	{
		add_shortcode('wsdm_subgroup_dashboard', array($this, 'wsdm_subgroup_dashboard_callback'));
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


	function ld_fetch_student_report_callback()
	{
		if (isset($_POST['course_id'])) {
			$course_id = $_POST['course_id'];
			$entries = $_POST['entries'];
			echo $this->get_users_from_course($course_id, $entries);
		} else {
			echo '<p>Invalid course ID.</p>';
		}
		wp_die(); // Terminate the script
	}
	function ld_fetch_student_course_report_callback()
	{
		if (isset($_POST['course_id']) && isset($_POST['user_id'])) {
			$course_id = $_POST['course_id'];
			$user_id = $_POST['user_id'];
			echo  $this->display_course_lessons_and_topics($course_id, $user_id);
		} else {
			echo '<p>Invalid course ID.</p>';
		}
		wp_die();
	}
	function ld_update_student_course_report_callback()
	{
		if (isset($_POST['course_id']) && isset($_POST['user_id'])) {
			$course_id = $_POST['course_id'];
			$user_id = $_POST['user_id'];
			$checked_values = $_POST['checked_values'];
			$all_lessons = learndash_get_course_lessons_list($course_id, $user_id,  $query_args = array());

			foreach ($all_lessons as $lesson) {
				$all_topics = learndash_get_topic_list($lesson['id'],  $course_id);
				foreach ($all_topics as $topic) {
					learndash_process_mark_incomplete($user_id, $course_id, $topic->ID, false);
				}
			}

			foreach ($checked_values as $topic_id) {
				learndash_process_mark_complete($user_id, $topic_id, false, $course_id);
			}
			echo $this->get_users_from_course($course_id);
		} else {
			echo '<p>Invalid course ID.</p>';
		}
		wp_die();
	}
	function learndash_mark_quiz_complete($user_id, $quiz_id)
	{
		// Check if LearnDash is active and available
		if (class_exists('SFWD_LMS')) {
			// Mark the quiz as complete
			$result = learndash_process_mark_complete($user_id, $quiz_id);

			if ($result) {
				return true;
			}
		}

		return false;
	}
	function get_users_from_course($course_id, $entries = 3)
	{
		$current_user = get_current_user_id();
		$group_id = $this->get_the_group_id($current_user);
		$users = learndash_get_groups_users($group_id);
		$content = '<table>
		<thead>
		<tr role="row">
		<th class="details-control sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 10%;"></th>
		<th class="dt-body-left name sorting_disabled" rowspan="1" colspan="1" aria-label="Name">Name</th>
		<th class="dt-body-left email sorting_disabled" rowspan="1" colspan="1" aria-label="Email ID">Email ID</th>
		<th class="dt-body-center dt-head-left course-progress sorting_disabled" rowspan="1" colspan="1" aria-label="Course Progress">Course Progress</th>
		<th class="dt-body-left action sorting_disabled" rowspan="1" colspan="1" aria-label="action">Action</th>
		</tr>
		</thead>
		<tbody>';
		$count = 0;
		foreach ($users as $user) {

			if ($count >= $entries) {
				break;
			}
			$count++;

			$group_user_ids = learndash_get_groups_user_ids($group_id);
			$total_steps     = learndash_course_get_steps_count($course_id);
			$completed_steps = learndash_course_get_completed_steps($user->ID, $course_id);


			if (!empty($group_user_ids)) {
				$content .= '<tr>
				<td class="details-control" data-title="" >
					<span class="dashicons dashicons-arrow-right-alt2 course-dashicons" data-user-id="' . $user->ID . '" data-course-id="' . $course_id . '"></span>
				</td>
							<td>' . $user->user_login . '</td>
							<td>' . $user->user_email . '</td>
							<td> ' . $completed_steps . ' / ' . $total_steps . ' steps completed</td>
							<td><button name="view_course" value="" class="view-student-course"  data-user-id="' . $user->ID . '" data-course-id="' . $course_id . '">View Course</button></td>
							</tr>
							<tr>
							<td colspan="5">
								<div class="flip wdm_course_report">
									
								</div>
							</td>
							</tr>';
			}
		}
		$content .= '</tbody></table>';
		return $content;
	}



	public function wsdm_subgroup_dashboard_callback($atts = [], $content = null)
	{
		$current_user = get_current_user_id();
		$group_id = $this->get_the_group_id($current_user);
		$users = learndash_get_groups_users($group_id);

		if (learndash_is_group_leader_user($current_user)) {
			$content.='<div id="table-top">';
			$content .= '<div>'.$this->selectCourse($group_id).'</div>';
			$content .= '<div>'.$this->selectEntries($group_id).'</div>';
			$content.='</div>';
			$content .=  '<div id="report_container">
		<table>
		<thead>
		<tr role="row">
		<th class="details-control sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 10%;"></th>
		<th class="dt-body-left name sorting_disabled" rowspan="1" colspan="1" aria-label="Name">Name</th>
		<th class="dt-body-left email sorting_disabled" rowspan="1" colspan="1" aria-label="Email ID">Email ID</th>
		<th class="dt-body-center dt-head-left course-progress sorting_disabled" rowspan="1" colspan="1" aria-label="Course Progress">Course Progress</th>
		<th class="dt-body-left action sorting_disabled" rowspan="1" colspan="1" aria-label="action">Action</th>
		</tr>
		</thead>
		</table>
			<div>';
		}

		return $content;
	}
	private function selectCourse($group_id)
	{
		$group_courses = learndash_group_enrolled_courses($group_id);
		$group_courses = apply_filters('ldgr_filter_group_course_list', $group_courses, $group_id);
		$html = '<select id="ld_course_id" name="ld_course_id">';
		foreach ($group_courses as $group_course) {
			$demo_title   = get_post($group_course);
			$course_title = $demo_title->post_title;
			$html .= '<option value="' . esc_html($group_course) . '" title="' . esc_attr($course_title) . '">' . esc_html(mb_strimwidth(esc_html($course_title), 0, 20, '...')) . '</option>';
		}
		$html .= '</select>
		
		<input
			type="button"
			id="show_report_button"
			class="ldgr-bg-color"
			name="show_report_button"
			value="' .
			esc_html(
				apply_filters(
					'wdm_ldgr_show_report_button_label',
					__('Show Report', 'wdm_ld_group')
				)
			) .
			'" />';

		return $html;
	}
	private function selectEntries($group_id)
	{
		$entries = array(1, 2, 4);
		$html = 'Select <select id="entries" name="entries">';
		foreach ($entries as $item) {
			$html .= '<option value="' . esc_attr($item)  . '">' . esc_html($item)  . '</option>';
		}
		$html .= '</select> Entries';

		return $html;
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

			foreach ($lessons_list as $lesson) {
				$content .= '<div class="lesson-container">';
				$content .= $this->display_lesson_and_topics($lesson, $user_id, $course_id);
				$content .= '</div>';
			}
		}
		$content .= $this->display_quizes($course_id, $user_id);
		$content .= '<div><button name="update_progression" value="" class="student-course-update"data-user-id="' . $user_id . '" data-course-id="' . $course_id . '" >Update</button></div>';
		return $content;
	}

	private function display_lesson_and_topics($lesson, $user_id, $course_id)
	{
	
		$content ='<i class=" list-arrow fas fa-angle-double-right"></i>';
		$checkbox_html = '<input type="checkbox" name="lesson_checkbox[]" value="' . $lesson['id'] . '" class="lesson-checkbox"';
		if (learndash_is_lesson_complete($user_id, $lesson['id'], $course_id)) {
			$checkbox_html .= ' checked';
		}
		$checkbox_html .= '>';
		$content .= $checkbox_html . $lesson['post']->post_title . '<br>';

		$topic_list = learndash_get_topic_list($lesson['id']);
		if ($topic_list) {
			$content .= '<div class="topic-container">';
			foreach ($topic_list as $topic) {
				$content .= $this->display_topic($topic, $user_id, $course_id);
			}
			$content .= '</div>';
		}

		return $content;
	}

	private function display_topic($topic, $user_id, $course_id)
	{
		$content = '';

		$content .= "&nbsp&nbsp";
		$checkbox_html = '<input type="checkbox" name="lesson_checkbox[]" value="' . $topic->ID . '" data-user-id="' . $user_id . '" class="topic-checkbox"';
		if (learndash_is_topic_complete($user_id, $topic->ID, $course_id)) {
			$checkbox_html .= ' checked';
		}
		$checkbox_html .= '>';
		$content .= $checkbox_html . $topic->post_title . '<br>';


		return $content;
	}
	private function display_quizes($course_id, $user_id)
	{
		$content = '';

		$quiz_list = learndash_get_course_quiz_list($course_id,   $user_id);
		if ($quiz_list) {
			$content .= '<div class="quiz-container">';
			foreach ($quiz_list as $quiz) {
				$content .= $this->display_quiz($quiz, $user_id, $course_id);
			}
			$content .= '</div>';
		}
		return $content;
	}

	private function display_quiz($quiz, $user_id, $course_id)
	{
		$content = '';

		$content .= "&nbsp&nbsp&nbsp";
		$checkbox_html = '<input type="checkbox" name="lesson_checkbox[]" value="' . $quiz['post']->ID . '" data-user-id="' . $user_id . '" class="lesson-checkbox"';
		if (learndash_is_quiz_complete($user_id, $quiz['post']->ID, $course_id)) {
			$checkbox_html .= ' checked';
		}
		$checkbox_html .= '>';
		$content .= $checkbox_html . $quiz['post']->post_title . '  <br>';


		return $content;
	}
}
