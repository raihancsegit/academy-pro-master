<?php
namespace AcademyProContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {
	public static function get_course_curriculum_array( $course_id ) {
		$course_curriculum = get_post_meta( $course_id, 'academy_course_curriculum', true );
		$prepare_curriculum = array();
		if ( is_array( $course_curriculum ) ) {
			foreach ( $course_curriculum as $curriculum ) {
				if ( is_array( $curriculum['topics'] ) ) {
					foreach ( $curriculum['topics'] as $topic ) {
						$prepare_curriculum[] = $topic;
					}
				}
			}
		}
		return $prepare_curriculum;
	}
	public static function get_previous_topic( $topics, $topic_id, $topic_type ) {
		foreach ( $topics as $key => $topic ) {
			if ( (int) $topic['id'] === (int) $topic_id && $topic['type'] === $topic_type ) {
				if ( isset( $topics[ $key - 1 ] ) ) {
					return $topics[ $key - 1 ];
				}
				return $topic;
			}
		}
		return false;
	}
	public static function is_complete_previous_topic( $course_id, $topic_id, $topic_type ) {
		$course_curriculum = self::get_course_curriculum_array( $course_id );
		$previous_topic = self::get_previous_topic( $course_curriculum, $topic_id, $topic_type );
		$completed_topics_lists = (array) json_decode( get_user_meta( get_current_user_id(), 'academy_course_' . $course_id . '_completed_topics', true ), true );
		if ( is_array( $previous_topic ) ) {
			// if first element and previous element is same then return true.
			if ( (int) $topic_id === (int) $previous_topic['id'] ) {
				return true;
			} elseif ( 'lesson' === $previous_topic['type'] ) {
				if ( \Academy\Helper::get_lesson_meta( $topic_id, 'is_previewable' ) ) {
					return true;
				}
				return (bool) isset( $completed_topics_lists[ $previous_topic['type'] ][ $previous_topic['id'] ] );
			} elseif ( 'quiz' === $previous_topic['type'] ) {
				return (bool) isset( $completed_topics_lists[ $previous_topic['type'] ][ $previous_topic['id'] ] );
			} elseif ( 'assignment' === $previous_topic['type'] ) {
				return (bool) isset( $completed_topics_lists[ $previous_topic['type'] ][ $previous_topic['id'] ] );
			} elseif ( 'booking' === $previous_topic['type'] ) {
				return (bool) isset( $completed_topics_lists[ $previous_topic['type'] ][ $previous_topic['id'] ] );
			} elseif ( 'zoom' === $previous_topic['type'] ) {
				return (bool) isset( $completed_topics_lists[ $previous_topic['type'] ][ $previous_topic['id'] ] );
			}
		}
		return false;
	}
}
