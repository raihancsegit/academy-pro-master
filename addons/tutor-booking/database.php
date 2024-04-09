<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Database {
	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'create_academy_booking_post_type' ] );
		add_action( 'rest_api_init', [ $self, 'register_academy_booking_meta' ] );
		add_filter( 'rest_prepare_academy_booking', [ $self, 'restrict_meta_key_in_rest_api' ], 10, 3 );
	}

	public function create_academy_booking_post_type() {
		$permalinks = Helper::get_permalink_structure();
		$post_type = 'academy_booking';
		$booking_page_id = \Academy\Helper::get_settings( 'tutor_booking_page' );
		$has_archive = get_post( $booking_page_id ) ? urldecode( get_page_uri( $booking_page_id ) ) : 'tutor-booking';
		register_post_type(
			$post_type,
			array(
				'labels'                => array(
					'name'                  => esc_html__( 'Tutor Booking', 'academy-pro' ),
					'singular_name'         => esc_html__( 'booking', 'academy-pro' ),
					'search_items'          => esc_html__( 'Search Tutor Booking', 'academy-pro' ),
					'parent_item_colon'     => esc_html__( 'Parent Tutor Booking:', 'academy-pro' ),
					'not_found'             => esc_html__( 'No booking found.', 'academy-pro' ),
					'not_found_in_trash'    => esc_html__( 'No booking found in Trash.', 'academy-pro' ),
					'archives'              => esc_html__( 'tutor booking archives', 'academy-pro' ),
				),
				'public'                => true,
				'publicly_queryable'    => true,
				'show_ui'               => false,
				'show_in_menu'          => false,
				'hierarchical'          => true,
				'rewrite'               => array( 'slug' => 'tutor-booking' ),
				'query_var'             => true,
				'has_archive'           => $has_archive,
				'rewrite'             => $permalinks['rewrite_slug'] ? array(
					'slug'       => $permalinks['rewrite_slug'],
					'with_front' => false,
					'feeds'      => true,
				) : false,
				'delete_with_user'      => false,
				'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'post-formats' ),
				'show_in_rest'          => true,
				'rest_base'             => $post_type,
				'rest_namespace'        => ACADEMY_PLUGIN_SLUG . '/v1',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'capability_type'           => 'post',
				'capabilities'              => array(
					'edit_post'             => 'edit_academy_booking',
					'read_post'             => 'read_academy_booking',
					'delete_post'           => 'delete_academy_booking',
					'delete_posts'          => 'delete_academy_bookings',
					'edit_posts'            => 'edit_academy_bookings',
					'edit_others_posts'     => 'edit_others_academy_bookings',
					'publish_posts'         => 'publish_academy_bookings',
					'read_private_posts'    => 'read_private_academy_bookings',
					'create_posts'          => 'edit_academy_bookings',
				),
			)
		);

		register_taxonomy(
			$post_type . '_category',
			$post_type,
			array(
				'hierarchical'          => true,
				'query_var'             => true,
				'public'                => true,
				'show_ui'               => false,
				'show_admin_column'     => false,
				'_builtin'              => true,
				'capabilities'          => array(
					'manage_terms' => 'manage_categories',
					'edit_terms'   => 'edit_categories',
					'delete_terms' => 'delete_categories',
					'assign_terms' => 'assign_categories',
				),
				'show_in_rest'          => true,
				'rest_base'             => $post_type . '_category',
				'rest_namespace'        => ACADEMY_PLUGIN_SLUG . '/v1',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
				'rewrite'               => array(
					'slug'         => $permalinks['category_rewrite_slug'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);

		register_taxonomy(
			$post_type . '_tag',
			$post_type,
			array(
				'hierarchical'          => false,
				'query_var'             => true,
				'public'                => true,
				'show_ui'               => false,
				'show_admin_column'     => false,
				'_builtin'              => true,
				'capabilities'          => array(
					'manage_terms' => 'manage_post_tags',
					'edit_terms'   => 'edit_post_tags',
					'delete_terms' => 'delete_post_tags',
					'assign_terms' => 'assign_post_tags',
				),
				'show_in_rest'          => true,
				'rest_base'             => $post_type . '_tag',
				'rest_namespace'        => ACADEMY_PLUGIN_SLUG . '/v1',
				'rest_controller_class' => 'WP_REST_Terms_Controller',
				'rewrite'               => array(
					'slug'         => $permalinks['tag_rewrite_slug'],
					'with_front'   => false,
					'hierarchical' => true,
				),
			)
		);
	}

	public function register_academy_booking_meta() {
		$course_meta = [
			'_academy_booking_type'                     => 'string',
			'_academy_booking_product_id'               => 'integer',
			'_academy_booking_class_type'               => 'string',
			'_academy_booking_schedule_type'            => 'string',
			'_academy_booking_private_booked_info'      => 'string',
			'_academy_booking_schedule_time_zone'       => 'string',
			'_academy_booking_duration'                 => 'integer',
		];

		foreach ( $course_meta as $meta_key => $meta_value_type ) {
			register_meta(
				'post',
				$meta_key,
				array(
					'object_subtype' => 'academy_booking',
					'type'           => $meta_value_type,
					'single'         => true,
					'show_in_rest'   => true,
				)
			);
		}

		register_meta(
			'post',
			'_academy_booking_schedule_time',
			array(
				'object_subtype' => 'academy_booking',
				'type'           => 'object',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'type'       => 'object',
						'properties' => [
							'date' => array(
								'type' => 'string',
							),
							'start_time' => array(
								'type' => 'string',
							),
							'end_time'  => array(
								'type' => 'string',
							),
						],
					),
				],
			)
		);

		register_meta(
			'post',
			'_academy_booking_schedule_repeated_times',
			array(
				'object_subtype' => 'academy_booking',
				'type'           => 'array',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'items' => array(
							'type'       => 'object',
							'properties' => [
								'day'   => array(
									'type' => 'string',
								),
								'scheduleTimes' => array(
									'type' => 'array',
									'items' => array(
										'type' => 'object',
										'properties' => [
											'start_time' => array(
												'type' => 'string',
											),
											'end_time' => array(
												'type' => 'string',
											),
										]
									)
								)
							],
						),
					),
				],
			)
		);
	}

	public function restrict_meta_key_in_rest_api( $item, $post, $request ) {
		$author_data = get_userdata( $item->data['author'] );
		$item->data['author_name'] = $author_data->display_name;
		// Check if the user has permission to edit the post
		if ( ! current_user_can( 'edit_academy_booking', $post->ID ) ) {
			if ( isset( $item->data['meta']['_academy_booking_private_booked_info'] ) ) {
				unset( $item->data['meta']['_academy_booking_private_booked_info'] );
			}
		}

		return $item;
	}
}
