<?php
namespace AcademyProEnrollment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Academy\Interfaces\AddonInterface;

final class Enrollment implements AddonInterface {
	private $addon_name = 'enrollment';
	private function __construct() {
		$this->define_constants();
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
		define( 'ACADEMY_PRO_ENROLLMENT_VERSION', '1.0' );
		define( 'ACADEMY_PRO_ENROLLMENT_INCLUDES_DIR_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'includes/addons/enrollment/includes/' );
	}
	public function init_addon() {
		// fire addon activation hook
		add_action( "academy/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! \Academy\Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

		// Run Addon functionality
		Admin\Ajax::init();
	}

	public function addon_activation_hook() {
	}
}
