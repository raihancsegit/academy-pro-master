<?php
namespace AcademyPro\Classes;

use Academy\Classes\ExportBase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class QuizExport extends ExportBase {
	public function get_quizzes_for_export() {
		$quiz_array = [];
		$quizzes = $this->get_all_quizzes();
		foreach ( $quizzes as $quiz ) {

			$quiz_array[] = array(
				'quiz_title' => $quiz->post_title,
				'quiz_content' => $quiz->post_content,
				'quiz_time' => get_post_meta( $quiz->ID, 'academy_quiz_time', true ),
				'quiz_time_unit' => get_post_meta( $quiz->ID, 'academy_quiz_time_unit', true ),
				'quiz_hide_time' => get_post_meta( $quiz->ID, 'academy_quiz_hide_quiz_time', true ),
				'quiz_feedback_mode' => get_post_meta( $quiz->ID, 'academy_quiz_feedback_mode', true ),
				'quiz_passing_grade' => get_post_meta( $quiz->ID, 'academy_quiz_passing_grade', true ),
				'quiz_max_attempts_allowed' => get_post_meta( $quiz->ID, 'academy_quiz_max_attempts_allowed', true ),
				'quiz_questions_order' => get_post_meta( $quiz->ID, 'academy_quiz_questions_order', true ),
				'quiz_hide_question_number' => get_post_meta( $quiz->ID, 'academy_quiz_hide_question_number', true ),
			);

			$questions = \AcademyQuizzes\Classes\Query::get_questions_by_quid_id( $quiz->ID );

			foreach ( $questions as $question ) {
				$quiz_array[] = $this->prepare_question_for_csv( $question );
				$question_answers = $this->get_quiz_answers( $question->question_id, $question->question_type );
				foreach ( $question_answers as $question_answer ) {
					$quiz_array[] = $question_answer;
				}
			}
		}//end foreach
		return $quiz_array;
	}

	public function get_quiz_answers( $question_id, $question_type ) {
		global $wpdb;
		$answers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT answer_id, quiz_id, answer_title, is_correct, answer_content, image_id, view_format, answer_order  FROM {$wpdb->prefix}academy_quiz_answers WHERE question_id=%d AND question_type=%s",
				$question_id,
				$question_type
			),
			OBJECT
		);

		$prepare_answers = [];
		foreach ( $answers as $answer ) {
			$prepare_answers[] = [
				'answer_title' => $answer->answer_title,
				'answer_content' => $answer->answer_content,
				'answer_is_correct' => $answer->is_correct,
				// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
				// 'answer_image_id' => $answer->image_id,
				'answer_view_format' => $answer->view_format,
			];
		}
		return $prepare_answers;
	}

	public function get_all_quizzes() {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, post_name, post_content  FROM {$wpdb->prefix}posts WHERE post_type=%s AND post_author=%d",
				'academy_quiz',
				get_current_user_id(),
			)
		);

	}

	public function prepare_question_for_csv( $question ) {
		$settings = json_decode( $question->question_settings );
		return array(
			'question_title' => $question->question_title,
			'question_type' => $question->question_type,
			'question_points' => $question->question_score,
			'question_content' => $question->question_content,
			'question_status' => $question->question_status,
			'question_display_points' => $settings->display_points,
			'question_answer_required' => $settings->answer_required,
			'question_randomize' => $settings->randomize,
			'question_order' => $question->question_order,
		);
	}

	/**
	 * Method Overwrite
	 *
	 * Overwrite export base class method
	 *
	 * @param array          $array
	 * @param resource|false $fp
	 * @return void
	 */
	public function write_nested_csv( $array, $fp ) {
		$previousItem = array();
		foreach ( $array as $row ) {
			$flattenRow = $this->flatten_array( $row );
			if ( isset( $flattenRow['quiz_title'] ) || isset( $flattenRow['question_title'] ) || ( isset( $flattenRow['answer_title'] ) && ! isset( $previousItem['answer_title'] ) ) ) {
				$row_header = array_keys( $flattenRow );
				fputcsv( $fp, $row_header );
			}
			fputcsv( $fp, $flattenRow );
			$previousItem = $row;
		}
	}
}
