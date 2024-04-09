<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {
	public static function init() {
		$self = new self();
		Frontend\Template::init();
		Frontend\Comments::init();
	}
}
