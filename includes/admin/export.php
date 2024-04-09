<?php
namespace AcademyPro\Admin;

use AcademyPro\Classes\QuizExport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Export {
	public static function init() {
		$self = new self();
		add_action( 'admin_init', [ $self, 'quiz_export_data' ] );
	}

	public function quiz_export_data() {
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$exportType = isset( $_GET['exportType'] ) ? sanitize_text_field( $_GET['exportType'] ) : '';
		if ( 'academy-tools' !== $page || 'quizzes' !== $exportType || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		// Verify nonce
		check_admin_referer( 'academy_nonce', 'security' );

		$QuizExport = new QuizExport();
		// Start Exporting
		$csv_data = $QuizExport->get_quizzes_for_export();
		if ( ! count( $csv_data ) ) {
			return false;
		}
		$filename = 'academy-' . $exportType;
		$filename .= '.' . gmdate( 'Y-m-d' ) . '.csv';
		$QuizExport->array_to_csv_download(
			$csv_data,
			$filename,
			false
		);
		exit();
	}


}
