<?php
namespace AcademyProEmail\Admin;

use Academy\Interfaces\SettingsInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Settings implements SettingsInterface {
	public static function get_settings_saved_data() {
		$settings = get_option( ACADEMY_PRO_EMAIL_SETTINGS_NAME );
		if ( $settings ) {
			return json_decode( $settings, true );
		}
		return [];
	}
	public static function get_settings_default_data() {
		return apply_filters('academy_pro_email/admin/settings_default_data', [
			// general
			'form_name' => 'Academy LMS',
			'email_address' => get_option( 'admin_email' ),
			'email_content_type' => 'html',
			'header_image' => '',
			'footer_text' => '<!-- wp:heading --><h2 class="wp-block-heading">Thank You</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Academy LMS</p><!-- /wp:paragraph -->',
			// template
			'enrolled_course' => [
				'admin' => [
					'is_enable' => true,
					'email_subject' => '{user_display_name} enrolled in {course_name}',
					'email_heading' => 'Course enrollment completed. ',
					'email_content' => '<!-- wp:paragraph --><p>User <strong>{user_display_name}</strong> <strong>({user_email})</strong> has enrolled course <strong>{course_name}</strong> successfully </p><!-- /wp:paragraph -->',
				],
				'instructor' => [
					'is_enable' => true,
					'email_subject' => '{user_display_name} has enrolled course {course_name}',
					'email_heading' => 'User completed course enrolment',
					'email_content' => '<!-- wp:paragraph --><p>User <strong>{user_display_name}</strong> <strong>({user_email})</strong> has enrolled course <strong>{course_name}</strong> successfully </p><!-- /wp:paragraph -->',
				],
				'user' => [
					'is_enable' => true,
					'email_subject' => '{site_title} you have successfully enrolled course',
					'email_heading' => 'You have completed course enrollment',
					'email_content' => '<!-- wp:paragraph --><p>Congratulations! You have successfully enrolled in course <strong>{course_name}</strong></p><!-- /wp:paragraph -->',
				],
			],
			'finished_course' => [
				'admin' => [
					'is_enable' => true,
					'email_subject' => '{user_display_name} has completed a course ',
					'email_heading' => 'User Completed a course',
					'email_content' => '<!-- wp:paragraph --><p>User <strong>{user_display_name}</strong> <strong>({user_email})</strong> has completed a course <strong>{course_name}</strong> successfully</p><!-- /wp:paragraph -->',
				],
				'instructor' => [
					'is_enable' => true,
					'email_subject' => '{user_display_name} has finished course',
					'email_heading' => 'User Completed a course',
					'email_content' => '<!-- wp:paragraph --><p>User <strong>{user_display_name}</strong> <strong>({user_email})</strong> has completed a course <strong>{course_name}</strong> successfully</p><!-- /wp:paragraph -->',
				],
				'user' => [
					'is_enable' => true,
					'email_subject' => '{site_title} You have completed a course',
					'email_heading' => 'You have Completed a course ',
					'email_content' => '<!-- wp:paragraph --><p>Congrats! You have completed a course <strong>{course_name}</strong> successfully</p><!-- /wp:paragraph -->',
				],
			],
			'become_an_instructor' => [
				'request' => [
					'is_enable' => true,
					'email_subject' => '{site_title} Request to become an instructor',
					'email_heading' => 'Work as an instructor',
					'email_content' => '<!-- wp:paragraph -->
						<p>User <strong>{request_email}</strong> has applied to work as an instructor at <strong>{site_title}</strong></p>        
						<p>Please login to <strong>{site_title}</strong> and access to <strong>{admin_instructor_manager}</strong> instructing to manage your application.</p>
					<!-- /wp:paragraph -->',
				],
				'accept' => [
					'is_enable' => true,
					'email_subject' => '{site_title} Your Application to become an instructor was accepted',
					'email_heading' => 'Become an instructor request accepted',
					'email_content' => '<!-- wp:paragraph --><p>Congratulations! Your request to become an Instructor was accepted. </p><p>Simply <strong>{login_url}</strong> to <strong>{site_title}</strong>  and begin instructing</p><!-- /wp:paragraph -->',
				],
				'denied' => [
					'is_enable' => true,
					'email_subject' => '{site_title} Your Application to become an instructor was rejected',
					'email_heading' => 'Become an instructor request rejected',
					'email_content' => '<!-- wp:paragraph --><p>Unfortunately, your request to become an Instructor at <strong>{site_title}</strong>, was rejected. Please try again later.</p><!-- /wp:paragraph -->',
				],
			]
		]);
	}

	public static function save_settings( $form_data = false ) {
		$default_data = self::get_settings_default_data();
		$saved_data = self::get_settings_saved_data();
		$settings_data = wp_parse_args( $saved_data, $default_data );
		if ( $form_data ) {
			$settings_data = wp_parse_args( $form_data, $settings_data );
		}
		// if settings already saved, then update it
		if ( count( $saved_data ) ) {
			return update_option( ACADEMY_PRO_EMAIL_SETTINGS_NAME, wp_json_encode( $settings_data ), false );
		}
		return add_option( ACADEMY_PRO_EMAIL_SETTINGS_NAME, wp_json_encode( $settings_data ), '', false );
	}
}
