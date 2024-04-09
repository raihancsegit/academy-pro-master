<?php
namespace  AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Miscellaneous {
	public static function init() {
		$self = new self();
		// public course
		add_filter( 'academy/is_public_course', array( $self, 'is_public_course' ), 10, 2 );
	}
	public function is_public_course( $status, $course_type ) {
		if ( 'public' === $course_type ) {
			return true;
		}
		return $status;
	}
}
