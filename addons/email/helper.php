<?php
namespace AcademyProEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {

	public static function sanitize_email_template_data( $template_data ) {
		if ( ! is_array( $template_data ) ) {
			return $template_data;
		}
		$prepared_template_data = [];
		foreach ( $template_data as $template_name => $template_arr ) {
			$prepared_template_data[ $template_name ] = array(
				'is_enable' => filter_var( sanitize_text_field( $template_arr['is_enable'] ), FILTER_VALIDATE_BOOLEAN ),
				'email_subject' => sanitize_text_field( $template_arr['email_subject'] ),
				'email_heading' => sanitize_text_field( $template_arr['email_heading'] ),
				'email_content' => wp_kses_post( $template_arr['email_content'] ),
			);
		}
		return $prepared_template_data;
	}

	public static function get_instructors_email_by_course_id( $courseid ) {
		global $wpdb;
		$instructorsdata = $wpdb->get_results(
			$wpdb->prepare( "SELECT {$wpdb->users}.ID as user_id,{$wpdb->users}.user_email,{$wpdb->posts}.ID AS course_id FROM {$wpdb->posts} INNER JOIN {$wpdb->usermeta} ON {$wpdb->posts}.ID = {$wpdb->usermeta}.meta_value  INNER JOIN {$wpdb->users} ON {$wpdb->usermeta}.user_id = {$wpdb->users}.ID WHERE {$wpdb->posts}.ID = %d", $courseid ), ARRAY_A
		);

		$instructorsemail = [];

		foreach ( $instructorsdata as $instructor ) {
			array_push( $instructorsemail, $instructor['user_email'] );
		}

		return $instructorsemail;
	}


	public static function get_users_email_by_role( $role = 'Administrator' ) {
		$adminsobj = new \WP_User_Query( array( 'role' => $role ) );
		$adminsdata = $adminsobj->results;
		$adminemails = [];
		foreach ( $adminsdata as $adminemail ) {
			array_push( $adminemails, $adminemail->data->user_email );
		}

		return $adminemails;
	}

	public static function get_email_template_name( $templatename, $templatesubname ) {
		return str_replace( '_', '-', $templatename ) . '-' . $templatesubname . '.php';
	}
}
