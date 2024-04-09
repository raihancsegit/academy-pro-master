<?php
namespace  AcademyProTutorBooking\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Academy;
use WP_Query;

class Template {
	public static function init() {
		$self = new self();
		$self->dispatch_hook();
		Template\Loader::init();
	}

	public function dispatch_hook() {
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
		add_action( 'template_redirect', array( $this, 'archive_booking_template_redirect' ) );
		add_filter( 'pre_get_document_title', array( $this, 'pre_get_archive_course_title' ), 30, 1 );
		add_filter( 'post_type_archive_title', array( $this, 'archive_course_document_title' ), 30, 2 );
	}

	/**
	 * Hook into pre_get_posts to do the main product query.
	 *
	 * @param WP_Query $q Query instance.
	 */
	public function pre_get_posts( $q ) {
		$per_page = (int) \Academy\Helper::get_customizer_settings( 'course_per_page', 12 );
		if ( $q->is_main_query() && ! $q->is_feed() && ! is_admin() ) {
			$queried_object = get_queried_object();
			if ( $queried_object instanceof \WP_Post ) {
				$page_id = $queried_object->ID;
				$booking_page = (int) \Academy\Helper::get_settings( 'tutor_booking_page' );
				if ( $booking_page === $page_id ) {
					$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
					$orderby = ( get_query_var( 'orderby' ) ) ? get_query_var( 'orderby' ) : 'menu_order';
					$q->set( 'post_type', array( 'academy_booking' ) );
					$q->set( 'posts_per_page', $per_page );
					$q->set( 'paged', $paged );
					$q->set( 'orderby', $orderby );
				}
			}
		}//end if
	}

	public function archive_booking_template_redirect() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['page_id'] ) && '' === get_option( 'permalink_structure' ) && (int) \Academy\Helper::get_settings( 'booking_page' ) === absint( $_GET['page_id'] ) ) {
			$archive_link = $this->get_post_type_archive_link( 'academy_courses' );
			if ( $archive_link ) {
				wp_safe_redirect( $this->get_post_type_archive_link( 'academy_courses' ) );
				exit;
			}
		}
	}

	public function get_post_type_archive_link( $post_type ) {
		global $wp_rewrite;

		$post_type_obj = get_post_type_object( $post_type );
		if ( ! $post_type_obj ) {
			return false;
		}

		if ( 'post' === $post_type ) {
			$show_on_front  = get_option( 'show_on_front' );
			$page_for_posts = get_option( 'page_for_posts' );

			if ( 'page' === $show_on_front && $page_for_posts ) {
				$link = get_permalink( $page_for_posts );
			} else {
				$link = get_home_url();
			}
			/** This filter is documented in wp-includes/link-template.php */
			return apply_filters( 'post_type_archive_link', $link, $post_type );
		}

		if ( ! $post_type_obj->has_archive ) {
			return false;
		}

		if ( get_option( 'permalink_structure' ) && is_array( $post_type_obj->rewrite ) ) {
			$struct = ( true === $post_type_obj->has_archive ) ? $post_type_obj->rewrite['slug'] : $post_type_obj->has_archive;
			if ( $post_type_obj->rewrite['with_front'] ) {
				$struct = $wp_rewrite->front . $struct;
			} else {
				$struct = $wp_rewrite->root . $struct;
			}
			$link = home_url( user_trailingslashit( $struct, 'post_type_archive' ) );
		} else {
			$link = home_url( '?post_type=' . $post_type );
		}

		return apply_filters( 'academy_pro/booking/frontend/post_type_archive_link', $link, $post_type );
	}

	public function pre_get_archive_course_title( $title ) {
		if ( class_exists( 'RankMath' ) ) {
			$page_id = (int) get_queried_object_id();
			$course_page = (int) \Academy\Helper::get_settings( 'tutor_booking_page' );
			if ( $page_id === $course_page ) {
				return;
			}
		}
		return $title;
	}
	public function archive_course_document_title( $name, $post_type ) {
		if ( 'academy_booking' === $post_type ) {
			$course_page = (int) \Academy\Helper::get_settings( 'tutor_booking_page' );
			return get_the_title( $course_page );
		}
		return $name;
	}
}
