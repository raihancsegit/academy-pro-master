<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Academy Templates Related all functions write here
 */

if ( ! function_exists( 'academy_pro_archive_booking_header' ) ) {
	function academy_pro_archive_booking_header() {
		\AcademyPro\Helper::get_template( 'booking/archive/header.php' );
	}
}

if ( ! function_exists( 'academy_pro_archive_booking_header_filter' ) ) {
	function academy_pro_archive_booking_header_filter() {
		global $wp_query;
		$orderby = ( get_query_var( 'orderby' ) ) ? get_query_var( 'orderby' ) : ''; ?>
		<div class="academy-bookings__header-filter">
			<p class="academy-bookings__header-result-count"><?php esc_html_e( 'Showing all', 'academy-pro' ); ?>
				<span><?php echo esc_html( $wp_query->found_posts ); ?></span> <?php esc_html_e( 'results', 'academy-pro' ); ?>
			</p>
			<form class="academy-bookings__header-ordering" method="get">
				<select name="orderby" class="academy-bookings__header-orderby" aria-label="Course order"
					onchange="this.form.submit()">
					<option value="" <?php selected( $orderby, '' ); ?>>
						<?php esc_html_e( 'Default Sorting', 'academy-pro' ); ?>
					</option>
					<option value="name" <?php selected( $orderby, 'name' ); ?>>
						<?php esc_html_e( 'Order by course name', 'academy-pro' ); ?>
					</option>
					<option value="date" <?php selected( $orderby, 'date' ); ?>>
						<?php esc_html_e( 'Order by Release Date', 'academy-pro' ); ?>
					</option>
					<option value="modified" <?php selected( $orderby, 'modified' ); ?>>
						<?php esc_html_e( 'Order by Modified Date', 'academy-pro' ); ?>
					</option>
				</select>
				<input type="hidden" name="paged" value="1">
			</form>
		</div>
		<?php
	}
}//end if

if ( ! function_exists( 'academy_pro_archive_booking_content' ) ) {
	function academy_pro_archive_booking_content() {
		\AcademyPro\Helper::get_template( 'booking/bookings.php' );
	}
}

if ( ! function_exists( 'academy_pro_archive_no_booking_found' ) ) {
	function academy_pro_archive_no_booking_found() {
		\AcademyPro\Helper::get_template( 'booking/archive/booking-none.php' );
	}
}

if ( ! function_exists( 'academy_pro_archive_booking_pagination' ) ) {
	function academy_pro_archive_booking_pagination() {
		\AcademyPro\Helper::get_template( 'booking/archive/pagination.php' );
	}
}

if ( ! function_exists( 'academy_pro_archive_booking_sidebar' ) ) {
	function academy_pro_archive_booking_sidebar() {
		\AcademyPro\Helper::get_template( 'booking/archive/sidebar.php' );
	}
}

if ( ! function_exists( 'academy_archive_booking_header_filter' ) ) {
	function academy_archive_booking_header_filter() {
		global $wp_query;
		$orderby = ( get_query_var( 'orderby' ) ) ? get_query_var( 'orderby' ) : ''; ?>
		<div class="academy-bookings__header-filter">
			<p class="academy-bookings__header-result-count"><?php esc_html_e( 'Showing all', 'academy-pro' ); ?>
				<span><?php echo esc_html( $wp_query->found_posts ); ?></span> <?php esc_html_e( 'results', 'academy-pro' ); ?>
			</p>
			<form class="academy-bookings__header-ordering" method="get">
				<select name="orderby" class="academy-bookings__header-orderby" aria-label="Booking order"
					onchange="this.form.submit()">
					<option value="" <?php selected( $orderby, '' ); ?>>
						<?php esc_html_e( 'Default Sorting', 'academy-pro' ); ?>
					</option>
					<option value="name" <?php selected( $orderby, 'name' ); ?>>
						<?php esc_html_e( 'Order by Booking name', 'academy-pro' ); ?>
					</option>
					<option value="date" <?php selected( $orderby, 'date' ); ?>>
						<?php esc_html_e( 'Order by Release Date', 'academy-pro' ); ?>
					</option>
					<option value="modified" <?php selected( $orderby, 'modified' ); ?>>
						<?php esc_html_e( 'Order by Modified Date', 'academy-pro' ); ?>
					</option>
				</select>
				<input type="hidden" name="paged" value="1">
			</form>
		</div>
		<?php
	}
}//end if

if ( ! function_exists( 'academy_pro_archive_booking_filter_widget' ) ) {
	function academy_pro_archive_booking_filter_widget() {
		$filters = \Academy\Helper::get_customizer_settings(
			'archive_booking_filters',
			array(
				'items' =>
					array(
						'search'        => 1,
						'category'      => 1,
						'tags'          => 1,
						'type'          => 1,
						'class_type'    => 1,
					),
			)
		);

		$filters = apply_filters( 'academy_pro/booking/archive/booking_filter_widget_args', $filters['items'] );

		foreach ( $filters as $key => $value ) {
			$filter_function = 'academy_pro_archive_booking_filter_by_' . $key;
			if ( $value && function_exists( $filter_function ) ) {
				$filter_function();
			}
		}
	}
}//end if



