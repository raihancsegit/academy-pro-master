<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Archive Course Page
 */
add_action( 'academy_pro/templates/booking/archive_booking_header', 'academy_pro_archive_booking_header', 10 );
add_action( 'academy_pro/templates/booking/archive_booking_content', 'academy_pro_archive_booking_content', 10 );
add_action( 'academy_pro/templates/booking/archive_booking_description', 'academy_archive_booking_header_filter', 10 );
add_action( 'academy_pro/templates/booking/no_booking_found', 'academy_pro_archive_no_booking_found', 10 );
add_action( 'academy_pro/templates/booking/after_course_loop', 'academy_pro_archive_booking_pagination', 10 );
add_action( 'academy_pro/templates/booking/archive_booking_sidebar', 'academy_pro_archive_booking_sidebar', 10 );
add_action( 'academy_pro/templates/booking/archive/booking_sidebar_content', 'academy_pro_archive_booking_filter_widget' );

/**
 * Booking Loop
 */
add_action( 'academy_pro/templates/booking/booking_loop_header', 'academy_pro_booking_loop_header', 10 );
add_action( 'academy_pro/templates/booking/booking_loop_content', 'academy_pro_booking_loop_content', 11 );
add_action( 'academy_pro/templates/booking/booking_loop_footer', 'academy_pro_booking_loop_footer', 12 );
add_action( 'academy_pro/templates/booking/booking_loop_footer_inner', 'academy_pro_booking_loop_rating', 12 );
add_action( 'academy_pro/templates/booking/booking_loop_footer_inner', 'academy_pro_booking_loop_footer_inner_price', 12 );

/**
 * Booking Details Page
 */
add_action( 'academy_pro/templates/booking/single_booking_sidebar', 'academy_pro_single_booking_sidebar', 10 );
add_action( 'academy_pro/templates/booking/single_booking_content', 'academy_pro_single_booking_header', 10 );
add_action( 'academy_pro/templates/booking/single_booking_content', 'academy_pro_single_booking_instructor', 15 );
add_action( 'academy_pro/templates/booking/single_booking_content', 'academy_pro_single_booking_description', 20 );
add_action( 'academy_pro/templates/booking/single_booking_content', 'academy_pro_single_booking_calendar', 35 );
add_action( 'academy_pro/templates/booking/single_booking_content', 'academy_pro_single_booking_feedback', 40 );
add_action( 'academy_pro/templates/booking/single_booking_content', 'academy_pro_single_booking_reviews', 45 );

/**
 * Review
 */
add_action( 'academy_pro/templates/booking/review_thumbnail', 'academy_pro_review_display_gravatar' );
add_action( 'academy_pro/templates/booking/review_thumbnail', 'academy_pro_review_display_rating' );
add_action( 'academy_pro/templates/booking/review_meta', 'academy_pro_review_display_meta' );
add_action( 'academy_pro/templates/booking/review_comment_text', 'academy_pro_review_display_comment_text' );


/**
 * Booking Widgets
 */
add_action( 'academy_pro/templates/booking/single_booking_sidebar_widgets', 'academy_pro_booking_widget' );
