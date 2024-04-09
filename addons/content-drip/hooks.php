<?php
namespace AcademyProContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Hooks {
	public static function init() {
		$self = new self();
		add_action( 'academy/frontend/before_render_lesson', array( $self, 'render_drip_lesson' ), 10, 3 );
		add_action( 'academy_quizzes/frontend/before_render_quiz', array( $self, 'render_drip_quiz' ), 10, 2 );
		add_action( 'academy_pro_assignments/frontend/before_render_assignment', array( $self, 'render_drip_assignment' ), 10, 2 );
		add_action( 'academy_pro_tutor_booking/frontend/before_render_booking', array( $self, 'render_drip_booking' ), 10, 2 );
	}

	public function render_drip_lesson( $lesson, $course_id, $lesson_id ) {
		$drip_content = isset( $lesson->meta['drip_content'] ) ? $lesson->meta['drip_content'] : '';
		if ( empty( $drip_content ) ) {
			$drip_content = $this->get_default_value();
		}
		$this->drip_content_checker( $drip_content, $course_id, 'lesson', $lesson_id );
	}

	public function render_drip_quiz( $course_id, $quiz_id ) {
		$drip_content = get_post_meta( $quiz_id, 'academy_quiz_drip_content', true );
		if ( empty( $drip_content ) ) {
			$drip_content = $this->get_default_value();
		}
		$this->drip_content_checker( $drip_content, $course_id, 'quiz', $quiz_id );
	}

	public function render_drip_assignment( $course_id, $assignment_id ) {
		$drip_content = get_post_meta( $assignment_id, 'academy_assignment_drip_content', true );
		if ( empty( $drip_content ) ) {
			$drip_content = $this->get_default_value();
		}
		$this->drip_content_checker( $drip_content, $course_id, 'assignment', $assignment_id );
	}

	public function render_drip_booking( $course_id, $booking_id ) {
		$drip_content = get_post_meta( $booking_id, '_academy_booking_drip_content', true );
		if ( empty( $drip_content ) ) {
			$drip_content = $this->get_default_value();
		}
		$this->drip_content_checker( $drip_content, $course_id, 'booking', $booking_id );
	}

	public function get_default_value() {
		return [
			'schedule_by_date' => '',
			'schedule_by_enroll_date' => 0,
			'schedule_by_prerequisite' => [],
		];
	}
	public function drip_content_checker( $drip_content, $course_id, $type, $topic_id ) {
		$drip_content_enabled = (bool) get_post_meta( $course_id, 'academy_course_drip_content_enabled', true );
		if ( $drip_content_enabled ) {
			$drip_content_type = get_post_meta( $course_id, 'academy_course_drip_content_type', true );
			$message = '';
			// check drip content isset or not
			if ( 'schedule_by_date' === $drip_content_type && $drip_content['schedule_by_date'] ) {
				if ( strtotime( $drip_content['schedule_by_date'] ) > wp_date( 'U' ) ) {
					$message = __( 'This content will be accessible on', 'academy-pro' ) . ' ' . gmdate( get_option( 'date_format' ), strtotime( $drip_content['schedule_by_date'] ) );
					wp_send_json_error( array(
						'message' => $message,
						'type' => $type
					) );
				}
			} elseif ( 'schedule_by_enroll_date' === $drip_content_type && $drip_content['schedule_by_enroll_date'] ) {
				$enrolled    = \Academy\Helper::is_enrolled( $course_id, get_current_user_id() );
				$content_available_date = strtotime( "$enrolled->post_date +" . $drip_content['schedule_by_enroll_date'] . ' days' );
				if ( $content_available_date > wp_date( 'U' ) ) {
					$message = __( 'This content will be accessible on', 'academy-pro' ) . ' ' . gmdate( get_option( 'date_format' ), $content_available_date );
					wp_send_json_error( array(
						'message' => $message,
						'type' => $type
					) );
				}
			} elseif ( 'schedule_by_sequentially' === $drip_content_type ) {
				if ( true !== Helper::is_complete_previous_topic( $course_id, $topic_id, $type ) ) {
					wp_send_json_error( array(
						'message' => __( 'You must complete the previous topic to proceed.', 'academy-pro' ),
						'type' => $type
					) );
				}
			} elseif ( 'schedule_by_prerequisite' === $drip_content_type && count( $drip_content['schedule_by_prerequisite'] ) ) {
				$completed_topics_lists = (array) json_decode( get_user_meta( get_current_user_id(), 'academy_course_' . $course_id . '_completed_topics', true ), true );
				$have_to_complete = array();
				if ( is_array( $drip_content['schedule_by_prerequisite'] ) ) {
					foreach ( $drip_content['schedule_by_prerequisite'] as $prerequisite_item ) {
						$is_completed = ( isset( $completed_topics_lists[ $prerequisite_item['type'] ][ $prerequisite_item['value'] ] ) ? $completed_topics_lists[ $prerequisite_item['type'] ][ $prerequisite_item['value'] ] : '' );
						if ( ! $is_completed ) {
							$have_to_complete[] = $prerequisite_item;
						}
					}
				}

				if ( count( $have_to_complete ) ) {
					wp_send_json_error( array(
						'message' => __( 'To access this topic, finish the following prerequisites.', 'academy-pro' ),
						'type' => $type,
						'data' => $have_to_complete,
					) );
				}
			}//end if
		}//end if
	}
}
