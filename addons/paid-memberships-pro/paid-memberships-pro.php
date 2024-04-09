<?php
namespace AcademyProPaidMembershipsPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Academy\Interfaces\AddonInterface;
final class PaidMembershipsPro implements AddonInterface {
	private $addon_name = 'paid-memberships-pro';
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
		define( 'ACADEMY_PRO_PMPRO_VERSION', '1.0.0' );
		define( 'ACADEMY_PRO_PMPRO_INCLUDES_DIR_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'includes/addons/paid-memberships-pro/includes/' );
		define( 'ACADEMY_PRO_PMPRO_ASSETS_URI', ACADEMY_PRO_PLUGIN_ROOT_URI . 'includes/addons/paid-memberships-pro/assets/' );
		define( 'ACADEMY_PRO_PMPRO_ASSETS_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'includes/addons/paid-memberships-pro/assets/' );
	}
	public function init_addon() {
		// fire addon activation hook
		add_action( "academy/addons/activated_{$this->addon_name}", array( $this, 'addon_activation_hook' ) );
		// if disable then stop running addon
		if ( ! \Academy\Helper::get_addon_active_status( $this->addon_name ) ) {
			return;
		}

		add_filter( 'academy/assets/backend_scripts_data', array( $this, 'add_pmpro_scripts' ) );

		// check is active PMPRO
		if ( ! Helper::is_active_pmpro() ) {
			return;
		}

		// Run Addon functionality
		if ( is_admin() ) {
			Admin::init();
		} else {
			Frontend::init();
		}
	}

	public function add_pmpro_scripts( $data ) {
		$data['pmpro_is_active'] = Helper::is_active_pmpro();
		return $data;
	}

	public function addon_activation_hook() {
	}
}
