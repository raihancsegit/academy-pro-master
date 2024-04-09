<?php
namespace AcademyPro\Integration;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woocommerce {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro/integration/woo_get_product', array( $self, 'woo_get_product' ) );
		add_action( 'wp_ajax_academy_pro/integration/woo_create_or_update_product', array( $self, 'woo_create_or_update_product' ) );
	}
	public function woo_get_product() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$productId = (int) sanitize_text_field( $_POST['productId'] );
		$product = new \WC_Product_Simple( $productId );

		$response = [
			'product_id' => $productId,
		];
		if ( $product->get_regular_price() ) {
			$response['regular_price'] = (float) $product->get_regular_price();
		}
		if ( $product->get_sale_price() ) {
			$response['sale_price'] = (float) $product->get_sale_price();
		}
		wp_send_json_success( $response );
	}
	public function woo_create_or_update_product() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$course_id = sanitize_text_field( $_POST['course_id'] );
		$product_id = sanitize_text_field( $_POST['product_id'] );
		$course_title = sanitize_text_field( $_POST['course_title'] );
		$course_slug = sanitize_text_field( $_POST['course_slug'] );
		$regular_price = sanitize_text_field( $_POST['regular_price'] );
		$sale_price = sanitize_text_field( $_POST['sale_price'] );

		// that's CRUD object
		$product = new \WC_Product_Simple( $product_id );
		$product->set_name( $course_title );
		$product->set_slug( $course_slug );
		$product->set_regular_price( $regular_price );
		if ( $sale_price ) {
			$product->set_sale_price( $sale_price );
		}
		$product_id = $product->save();

		if ( $product_id ) {
			update_post_meta( $product_id, '_academy_product', 'yes' );
		}

		if ( $course_id ) {
			update_post_meta( $course_id, 'academy_course_product_id', $product_id );
		}
		$response = [
			'product_id' => $product_id,
		];
		if ( $product->get_regular_price() ) {
			$response['regular_price'] = (float) $product->get_regular_price();
		}
		if ( $product->get_sale_price() ) {
			$response['sale_price'] = (float) $product->get_sale_price();
		}
		wp_send_json_success( $response );
	}
}
