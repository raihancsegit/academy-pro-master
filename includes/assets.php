<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Assets {

	public static function init() {
		$self = new self();
		add_action( 'wp_enqueue_scripts', [ $self, 'enqueue_recaptcha_script' ], 10 );
		add_filter( 'academy/assets/frontend_scripts_data', [ $self, 'localize_scripts_data' ] );
	}
	public function enqueue_recaptcha_script() {
		global $post;
		if ( ! \Academy\Helper::get_settings( 'is_enabled_recaptcha', false ) || 'v3' !== \Academy\Helper::get_settings( 'recaptcha_type', 'v2' ) ) {
			return;
		}

		if (
			$post &&
			(
				is_singular( 'academy_courses' ) ||
				(int) \Academy\Helper::get_settings( 'frontend_instructor_reg_page' ) === $post->ID ||
				(int) \Academy\Helper::get_settings( 'frontend_student_reg_page' ) === $post->ID ||
				has_shortcode( $post->post_content, 'academy_dashboard' ) ||
				has_shortcode( $post->post_content, 'academy_instructor_registration_form' ) ||
				has_shortcode( $post->post_content, 'academy_student_registration_form' ) ||
				has_shortcode( $post->post_content, 'academy_login_form' )
			)
		) {
			wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . \Academy\Helper::get_settings( 'recaptcha_v3_site_key', '' ), array(), ACADEMY_PRO_VERSION, false );
		}
	}
	public function localize_scripts_data( $data ) {
		// recaptcha
		$data['is_enabled_recaptcha'] = \Academy\Helper::get_settings( 'is_enabled_recaptcha', false );
		if ( $data['is_enabled_recaptcha'] ) {
			$data['recaptcha_type'] = \Academy\Helper::get_settings( 'recaptcha_type', 'v2' );
			if ( 'v3' === $data['recaptcha_type'] ) {
				$data['recaptcha_v3_site_key'] = \Academy\Helper::get_settings( 'recaptcha_v3_site_key', '' );
			} else {
				$data['recaptcha_v2_site_key'] = \Academy\Helper::get_settings( 'recaptcha_v2_site_key', '' );
			}
		}
		return $data;
	}
}
