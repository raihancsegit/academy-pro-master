<?php
namespace AcademyProAssignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Miscellaneous {
	public static function init() {
		$self = new self();
		add_filter( 'academy/get_analytics', array( $self, 'add_total_assignments' ) );
	}
	public function add_total_assignments( $analytics ) {
		$analytics['total_assignments'] = Classes\Query::get_total_number_of_assignments();
		return $analytics;
	}
}
