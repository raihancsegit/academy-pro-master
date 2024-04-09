<?php
namespace AcademyProAdvancedAnalytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Analytics {
	public static function init() {
		$self = new self();
		add_filter( 'academy/get_analytics', array( $self, 'set_advanced_analytics' ) );
	}

	public function set_advanced_analytics( $analytics ) {
		if ( \Academy\Helper::get_addon_active_status( 'quizzes' ) ) {
			$analytics['total_quizzes'] = Helper::get_total_number_of_quizzes();
		}
		$analytics['total_completed_course'] = Helper::get_total_number_of_completed_course();
		return $analytics;
	}
}
