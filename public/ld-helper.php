<?php 
class Subgroup_Course_Management_Helper_Function{
	
    function get_users_from_course($course_id, $entries = 4)
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
    function get_the_group_id($cuurent_user)
	{
		// Code to generate shortcode output...
		$group_ids = learndash_get_administrators_group_ids($cuurent_user);
		foreach ($group_ids as $id) {
			return $id;
		}
	}
	 function selectCourse($group_id)
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
     function selectEntries($group_id)
	{
		$users = learndash_get_groups_users($group_id);
		$total_entries = count($users);
		
		$entries = array("10"=>10, "25"=>25, "50"=>50,"All"=>$total_entries);;
		$html = 'Select <select id="entries" name="entries">';
		foreach ($entries as $key => $value) {
			$html .= '<option value="' . esc_attr($value)  . '">' . esc_html($key)  . '</option>';
		}
		$html .= '</select> Entries';

		return $html;
	}	

     function display_course_lessons_and_topics($course_id, $user_id)
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
	 function display_lesson_and_topics($lesson, $user_id, $course_id)
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
     function display_topic($topic, $user_id, $course_id)
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
     function display_quizes($course_id, $user_id)
	{
		$content = '';
		
		$quiz_list = learndash_get_course_quiz_list($course_id,   $user_id);
		if ($quiz_list) {
			$content .= '<div class="quiz-container">';
			foreach ($quiz_list as $quiz) {
				$content .=$this->display_quiz($quiz, $user_id, $course_id);
			}
			$content .= '</div>';
		}
		return $content;
	}
     function display_quiz($quiz, $user_id, $course_id)
	{
		$content = '';
	
		$content .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		
		$checkbox_html = sprintf(
			'<input type="checkbox" name="lesson_checkbox[]" value="%s" data-user-id="%s" class="lesson-checkbox"%s>',
			esc_attr($quiz['post']->ID),
			esc_attr($user_id),
			learndash_is_quiz_complete($user_id, $quiz['post']->ID, $course_id) ? ' checked' : ''
		);
	
		$content .= $checkbox_html . esc_html($quiz['post']->post_title) . '  <br>';
	
		return $content;
	}
    
}