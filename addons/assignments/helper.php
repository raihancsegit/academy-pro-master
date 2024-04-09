<?php
namespace AcademyProAssignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {
	public static function prepare_submitted_assignment_item( $submitted_assignment ) {
		$submitted_assignment->post_title = get_the_title( $submitted_assignment->comment_post_ID );
		$submitted_assignment->academy_assignment_settings = get_post_meta( $submitted_assignment->comment_post_ID, 'academy_assignment_settings', true );
		$submitted_assignment->meta = array(
			'academy_pro_assignment_start_time' => get_comment_meta( $submitted_assignment->comment_ID, 'academy_pro_assignment_start_time', true ),
			'academy_pro_assignment_end_time' => get_comment_meta( $submitted_assignment->comment_ID, 'academy_pro_assignment_end_time', true ),
			'academy_pro_assignment_attachment' => (int) get_comment_meta( $submitted_assignment->comment_ID, 'academy_pro_assignment_attachment', true ),
			'academy_pro_assignment_evaluate_point' => (int) get_comment_meta( $submitted_assignment->comment_ID, 'academy_pro_assignment_evaluate_point', true ),
			'academy_pro_assignment_evaluate_feedback' => get_comment_meta( $submitted_assignment->comment_ID, 'academy_pro_assignment_evaluate_feedback', true ),
		);
		return $submitted_assignment;
	}
	public static function prepare_submitted_assignment_items( $submitted_assignments ) {
		$response = [];
		foreach ( $submitted_assignments as $submitted_assignment ) {
			$response[] = self::prepare_submitted_assignment_item( $submitted_assignment );
		}
		return $response;
	}
	public static function get_hours_from_submission_time( $number, $unit ) {
		$hours = $number;
		if ( 'weeks' === $unit ) {
			$hours = $number * 7 * 24;
			return $hours .= ' hours';
		} elseif ( 'days' === $unit ) {
			$hours = $number * 24;
			return $hours .= ' hours';
		}
		if ( $hours <= 1 ) {
			return $hours . ' hour';
		}
		return $hours . ' hours';
	}
	public static function is_assignment_submission_expired( $assignment, $time_zone ) {
		if ( $assignment->submitted_assignment && 'submitting' === $assignment->submitted_assignment->comment_approved ) {
			$assignment_duration_hours = self::get_hours_from_submission_time( $assignment->settings['submission_time'], $assignment->settings['submission_time_unit'] );
			$assignment_last_submission_time = strtotime( $assignment->submitted_assignment->meta['academy_pro_assignment_start_time'] . "+ $assignment_duration_hours" );
			$current_time = strtotime( wp_date( 'Y-m-d H:i:s', null, new \DateTimeZone( $time_zone ) ) );
			$assignment->last_submission_timestamp = $assignment_last_submission_time;
			if ( $assignment_last_submission_time < $current_time ) {
				return true;
			}
		}
		return false;
	}
	public static function has_submitted_assignment( $course_id, $user_id ) {
		$submitted_assignments = get_comments(array(
			'post_id' => $course_id,
			'comment_type' => 'academy_assignments',
			'user_id'   => $user_id,
			'status' => 'submitted',
			'number' => 1
		));
		return $submitted_assignments;
	}
}
