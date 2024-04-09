<?php
namespace AcademyProEnrollment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {
	public static function cancel_enroll( $course_id, $user_id ) {
		if ( ! $course_id ) {
			return false;
		}
		global $wpdb;

		$enroll_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID
			FROM 	{$wpdb->posts}
			WHERE 	post_type = %s
					AND post_status = %s
					AND post_parent = %s
					AND post_author = %d;
			",
				'academy_enrolled',
				'completed',
				$course_id,
				$user_id
			)
		);
		$deleted_enroll_ids = [];
		if ( is_array( $enroll_ids ) ) {
			foreach ( $enroll_ids as $enroll_id ) {
				do_action( 'academy_pro_enrollment/course/before_cancel_enroll', $course_id, $enroll_id, $user_id );
				$is_deleted = wp_delete_post( $enroll_id, true );
				if ( $is_deleted ) {
					$deleted_enroll_ids[] = $is_deleted;
					delete_post_meta( $enroll_id, 'academy_enrolled_by_order_id' );
					delete_post_meta( $enroll_id, 'academy_enrolled_by_product_id' );
					do_action( 'academy_pro_enrollment/course/after_cancel_enroll', $course_id, $enroll_id, $user_id );
				}
			}
		}
		return $deleted_enroll_ids;
	}
}
