<?php
namespace AcademyProPaidMembershipsPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Assets {
	public static function init() {
		$self = new self();
		add_action( 'admin_enqueue_scripts', array( $self, 'admin_script' ) );
	}
	public function admin_script( $hook_suffix ) {
		if ( 'memberships_page_pmpro-membershiplevels' !== $hook_suffix ) {
			return;
		}
		wp_enqueue_style( 'academy-pro-pmpro-settings', ACADEMY_PRO_PMPRO_ASSETS_URI . 'css/backend.css', array(), filemtime( ACADEMY_PRO_PMPRO_ASSETS_PATH . 'css/backend.css' ), 'all' );
		wp_enqueue_script( 'academy-pro-pmpro-settings', ACADEMY_PRO_PMPRO_ASSETS_URI . 'js/backend.js', array( 'jquery' ), filemtime( ACADEMY_PRO_PMPRO_ASSETS_PATH . 'js/backend.js' ), true );
	}
}
