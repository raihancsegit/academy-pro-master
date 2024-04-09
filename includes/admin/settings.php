<?php
namespace AcademyPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Settings {
	public static function init() {
		$self = new self();
		add_filter( 'academy/admin/settings_default_data', array( $self, 'settings_default_data' ) );
	}
	public function settings_default_data( $default_settings ) {
		$default_settings['required_register_email_verification'] = false;
		$default_settings['is_expire_course_enrollment'] = false;
		$default_settings['auto_load_next_lesson'] = false;
		$default_settings['auto_complete_topic'] = false;
		// recaptcha
		$default_settings['is_enabled_recaptcha'] = false;
		$default_settings['recaptcha_type'] = 'v2';
		$default_settings['recaptcha_v2_site_key'] = '';
		$default_settings['recaptcha_v2_secret_key'] = '';
		$default_settings['recaptcha_v3_site_key'] = '';
		$default_settings['recaptcha_v3_secret_key'] = '';
		return $default_settings;
	}
}
