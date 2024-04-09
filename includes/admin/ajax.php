<?php
namespace AcademyPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro/admin/bulk_import_instructor', array( $self, 'bulk_import_instructor' ) );
		add_action( 'wp_ajax_academy_pro/admin/bulk_import_student', array( $self, 'bulk_import_student' ) );
		add_action( 'wp_ajax_academy_pro/admin/bulk_enroll_student', array( $self, 'bulk_enroll_student' ) );
		add_action( 'wp_ajax_academy_pro/admin/import_quizzes', array( $self, 'import_quizzes' ) );
	}

	public function bulk_import_instructor() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		if ( ! isset( $_FILES['upload_file'] ) ) {
			wp_send_json_error( __( 'Upload File is empty.', 'academy-pro' ) );
		}

		$file = $_FILES['upload_file'];
		if ( 'csv' !== pathinfo( $file['name'] )['extension'] ) {
			wp_send_json_error( __( 'Wrong File Format! Please import csv file.', 'academy-pro' ) );
		}

		$link_header = [];
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file_open = fopen( $file['tmp_name'], 'r' );
		if ( false !== $file_open ) {
			$results = [];
			$count = 0;
			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			while ( false !== ( $item = fgetcsv( $file_open ) ) ) {
				if ( 0 === $count ) {
					$link_header = array_map( 'strtolower', $item );
					$count++;
					continue;
				}

				$item = array_combine( $link_header, $item );

				$first_name = ( isset( $item['first_name'] ) ? sanitize_text_field( $item['first_name'] ) : '' );
				$last_name = ( isset( $item['last_name'] ) ? sanitize_text_field( $item['last_name'] ) : '' );
				$username = ( isset( $item['username'] ) ? sanitize_text_field( $item['username'] ) : '' );
				$email = ( isset( $item['email'] ) ? sanitize_text_field( $item['email'] ) : '' );
				$password = ( isset( $item['password'] ) ? sanitize_text_field( $item['password'] ) : '' );
				$instructor = \Academy\Helper::insert_instructor( $email, $first_name, $last_name, $username, $password );
				if ( is_numeric( $instructor ) ) {
					$results[] = [
						'email' => $item['email'],
						'message' => __( 'Successfully, Inserted.', 'academy-pro' ),
					];
				} else {
					$results[] = [
						'email' => $item['email'],
						'message' => $instructor,
					];
				}
			}//end while
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			fclose( $file_open );

			wp_send_json_success( $results );
		}//end if
		wp_send_json_error( __( 'Failed to open the file', 'academy-pro' ) );
	}

	public function bulk_import_student() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		if ( ! isset( $_FILES['upload_file'] ) ) {
			wp_send_json_error( __( 'Upload File is empty.', 'academy-pro' ) );
		}

		$file = $_FILES['upload_file'];
		if ( 'csv' !== pathinfo( $file['name'] )['extension'] ) {
			wp_send_json_error( __( 'Wrong File Format! Please import csv file.', 'academy-pro' ) );
		}

		$link_header = [];
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file_open = fopen( $file['tmp_name'], 'r' );
		if ( false !== $file_open ) {
			$results = [];
			$count = 0;
			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			while ( false !== ( $item = fgetcsv( $file_open ) ) ) {
				if ( 0 === $count ) {
					$link_header = array_map( 'strtolower', $item );
					$count++;
					continue;
				}

				$item = array_combine( $link_header, $item );

				$first_name = ( isset( $item['first_name'] ) ? sanitize_text_field( $item['first_name'] ) : '' );
				$last_name = ( isset( $item['last_name'] ) ? sanitize_text_field( $item['last_name'] ) : '' );
				$username = ( isset( $item['username'] ) ? sanitize_text_field( $item['username'] ) : '' );
				$email = ( isset( $item['email'] ) ? sanitize_text_field( $item['email'] ) : '' );
				$password = ( isset( $item['password'] ) ? sanitize_text_field( $item['password'] ) : '' );

				$student = \Academy\Helper::insert_student( $email, $first_name, $last_name, $username, $password );
				if ( is_numeric( $student ) ) {
					$results[] = [
						'email' => $item['email'],
						'message' => __( 'Successfully, Inserted.', 'academy-pro' ),
					];
				} else {
					$results[] = [
						'email' => $item['email'],
						'message' => $student,
					];
				}
			}//end while
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			fclose( $file_open );

			wp_send_json_success( $results );
		}//end if
		wp_send_json_error( __( 'Failed to open the file', 'academy-pro' ) );
	}

	public function bulk_enroll_student() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		if ( ! isset( $_FILES['upload_file'] ) ) {
			wp_send_json_error( __( 'Upload File is empty.', 'academy-pro' ) );
		}

		$file = $_FILES['upload_file'];
		if ( 'csv' !== pathinfo( $file['name'] )['extension'] ) {
			wp_send_json_error( __( 'Wrong File Format! Please import csv file.', 'academy-pro' ) );
		}

		$link_header = [];
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file_open = fopen( $file['tmp_name'], 'r' );
		if ( false !== $file_open ) {
			$results = [];
			$count = 0;
			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			while ( false !== ( $item = fgetcsv( $file_open ) ) ) {
				if ( 0 === $count ) {
					$link_header = array_map( 'strtolower', $item );
					$count++;
					continue;
				}

				$item = array_combine( $link_header, $item );

				$first_name = ( isset( $item['first_name'] ) ? sanitize_text_field( $item['first_name'] ) : '' );
				$last_name = ( isset( $item['last_name'] ) ? sanitize_text_field( $item['last_name'] ) : '' );
				$username = ( isset( $item['username'] ) ? sanitize_text_field( $item['username'] ) : '' );
				$email = ( isset( $item['email'] ) ? sanitize_text_field( $item['email'] ) : '' );
				$password = ( isset( $item['password'] ) ? sanitize_text_field( $item['password'] ) : '' );
				$course_id = (int) ( isset( $item['course_id'] ) ? sanitize_text_field( $item['course_id'] ) : 0 );

				if ( empty( $email ) || empty( $course_id ) ) {
					$results[] = [
						'email' => $item['email'],
						'message' => __( 'Failed! Email and Course Id is required to enroll', 'academy-pro' ),
					];
					continue;
				}

				// Register
				$student = get_user_by( 'email', $email );
				if ( ! $student ) {
					$inserted_student = \Academy\Helper::insert_student( $email, $first_name, $last_name, $username, $password );
					$student = is_numeric( $inserted_student ) ? get_user_by( 'ID', $inserted_student ) : null;
					if ( ! $student ) {
						$results[] = [
							'email' => $item['email'],
							'message' => __( 'Failed! Something wrong.', 'academy-pro' ),
						];
						continue;
					}
				}

				// enroll
				$is_enrolled = \Academy\Helper::is_enrolled( $course_id, $student->ID );
				if ( $is_enrolled ) {
					$results[] = [
						'email' => $item['email'],
						'message' => __( 'Failed! Already Enrolled.', 'academy-pro' ),
					];
				} else {
					$is_enrolled = \Academy\Helper::do_enroll( $course_id, $student->ID );
					$results[] = [
						'email' => $item['email'],
						'message' => $is_enrolled ? __( 'Successfully, Inserted and enrolled.', 'academy-pro' ) : __( 'Failed! Sorry failed to enroll.', 'academy-pro' ),
					];
				}
			}//end while
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			fclose( $file_open );

			wp_send_json_success( $results );
		}//end if
		wp_send_json_error( __( 'Failed to open the file', 'academy-pro' ) );
	}

	public function import_quizzes() {
		global $wpdb;
		check_admin_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		if ( ! isset( $_FILES['upload_file'] ) ) {
			wp_send_json_error( __( 'Upload File is empty.', 'academy-pro' ) );
		}

		$file = $_FILES['upload_file'];
		if ( 'csv' !== pathinfo( $file['name'] )['extension'] ) {
			wp_send_json_error( __( 'Wrong File Format! Please import csv file.', 'academy-pro' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file_open = fopen( $file['tmp_name'], 'r' );
		if ( false !== $file_open ) {
			$quiz_header = [];
			$question_header = [];
			$answer_header = [];
			$has_quiz = false;
			$has_question = false;
			$has_answer = false;
			$new_quiz_id = 0;
			$new_question_id = 0;
			$response = [];
			// phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			while ( false !== ( $item = fgetcsv( $file_open ) ) ) {
				// Set Header
				if ( in_array( 'quiz_title', $item, true ) ) {
					$quiz_header = array_map( 'strtolower', $item );
					$has_quiz = true;
					$has_question = false;
					$has_answer = false;
					continue;

				} elseif ( in_array( 'question_title', $item, true ) ) {
					$question_header = array_map( 'strtolower', $item );
					$has_question = true;
					$has_answer = false;
					continue;

				} elseif ( in_array( 'answer_title', $item, true ) ) {
					$answer_header = array_map( 'strtolower', $item );
					$has_answer = true;
					continue;
				}

				// Insert Quiz, Question and answer
				if ( $has_quiz ) {
					$quiz_item = array_combine( $quiz_header, $item );
					$quiz_title = ( isset( $quiz_item['quiz_title'] ) ? sanitize_text_field( $quiz_item['quiz_title'] ) : '' );
					$quiz_content = ( isset( $quiz_item['quiz_content'] ) ? sanitize_text_field( $quiz_item['quiz_content'] ) : '' );
					$quiz_time = (int) ( isset( $quiz_item['quiz_time'] ) ? sanitize_text_field( $quiz_item['quiz_time'] ) : 0 );
					$quiz_time_unit = ( isset( $quiz_item['quiz_time_unit'] ) ? sanitize_text_field( $quiz_item['quiz_time_unit'] ) : '' );
					$quiz_hide_time = (bool) ( isset( $quiz_item['quiz_hide_time'] ) ? sanitize_text_field( $quiz_item['quiz_hide_time'] ) : false );
					$quiz_feedback_mode = ( isset( $quiz_item['quiz_feedback_mode'] ) ? sanitize_text_field( $quiz_item['quiz_feedback_mode'] ) : '' );
					$quiz_passing_grade = (int) ( isset( $quiz_item['quiz_passing_grade'] ) ? sanitize_text_field( $quiz_item['quiz_passing_grade'] ) : 80 );
					$quiz_max_attempts_allowed = (int) ( isset( $quiz_item['quiz_max_attempts_allowed'] ) ? sanitize_text_field( $quiz_item['quiz_max_attempts_allowed'] ) : 10 );
					$quiz_question_order = ( isset( $quiz_item['quiz_questions_order'] ) ? sanitize_text_field( $quiz_item['quiz_questions_order'] ) : 'rnd' );
					$quiz_hide_question_number = (bool) ( isset( $quiz_item['quiz_hide_question_number'] ) ? sanitize_text_field( $quiz_item['quiz_hide_question_number'] ) : false );
					$quiz_meta = array(
						'academy_quiz_time' => $quiz_time,
						'academy_quiz_time_unit' => $quiz_time_unit,
						'academy_quiz_hide_quiz_time' => $quiz_hide_time,
						'academy_quiz_feedback_mode' => $quiz_feedback_mode,
						'academy_quiz_passing_grade' => $quiz_passing_grade,
						'academy_quiz_max_questions_for_answer' => 0,
						'academy_quiz_max_attempts_allowed' => $quiz_max_attempts_allowed,
						'academy_quiz_auto_start' => false,
						'academy_quiz_questions_order' => $quiz_question_order,
						'academy_quiz_hide_question_number' => $quiz_hide_question_number,
						'academy_quiz_short_answer_characters_limit' => 0,
						'academy_quiz_questions' => []
					);

					$quiz_post = \Academy\Helper::get_page_by_title( $quiz_title, 'academy_quiz' );
					if ( ! $quiz_post ) {
						$new_quiz_id = wp_insert_post( array(
							'post_title' => $quiz_title,
							'post_type' => 'academy_quiz',
							'post_content' => $quiz_content,
							'post_status' => 'publish'
						) );
						foreach ( $quiz_meta as $key => $value ) {
							add_post_meta( $new_quiz_id, $key, $value, true );
						}
						$response[] = __( 'Successfully Inserted Quiz', 'academy-pro' ) . ' - ' . $quiz_title;
					} else {
						$new_quiz_id = $quiz_post->ID;
						$response[] = __( 'Failed, Already Inserted Quiz', 'academy-pro' ) . ' - ' . $quiz_title;
					}

					$has_quiz = false;

				} elseif ( $has_question ) {
					$question_item = array_combine( $question_header, $item );
					$question_title = ( isset( $question_item['question_title'] ) ? sanitize_text_field( $question_item['question_title'] ) : '' );
					if ( empty( $question_title ) ) {
						$has_question = false;
						continue;
					}
					$question_type = ( isset( $question_item['question_type'] ) ? sanitize_text_field( $question_item['question_type'] ) : '' );
					$question_points = (float) ( isset( $question_item['question_points'] ) ? sanitize_text_field( $question_item['question_points'] ) : 5 );
					$question_description = ( isset( $question_item['question_description'] ) ? sanitize_text_field( $question_item['question_description'] ) : '' );
					$question_status = ( isset( $question_item['question_status'] ) ? sanitize_text_field( $question_item['question_status'] ) : '' );
					$question_display_points = (bool) ( isset( $question_item['question_display_points'] ) ? sanitize_text_field( $question_item['question_display_points'] ) : false );
					$question_answer_required = (bool) ( isset( $question_item['question_answer_required'] ) ? sanitize_text_field( $question_item['question_answer_required'] ) : false );
					$question_randomize = (bool) ( isset( $question_item['question_randomize'] ) ? sanitize_text_field( $question_item['question_randomize'] ) : false );
					$question_order = (int) ( isset( $question_item['question_order'] ) ? sanitize_text_field( $question_item['question_order'] ) : 0 );
					$question_array = [
						'quiz_id'               => $new_quiz_id,
						'question_title'        => $question_title,
						'question_name'         => '',
						'question_content'      => $question_description,
						'question_status'       => $question_status,
						'question_level'        => '',
						'question_type'         => $question_type,
						'question_score'        => $question_points,
						'question_order'        => $question_order,
						'question_settings'     => wp_json_encode(
							array(
								'display_points' => $question_display_points,
								'answer_required' => $question_answer_required,
								'randomize'  => $question_randomize,
							)
						),
					];
					$questions = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM {$wpdb->prefix}academy_quiz_questions WHERE quiz_id = %d AND question_title LIKE %s",
							$new_quiz_id,
							'%' . $wpdb->esc_like( sanitize_text_field( $question_title ) ) . '%'
						)
					);
					if ( $questions && count( $questions ) ) {
						foreach ( $questions as $question ) {
							$new_question_id = $question->question_id;
						}
						$response[] = __( 'Failed, Already Inserted Question', 'academy-pro' ) . ' - ' . $question_title;
					} else {
						$new_question_id = \AcademyQuizzes\Classes\Query::quiz_question_insert( $question_array );
						$new_quiz_question = (array) get_post_meta( $new_quiz_id, 'academy_quiz_questions', true );
						$new_quiz_question = array_reduce($new_quiz_question, function( $carry, $item ) use ( $question_title ) {
							if ( isset( $item['title'] ) && $item['title'] !== $question_title ) {
								$carry[] = $item;
							}
							return $carry;
						}, []);
						$new_quiz_question[] = array(
							'id' => $new_question_id,
							'title' => $question_title,
						);
						// Update the 'academy_quiz_questions' post meta with the updated array of questions
						update_post_meta( $new_quiz_id, 'academy_quiz_questions', $new_quiz_question );
						$response[] = __( 'Successfully Inserted Question', 'academy-pro' ) . ' - ' . $question_title;
					}//end if
					// Update flag
					$has_question = false;
				} elseif ( $has_answer ) {
					$answer_item = array_combine( $answer_header, $item );
					$answers = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM {$wpdb->prefix}academy_quiz_answers WHERE answer_title LIKE %s AND question_id = %d ",
							'%' . $wpdb->esc_like( sanitize_text_field( $answer_item['answer_title'] ) ) . '%', $new_question_id
						)
					);
					if ( $answers && count( $answers ) ) {
						$response[] = __( 'Failed, Already Inserted Answer', 'academy-pro' ) . ' - ' . $answer_item['answer_title'];
					} else {
						$question = \AcademyQuizzes\Classes\Query::get_quiz_question( $new_question_id );
						\AcademyQuizzes\Classes\Query::quiz_answer_insert( [
							'quiz_id' => $new_quiz_id,
							'question_id' => $new_question_id,
							'question_type' => $question->question_type,
							'answer_title' => sanitize_text_field( $answer_item['answer_title'] ),
							'answer_content' => sanitize_text_field( $answer_item['answer_content'] ),
							'is_correct' => (bool) sanitize_text_field( $answer_item['answer_is_correct'] ),
							// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
							// 'image_id' => $answer_items['answer_image_id'],
							'view_format' => sanitize_text_field( $answer_item['answer_view_format'] ),

						] );
						$response[] = __( 'Successfully Inserted Answer', 'academy-pro' ) . ' - ' . $answer_item['answer_title'];
					}
				}//end if
			}//end while

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			fclose( $file_open );

			wp_send_json_success( $response );
		}//end if
		wp_send_json_error( $response );
	}
}
