<?php
namespace AcademyProEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_email/get_email_settings', array( $self, 'get_email_settings' ) );
		add_action( 'wp_ajax_academy_pro_email/save_email_settings', array( $self, 'save_email_settings' ) );
		// Test Email
		add_action( 'wp_ajax_academy_pro_email/preview_template', array( $self, 'preview_template' ) );
		add_action( 'wp_ajax_academy_pro_email/test_email', array( $self, 'test_email' ) );
	}
	public function get_email_settings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$settings = Admin\Settings::get_settings_saved_data();
		wp_send_json_success( $settings );
	}

	public function save_email_settings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$form_name = sanitize_text_field( $_POST['form_name'] );
		$email_address = sanitize_text_field( $_POST['email_address'] );
		$email_content_type = sanitize_text_field( $_POST['email_content_type'] );
		$header_image = sanitize_text_field( $_POST['header_image'] );
		$footer_text = wp_kses_post( wp_unslash( $_POST['footer_text'] ) );
		$enrolled_course = Helper::sanitize_email_template_data( json_decode( wp_unslash( $_POST['enrolled_course'] ), true ) );
		$finished_course = Helper::sanitize_email_template_data( json_decode( wp_unslash( $_POST['finished_course'] ), true ) );
		$become_an_instructor = Helper::sanitize_email_template_data( json_decode( wp_unslash( $_POST['become_an_instructor'] ), true ) );

		$is_saved = Admin\Settings::save_settings(array(
			'form_name' => $form_name,
			'email_address' => $email_address,
			'email_content_type' => $email_content_type,
			'header_image'  => $header_image,
			'footer_text'  => $footer_text,
			'enrolled_course'  => $enrolled_course,
			'finished_course' => $finished_course,
			'become_an_instructor' => $become_an_instructor
		));
		if ( $is_saved ) {
			$saved_data = Admin\Settings::get_settings_saved_data();
			wp_send_json_success( $saved_data );
		}
		wp_send_json_error( __( 'Something went wrong!!', 'academy-pro' ) );
	}

	public function test_email() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$templateName = sanitize_text_field( $_POST['templateName'] );
		$templateSubName = sanitize_text_field( $_POST['templateSubName'] );
		$settings = Admin\Settings::get_settings_saved_data();
		$footer = $settings['footer_text'];
		$emailtype = $settings['email_content_type'];
		$settings = $settings[ $templateName ][ $templateSubName ];
		$is_enable = (bool) $settings['is_enable'];
		if ( ! $is_enable ) {
			wp_send_json_error( ucfirst( $templateSubName ) . ' ' . __( 'Mail Settings is disable. Please Enable it and try again.', 'academy-pro' ) );
		}

		$to = get_option( 'admin_email' );

		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );

		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}' ],
			[ 'Test Display Name', $site_name, $site_url ],
			$settings['email_subject']
		);

		$templateName = Helper::get_email_template_name( $templateName, $templateSubName );

		if ( 'plainText' === $emailtype ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/' . $templateName, array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}
		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{course_url}', '{student_profile}', '{instructor_dashboard}', '{site_title}', '{request_email}', '{admin_instructor_manager}', '{login_url}' ],
			[ 'Test Display Name', 'test@admin.com', 'Simple Course Name', $site_url, $site_url, \Academy\Helper::get_page_permalink( 'frontend_dashboard_page' ), $site_name, 'example@instructor.com', esc_url( admin_url( 'admin.php?page=academy-instructors' ) ), esc_url( wp_login_url() ) ],
			$body
		);

		$mail = new Email\Mail();
		$is_send = $mail->send_mail( $to, $subject, $body, $headers );

		if ( $is_send ) {
			wp_send_json_success( ucfirst( $templateSubName ) . ' ' . __( 'Test Mail Send Successfully.', 'academy-pro' ) );

		}
		wp_send_json_error( ucfirst( $templateSubName ) . ' ' . __( 'Test Mail Send Failed.', 'academy-pro' ) );
	}

	public function preview_template() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}
		$templateName = sanitize_text_field( $_POST['templateName'] );
		$templateSubName = sanitize_text_field( $_POST['templateSubName'] );

		$settings = Admin\Settings::get_settings_saved_data();
		$footer = $settings['footer_text'];
		$settings = $settings[ $templateName ][ $templateSubName ];

		$templateName = Helper::get_email_template_name( $templateName, $templateSubName );
		ob_start();
		\AcademyPro\Helper::get_template('email/' . $templateName, array(
			'heading' => $settings['email_heading'],
			'content' => $settings['email_content'],
			'footer'  => $footer,
		));
		$preview = ob_get_clean();
		wp_send_json_success( $preview );
	}
}
