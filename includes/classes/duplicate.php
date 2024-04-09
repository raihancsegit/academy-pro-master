<?php
namespace AcademyPro\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Duplicate {
	public function update_course_post_meta( $old_course_id, $new_course_id ) {
		$metadata = get_post_meta( $old_course_id );
		foreach ( $metadata as $key => $value ) {
			$value = is_array( $value ) ? ( isset( $value[0] ) ? $value[0] : '' ) : '';
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$value = is_serialized( $value ) ? unserialize( $value ) : $value;
			update_post_meta( $new_course_id, $key, $value );
		}
	}
	public function update_course_slug( $old_course_id, $new_duplicate_id ) {
		$original_post_slug = basename( get_the_permalink( $old_course_id ) );
		$duplicated_post_slug = wp_unique_post_slug( sanitize_title( $original_post_slug ), $new_duplicate_id, 'draft', 'academy_courses', null );
		wp_update_post(array(
			'ID'         => $new_duplicate_id,
			'post_name'  => $duplicated_post_slug,
		));
	}
	public function course_taxonomy_update( $old_id, $new_id, $taxonomy ) {
		if ( 'academy_courses_category' === $taxonomy || 'academy_courses_tag' === $taxonomy ) {
			$old_terms = get_the_terms( $old_id, $taxonomy );

			if ( is_array( $old_terms ) && ! empty( $old_terms ) ) {
				$term_ids = array();

				foreach ( $old_terms as $term ) {
					$term_ids[] = $term->term_id;
				}

				if ( count( $term_ids ) > 0 ) {
					wp_set_post_terms( $new_id, $term_ids, $taxonomy );
				}
			}
		}
	}

	public function duplicate_lesson_insert( $lesson ) {
		$arg = array(
			'lesson_author'   => get_current_user_id(),
			'lesson_title'    => $lesson->lesson_title . ' (copy)',
			'lesson_name'     => $lesson->lesson_name,
			'lesson_content'  => $lesson->lesson_content,
			'lesson_excerpt'  => $lesson->lesson_excerpt,
			'lesson_status'   => 'draft',
			'comment_status'  => $lesson->comment_status,
			'comment_count'   => 0,
			'lesson_password' => $lesson->lesson_password,
		);
		$lesson_id = \Academy\Classes\Query::lesson_insert( $arg );
		return $lesson_id;
	}

	public function duplicate_lesson_meta_insert( $old_lesson_id, $new_lesson_id ) {
		// get lesson meta
		$lesson_meta_post = \Academy\Helper::get_lesson_meta_data( $old_lesson_id );
		\Academy\Classes\Query::lesson_meta_insert( $new_lesson_id, $lesson_meta_post );
	}

	public function duplicate_quiz( $quiz_id ) {
		$quiz_post = get_post( $quiz_id );
		$args = array(
			'post_author' => get_current_user_id(),
			'post_content' => $quiz_post->post_content,
			'post_title' => $quiz_post->post_title . ' (copy)',
			'post_status'  => $quiz_post->post_status,
			'post_excerpt' => $quiz_post->post_excerpt,
			'comment_status' => $quiz_post->comment_status,
			'ping_status' => $quiz_post->ping_status,
			'post_password' => $quiz_post->post_password,
			'post_name' => $quiz_post->post_name,
			'to_ping' => $quiz_post->to_ping,
			'pinged' => $quiz_post->pinged,
			'post_content_filtered' => $quiz_post->post_content_filtered,
			'post_parent' => $quiz_post->post_parent,
			'guid' => $quiz_post->guid,
			'menu_order' => $quiz_post->menu_order,
			'post_type' => $quiz_post->post_type,
			'post_mime_type' => $quiz_post->post_mime_type,
			'comment_count' => 0,
		);
		return wp_insert_post( $args );
	}

	public function duplicate_quiz_meta( $quiz_id, $new_quiz_id ) {
		$old_quiz_meta = get_post_meta( $quiz_id );
		foreach ( $old_quiz_meta as $key => $value ) {
			$value = is_array( $value ) ? ( isset( $value[0] ) ? $value[0] : '' ) : '';
			$value = is_serialized( $value ) ? unserialize( $value ) : $value; //phpcs:ignore
			update_post_meta( $new_quiz_id, $key, $value );
		}
	}

	public function get_quiz_answers_by_question_id( $question_id, $question_type ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT answer_id, quiz_id, answer_title, is_correct, answer_content, image_id, view_format, answer_order  FROM {$wpdb->prefix}academy_quiz_answers WHERE question_id=%d AND question_type=%s",
				$question_id,
				$question_type
			),
			OBJECT
		);
	}

	public function duplicate_quiz_answer_insert( $quiz_id, $question_id, $answers, $question_type ) {
		foreach ( $answers as $answer ) {
			\AcademyQuizzes\Classes\Query::quiz_answer_insert(array(
				'quiz_id'            => $quiz_id,
				'question_id'        => $question_id,
				'question_type'      => $question_type,
				'answer_title'       => $answer->answer_title,
				'answer_content'     => $answer->answer_content,
				'is_correct'         => $answer->is_correct,
				'image_id'           => $answer->image_id,
				'view_format'        => $answer->view_format,
				'answer_order'       => $answer->answer_order,
			));
		}
	}
}
