<?php
namespace AcademyProEnrollment\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_enrollment/admin/get_users', array( $self, 'get_users' ) );
		add_action( 'wp_ajax_academy_pro_enrollment/admin/do_enrollment', array( $self, 'do_enrollment' ) );
		add_action( 'wp_ajax_academy_pro_enrollment/admin/cancel_enrollment', array( $self, 'cancel_enrollment' ) );
	}

	public function get_users() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$keyword = ( isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '' );
		$args = array(
			'number' => 10,
			'fields' => array( 'ID', 'user_login', 'user_email', 'user_nicename', 'display_name' )
		);

		if ( $keyword ) {
			$args['search'] = $keyword;
			$args['search_columns'] = array( 'ID', 'user_login', 'user_email', 'user_nicename', 'display_name' );
		}

		$all_users = get_users( $args );

		$prepared_response = [];
		foreach ( $all_users as $user ) {
			$prepared_response[] = array(
				'label' => $user->display_name . ' (' . $user->ID . ' - ' . $user->user_email . ')',
				'value' => (int) $user->ID
			);
		}
		wp_send_json_success( $prepared_response );
	}

	public static function do_enrollment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$course_id = (int) ( isset( $_POST['course_id'] ) ? sanitize_text_field( $_POST['course_id'] ) : '' );
		$student_id = (int) ( isset( $_POST['student_id'] ) ? sanitize_text_field( $_POST['student_id'] ) : '' );
		if ( ! $course_id || ! $student_id ) {
			wp_send_json_error( __( 'Course ID and Student ID is Required', 'academy-pro' ) );
		}

		$enrolled    = \Academy\Helper::is_enrolled( $course_id, $student_id, 'any' );
		if ( $enrolled ) {
			if ( 'completed' !== $enrolled->enrolled_status ) {
				$is_enrolled = \Academy\Helper::update_enrollment_status( $course_id, $enrolled->ID, $student_id );
				if ( $is_enrolled ) {
					wp_send_json_success( __( 'Successfully Enrolled!', 'academy-pro' ) );
				}
				// translators: %s represents the enrolled status of a user
				wp_send_json_error( sprintf( __( 'Enroll Status is %s', 'academy-pro' ), $enrolled->enrolled_status ) );
			}
			wp_send_json_error( __( 'Already Enrolled!', 'academy-pro' ) );
		}

		$is_enrolled = \Academy\Helper::do_enroll( $course_id, $student_id );
		if ( $is_enrolled ) {
			wp_send_json_success( __( 'Successfully Enrolled!', 'academy-pro' ) );
		}

		wp_send_json_error( __( 'Failed, something went wrong!', 'academy-pro' ) );
	}

	public static function cancel_enrollment() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$course_id = (int) ( isset( $_POST['course_id'] ) ? sanitize_text_field( $_POST['course_id'] ) : '' );
		$student_id = (int) ( isset( $_POST['student_id'] ) ? sanitize_text_field( $_POST['student_id'] ) : '' );

		if ( ! $course_id || ! $student_id ) {
			wp_send_json_error( __( 'Course ID and Student ID is Required', 'academy-pro' ) );
		}

		$cancel_enroll_ids = \AcademyProEnrollment\Helper::cancel_enroll( $course_id, $student_id );
		if ( count( $cancel_enroll_ids ) ) {
			wp_send_json_success( __( 'Successfully Remove Enrolled!', 'academy-pro' ) );
		}

		wp_send_json_error( __( 'Failed, something went wrong!', 'academy-pro' ) );
	}
}
