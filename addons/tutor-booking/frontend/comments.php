<?php
namespace AcademyProTutorBooking\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Comments {
	public static function init() {
		$self = new self();
		add_action( 'comment_post', array( $self, 'add_comment_rating' ), 1 );

	}

	/**
	 * Rating field for comments.
	 *
	 * @param int $comment_id Comment ID.
	 */
	public function add_comment_rating( $comment_id ) {
		if ( isset( $_POST['comment_post_ID'] ) && 'academy_booking' === get_post_type( absint( $_POST['comment_post_ID'] ) ) ) { // phpcs:ignore input var ok, CSRF ok.
			wp_update_comment(
				[
					'comment_ID'   => $comment_id,
					'comment_type' => 'academy_booking',
				]
			);

			if ( ! $_POST['academy_rating'] || $_POST['academy_rating'] > 5 || $_POST['academy_rating'] < 0 ) { // phpcs:ignore input var ok, CSRF ok.
				return;
			}

			if ( isset( $_POST['academy_rating'] ) ) {
				add_comment_meta( $comment_id, 'academy_rating', intval( $_POST['academy_rating'] ), true ); // phpcs:ignore input var ok, CSRF ok.
			}
		}
	}
}
