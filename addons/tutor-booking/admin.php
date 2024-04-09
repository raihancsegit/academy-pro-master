<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Admin {
	public static function init() {
		$self = new self();
		Admin\Settings::init();
		$self->dispatch_hooks();
	}
	public function dispatch_hooks() {
		add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
		add_action( 'current_screen', array( $this, 'conditional_loaded' ) );
	}
	/**
	 * Add a post display state for special WC pages in the page list table.
	 *
	 * @param array   $post_states An array of post display states.
	 * @param WP_Post $post        The current post object.
	 */
	public function add_display_post_states( $post_states, $post ) {
		if ( (int) \Academy\Helper::get_settings( 'tutor_booking_page' ) === $post->ID ) {
			$post_states['academy_page_for_booking'] = __( 'Academy Tutor Booking Page', 'academy-pro' );
		}
		return $post_states;
	}

	public function conditional_loaded() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		switch ( $screen->id ) {
			case 'options-permalink':
				Admin\PermalinkSettings::init();
				break;
		}
	}
}
