<?php
/*
 * Plugin Name:		Academy LMS Pro
 * Plugin URI:		http://academylms.net
 * Description:		Extend Academy LMS functionality With Academy LMS Pro
 * Version:			1.7.4
 * Author:			Academy LMS
 * Author URI:		http://academylms.net
 * Text Domain:		academy-pro
 * Domain Path:		/languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class AcademyPro {
	private function __construct() {
		$this->define_constants();
		$this->load_dependency();
		$this->check_compatibility();
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
		add_action( 'activated_plugin', array( $this, 'activated_redirect' ), 10, 2 );
		add_action( 'academy_loaded', [ $this, 'init_plugin' ] );
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
		 * Defines CONSTANTS for Whole plugins.
		 */
		define( 'ACADEMY_PRO_VERSION', '1.7.4' );
		define( 'ACADEMY_PRO_REQUIRED_CORE_VERSION', '1.9.0' );
		define( 'ACADEMY_PRO_SETTINGS_NAME', 'academy_pro_settings' );
		define( 'ACADEMY_PRO_PLUGIN_FILE', __FILE__ );
		define( 'ACADEMY_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'ACADEMY_PRO_PLUGIN_SLUG', 'academy-pro' );
		define( 'ACADEMY_PRO_PLUGIN_ROOT_URI', plugins_url( '/', __FILE__ ) );
		define( 'ACADEMY_PRO_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'ACADEMY_PRO_INCLUDES_DIR_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'includes/' );
		define( 'ACADEMY_PRO_ASSETS_DIR_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'assets/' );
		define( 'ACADEMY_PRO_ADDONS_DIR_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'addons/' );
		define( 'ACADEMY_PRO_LIBRARY_DIR_PATH', ACADEMY_PRO_ROOT_DIR_PATH . 'library/' );
		define( 'ACADEMY_PRO_ASSETS_URI', ACADEMY_PRO_PLUGIN_ROOT_URI . 'assets/' );
		define( 'ACADEMY_PRO_TEMPLATE_DEBUG_MODE', false );
	}



	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
		// Check Compatibility
		if ( ! version_compare( ACADEMY_VERSION, ACADEMY_PRO_REQUIRED_CORE_VERSION, '>=' ) ) {
			return;
		}
		// Init action.
		do_action( 'academy_pro_before_init' );
		$this->load_textdomain();
		$this->dispatch_hooks();
		$this->load_addons();
		// Init action.
		do_action( 'academy_pro_init' );
	}

	public function dispatch_hooks() {
		AcademyPro\Database::init();
		AcademyPro\Integration::init();
		AcademyPro\Assets::init();
		AcademyPro\Miscellaneous::init();
		AcademyPro\Ajax::init();
		AcademyPro\Shortcode::init();
		if ( is_admin() ) {
			AcademyPro\Admin::init();
		} else {
			AcademyPro\Frontend::init();
		}
	}

	public function check_compatibility() {
		AcademyPro\Admin\Notice::init();
	}

	public function load_addons() {
		AcademyPro\Addons::init();
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'academy-pro',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
	}

	public function load_dependency() {
		require_once ACADEMY_PRO_INCLUDES_DIR_PATH . 'autoload.php';
	}

	public function activate() {
		AcademyPro\Installer::init();
	}

	public function deactivate() {

	}

	public function activated_redirect( $plugin, $network_wide = null ) {
		if ( ACADEMY_PRO_PLUGIN_BASENAME === $plugin && \AcademyPro\Helper::is_active_academy() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=academy-license' ) );
			exit;
		}
	}
}

/**
 * Initializes the main plugin
 *
 * @return \Academy
 */
function academy_pro_start() {
	return AcademyPro::init();
}

// Plugin Start
academy_pro_start();
