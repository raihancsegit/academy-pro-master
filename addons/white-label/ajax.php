<?php
namespace AcademyProWhiteLabel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_white_label/admin/get_settings', array( $self, 'get_settings' ) );
		add_action( 'wp_ajax_academy_pro_white_label/admin/save_settings', array( $self, 'save_settings' ) );
	}
	public function get_settings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$settings = Admin\Settings::get_settings_saved_data();
		wp_send_json_success( $settings );
	}
	public function save_settings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$title = sanitize_text_field( $_POST['title'] );
		$active_menu_icon = (int) sanitize_text_field( $_POST['active_menu_icon'] );
		$inactive_menu_icon = (int) sanitize_text_field( $_POST['inactive_menu_icon'] );
		$logo = (int) sanitize_text_field( $_POST['logo'] );
		$is_hide_settings = (bool) \Academy\Helper::sanitize_checkbox_field( $_POST['is_hide_settings'] );

		$is_saved = Admin\Settings::save_settings(array(
			'title' => $title,
			'active_menu_icon' => $active_menu_icon,
			'inactive_menu_icon' => $inactive_menu_icon,
			'logo' => $logo,
			'is_hide_settings' => $is_hide_settings,
		));
		if ( $is_saved ) {
			$saved_data = Admin\Settings::get_settings_saved_data();
			wp_send_json_success( $saved_data );
		}
		wp_send_json_error( __( 'Something went wrong!!', 'academy-pro' ) );
	}
}
