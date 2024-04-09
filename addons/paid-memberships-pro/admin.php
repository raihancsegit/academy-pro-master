<?php
namespace AcademyProPaidMembershipsPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Admin {
	public static function init() {
		$self = new self();
		$self->load_settings();
		$self->enqueue_assets();
	}
	public function load_settings() {
		Admin\Settings::init();
	}
	public function enqueue_assets() {
		Admin\Assets::init();
	}
}
