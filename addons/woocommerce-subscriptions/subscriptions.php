<?php

namespace AcademyProWoocommerceSubscriptions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Subscriptions {
	public static function init() {
		$self = new self();
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Subscriptions' ) ) {
			return;
		}
		add_filter( 'academy/course/is_enrolled', array( $self, 'is_user_enrolled' ), 10, 3 );
		add_action( 'woocommerce_subscription_status_updated', array( $self, 'update_enroll_status' ), 10, 3 );
	}

	public function is_user_enrolled( $enrolled_info, $course_id, $user_id ) {
		$product_id = \Academy\Helper::get_course_product_id( $course_id );
		if ( $product_id ) {
			$product = wc_get_product( $product_id );
			$type = is_object( $product ) && isset( $product->get_type ) ? $product->get_type() : null;

			if ( 'subscription' === $type || 'variable-subscription' === $type ) {
				$subscriptions = $this->get_user_subscription( $user_id );
				$has_subscription_flag = false;
				foreach ( $subscriptions as $subscription ) {
					if ( $subscription->has_product( $product_id ) ) {
						$has_subscription_flag = true;
					}
				}
				if ( $has_subscription_flag ) {
					return $enrolled_info;
				}
				return false;
			}
		}
		return $enrolled_info;
	}
	public function update_enroll_status( $wc_subscription, $new_status, $old_status ) {
		$order_id = method_exists( $wc_subscription, 'get_parent_id' ) ? $wc_subscription->get_parent_id() : $wc_subscription->order->id;
		if ( $order_id && \Academy\Helper::is_academy_order( $order_id ) ) {
			$enroll_status = 'active' === $new_status ? 'completed' : 'cancel';
			$enrollments = \Academy\Helper::get_course_enrolled_ids_by_order_id( $order_id );
			if ( is_array( $enrollments ) && count( $enrollments ) ) {
				$ids = array();
				foreach ( $enrollments as $enrollment ) {
					$ids[] = $enrollment['enrolled_id'];
				}
				if ( count( $ids ) ) {
					\Academy\Helper::update_enroll_status_by_course_ids( $enroll_status, $ids );
				}
			}
		}
	}
	public function get_user_subscription( $user_id = 0 ) {
		$query = new \WP_Query();
		$subscription_ids = $query->query( array(
			'post_type'           => 'shop_subscription',
			'posts_per_page'      => -1,
			'post_status'         => 'wc-active',
			'orderby'             => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'meta_query'          => array(
				array(
					'key'   => '_customer_user',
					'value' => $user_id,
				),
			),
		) );

		$results = array();
		foreach ( $subscription_ids as $subscription_id ) {
			$subscription = \wcs_get_subscription( $subscription_id );
			if ( $subscription ) {
				$results[ $subscription_id ] = $subscription;
			}
		}
		return $results;
	}
}
