<?php
namespace AcademyProAdvancedAnalytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_advanced_analytics/get_analytics_for_earnings', array( $self, 'get_analytics_for_earnings' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/get_analytics_for_refunds', array( $self, 'get_analytics_for_refunds' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/get_analytics_for_discounts', array( $self, 'get_analytics_for_discounts' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/get_popular_courses', array( $self, 'get_popular_courses' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/recent_enrolled_courses', array( $self, 'recent_enrolled_courses' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/recent_reviews', array( $self, 'recent_reviews' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/recent_students', array( $self, 'recent_students' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/recent_instructors', array( $self, 'recent_instructors' ) );

		add_action( 'wp_ajax_academy_pro_advanced_analytics/admin/get_courses', array( $self, 'get_courses' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/admin/get_students_by_course_id', array( $self, 'get_students_by_course_id' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/admin/get_instructors_by_course_id', array( $self, 'get_instructors_by_course_id' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/admin/get_students', array( $self, 'get_students' ) );
		add_action( 'wp_ajax_academy_pro_advanced_analytics/admin/get_student_details', array( $self, 'get_student_details' ) );
	}

	public function get_analytics_for_earnings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$earnings = Helper::get_earnings_analytics();
		wp_send_json_success( $earnings );
	}

	public function get_analytics_for_refunds() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$refunds = Helper::get_refunds_analytics();
		wp_send_json_success( $refunds );
	}

	public function get_analytics_for_discounts() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$refunds = Helper::get_discounts_analytics();
		wp_send_json_success( $refunds );
	}

	public function get_popular_courses() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$response = [];
		$courses = Helper::get_popular_courses();
		if ( is_array( $courses ) ) {
			foreach ( $courses as $course ) {
				$course->permalink = get_permalink( $course->ID );
				$course->reviews = \Academy\Helper::get_course_rating( $course->ID );
				$response[] = $course;
			}
		}
		wp_send_json_success( $response );
	}

	public function recent_enrolled_courses() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$response = [];
		$courses = Helper::get_recent_enrolled_courses();
		if ( is_array( $courses ) ) {
			foreach ( $courses as $course ) {
				$course->permalink = get_permalink( $course->ID );
				$course->reviews = \Academy\Helper::get_course_rating( $course->ID );
				$response[] = $course;
			}
		}
		wp_send_json_success( $response );
	}

	public function recent_reviews() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$response = [];
		$reviews = Helper::get_recent_reviews();
		if ( is_array( $reviews ) ) {
			foreach ( $reviews as $review ) {
				$review->course = [
					'title' => get_the_title( $review->comment_post_ID ),
					'permalink' => get_permalink( $review->comment_post_ID )
				];
				$response[] = $review;
			}
		}
		wp_send_json_success( $response );
	}
	public function recent_students() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$students = Helper::get_recent_registered_students();
		wp_send_json_success( $students );
	}
	public function recent_instructors() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$courses = Helper::get_recent_registered_instructors();
		wp_send_json_success( $courses );
	}

	public function get_courses() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$page = (int) ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = (int) ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$offset = ( $page - 1 ) * $per_page;

		$response = [];

		$args = array(
			'post_type'     => 'academy_courses',
			'post_status'   => 'publish',
			'offset'         => $offset,
			'posts_per_page' => $per_page
		);

		$query = new \WP_Query( $args );

		// Set the x-wp-total header
		header( 'x-wp-total: ' . $query->found_posts );

		$Analytics = new \Academy\Classes\Analytics();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) :
				$query->the_post();
				$course = get_post();
				$course_curriculums = \Academy\Helper::get_course_curriculums_number_of_counts( $course->ID );
				$course->number_of_questions     = $Analytics->get_number_of_questions_by_course_id( $course->ID );
				$course->number_of_instructors   = $Analytics->get_total_number_of_instructors( $course->ID );
				$course->number_of_lessons      = $course_curriculums['total_lessons'];
				$course->number_of_quizzes         = $course_curriculums['total_quizzes'];
				$course->number_of_assignments   = $course_curriculums['total_assignments'];
				$course->number_of_tutor_bookings  = $course_curriculums['total_tutor_bookings'];
				$course->number_of_zoom_meetings = $course_curriculums['total_zoom_meetings'];
				$course->number_of_enrolled     = $Analytics->get_total_number_of_enrolled_by_course_id( $course->ID );
				$course->number_of_reviews                 = $Analytics->get_total_number_of_reviews_by_course_id( $course->ID );
				$course_type = \Academy\Helper::get_course_type( $course->ID );
				$course->course_type = $course_type;
				if ( 'paid' === $course_type ) {
					$course->total_earnings          = $Analytics->get_total_earning_by_course_id( $course->ID );
				}
				$response[] = $course;
			endwhile;
			wp_reset_postdata();
		}//end if
		wp_send_json_success( $response );
	}

	public function get_students_by_course_id() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$page = (int) ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = (int) ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$offset = ( $page - 1 ) * $per_page;

		global $wpdb;
		$courses_data = [];
		$course_id = (int) isset( $_POST['course_id'] ) ? sanitize_text_field( $_POST['course_id'] ) : 0;

		$courses = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * 
				FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_parent = %d ORDER BY post_date DESC LIMIT %d, %d;",
				'academy_enrolled', $course_id, $offset, $per_page
			)
		);

		$Analytics = new \Academy\Classes\Analytics();
		// Set the x-wp-total header
		header( 'x-wp-total: ' . $Analytics->get_total_number_of_student_by_course_id( $course_id ) );

		$course_curriculums = \Academy\Helper::get_course_curriculums_number_of_counts( $course_id );
		foreach ( $courses as $course ) {
			$student = get_user_by( 'ID', $course->post_author );
			$completed_topics = json_decode( get_user_meta( $student->ID, 'academy_course_' . $course_id . '_completed_topics', true ) );

			$total_completed_topics = \Academy\Helper::get_total_number_of_completed_course_topics_by_course_and_student_id( $course_id, $student->ID );
			$percentage              = \Academy\Helper::calculate_percentage( $course_curriculums['total_topics'], $total_completed_topics );
			$course->student_ID                 = $student->ID; //phpcs:ignore
			$course->student_display_name       = $student->display_name;
			$course->number_of_lessons          = $course_curriculums['total_lessons'];
			$course->number_of_quizzes          = $course_curriculums['total_quizzes'];
			$course->number_of_assignments      = $course_curriculums['total_assignments'];
			$course->number_of_tutor_bookings   = $course_curriculums['total_tutor_bookings'];
			$course->number_of_zoom_meetings    = $course_curriculums['total_zoom_meetings'];
			$course->completed_topics           = $completed_topics;
			$course->progress_percentage       = $percentage . '%';
			$courses_data[]                     = $course;
		}
		wp_send_json_success( $courses_data );
	}

	public function get_instructors_by_course_id() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$response = [];
		$instructors = [];
		$course_id = (int) isset( $_POST['course_id'] ) ? sanitize_text_field( $_POST['course_id'] ) : '';
		$page = (int) ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = (int) ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$offset = ( $page - 1 ) * $per_page;
		$course = get_post( $course_id );

		$Analytics = new \Academy\Classes\Analytics();
		// Set the x-wp-total header
		header( 'x-wp-total: ' . $Analytics->get_total_number_of_instructors( $course_id ) );

		if ( \Academy\Helper::get_addon_active_status( 'multi_instructor' ) ) {
			$instructors = \Academy\Helper::get_instructors_by_course_id( $course_id, $offset, $per_page );
		} else {
			$instructors = \Academy\Helper::get_instructor_by_author_id( $course->post_author );
		}
		if ( count( $instructors ) ) {
			foreach ( $instructors as $instructor ) {
				$instructor->total_number_of_courses = count_user_posts( $instructor->ID, 'academy_courses' );
				$instructor->total_number_of_students = \Academy\Helper::get_total_number_of_students_by_instructor( $instructor->ID );
				$instructor->reviews = \Academy\Helper::get_instructor_ratings( $instructor->ID );
				$response[] = $instructor;
			}
		}
		wp_send_json_success( $response );
	}

	public function get_students() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$page = (int) ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = (int) ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$offset = ( $page - 1 ) * $per_page;

		$Analytics = new \Academy\Classes\Analytics();

		// Set the x-wp-total header
		header( 'x-wp-total: ' . $Analytics->get_total_number_of_students() );

		$array = [];
		$students = \Academy\Helper::get_all_students( $offset, $per_page );
		foreach ( $students as $student ) {
			$Analytics    = new \Academy\Classes\Analytics();
			$complete_course            = (int) $Analytics->get_total_number_of_completed_courses_by_student_id( $student->ID );
			$course_taken               = (int) $Analytics->get_total_number_of_enrolled_courses( $student->ID );
			$student->total_number_of_enrolled_course   = $course_taken;
			$student->total_number_of_complete_course   = $complete_course;
			$student->total_number_of_course_taken      = $course_taken;
			$student->total_number_of_inprogress_course = $course_taken - $complete_course;
			$student->total_number_of_write_reviews = $Analytics->get_total_number_of_reviews( $student->ID );
			$array[]                    = $student;
		}
		wp_send_json_success( $array );
	}

	public function get_student_details() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$page = (int) ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = (int) ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$offset = ( $page - 1 ) * $per_page;
		$student_id = (int) isset( $_POST['student_id'] ) ? sanitize_text_field( $_POST['student_id'] ) : '';
		$Analytics    = new \Academy\Classes\Analytics();
		$complete_course            = (int) $Analytics->get_total_number_of_completed_courses_by_student_id( $student_id );
		$course_taken               = (int) $Analytics->get_total_number_of_enrolled_courses( $student_id );

		$response = [
			'total_number_of_enrolled_course' => $course_taken,
			'total_number_of_complete_course' => $complete_course,
			'total_number_of_course_taken'      => $course_taken,
			'total_number_of_inprogress_course' => $course_taken - $complete_course,
			'total_number_of_write_reviews' => $Analytics->get_total_number_of_reviews( $student_id ),
			'enrolled_courses' => []
		];

		$enrolled_courses = [];

		// Set the x-wp-total header
		header( 'x-wp-total: ' . $Analytics->get_total_number_of_enrolled_courses( $student_id ) );

		// custom query
		$query = \Academy\Helper::get_enrolled_courses_by_user( $student_id, 'publish', $offset, $per_page );
		if ( $query && $query->have_posts() ) {
			while ( $query->have_posts() ) :
				$query->the_post();
					$course_id = get_the_ID();

					$course_curriculums = \Academy\Helper::get_course_curriculums_number_of_counts( $course_id );

					$total_completed_topics = \Academy\Helper::get_total_number_of_completed_course_topics_by_course_and_student_id( $course_id, $student_id );
					$percentage              = \Academy\Helper::calculate_percentage( $course_curriculums['total_topics'], $total_completed_topics );
					$enrolled_courses[] = [
						'title' => html_entity_decode( get_the_title() ),
						'date'  => get_the_date(),
						'number_of_lessons'          => $course_curriculums['total_lessons'],
						'number_of_quizzes'          => $course_curriculums['total_quizzes'],
						'number_of_assignments'      => $course_curriculums['total_assignments'],
						'number_of_tutor_bookings'       => $course_curriculums['total_tutor_bookings'],
						'number_of_zoom_meetings'    => $course_curriculums['total_zoom_meetings'],
						'completed_topics'            => \Academy\Helper::get_completed_course_topics_by_course_and_student_id( $course_id, $student_id ),
						'progress_percentage'        => $percentage . '%'
					];
					endwhile;
		}//end if
		$response['enrolled_courses'] = $enrolled_courses;
		wp_send_json_success( $response );
	}
}

