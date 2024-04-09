<?php
namespace AcademyProWhiteLabel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Academy\Interfaces\AddonInterface;
final class WhiteLabel implements AddonInterface {
	private $addon_name = 'white-label';
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
		define( 'ACADEMY_PRO_WHITE_LABEL_VERSION', '1.0' );
		define( 'ACADEMY_PRO_WHITE_LABEL_SETTINGS_NAME', 'academy_pro_white_label_settings' );
	}
	public function init_addon() {
		// fire addon activation hook
		add_action( "academy/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! \Academy\Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

		// Run Addon functionality
		Ajax::init();
		Hooks::init();
	}

	public function addon_activation_hook() {
		Admin\Settings::save_settings();
	}
}
