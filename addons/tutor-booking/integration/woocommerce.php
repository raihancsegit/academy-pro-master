<?php
namespace AcademyProTutorBooking\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Woocommerce {
	public static function init() {
		$self = new self();
		/**
		 * After create new order
		 */
		add_action( 'woocommerce_new_order_item', array( $self, 'create_booked_order_from_customer' ), 10, 3 );

		/**
		 * Order Status Hook
		 *
		 * Remove course from active courses if an order is cancelled or refunded
		 */
		add_action( 'woocommerce_order_status_changed', array( $self, 'booked_order_status_change' ), 10, 3 );

	}

	public function create_booked_order_from_customer( $item_id, $item, $order_id ) {
		if ( is_admin() || ! isset( $item->legacy_values ) ) {
			return;
		}
		$booked_schedule_date_time = isset( $item->legacy_values['booked_schedule_date_time'] ) ? $item->legacy_values['booked_schedule_date_time'] : '';
		if ( empty( $booked_schedule_date_time ) ) {
			return;
		}
		$item          = new \WC_Order_Item_Product( $item );
		$product_id    = $item->get_product_id();
		$customer_id   = get_current_user_id();
		$has_course = \AcademyProTutorBooking\Helper::product_belongs_with_booking( $product_id );
		if ( $has_course ) {
			$course_id = $has_course->post_id;
			$course_attach_product_id = $has_course->meta_value;
			if ( $course_id && $course_attach_product_id ) {
				\AcademyProTutorBooking\Helper::do_booked( $course_id, $customer_id, $booked_schedule_date_time, $order_id );
			}
		}
	}

	public function booked_order_status_change( $order_id, $status_from, $status_to ) {
		if ( ! \AcademyProTutorBooking\Helper::is_tutor_booked_order( $order_id ) ) {
			return;
		}

		global $wpdb;
		$booked_ids_with_course = \AcademyProTutorBooking\Helper::get_booking_booked_ids_by_order_id( $order_id );
		if ( $booked_ids_with_course ) {
			$booked_ids = wp_list_pluck( $booked_ids_with_course, 'booked_id' );
			if ( is_array( $booked_ids ) && count( $booked_ids ) ) {
				foreach ( $booked_ids as $booked_id ) {
					if ( ! is_admin() && \Academy\Integration\Woocommerce::is_order_will_be_automatically_completed( $order_id ) ) {
						$status_to = 'completed';
						\Academy\Integration\Woocommerce::order_mark_as_completed( $order_id );
					}
					$wpdb->update( $wpdb->posts, array( 'post_status' => $status_to ), array( 'ID' => $booked_id ) );
				}
			}
		}
	}
}
