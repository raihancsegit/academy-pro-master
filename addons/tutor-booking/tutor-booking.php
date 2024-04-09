<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Academy\Interfaces\AddonInterface;
final class TutorBooking implements AddonInterface {
	private $addon_name = 'tutor-booking';
	private function __construct() {
		$this->define_constants();
		$this->load_dependency();
		$this->init_addon();
	}
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
	public function define_constants() {
		/**
		 * Defines CONSTANTS for Whole Addon.
		 */
		define( 'ACADEMY_PRO_TUTOR_BOOKING_VERSION', '1.0.0' );
		define( 'ACADEMY_PRO_TUTOR_BOOKING_INCLUDES_DIR_PATH', ACADEMY_PRO_ADDONS_DIR_PATH . 'tutor-booking/' );
		define( 'ACADEMY_PRO_TUTOR_BOOKING_ASSETS_URI', ACADEMY_PRO_ADDONS_DIR_PATH . 'tutor-booking/assets/' );
	}
	public function init_addon() {
		// fire addon activation hook
		add_action( "academy/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! \Academy\Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}
		// Run Addon functionality
		Database::init();
		Assets::init();
		Ajax::init();
		Integration::init();
		if ( is_admin() ) {
			Admin::init();
		} else {
			Frontend::init();
		}
	}

	public function load_dependency() {
		require_once ACADEMY_PRO_TUTOR_BOOKING_INCLUDES_DIR_PATH . 'functions.php';
		require_once ACADEMY_PRO_TUTOR_BOOKING_INCLUDES_DIR_PATH . 'hooks.php';
	}

	public function addon_activation_hook() {
		Installer::init();
	}
}