if ( ! function_exists( 'academy_pro_archive_booking_filter_by_search' ) ) {
	function academy_pro_archive_booking_filter_by_search() {
		\AcademyPro\Helper::get_template( 'booking/archive/widgets/search.php', apply_filters( 'academy_pro/booking/archive/booking_filter_by_search_args', [] ) );
	}
}

if ( ! function_exists( 'academy_pro_archive_booking_filter_by_category' ) ) {
	function academy_pro_archive_booking_filter_by_category() {
		$categories = AcademyProTutorBooking\Helper::get_all_booking_category_lists();
		\AcademyPro\Helper::get_template(
			'booking/archive/widgets/category.php',
			apply_filters(
				'academy_pro/booking/archive/booking_filter_by_category_args',
				[
					'categories' => $categories,
				]
			)
		);
	}
}

if ( ! function_exists( 'academy_pro_archive_booking_filter_by_tags' ) ) {
	function academy_pro_archive_booking_filter_by_tags() {
		$tags = get_terms(
			array(
				'taxonomy'   => 'academy_booking_tag',
				'hide_empty' => true,
			)
		);

		\AcademyPro\Helper::get_template(
			'booking/archive/widgets/tags.php',
			apply_filters(
				'academy_pro/booking/archive/booking_filter_by_tags_args',
				[
					'tags' => $tags,
				]
			)
		);
	}
}//end if

if ( ! function_exists( 'academy_pro_archive_booking_filter_by_type' ) ) {
	function academy_pro_archive_booking_filter_by_type() {
		$type = array(
			'free' => __( 'Free', 'academy-pro' ),
			'paid' => __( 'Paid', 'academy-pro' ),
		);
		\AcademyPro\Helper::get_template(
			'booking/archive/widgets/type.php',
			apply_filters(
				'academy_pro/booking/archive/booking_filter_by_type_args',
				[
					'type' => $type,
				]
			)
		);
	}
}

if ( ! function_exists( 'academy_pro_archive_booking_filter_by_class_type' ) ) {
	function academy_pro_archive_booking_filter_by_class_type() {
		$type = array(
			'live'          => __( 'Live Class', 'academy-pro' ),
			'pre-recorded' => __( 'Pre-Recorded', 'academy-pro' ),
		);
		\AcademyPro\Helper::get_template(
			'booking/archive/widgets/class-type.php',
			apply_filters(
				'academy_pro/booking/archive/booking_filter_by_class_type_args',
				[
					'type' => $type,
				]
			)
		);
	}
}

if ( ! function_exists( 'academy_pro_booking_loop_header' ) ) {
	function academy_pro_booking_loop_header() {
		\AcademyPro\Helper::get_template( 'booking/loop/header.php' );
	}
}

if ( ! function_exists( 'academy_pro_booking_loop_content' ) ) {
	function academy_pro_booking_loop_content() {
		\AcademyPro\Helper::get_template( 'booking/loop/content.php' );
	}
}

if ( ! function_exists( 'academy_pro_booking_loop_footer' ) ) {
	function academy_pro_booking_loop_footer() {
		\AcademyPro\Helper::get_template( 'booking/loop/footer.php' );
	}
}

if ( ! function_exists( 'academy_pro_booking_loop_rating' ) ) {
	function academy_pro_booking_loop_rating() {
		$rating = \AcademyProTutorBooking\Helper::get_booking_rating( get_the_ID() );
		$reviews_status = \Academy\Helper::get_customizer_settings( 'course_reviews_status', 'on' );
		if ( 'on' === $reviews_status ) {
			\AcademyPro\Helper::get_template( 'booking/loop/rating.php', [ 'rating' => $rating ] );
		}
	}
}

if ( ! function_exists( 'academy_pro_booking_loop_footer_inner_price' ) ) {
	function academy_pro_booking_loop_footer_inner_price() {
		$course_id = get_the_ID();
		$is_paid   = \AcademyProTutorBooking\Helper::is_booking_purchasable( $course_id );
		$price     = '';
		if ( \Academy\Helper::is_active_woocommerce() && $is_paid ) {
			$product_id = AcademyProTutorBooking\Helper::get_booked_product_id( $course_id );
			if ( $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$price   = $product->get_price_html();
				}
			}
		}
		\AcademyPro\Helper::get_template(
			'booking/loop/price.php',
			array(
				'price'   => $price,
				'is_paid' => $is_paid,
			)
		);
	}
}//end if


if ( ! function_exists( 'academy_pro_single_booking_sidebar' ) ) {
	function academy_pro_single_booking_sidebar() {
		\AcademyPro\Helper::get_template( 'booking/single-booking/sidebar.php' );
	}
}

