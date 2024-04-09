<?php
namespace AcademyProAssignments;

use Academy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_assignments/get_submitted_assignments', array( $self, 'get_submitted_assignments' ) );
		add_action( 'wp_ajax_academy_pro_assignments/evaluate_submitted_assignment', array( $self, 'evaluate_submitted_assignment' ) );
		add_action( 'wp_ajax_academy_pro_assignments/frontend/render_assignment', array( $self, 'render_assignment' ) );
		add_action( 'wp_ajax_academy_pro_assignments/frontend/start_assignment', array( $self, 'start_assignment' ) );
		add_action( 'wp_ajax_academy_pro_assignments/frontend/submit_assignment', array( $self, 'submit_assignment' ) );
		add_action( 'wp_ajax_academy_pro_assignments/delete_submitted_assignment', array( $self, 'delete_submitted_assignment' ) );
		// Mark as complete
		add_action( 'academy/frontend/before_mark_topic_complete', array( $self, 'mark_assignment_complete' ), 10, 4 );
	}
	public function get_submitted_assignments() {
		check_ajax_referer( 'academy_nonce', 'security' );

		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$user_id = ( isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : 0 );
		$page = ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$search = ( isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '' );
		$offset = ( $page - 1 ) * $per_page;

		$args = array(
			'comment_type' => 'academy_assignments',
			'status' => 'submitted',
		);

		if ( $user_id ) {
			$args['post_author'] = $user_id;
		}

		$total_comments = count( get_comments( $args ) );

		// Set the x-wp-total header
		header( 'x-wp-total: ' . $total_comments );

		if ( ! empty( $search ) ) {
			$args['search'] = $search;
		}
		// pagination
		$args['offset'] = $offset;
		$args['number'] = $per_page;
		$submitted_assignments = get_comments( $args );
		$submitted_assignments = Helper::prepare_submitted_assignment_items( $submitted_assignments );
		wp_send_json_success( $submitted_assignments );
	}
	public function evaluate_submitted_assignment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$comment_id = (int) sanitize_text_field( $_POST['comment_id'] );
		$evaluate_point = (int) sanitize_text_field( $_POST['evaluate_point'] );
		$evaluate_feedback = sanitize_text_field( $_POST['evaluate_feedback'] );

		update_comment_meta( $comment_id, 'academy_pro_assignment_evaluate_point', $evaluate_point );
		update_comment_meta( $comment_id, 'academy_pro_assignment_evaluate_feedback', $evaluate_feedback );

		$response = get_comment( $comment_id );
		$response = Helper::prepare_submitted_assignment_item( $response );
		wp_send_json_success( $response );
	}
	public function render_assignment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$course_id = (int) sanitize_text_field( $_POST['course_id'] );
		$assignment_id = (int) sanitize_text_field( $_POST['assignment_id'] );
		$time_zone = sanitize_text_field( $_POST['time_zone'] );
		$user_id   = (int) get_current_user_id();

		$is_administrator = current_user_can( 'administrator' );
		$is_instructor    = \Academy\Helper::is_instructor_of_this_course( $user_id, $course_id );
		$enrolled         = \Academy\Helper::is_enrolled( $course_id, $user_id );
		$is_public_course = \Academy\Helper::is_public_course( $course_id );
		if ( $is_administrator || $is_instructor || $enrolled || $is_public_course ) {
			do_action( 'academy_pro_assignments/frontend/before_render_assignment', $course_id, $assignment_id );
			$assignment = get_post( $assignment_id );
			if ( ! $assignment ) {
				wp_send_json_error( esc_html__( 'Sorry, something went wrong!', 'academy-pro' ) );
			}
			$assignment->post_content_rendered = \Academy\Helper::get_content_html( stripslashes( $assignment->post_content ) );
			$attachment_id = (int) get_post_meta( $assignment_id, 'academy_assignment_attachment', true );
			$assignment->attachment = [
				'ID' => $attachment_id ? $attachment_id : (int) get_post_meta( $assignment_id, '_thumbnail_id', true ),
				'url' => get_the_post_thumbnail_url( $assignment_id ),
			];
			$assignment->settings = get_post_meta( $assignment_id, 'academy_assignment_settings', true );
			$submitted_assignments = get_comments(array(
				'post_id' => $assignment->ID,
				'comment_type' => 'academy_assignments',
				'parent'    => $course_id,
				'user_id'   => $user_id,
				'status' => array( 'submitted', 'submitting' )
			));
			if ( $submitted_assignments ) {
				$submitted_assignments = Helper::prepare_submitted_assignment_items( $submitted_assignments );
				$assignment->submitted_assignment = current( $submitted_assignments );
			}
			$assignment->submission_status = 'active';
			if ( Helper::is_assignment_submission_expired( $assignment, $time_zone ) ) {
				$assignment->submission_status = 'expired';
			}
			wp_send_json_success( $assignment );
		}//end if
		wp_send_json_error( __( 'Access Denied', 'academy-pro' ) );
	}

	public function start_assignment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$course_id              = (int) sanitize_text_field( $_POST['course_id'] );
		$assignment_id          = (int) sanitize_text_field( $_POST['assignment_id'] );
		$submitting_time        = sanitize_text_field( $_POST['submitting_time'] );
		$time_zone              = sanitize_text_field( $_POST['time_zone'] );
		$current_user           = wp_get_current_user();
		$is_administrator       = current_user_can( 'administrator' );
		$is_instructor          = \Academy\Helper::is_instructor_of_this_course( $current_user->ID, $course_id );
		$is_enrolled               = \Academy\Helper::is_enrolled( $course_id, $current_user->ID );
		$is_public_course = \Academy\Helper::is_public_course( $course_id );

		$submitting_assignments = get_comments(array(
			'post_id'           => $assignment_id,
			'parent'    => $course_id,
			'user_id'   => $current_user->ID,
			'comment_type' => 'academy_assignments',
			'status' => array( 'submitted', 'submitting' )
		));

		if ( count( $submitting_assignments ) ) {
			wp_send_json_error( __( 'Already Submitted', 'academy-pro' ) );
		}

		if ( $is_administrator || $is_instructor || $is_enrolled || $is_public_course ) {
			$data = array(
				'comment_post_ID'      => $assignment_id,
				'comment_parent'       => $course_id,
				'user_id'              => $current_user->ID,
				'comment_author'       => $current_user->user_login,
				'comment_author_email' => $current_user->user_email,
				'comment_author_url'   => $current_user->user_url,
				'comment_approved'     => 'submitting',
				'comment_agent'        => 'academy',
				'comment_type'         => 'academy_assignments',
				'comment_meta'         => array(
					'academy_pro_assignment_start_time' => $submitting_time,
				)
			);
			$assignment_answer_id = wp_insert_comment( $data );
			$response = get_comment( $assignment_answer_id );
			$response = Helper::prepare_submitted_assignment_item( $response );
			wp_send_json_success( $response );
		}
		wp_send_json_error( __( 'Access Denied', 'academy-pro' ) );
	}
	public function submit_assignment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$course_id              = (int) sanitize_text_field( $_POST['course_id'] );
		$assignment_id          = (int) sanitize_text_field( $_POST['assignment_id'] );
		$assignment_comment_id  = (int) sanitize_text_field( $_POST['assignment_comment_id'] );
		$submitted_time         = sanitize_text_field( $_POST['submitted_time'] );
		$time_zone              = sanitize_text_field( $_POST['time_zone'] );
		$current_user           = wp_get_current_user();
		$is_administrator       = current_user_can( 'administrator' );
		$is_instructor          = \Academy\Helper::is_instructor_of_this_course( $current_user->ID, $course_id );
		$is_enrolled               = \Academy\Helper::is_enrolled( $course_id, $current_user->ID );
		$is_public_course = \Academy\Helper::is_public_course( $course_id );
		$assignment_answer      = ( isset( $_POST['assignment_answer'] ) ? wp_kses_post( $_POST['assignment_answer'] ) : '' );
		$assignment_attachment  = ( isset( $_POST['assignment_attachment'] ) ? sanitize_text_field( $_POST['assignment_attachment'] ) : '' );

		$started_assignment = get_comment( $assignment_comment_id );

		if ( ! $started_assignment ) {
			wp_send_json_error( __( 'Assignment Haven\'t started yet.', 'academy-pro' ) );
		}

		if ( $is_administrator || $is_instructor || $is_enrolled || $is_public_course ) {
			// first check submission is expired or not
			$assignment = get_post( $assignment_id );
			$assignment->settings = get_post_meta( $assignment_id, 'academy_assignment_settings', true );
			$submitted_assignments = get_comments(array(
				'post_id' => $assignment->ID,
				'comment_type' => 'academy_assignments',
				'status' => array( 'submitted', 'submitting' )
			));
			$submitted_assignments = Helper::prepare_submitted_assignment_items( $submitted_assignments );
			$assignment->submitted_assignment = current( $submitted_assignments );

			if ( Helper::is_assignment_submission_expired( $assignment, $time_zone ) ) {
				wp_send_json_error( __( 'Expired Your Submission. Contact with instructor', 'academy-pro' ) );
			}

			// Update comment
			$data = array(
				'comment_ID'           => $assignment_comment_id,
				'comment_post_ID'      => $assignment_id,
				'comment_content'      => $assignment_answer,
				'comment_date_gmt'     => $started_assignment->comment_date_gmt,
				'comment_approved'     => 'submitted',
				'comment_meta'         => array(
					'academy_pro_assignment_end_time' => $submitted_time,
					'academy_pro_assignment_attachment' => $assignment_attachment,
				)
			);
			wp_update_comment( $data );
			$response = get_comment( $assignment_comment_id );
			$response = Helper::prepare_submitted_assignment_item( $response );
			wp_send_json_success( $response );
		}//end if
		wp_send_json_error( __( 'Access Denied', 'academy-pro' ) );
	}

	public function delete_submitted_assignment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$comment_ID = (int) sanitize_text_field( $_POST['comment_ID'] );
		$is_delete = wp_delete_comment( $comment_ID, true );
		if ( $is_delete ) {
			wp_send_json_success( array(
				'id' => $comment_ID
			) );
		}

		wp_send_json_error( __( 'Sorry, Failed to delete submission.', 'academy-pro' ) );
	}

	public function mark_assignment_complete( $topic_type, $course_id, $topic_id, $user_id ) {
		if ( 'assignment' === $topic_type && ! \AcademyProAssignments\Helper::has_submitted_assignment( $topic_id, $user_id ) ) {
			wp_send_json_error( __( 'Complete the assignment before marking it as done.', 'academy-pro' ) );
		}
	}
}
