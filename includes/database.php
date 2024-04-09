<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Database {

	public static function init() {
		$self = new self();
		add_action( 'rest_api_init', [ $self, 'register_academy_courses_meta' ] );
	}

	public function register_academy_courses_meta() {
		$course_meta = [
			'academy_course_expire_enrollment'               => 'integer',
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
}
