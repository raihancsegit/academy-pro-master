<?php
namespace AcademyProZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Academy\Interfaces\AddonInterface;
final class Zoom implements AddonInterface {
	private $addon_name = 'zoom';
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
		define( 'ACADEMY_PRO_ZOOM_VERSION', '1.0' );
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
		API::init();
		Ajax::init();
	}

	public function addon_activation_hook() {
		$user_id = get_current_user_id();
		if ( ! get_user_meta( $user_id, 'academy_pro_zoom_settings', true ) ) {
			update_user_meta(get_current_user_id(), 'academy_pro_zoom_settings', wp_json_encode([
				'join_before_host'      => true,
				'host_video'            => true,
				'participants_video'    => true,
				'mute_participants'     => true,
				'enforce_login'         => true,
				'recording_settings'    => 'cloud',
			]));
		}
	}
}
