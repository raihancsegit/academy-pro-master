<?php
namespace AcademyProCoursePrerequisites;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Database {

	public static function init() {
		$self = new self();
		add_action( 'rest_api_init', [ $self, 'register_prerequisites_academy_courses_meta' ] );
	}

	public function register_prerequisites_academy_courses_meta() {
		register_meta(
			'post',
			'academy_prerequisite_type',
			array(
				'object_subtype' => 'academy_courses',
				'type'           => 'string',
				'single'         => true,
				'show_in_rest'   => true,
			)
		);
		register_meta(
			'post',
			'academy_prerequisite_courses',
			array(
				'object_subtype' => 'academy_courses',
				'type'           => 'array',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'items' => array(
							'type'       => 'object',
							'properties' => [
								'label'   => array(
									'type' => 'string',
								),
								'value' => array(
									'type' => 'integer',
								),
							],
						),
					),
				],
			)
		);
		register_meta(
			'post',
			'academy_prerequisite_categories',
			array(
				'object_subtype' => 'academy_courses',
				'type'           => 'array',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'items' => array(
							'type'       => 'object',
							'properties' => [
								'label'   => array(
									'type' => 'string',
								),
								'value' => array(
									'type' => 'integer',
								),
							],
						),
					),
				],
			)
		);
	}

}
