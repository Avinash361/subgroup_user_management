(function ($) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	//Get Course id


	jQuery(document).ready(function ($) {
		let courseID = $('#ld_course_id').val();
		let entries = $('#entries').val();
		fetchStudentReport(courseID, entries);



		$('#entries').on('change', function () {
			let courseID = $('#ld_course_id').val();
			let entries = $('#entries').val();

			fetchStudentReport(courseID, entries);
		})
		//Save custom fields value from frontend
		$('#show_report_button').on('click', function () {
			var courseID = $('#ld_course_id').val();
			let entries = $('#entries').val();

			if (courseID) {
				fetchStudentReport(courseID, entries);
			} else {
				alert('Please select a course.');
			}
		});

		function fetchStudentReport(courseID, entries) {
			$.ajax({
				url: '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: {
					action: 'ld_fetch_student_report',
					course_id: courseID,
					entries: entries
				},
				beforeSend: function () {
					// Display a loading spinner or message
					$('#report_container').html('<center><div class="d1"></div><div class="d2"></div><div class="d3"></div><div class="d4"></div><div class="d5"></div> </center > ');

				},
				success: function (response) {
					// Update the report container with the fetched data
					// alert(response);
					$('#report_container').html(response);
				},
				error: function (xhr, status, error) {
					console.log(error);
				}
			});
		}
		$(document).on('click', '.view-student-course', function () {
			var userId = $(this).data('user-id');
			var courseId = $(this).data('course-id');
			var reportContainer = $(this).closest('tr').next().find('.wdm_course_report');

			var dashicon = $(this).closest('tr').find('.dashicons');

			// Change the direction of the dashicon to down
			dashicon.removeClass('dashicons-arrow-right-alt2');
			dashicon.addClass('dashicons-arrow-down-alt2');

			if (userId && courseId) {
				fetchStudentCourseReport(reportContainer, userId, courseId);
			} else {
				alert('Please select a course.');
			}
		});


		function fetchStudentCourseReport(reportContainer, userId, courseId) {
			// Perform actions when dashicon is clicked
			$.ajax({
				url: '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: {
					action: 'ld_fetch_student_course_report',
					course_id: courseId,
					user_id: userId
				},
				beforeSend: function () {
					// Display a loading spinner or message
					reportContainer.html('<center><div class="d1"></div><div class="d2"></div><div class="d3"></div><div class="d4"></div><div class="d5"></div> </center > ');
				},
				success: function (response) {
					// Update the report container with the fetched data

					reportContainer.html(response);
				},
				error: function (xhr, status, error) {
					console.log(error);
				}
			});
		}


		$(document).on('click', '.student-course-update', function () {
			var userId = $(this).data('user-id');
			var courseId = $(this).data('course-id');
			let entries = $('#entries').val();
			var reportContainer = $(this).closest('tr').find('.wdm_course_report');

			// Retrieve all checked checkboxes
			var checkedCheckboxes = $('input[name="lesson_checkbox[]"]:checked');
			var uncheckedCheckboxes = $('input[name="lesson_checkbox[]"]').not(':checked');


			// Create an array to store the values of checked checkboxes
			var checkedValues = [];
			var uncheckedValues = [];

			// Iterate over each checked checkbox and add its value to the array
			checkedCheckboxes.each(function () {
				var user_id = $(this).data('user-id');

				if (userId == user_id) {
					checkedValues.push($(this).val());
				}
			});
			uncheckedCheckboxes.each(function () {
				var user_id = $(this).data('user-id');

				if (userId == user_id) {
					uncheckedValues.push($(this).val());
				}
			})

			if (userId && courseId) {
				updateStudentCourseReport(reportContainer, userId, courseId, checkedValues,uncheckedValues, entries);
			} else {
				alert('Please select a course.');
			}
		});
		function updateStudentCourseReport(reportContainer, userId, courseId, checkedValues, uncheckedValues, entries) {
			// Perform actions when dashicon is clicked
			$.ajax({
				url: '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: {
					action: 'ld_update_student_course_report',
					course_id: courseId,
					user_id: userId,
					checked_values: checkedValues,
					unchecked_values: uncheckedValues,
					entries: entries
				},
				beforeSend: function () {
					// Display a loading spinner or message
					reportContainer.html('<center><div class="d1"></div><div class="d2"></div><div class="d3"></div><div class="d4"></div><div class="d5"></div> </center > ');
				},
				success: function (response) {
					// Update the report container with the fetched data
					$('#report_container').html(response);
					// reportContainer.html(response);
				},
				error: function (xhr, status, error) {
					console.log(error);
				}
			});
		}

	});

	$(document).on('click', '.dashicons', function () {

		var reportContainer = $(this).closest('tr').next().find('.wdm_course_report');
		$(this).removeClass('dashicons-arrow-down-alt2');
		$(this).addClass('dashicons-arrow-right-alt2');

		reportContainer.html('');
	});

	$(document).on('change', '.lesson-checkbox', function () {
		// Get the checkbox element and its value
		var checkbox = $(this);

		// Find the topics container for the corresponding lesson
		var topicContainer = checkbox.closest('.lesson-container').find('.topic-container');

		// Check or uncheck all the topic checkboxes within the container based on the lesson checkbox state
		topicContainer.find('.topic-checkbox').prop('checked', checkbox.prop('checked'));
	});
	$(document).on('click', '.list-arrow', function () {
		$(this).siblings('.topic-container').toggle();
		$(this).toggleClass('fa-angle-double-right fa-angle-double-down');
	});



})(jQuery);



