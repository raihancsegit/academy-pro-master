<?php
namespace AcademyProContentDrip;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Database {

	public static function init() {
		$self = new self();
		add_action( 'rest_api_init', [ $self, 'register_drip_content_academy_courses_meta' ] );
		add_action( 'rest_api_init', [ $self, 'register_drip_content_academy_quiz_meta' ] );
		add_action( 'rest_api_init', [ $self, 'register_drip_content_academy_assignments_meta' ] );
		add_action( 'rest_api_init', [ $self, 'register_drip_content_academy_booking_meta' ] );

		// lesson
		add_filter( 'academy/api/lesson/public_item_schema', [ $self, 'drip_content_public_item_schema' ] );
		add_filter( 'academy/api/lesson/item_schema', [ $self, 'drip_content_item_schema' ] );
		add_filter( 'academy/api/lesson/rest_pre_insert_lesson_meta', [ $self, 'drip_content_rest_pre_insert_lesson_meta' ], 10, 3 );
		add_filter( 'academy/api/lesson/rest_prepare_meta_item', [ $self, 'drip_content_rest_prepare_meta_item' ], 10, 4 );
	}

	public function register_drip_content_academy_courses_meta() {
		$course_meta = [
			'academy_course_drip_content_enabled'        => 'boolean',
			'academy_course_drip_content_type'           => 'string',
		];

		foreach ( $course_meta as $meta_key => $meta_value_type ) {
			register_meta(
				'post',
				$meta_key,
				array(
					'object_subtype' => 'academy_courses',
					'type'           => $meta_value_type,
					'single'         => true,
					'show_in_rest'   => true,
				)
			);
		}
	}

	public function register_drip_content_academy_quiz_meta() {
		if ( ! \Academy\Helper::get_addon_active_status( 'quizzes' ) ) {
			return;
		}

		register_meta(
			'post',
			'academy_quiz_drip_content',
			array(
				'object_subtype' => 'academy_quiz',
				'type'           => 'object',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'type'       => 'object',
						'properties' => [
							'schedule_by_date'   => array(
								'type' => 'string',
							),
							'schedule_by_enroll_date' => array(
								'type' => 'integer',
							),
							'schedule_by_prerequisite' => array(
								'type' => 'array',
								'items' => array(
									'type' => 'object',
									'properties' => [
										'label'   => array(
											'type' => 'string',
										),
										'type'   => array(
											'type' => 'string',
										),
										'value' => array(
											'type' => 'integer',
										),
									],
								),
							),
						],
					),
				],
			)
		);
	}

	public function register_drip_content_academy_assignments_meta() {
		if ( ! \Academy\Helper::get_addon_active_status( 'assignments' ) ) {
			return;
		}

		register_meta(
			'post',
			'academy_assignment_drip_content',
			array(
				'object_subtype' => 'academy_assignments',
				'type'           => 'object',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'type'       => 'object',
						'properties' => [
							'schedule_by_date'   => array(
								'type' => 'string',
							),
							'schedule_by_enroll_date' => array(
								'type' => 'integer',
							),
							'schedule_by_prerequisite' => array(
								'type' => 'array',
								'items' => array(
									'type' => 'object',
									'properties' => [
										'label'   => array(
											'type' => 'string',
										),
										'type'   => array(
											'type' => 'string',
										),
										'value' => array(
											'type' => 'integer',
										),
									],
								),
							),
						],
					),
				],
			)
		);
	}

	public function register_drip_content_academy_booking_meta() {
		if ( ! \Academy\Helper::get_addon_active_status( 'tutor-booking' ) ) {
			return;
		}

		register_meta(
			'post',
			'_academy_booking_drip_content',
			array(
				'object_subtype' => 'academy_booking',
				'type'           => 'object',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'type'       => 'object',
						'properties' => [
							'schedule_by_date'   => array(
								'type' => 'string',
							),
							'schedule_by_enroll_date' => array(
								'type' => 'integer',
							),
							'schedule_by_prerequisite' => array(
								'type' => 'array',
								'items' => array(
									'type' => 'object',
									'properties' => [
										'label'   => array(
											'type' => 'string',
										),
										'type'   => array(
											'type' => 'string',
										),
										'value' => array(
											'type' => 'integer',
										),
									],
								),
							),
						],
					),
				],
			)
		);
	}

	public function drip_content_public_item_schema( $schema ) {
		if ( ! isset( $schema['properties']['meta']['properties'] ) ) {
			return $schema;
		}
		$meta = $schema['properties']['meta']['properties'];
		$meta['drip_content'] = [
			'type'          => 'object',
		];
		$schema['properties']['meta']['properties'] = $meta;
		return $schema;
	}

	public function drip_content_item_schema( $schema ) {
		if ( ! isset( $schema['meta']['properties'] ) ) {
			return $schema;
		}
		$meta = $schema['meta'];
		$meta['properties']['drip_content'] = [
			'type'          => 'object',
			'properties' => [
				'schedule_by_date' => [
					'type'          => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'schedule_by_enroll_date' => [
					'type'          => 'integer',
					'sanitize_callback' => 'integer',
				],
				'schedule_by_prerequisite' => [
					'type'          => 'array',
					'sanitize_callback' => 'rest_sanitize_array',
				],
			]
		];
		$schema['meta'] = $meta;
		return $schema;
	}

	public function drip_content_rest_pre_insert_lesson_meta( $lesson_meta, $request, $schema ) {
		if ( ! empty( $schema['meta']['properties']['drip_content'] ) && isset( $request['meta']['drip_content'] ) ) {
			if ( is_array( $request['meta']['drip_content'] ) ) {
				$lesson_meta->drip_content = $request['meta']['drip_content'];
			}
		}

		return $lesson_meta;
	}

	public function drip_content_rest_prepare_meta_item( $data, $lesson_meta, $request, $schema ) {
		if ( isset( $schema['meta']['properties']['drip_content'] ) && isset( $lesson_meta['drip_content'] ) ) {
			$data['drip_content'] = $lesson_meta['drip_content'];
		}
		return $data;
	}
}
