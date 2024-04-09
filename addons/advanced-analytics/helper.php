<?php
namespace AcademyProAdvancedAnalytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {
	public static function get_total_number_of_quizzes() {
		global $wpdb;
		$results = $wpdb->get_var(
			$wpdb->prepare("SELECT COUNT(ID) 
            FROM {$wpdb->posts} 
            WHERE post_type = %s 
            AND post_status = %s", 'academy_quiz', 'publish')
		);
		return (int) $results;
	}

	public static function get_total_number_of_completed_course() {
		global $wpdb;
		$results = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(comment_ID)
			FROM	{$wpdb->comments} 
			WHERE 	comment_agent = %s 
					AND comment_type = %s 
			",
				'academy',
				'course_completed'
			)
		);
		return (int) $results;
	}

	public static function get_earnings_analytics() {
		global $wpdb;
		$from    = gmdate( 'Y-m-d', strtotime( ' - 30 days' ) );
		$to      = gmdate( 'Y-m-d', strtotime( ' + 1 days' ) );
		$earnings = $wpdb->get_results(
			$wpdb->prepare("SELECT  SUM(admin_amount) AS total,
			DATE(created_at) AS date_format
			FROM	{$wpdb->prefix}academy_earnings
			WHERE 	order_status = %s
			AND (created_at BETWEEN %s AND %s )
			GROUP BY date_format
			ORDER BY created_at ASC;", 'completed', $from, $to)
		);
		$analytics = new \Academy\Classes\Analytics();
		$earnings = $analytics->format_query_results_to_chart_data( $from, $to, $earnings );
		return $earnings;
	}

	public static function get_refunds_analytics() {
		global $wpdb;
		$from    = gmdate( 'Y-m-d', strtotime( ' - 30 days' ) );
		$to      = gmdate( 'Y-m-d', strtotime( ' + 1 days' ) );
		$refunds = $wpdb->get_results(
			$wpdb->prepare("SELECT  SUM(wc_order_details.total_sales) AS total,
			DATE(wc_order_details.date_created) AS date_format
			FROM {$wpdb->posts} AS post
					INNER JOIN {$wpdb->postmeta} as postmeta ON postmeta.post_id = post.ID
					INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS wc_order ON wc_order.product_id = postmeta.meta_value
					INNER JOIN {$wpdb->prefix}wc_order_stats AS wc_order_details ON wc_order_details.order_id = wc_order.order_id
			WHERE postmeta.meta_key = %s
			AND post.post_type = %s
			AND post.post_status = %s
			AND wc_order_details.status = %s
			AND (wc_order_details.date_created BETWEEN %s AND %s )
			GROUP BY date_format
			ORDER BY wc_order_details.date_created ASC;", 'academy_course_product_id', 'academy_courses', 'publish', 'wc-refunded', $from, $to)
		);
		$analytics = new \Academy\Classes\Analytics();
		$refunds = $analytics->format_query_results_to_chart_data( $from, $to, $refunds );
		return $refunds;
	}

	public static function get_discounts_analytics() {
		global $wpdb;
		$from    = gmdate( 'Y-m-d', strtotime( ' - 30 days' ) );
		$to      = gmdate( 'Y-m-d', strtotime( ' + 1 days' ) );

		$discounts = $wpdb->get_results(
			$wpdb->prepare("SELECT  SUM(wc_order.coupon_amount) AS total,
			DATE(wc_order_details.date_created) AS date_format
			FROM {$wpdb->posts} AS post
					INNER JOIN {$wpdb->postmeta} as postmeta ON postmeta.post_id = post.ID
					INNER JOIN {$wpdb->prefix}wc_order_product_lookup AS wc_order ON wc_order.product_id = postmeta.meta_value
					INNER JOIN {$wpdb->prefix}wc_order_stats AS wc_order_details ON wc_order_details.order_id = wc_order.order_id
			WHERE postmeta.meta_key = %s
			AND post.post_type = %s
			AND post.post_status = %s
			AND wc_order_details.status = %s
			AND (wc_order_details.date_created BETWEEN %s AND %s )
			GROUP BY date_format
			ORDER BY wc_order_details.date_created ASC;", 'academy_course_product_id', 'academy_courses', 'publish', 'wc-completed', $from, $to)
		);
		$analytics = new \Academy\Classes\Analytics();
		$discounts = $analytics->format_query_results_to_chart_data( $from, $to, $discounts );
		return $discounts;
	}

	public static function get_popular_courses() {
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT COUNT(enrolled.ID) AS enrolled_count,
				enrolled.post_parent as parent_course_id,
				course.ID,
				course.post_title,
				course.post_author,
				course.post_date,
				course.post_date_gmt
            FROM {$wpdb->posts} enrolled
				INNER JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
            WHERE enrolled.post_type =%s
				AND enrolled.post_status =%s
				AND course.post_type =%s
            GROUP BY parent_course_id ORDER BY enrolled_count DESC LIMIT 0, %d;", 'academy_enrolled', 'completed', 'academy_courses', 10)
		);
		return $results;
	}

	public static function get_recent_enrolled_courses() {
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT MAX(enrolled.post_date) AS enrolled_date,
				enrolled.post_parent as parent_course_id,
				course.ID,
				course.post_title,
				course.post_author,
				course.post_date,
				course.post_date_gmt
            FROM {$wpdb->posts} enrolled
				INNER JOIN {$wpdb->posts} course ON enrolled.post_parent = course.ID
            WHERE enrolled.post_type =%s
				AND enrolled.post_status =%s
				AND course.post_type =%s
            GROUP BY parent_course_id ORDER BY enrolled_date DESC LIMIT 0, %d;", 'academy_enrolled', 'completed', 'academy_courses', 10)
		);
		return $results;
	}

	public static function get_recent_reviews() {
		global $wpdb;
		$reviews = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$wpdb->comments}.comment_ID, 
					{$wpdb->comments}.comment_post_ID, 
					{$wpdb->comments}.comment_author, 
					{$wpdb->comments}.comment_author_email, 
					{$wpdb->comments}.comment_date, 
					{$wpdb->comments}.comment_content, 
					{$wpdb->comments}.user_id, 
					{$wpdb->commentmeta}.meta_value AS rating,
					{$wpdb->users}.display_name 
			
			FROM 	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta} 
					ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
					LEFT JOIN {$wpdb->users}
					ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE comment_type = 'academy_courses' AND meta_key = 'academy_rating'
			ORDER BY comment_ID DESC
			LIMIT 	%d, %d;
			",
				0,
				10
			)
		);
		return $reviews;
	}

	public static function get_recent_registered_students() {
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, display_name, user_nicename, user_email, user_registered
			FROM 	{$wpdb->users} 
					INNER JOIN {$wpdb->usermeta} 
							ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id )
			WHERE 	{$wpdb->usermeta}.meta_key = %s ORDER BY ID DESC 
			LIMIT 0, %d;",
				'is_academy_student',
				10
			)
		);
		return $results;
	}

	public static function get_recent_registered_instructors() {
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, display_name, user_nicename, user_email, user_registered
			FROM 	{$wpdb->users} 
					INNER JOIN {$wpdb->usermeta} 
							ON ( {$wpdb->users}.ID = {$wpdb->usermeta}.user_id )
			WHERE 	{$wpdb->usermeta}.meta_key = %s ORDER BY ID DESC 
			LIMIT 0, %d;",
				'is_academy_instructor',
				10
			)
		);
		return $results;
	}
}
