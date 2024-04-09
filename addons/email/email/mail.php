<?php
namespace AcademyProEmail\Email;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Mail {
	public function send_mail( $to, $subject, $body, $headers ) {
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		$is_send = wp_mail( $to, $subject, $body, $headers );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		return $is_send;
	}

	public function get_from_name( $name ) {
		$settings = \AcademyProEmail\Admin\Settings::get_settings_saved_data();
		if ( isset( $settings['form_name'] ) && ! empty( $settings['form_name'] ) ) {
			return sanitize_text_field( $settings['form_name'] );
		}
		return $name;
	}
	public function get_from_address( $email_address ) {
		$settings = \AcademyProEmail\Admin\Settings::get_settings_saved_data();
		if ( isset( $settings['email_address'] ) && ! empty( $settings['email_address'] ) && is_email( $settings['email_address'] ) ) {
			return sanitize_text_field( $settings['email_address'] );
		}
		return $email_address;
	}
}
