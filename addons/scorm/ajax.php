<?php
namespace AcademyProScorm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Academy\Classes\FileUpload;

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_scorm/admin/scorm_file_upload', array( $self, 'scorm_file_upload' ) );
		add_action( 'wp_ajax_academy_pro_scorm/admin/delete_upload_scorm_file', array( $self, 'delete_upload_scorm_file' ) );

		add_action( 'wp_ajax_academy_pro/frontend/get_scorm_course_progress', array( $self, 'get_scorm_course_progress' ) );
		add_action( 'wp_ajax_academy_pro/frontend/update_scorm_course_progress', array( $self, 'update_scorm_course_progress' ) );
	}
	public function scorm_file_upload() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$upload_file = $_FILES['upload_file'];
		$FileUpload = new FileUpload();
		$has_upload = $FileUpload->upload_file( $upload_file, [ 'zip' ] );
		if ( $has_upload['error'] ) {
			wp_send_json_error( $has_upload['error'] );
		}
		// Unzip File
		$is_unzip = $FileUpload->unzip_uploaded_file( $has_upload['path'], $has_upload['file_name'] );
		if ( $is_unzip ) {
			wp_send_json_success( $is_unzip );
		}
		wp_send_json_error( __( 'Successfully uploaded but failed to unzip', 'academy-pro' ) );
	}
	public function delete_upload_scorm_file() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$file_name = sanitize_text_field( $_POST['file_name'] );
		$FileUpload = new FileUpload();
		$is_delete = $FileUpload->delete_file( $FileUpload->get_file_path( $file_name ) );
		if ( $is_delete ) {
			wp_send_json_success( __( 'Successfully Removed', 'academy-pro' ) );
		}
		wp_send_json_error( __( 'Sorry, failed to delete', 'academy-pro' ) );
	}

	public function get_scorm_course_progress() {

		wp_send_json_success( false );
	}
	public function update_scorm_course_progress() {

		wp_send_json_success( false );
	}
}
