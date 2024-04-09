<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Installer {

	public static function init() {
		$self = new self();
		$self->create_initial_pages();
	}
	public function get_initial_pages() {
		$page_lists = [
			'tutor_booking_page' => [
				'post_title'    => esc_html__( 'Tutor Booking', 'academy-pro' ),
				'post_status'   => 'publish',
				'post_type' => 'page',
			],
		];
		return apply_filters( 'academy/booking/necessary_pages', $page_lists );
	}
	public function create_initial_pages() {
		$settings = json_decode( get_option( ACADEMY_SETTINGS_NAME, '{}' ), true );
		$page_lists = $this->get_initial_pages();

		foreach ( $page_lists as $key => $page ) {
			$have_page = \Academy\Helper::get_page_by_title( $page['post_title'] );
			if ( $have_page ) {
				// check page status
				if ( 'publish' !== $have_page->post_status ) {
					$have_page->post_status = 'publish';
					wp_update_post( $have_page );
				}
				// assign page id inside academy settings
				if ( $settings[ $key ] !== $have_page->ID ) {
					$settings[ $key ] = $have_page->ID;
					// set page template
					update_post_meta( $have_page->ID, '_wp_page_template', 'academy-canvas.php' );
				}
			} else {
				$post_id = (string) wp_insert_post( $page );
				if ( $post_id && empty( $settings[ $key ] ) ) {
					$settings[ $key ] = $post_id;
					// set page template
					update_post_meta( $post_id, '_wp_page_template', 'academy-canvas.php' );
				}
			}
		}//end foreach
		// Save Settings
		update_option( ACADEMY_SETTINGS_NAME, wp_json_encode( $settings ), false );
		// Flash role & rewrite
		update_option( 'academy_flash_role_management', true );
		update_option( 'academy_required_rewrite_flush', \Academy\Helper::get_time() );
		return true;
	}
}
