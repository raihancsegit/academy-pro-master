<?php
namespace AcademyPro;

use Academy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public static function init() {
		$self = new self();

		$self->init_admin_request();
		$self->init_frontend_request();
		// need write all ajax here
		add_action( 'wp_ajax_academy_pro/duplicate_course', array( $self, 'duplicate_course' ) );
		add_action( 'wp_ajax_academy_pro/duplicate_lesson', array( $self, 'duplicate_lesson' ) );
		add_action( 'wp_ajax_academy_pro/duplicate_quiz', array( $self, 'duplicate_quiz' ) );
	}

	public function init_admin_request() {
		Admin\Ajax::init();
	}
	public function init_frontend_request() {
		Frontend\Ajax::init();
	}
	public function duplicate_course() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$course_id = (int) isset( $_POST['course_id'] ) ? sanitize_post( $_POST['course_id'] ) : 0;
		if ( ! $course_id ) {
			return;
		}

		$posts = get_post( $course_id );
		$duplicate_post = array(
			'post_author' => get_current_user_id(),
			'post_status' => 'draft',
			'post_title' => $posts->post_title . ' (copy)',
			'comment_status' => $posts->comment_status,
			'ping_status' => $posts->ping_status,
			'post_type' => $posts->post_type,
			'post_content' => $posts->post_content,
			'post_excerpt' => $posts->post_excerpt,
			'post_password' => $posts->post_password,
			'post_name' => sanitize_title( $posts->post_name . ' (copy)', 'untitled-course' ),
			'to_ping' => $posts->to_ping,
			'pinged' => $posts->pinged,
			'post_content_filtered' => $posts->post_content_filtered,
			'guid' => $posts->guid,
			'menu_order' => $posts->menu_order,
			'post_mime_type' => $posts->post_mime_type,
			'comment_count' => 0,
		);
		// update post
		$duplicate_id = wp_insert_post( $duplicate_post );
		if ( $duplicate_id ) {
			add_user_meta( get_current_user_id(), 'academy_instructor_course_id', $duplicate_id );
			$Duplicate = new Classes\Duplicate();
			// update post slug
			$Duplicate->update_course_slug( $course_id, $duplicate_id );
			// update post meta
			$Duplicate->update_course_post_meta( $course_id, $duplicate_id );
			// duplicate taxonomy
			$Duplicate->course_taxonomy_update( $course_id, $duplicate_id, 'academy_courses_category' );
			$Duplicate->course_taxonomy_update( $course_id, $duplicate_id, 'academy_courses_tag' );

			wp_send_json_success( __( 'Successfully Duplicate Course!', 'academy-pro' ) );
		}
		wp_send_json_error( __( 'Sorry, Failed To Duplicate Course!', 'academy-pro' ) );
	}
	public function duplicate_lesson() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$lesson_id = (int) isset( $_POST['lesson_id'] ) ? sanitize_post( $_POST['lesson_id'] ) : 0;
		if ( ! $lesson_id ) {
			return;
		}
		// get lesson data
		$lesson = \Academy\Helper::get_lesson( $lesson_id );
		$Duplicate = new Classes\Duplicate();

		// insert duplicate lesson data
		$new_lesson_id = $Duplicate->duplicate_lesson_insert( $lesson );
		if ( $new_lesson_id ) {
			$new_lesson_id = $Duplicate->duplicate_lesson_meta_insert( $lesson_id, $new_lesson_id );
			wp_send_json_success( __( 'Successfully Duplicate Lesson!', 'academy-pro' ) );
		}
		wp_send_json_error( __( 'Sorry, Failed To Duplicate Lesson!', 'academy-pro' ) );
	}
	public function duplicate_quiz() {
		check_admin_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$quiz_id = (int) isset( $_POST['quiz_id'] ) ? sanitize_post( $_POST['quiz_id'] ) : 0;
		if ( ! $quiz_id ) {
			wp_send_json_error( __( 'Quiz ID is required.', 'academy-pro' ) );
		}
		// get quiz and insert duplicate quiz
		$Duplicate = new Classes\Duplicate();
		$new_quiz_id = $Duplicate->duplicate_quiz( $quiz_id );
		if ( isset( $new_quiz_id ) ) {

			$Duplicate->duplicate_quiz_meta( $quiz_id, $new_quiz_id );
			$questions = \AcademyQuizzes\Classes\Query::get_questions_by_quid_id( $quiz_id );
			foreach ( $questions as $question ) {
				$new_question_id = \AcademyQuizzes\Classes\Query::quiz_question_insert(array(
					'quiz_id'               => $new_quiz_id,
					'question_title'        => $question->question_title,
					'question_name'         => $question->question_name,
					'question_content'      => $question->question_content,
					'question_level'        => $question->question_level,
					'question_type'         => $question->question_type,
					'question_score'        => $question->question_score,
					'question_settings'     => $question->question_settings,
					'question_order'        => $question->question_order,
				));
				$answers = $Duplicate->get_quiz_answers_by_question_id( $question->question_id, $question->question_type );
				if ( $answers ) {
					$Duplicate->duplicate_quiz_answer_insert( $new_quiz_id, $new_question_id, $answers, $question->question_type );
				}
			}
			wp_send_json_success( __( 'Successfully Duplicate Quiz!', 'academy-pro' ) );
		}//end if
		wp_send_json_error( __( 'Sorry, Failed To Duplicate Quiz!', 'academy-pro' ) );
	}

}
