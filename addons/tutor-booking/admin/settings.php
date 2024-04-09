<?php
namespace AcademyProTutorBooking\Admin;

use Academy\Interfaces\SettingsExtendInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Settings implements SettingsExtendInterface {
	public static function init() {
		$self = new self();
		add_filter( 'academy/admin/settings_default_data', array( $self, 'set_settings_default_data' ) );
	}
	public function set_settings_default_data( $default_settings ) {
		$default_settings['tutor_booking_page'] = '';
		return $default_settings;
	}
}
