<?php
namespace AcademyProCoursePrerequisites;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Frontend {
	public static function init() {
		$self = new self();
		add_filter( 'academy/templates/single_course/enroll_form', array( $self, 'prerequisites_info' ), 10, 2 );
	}
	public function prerequisites_info( $html, $course_id ) {
		$prerequisite_type = get_post_meta( $course_id, 'academy_prerequisite_type', true );
		$required_courses = [];
		$user_id = get_current_user_id();

		if ( 'category' === $prerequisite_type ) {
			$prerequisite_category = get_post_meta( $course_id, 'academy_prerequisite_categories', true );
			if ( ! is_array( $prerequisite_category ) || ! count( $prerequisite_category ) ) {
				return $html;
			}
			$term_ids = wp_list_pluck( $prerequisite_category, 'value' );
			$course_ids = get_posts(array(
				'post_type' => 'academy_courses',
				'tax_query' => array(
					array(
						'taxonomy' => 'academy_courses_category', // Replace with your custom taxonomy slug
						'field' => 'term_id',
						'terms' => $term_ids,
					),
				),
				'fields' => 'ids', // Retrieve only post IDs
				'posts_per_page' => -1, // Set this to the number of posts you want to retrieve. -1 means all posts.
			));
			foreach ( $course_ids as $course_id ) {
				if ( ! \Academy\Helper::is_completed_course( $course_id, $user_id ) ) {
					$required_courses[] = $course_id;
				}
			}
		} else {
			$course_prerequisites = get_post_meta( $course_id, 'academy_prerequisite_courses', true );
			if ( ! is_array( $course_prerequisites ) || ! count( $course_prerequisites ) ) {
				return $html;
			}
			foreach ( $course_prerequisites as $course_prerequisite ) {
				$course_id = (int) $course_prerequisite['value'];
				if ( ! \Academy\Helper::is_completed_course( $course_id, $user_id ) ) {
					$required_courses[] = $course_id;
				}
			}
		}//end if

		if ( ! count( $required_courses ) ) {
			return $html;
		}

		ob_start();
		\AcademyPro\Helper::get_template('course-prerequisites/prerequisites.php', array(
			'required_courses' => $required_courses,
		) );
		return ob_get_clean();
	}
}
