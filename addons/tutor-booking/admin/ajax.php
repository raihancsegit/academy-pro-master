<?php
namespace AcademyProTutorBooking\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro/booking/admin/get_booked_schedules', array( $self, 'get_booked_schedules' ) );
		add_action( 'wp_ajax_academy_pro/booking/admin/get_booked_schedule_details', array( $self, 'get_booked_schedule_details' ) );
		add_action( 'wp_ajax_academy_pro/booking/admin/delete_booked_schedule', array( $self, 'delete_booked_schedule' ) );
	}


	public function get_booked_schedules() {
		check_ajax_referer( 'academy_nonce', 'security' );

		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$page = ( isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : 1 );
		$per_page = ( isset( $_POST['per_page'] ) ? sanitize_text_field( $_POST['per_page'] ) : 10 );
		$offset = ( $page - 1 ) * $per_page;

		$total_booked = new \WP_Query( array(
			'post_type' => 'academy_booked',
			'post_status' => 'any',
		) );
		wp_reset_postdata();
		// Set the x-wp-total header
		header( 'x-wp-total: ' . $total_booked->found_posts );

		$args = array(
			'post_type' => 'academy_booked',
			'post_status' => 'any',
			'posts_per_page' => $per_page,
			'offset' => $offset
		);

		$all_booked = get_posts( $args );
		$response = array();
		foreach ( $all_booked as $booked_item ) {
			$response[] = \AcademyProTutorBooking\Helper::prepare_booked_response( $booked_item );
		}
		wp_send_json_success( $response );
	}
	public function get_booked_schedule_details() {
		check_ajax_referer( 'academy_nonce', 'security' );

		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$user_id = sanitize_text_field( $_POST['user_id'] );
		$booking_id = sanitize_text_field( $_POST['booking_id'] );
		$order_id = get_post_meta( $booking_id, '_academy_booked_by_order_id', true );

		$user = get_user_by( 'id', $user_id );
		$first_name = $user->first_name;
		$last_name = $user->last_name;
		$full_name = $first_name . ' ' . $last_name;
		$email_address = $user->user_email;

		$response = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'full_name' => $full_name,
			'email_address' => $email_address,
		);

		if ( $order_id && \Academy\Helper::is_active_woocommerce() ) {
			$order = \wc_get_order( $order_id );
			$response['payment_method'] = \wc_get_payment_gateway_by_order( $order )->get_title();
			$response['payment_status'] = $order->get_status();
		}

		wp_send_json_success( $response );
	}
	public function delete_booked_schedule() {
		check_ajax_referer( 'academy_nonce', 'security' );

		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$booked_id = (int) sanitize_text_field( $_POST['booked_id'] );

		wp_delete_post( $booked_id, true );

		wp_send_json_success( $booked_id );
	}
}
