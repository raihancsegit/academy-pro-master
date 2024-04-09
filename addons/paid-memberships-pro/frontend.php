<?php
namespace AcademyProPaidMembershipsPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Frontend {
	public static function init() {
		$self = new self();
		// check engine
		if ( 'paid-memberships-pro' !== \Academy\Helper::get_settings( 'monetization_engine' ) ) {
			return;
		}
		add_filter( 'academy/templates/single_course/enroll_content', array( $self, 'pmpro_enroll_widget_content' ), 10, 2 );
		add_filter( 'academy/templates/single_course/enroll_form', array( $self, 'pmpro_pricing_widget' ), 10, 2 );
		add_filter( 'academy/course/get_course_type', array( $self, 'get_course_type' ), 10, 2 );
	}
	public function pmpro_enroll_widget_content( $html, $course_id ) {
		$required_levels = Helper::has_course_access( $course_id );
		if ( ! is_array( $required_levels ) && true === $required_levels ) {
			return $html;
		}
		return '';
	}
	public function pmpro_pricing_widget( $html, $course_id ) {
		$required_levels = Helper::has_course_access( $course_id );
		if ( ! is_array( $required_levels ) && true === $required_levels ) {
			return $html;
		}

		$level_page_id = apply_filters( 'academy_pmpro_level_page_id', pmpro_getOption( 'levels_page_id' ) );
		$level_page_url = get_the_permalink( $level_page_id );

		ob_start();
		\AcademyPro\Helper::get_template('paid-memberships-pro/pricing.php', array(
			'required_levels' => $required_levels,
		) );
		return ob_get_clean();
	}
	public function get_course_type( $course_type, $course_id ) {
		$required_levels = Helper::has_course_access( $course_id );
		if ( ! is_array( $required_levels ) && true === $required_levels ) {
			return 'free';
		}
		return $course_type;
	}
}
