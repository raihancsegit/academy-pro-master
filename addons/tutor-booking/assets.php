<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Assets {
	public static function init() {
		$self = new self();
		add_action( 'wp_enqueue_scripts', [ $self, 'frontend_scripts' ], 10 );
	}

	public function frontend_scripts() {
		global $post;
		if ( is_post_type_archive( 'academy_booking' ) ||
			is_tax( 'academy_booking_category' ) ||
			is_tax( 'academy_booking_tag' ) ||
			(
				$post && get_post_type( $post->ID ) === 'academy_booking'
			)
		) {
			$this->load_web_font_and_icon();
			$dependencies = include_once ACADEMY_PRO_ASSETS_DIR_PATH . sprintf( 'build/tutorBookingCommon.%s.asset.php', ACADEMY_PRO_VERSION );
			wp_enqueue_style( 'academy-pro-tutor-booking-common-styles', ACADEMY_PRO_ASSETS_URI . 'build/tutorBookingCommon.css', array(), filemtime( ACADEMY_ASSETS_DIR_PATH . 'build/frontendCommon.css' ), 'all' );

			// js
			wp_enqueue_script( 'academy-pro-sticksy', ACADEMY_ASSETS_URI . 'lib/js/sticksy.min.js', array( 'jquery' ), $dependencies['version'], false );
			wp_enqueue_script( 'academy-pro-SocialShare', ACADEMY_ASSETS_URI . 'lib/js/SocialShare.min.js', array( 'jquery' ), $dependencies['version'], false );
			wp_enqueue_script(
				'academy-pro-tutor-booking-scripts',
				ACADEMY_PRO_ASSETS_URI . sprintf( 'build/tutorBookingCommon.%s.js', ACADEMY_PRO_VERSION ),
				$dependencies['dependencies'],
				$dependencies['version'],
				true
			);
			$ScriptsBase = new \Academy\Classes\ScriptsBase();
			wp_localize_script( 'academy-pro-tutor-booking-scripts', 'AcademyGlobal', $ScriptsBase->get_frontend_scripts_data() );
		}
	}
	public function load_web_font_and_icon() {
		// load global styles
		if ( \Academy\Helper::get_settings( 'is_enabled_academy_web_font' ) ) {
			$ScriptsBase = new \Academy\Classes\ScriptsBase();
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			wp_enqueue_style( 'academy-web-font', $ScriptsBase->web_fonts_url( 'Inter:wght@300;400;500;600;700;800;900|Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap' ), array() );
		}
		wp_enqueue_style( 'academy-icon', ACADEMY_ASSETS_URI . 'lib/css/academy-icon.css', array(), filemtime( ACADEMY_ASSETS_DIR_PATH . 'lib/css/academy-icon.css' ), 'all' );
	}
}
