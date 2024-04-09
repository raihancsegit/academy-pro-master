<?php
namespace AcademyProScorm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Database {

	public static function init() {
		$self = new self();
		add_action( 'rest_api_init', [ $self, 'register_scorm_academy_courses_meta' ] );
	}

	public function register_scorm_academy_courses_meta() {
		register_meta(
			'post',
			'_academy_course_builder_scorm_file',
			array(
				'object_subtype' => 'academy_courses',
				'type'           => 'string',
				'single'         => true,
				'show_in_rest'   => true,
			)
		);
	}
}
