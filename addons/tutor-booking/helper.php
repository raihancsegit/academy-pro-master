<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {
	public static function is_booking_taxonomy() {
		return is_tax( get_object_taxonomies( 'academy_booking' ) );
	}
	public static function get_all_booking_category_lists() {
		$categories = get_terms(
			array(
				'taxonomy'   => 'academy_booking_category',
				'hide_empty' => true,
			)
		);
		return \academy\Helper::prepare_category_results( $categories );
	}

	public static function get_booking_type( $booking_id ) {
		return get_post_meta( $booking_id, '_academy_booking_type', true );
	}
	public static function is_booking_purchasable( $booking_id ) {
		$booking_type = self::get_booking_type( $booking_id );
		if ( 'paid' === $booking_type ) {
			return apply_filters( 'academy_pro/booking/is_booking_purchasable', true, $booking_id );
		}
		return apply_filters( 'academy_pro/booking/is_booking_purchasable', false, $booking_id );
	}

	public static function get_booked_product_id( $booking_id ) {
		$product_id = (int) get_post_meta( $booking_id, '_academy_booking_product_id', true );
		return apply_filters( 'academy_pro/booking/get_booking_product_id', $product_id, $booking_id );
	}

	public static function product_belongs_with_booking( $product_id ) {
		global $wpdb;

		$query = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT *
			FROM 	{$wpdb->postmeta}
			WHERE	meta_key = %s 
					AND meta_value = %d 
			limit 1
			",
				'_academy_booking_product_id',
				$product_id
			)
		);

		return $query;
	}

	public static function get_user_booked_ids_by_booking_id( $booking_id, $user_id ) {
		global $wpdb;
		$course_ids = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID
			FROM 	{$wpdb->posts}
			WHERE 	post_type = %s
					AND post_parent = %d
					AND post_author = %d
					AND post_status = %s;
			",
				'academy_booked',
				$booking_id,
				$user_id,
				'completed'
			)
		);

		return $course_ids;
	}

	public static function is_booked( $booking_id, $user_id ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		global $wpdb;

		do_action( 'academy_pro/booking/is_booked_before', $booking_id, $user_id );

		$getBooked = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID,
					post_author,
					post_date,
					post_date_gmt,
					post_title
			FROM 	{$wpdb->posts}
			WHERE 	post_type = %s
					AND post_parent = %d
					AND post_author = %d
					AND post_status = %s;
			",
				'academy_booked',
				$booking_id,
				$user_id,
				'completed'
			)
		);

		if ( $getBooked ) {
			return apply_filters( 'academy_pro/booking/is_booked', $getBooked, $booking_id, $user_id );
		}

		return false;
	}

	public static function do_booked( $booking_id, $user_id, $schedule_date_time, $order_id = 0 ) {
		if ( ! $booking_id || ! $user_id ) {
			return false;
		}

		do_action( 'academy_pro/booking/before_booked', $booking_id );

		$title = __( 'Booking Booked', 'academy-pro' ) . ' &ndash; ' . gmdate( get_option( 'date_format' ) ) . ' @ ' . gmdate( get_option( 'time_format' ) );

		$booking_status = 'completed';

		$booked_data = apply_filters(
			'academy_pro/booking/booked_data',
			array(
				'post_type'   => 'academy_booked',
				'post_title'  => $title,
				'post_status' => $booking_status,
				'post_author' => $user_id,
				'post_parent' => $booking_id,
			)
		);

		// Insert the post into the database
		$booked_id = wp_insert_post( $booked_data );
		if ( $booked_id ) {
			do_action( 'academy_pro/booking/after_booked', $booking_id, $booked_id, $user_id );
			if ( $schedule_date_time ) {
				update_post_meta( $booked_id, '_academy_booked_schedule_time', $schedule_date_time );
			}

			if ( $order_id ) {
				$product_id = self::get_booked_product_id( $booking_id );
				update_post_meta( $booked_id, '_academy_booked_by_order_id', $order_id );
				update_post_meta( $booked_id, '_academy_booked_by_product_id', $product_id );
				update_post_meta( $order_id, '_is_academy_order_for_booking', \Academy\Helper::get_time() );
				update_post_meta( $order_id, '_academy_order_for_booking_id_' . $booking_id, $booked_id );
			}

			return true;
		}
		return false;
	}
	public static function get_booking_calendar_settings( $booking_id ) {
		$schedule_type = get_post_meta( $booking_id, '_academy_booking_schedule_type', true );
		$schedule_time = get_post_meta( $booking_id, '_academy_booking_schedule_time', true );
		$duration = get_post_meta( $booking_id, '_academy_booking_duration', true );
		$time_zone = get_post_meta( $booking_id, '_academy_booking_schedule_time_zone', true );
		$schedule_repeated_times = get_post_meta( $booking_id, '_academy_booking_schedule_repeated_times', true );
		$booked = array();
		$all_booked_ids = self::get_user_booked_ids_by_booking_id( $booking_id, get_current_user_ID() );
		foreach ( $all_booked_ids as $booked_item ) {
			$booked[] = array(
				'ID'            => $booked_item->ID,
				'schedule_time' => get_post_meta( $booked_item->ID, '_academy_booked_schedule_time', true )
			);
		}
		return wp_json_encode(
			array(
				'schedule_type'             => $schedule_type,
				'schedule_time'             => $schedule_time,
				'duration'                  => $duration,
				'time_zone'                 => $time_zone,
				'schedule_repeated_times'   => $schedule_repeated_times,
				'booked'                    => $booked
			)
		);
	}
	public static function get_the_booking_category( $ID ) {
		return get_the_terms( $ID, 'academy_booking_category' );
	}
	public static function get_booking_rating( $course_id ) {
		global $wpdb;

		$ratings = array(
			'rating_count'   => 0,
			'rating_sum'     => 0,
			'rating_avg'     => 0.00,
			'count_by_value' => array(
				5 => 0,
				4 => 0,
				3 => 0,
				2 => 0,
				1 => 0,
			),
		);

		$rating = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(meta_value) AS rating_count,
					SUM(meta_value) AS rating_sum
			FROM	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta}
							ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
			WHERE 	{$wpdb->comments}.comment_post_ID = %d
					AND {$wpdb->comments}.comment_type = %s
					AND meta_key = %s;
			",
				$course_id,
				'academy_booking',
				'academy_rating'
			)
		);

		if ( $rating->rating_count ) {
			$avg_rating = number_format( ( $rating->rating_sum / $rating->rating_count ), 1 );

			$stars = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT commentmeta.meta_value AS rating, 
						COUNT(commentmeta.meta_value) as rating_count 
				FROM	{$wpdb->comments} comments
						INNER JOIN {$wpdb->commentmeta} commentmeta
								ON comments.comment_ID = commentmeta.comment_id
				WHERE	comments.comment_post_ID = %d 
						AND comments.comment_type = %s
						AND commentmeta.meta_key = %s
				GROUP BY commentmeta.meta_value;
				",
					$course_id,
					'academy_booking',
					'academy_rating'
				)
			);

			$ratings = array(
				5 => 0,
				4 => 0,
				3 => 0,
				2 => 0,
				1 => 0,
			);
			foreach ( $stars as $star ) {
				$index = (int) $star->rating;
				array_key_exists( $index, $ratings ) ? $ratings[ $index ] = $star->rating_count : 0;
			}

			$ratings = array(
				'rating_count'   => $rating->rating_count,
				'rating_sum'     => $rating->rating_sum,
				'rating_avg'     => $avg_rating,
				'count_by_value' => $ratings,
			);
		}//end if

		return (object) $ratings;
	}

	public static function prepare_booked_response( $booked_item ) {
		$booking_type = get_post_meta( $booked_item->post_parent, '_academy_booking_type', true );
		$booked_item->parent = array(
			'post_title' => get_the_title( $booked_item->post_parent ),
			'permalink' => get_the_permalink( $booked_item->post_parent ),
			'meta'       => array(
				'_academy_booking_type' => $booking_type,
				'_academy_booking_product_id' => get_post_meta( $booked_item->post_parent, '_academy_booking_product_id', true ),
				'_academy_booking_class_type' => get_post_meta( $booked_item->post_parent, '_academy_booking_class_type', true ),
				'_academy_booking_schedule_type' => get_post_meta( $booked_item->post_parent, '_academy_booking_schedule_type', true ),
				'_academy_booking_schedule_time' => get_post_meta( $booked_item->post_parent, '_academy_booking_schedule_time', true ),
				'_academy_booking_schedule_repeated_times' => get_post_meta( $booked_item->post_parent, '_academy_booking_schedule_repeated_times', true ),
				'_academy_booking_duration' => get_post_meta( $booked_item->post_parent, '_academy_booking_duration', true ),
			)
		);
		if ( 'completed' === $booked_item->post_status ) {
			$user_data = get_userdata( $booked_item->post_author );
			$booked_info = get_post_meta( $booked_item->post_parent, '_academy_booking_private_booked_info', true );
			$booked_item->parent['meta']['_academy_booking_private_booked_info'] = $booked_info ? $booked_info : __( 'The instructor has not set any meeting/class details. Please contact the instructor via email at', 'academy-pro' ) . ' ' . $user_data->user_email;
		}
		if ( 'paid' === $booking_type && \Academy\Helper::is_active_woocommerce() ) {
			$order = wc_get_order( get_post_meta( $booked_item->ID, '_academy_booked_by_order_id', true ) );
			if ( $order ) {
				$booked_item->payment = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) ) . $order->get_total();
			}
		}
		$booked_item->meta = array(
			'_academy_booked_schedule_time' => get_post_meta( $booked_item->ID, '_academy_booked_schedule_time', true ),
		);
		return $booked_item;
	}

	public static function prepare_booking_search_query_args( $data ) {
		$defaults = array(
			'search'         => '',
			'category'       => [],
			'tags'           => [],
			'levels'         => [],
			'type'           => [],
			'classType'      => [],
			'paged'          => 1,
			'posts_per_page' => 12,
		);
		$data     = wp_parse_args( $data, $defaults );

		// base
		$args = array(
			'post_type'      => 'academy_booking',
			'posts_per_page' => $data['posts_per_page'],
			'paged'          => $data['paged'],
		);
		// taxonomy
		$tax_query = array();
		if ( count( $data['category'] ) > 0 ) {
			$tax_query[] = array(
				'taxonomy' => 'academy_booking_category',
				'field'    => 'slug',
				'terms'    => $data['category'],
			);
		}
		if ( count( $data['tags'] ) > 0 ) {
			$tax_query[] = array(
				'taxonomy' => 'academy_booking_tag',
				'field'    => 'slug',
				'terms'    => $data['tags'],
			);
		}
		if ( count( $tax_query ) > 0 ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query']     = $tax_query;
		}
		// meta
		$meta_query = array();
		if ( count( $data['levels'] ) > 0 ) {
			$meta_query[] = array(
				'key'     => 'academy_course_difficulty_level',
				'value'   => $data['levels'],
				'compare' => 'IN',
			);
		}

		if ( count( $data['type'] ) > 0 ) {
			$meta_query[] = array(
				'key'     => '_academy_booking_type',
				'value'   => $data['type'],
				'compare' => 'IN',
			);
		}

		if ( count( $data['classType'] ) > 0 ) {
			$meta_query[] = array(
				'key'     => '_academy_booking_class_type',
				'value'   => $data['classType'],
				'compare' => 'IN',
			);
		}

		if ( count( $meta_query ) > 0 ) {
			$tax_query['relation'] = 'AND';
			$args['meta_query']    = $meta_query;
		}

		// search
		if ( ! empty( $data['search'] ) ) {
			$args['s'] = $data['search'];
		}

		// order by
		if ( isset( $data['orderby'] ) ) {
			switch ( $data['orderby'] ) {
				case 'name':
					$args['orderby'] = 'post_title';
					$args['order']   = 'asc';
					break;
				case 'date':
					$args['orderby'] = 'publish_date';
					$args['order']   = 'desc';
					break;
				case 'modified':
					$args['orderby'] = 'modified';
					$args['order']   = 'desc';
					break;
				case 'ratings':
					$args['orderby'] = 'comment_count';
					$args['order']   = 'desc';
					break;
				default:
					$args['orderby'] = 'ID';
					$args['order']   = 'desc';
			}//end switch
		}//end if

		return $args;
	}

	public static function get_the_booking_thumbnail_url( $size = 'post-thumbnail' ) {
		$post_id           = get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );
		if ( $post_thumbnail_id ) {
			$size = apply_filters( 'academy_pro/booking/booking_thumbnail_size', $size, $post_id );
			return wp_get_attachment_image_url( $post_thumbnail_id, $size );
		}
		return ACADEMY_ASSETS_URI . '/images/thumbnail-placeholder.png';
	}

	public static function is_tutor_booked_order( $order_id ) {
		return get_post_meta( $order_id, '_is_academy_order_for_booking', true );
	}

	public static function get_booking_booked_ids_by_order_id( $order_id ) {
		global $wpdb;
		$booked_ids = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s", $order_id, '_academy_order_for_booking_id_%' ) );
		if ( is_array( $booked_ids ) && count( $booked_ids ) ) {
			$booked_by_order = array();
			foreach ( $booked_ids as $booked_id ) {
				$booking_id                  = str_replace( '_academy_order_for_booking_id_', '', $booked_id->meta_key );
				$booked_by_order[] = array(
					'booking_id'   => $booking_id,
					'booked_id' => $booked_id->meta_value,
					'order_id'    => $booked_id->post_id,
				);
			}
			return $booked_by_order;
		}
		return false;
	}

	public static function get_permalink_structure() {
		$saved_permalinks = (array) get_option( 'academy_pro_tutor_permalinks', array() );
		$permalinks       = wp_parse_args(
			array_filter( $saved_permalinks ),
			array(
				'booking_base'                      => _x( 'booking', 'slug', 'academy-pro' ),
				'category_base'          => _x( 'booking-category', 'slug', 'academy-pro' ),
				'tag_base'               => _x( 'booking-tag', 'slug', 'academy-pro' ),
				'use_verbose_page_rules' => false,
			)
		);

		if ( $saved_permalinks !== $permalinks ) {
			update_option( 'academy_pro_tutor_permalinks', $permalinks );
		}

		$permalinks['rewrite_slug']   = untrailingslashit( $permalinks['booking_base'] );
		$permalinks['category_rewrite_slug']  = untrailingslashit( $permalinks['category_base'] );
		$permalinks['tag_rewrite_slug']       = untrailingslashit( $permalinks['tag_base'] );

		return $permalinks;
	}
}