if ( ! function_exists( 'academy_pro_booking_widget' ) ) {
	function academy_pro_booking_widget() {
		$booking_id   = get_the_ID();
		$is_paid     = (bool) \AcademyProTutorBooking\Helper::is_booking_purchasable( $booking_id );
		$price       = '';
		if ( $is_paid && \Academy\Helper::is_active_woocommerce() ) {
			$product_id = AcademyProTutorBooking\Helper::get_booked_product_id( $booking_id );
			if ( $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$price   = $product->get_price_html();
				}
			}
		}

		$class_types = get_post_meta( $booking_id, '_academy_booking_class_type', true );
		$schedule_type = get_post_meta( $booking_id, '_academy_booking_schedule_type', true );
		$duration = get_post_meta( $booking_id, '_academy_booking_duration', true );

		$my_booking_page_url = Academy\Helper::get_page_permalink( 'frontend_dashboard_page' ) . '/#/my-booking';

		\AcademyPro\Helper::get_template( 'booking/single-booking/booking.php', array(
			'is_paid'           => $is_paid,
			'price'             => $price,
			'my_booking_page_url'   => $my_booking_page_url,
			'class_types' => $class_types,
			'schedule_type' => $schedule_type,
			'duration' => $duration,
		) );
	}
}//end if

if ( ! function_exists( 'academy_pro_single_booking_calendar' ) ) {
	function academy_pro_single_booking_calendar() {
		\AcademyPro\Helper::get_template( 'booking/single-booking/booking-calendar.php' );
	}
}

if ( ! function_exists( 'academy_pro_single_booking_header' ) ) {
	function academy_pro_single_booking_header() {

		\AcademyPro\Helper::get_template(
			'booking/single-booking/header.php'
		);
	}
}

if ( ! function_exists( 'academy_pro_single_booking_instructor' ) ) {
	function academy_pro_single_booking_instructor() {
		global $post;
		$author_id = $post->post_author;
		$instructors = \Academy\Helper::get_instructor_by_author_id( $author_id );
		\AcademyPro\Helper::get_template(
			'booking/single-booking/instructor.php',
			apply_filters(
				'academy_pro/booking/single_booking_content_instructor_args',
				[
					'instructors' => $instructors,
				]
			)
		);
	}
}

if ( ! function_exists( 'academy_pro_single_booking_description' ) ) {
	function academy_pro_single_booking_description() {
		\AcademyPro\Helper::get_template( 'booking/single-booking/description.php' );
	}
}

if ( ! function_exists( 'academy_pro_single_booking_feedback' ) ) {
	function academy_pro_single_booking_feedback() {
		$rating = \AcademyProTutorBooking\Helper::get_booking_rating( get_the_ID() );
		\AcademyPro\Helper::get_template( 'booking/single-booking/feedback.php', array( 'rating' => $rating ) );
	}
}

if ( ! function_exists( 'academy_pro_single_booking_reviews' ) ) {
	function academy_pro_single_booking_reviews() {
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	}
}

if ( ! function_exists( 'academy_pro_single_booking_review_form' ) ) {
	function academy_pro_single_booking_review_form() {
		$has_booked = \AcademyProTutorBooking\Helper::get_user_booked_ids_by_booking_id( get_the_ID(), get_current_user_ID() );
		\AcademyPro\Helper::get_template( 'booking/single-booking/review-form.php', array(
			'has_booked' => $has_booked
		) );
	}
}


if ( ! function_exists( 'academy_pro_review_display_gravatar' ) ) {
	/**
	 * Display the review authors gravatar
	 *
	 * @param array $comment WP_Comment.
	 * @return void
	 */
	function academy_pro_review_display_gravatar( $comment ) {
		echo get_avatar( $comment->comment_author_email, apply_filters( 'academy/review_gravatar_size', '80' ), '' );
	}
}

if ( ! function_exists( 'academy_pro_review_display_rating' ) ) {
	/**
	 * Display the reviewers star rating
	 *
	 * @return void
	 */
	function academy_pro_review_display_rating() {
		if ( post_type_supports( 'academy_booking', 'comments' ) ) {
			\AcademyPro\Helper::get_template( 'booking/single-booking/review-rating.php' );
		}
	}
}

if ( ! function_exists( 'academy_pro_review_display_meta' ) ) {
	/**
	 * Display the review authors meta (name, verified owner, review date)
	 *
	 * @return void
	 */
	function academy_pro_review_display_meta() {
		\AcademyPro\Helper::get_template( 'booking/single-booking/review-meta.php' );
	}
}


if ( ! function_exists( 'academy_pro_review_display_comment_text' ) ) {

	/**
	 * Display the review content.
	 */
	function academy_pro_review_display_comment_text() {
		echo '<div class="academy-review-description">';
		comment_text();
		echo '</div>';
	}
}
