<?php
namespace AcademyProZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro_zoom/frontend/render_zoom', array( $self, 'render_zoom' ) );
		add_action( 'wp_ajax_academy_pro_zoom/get_zoom_settings', array( $self, 'get_zoom_settings' ) );
		add_action( 'wp_ajax_academy_pro_zoom/save_zoom_settings', array( $self, 'save_zoom_settings' ) );
		add_action( 'wp_ajax_academy_pro_zoom/get_zoom_meeting_credentials', array( $self, 'get_zoom_meeting_credentials' ) );
		add_action( 'wp_ajax_academy_pro_zoom/save_zoom_meeting_credentials', array( $self, 'save_zoom_meeting_credentials' ) );
		add_action( 'wp_ajax_academy_pro_zoom/create_zoom_meeting', array( $self, 'create_zoom_meeting' ) );
		add_action( 'wp_ajax_academy_pro_zoom/update_zoom_meeting', array( $self, 'update_zoom_meeting' ) );
		add_action( 'wp_ajax_academy_pro_zoom/delete_zoom_meeting', array( $self, 'delete_zoom_meeting' ) );
	}
	public function render_zoom() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$course_id = (int) sanitize_text_field( $_POST['course_id'] );
		$zoom_id = (int) sanitize_text_field( $_POST['zoom_id'] );
		$user_id   = (int) get_current_user_id();

		$is_administrator = current_user_can( 'administrator' );
		$is_instructor    = \Academy\Helper::is_instructor_of_this_course( $user_id, $course_id );
		$enrolled         = \Academy\Helper::is_enrolled( $course_id, $user_id );
		$is_public_course = \Academy\Helper::is_public_course( $course_id );

		if ( $is_administrator || $is_instructor || $enrolled || $is_public_course ) {
			do_action( 'academy_pro_zoom/frontend/before_render_zoom', $course_id, $zoom_id );
			$zoom_meeting = get_post( $zoom_id );
			if ( ! $zoom_meeting ) {
				wp_send_json_error( esc_html__( 'Sorry, something went wrong!', 'academy-pro' ) );
			}
			$meeting = json_decode( get_post_meta( $zoom_id, 'academy_zoom_response', true ), true );
			$zoom_author_id = (int) get_post_field( 'post_author', $zoom_id );
			$meeting['is_meeting_creator'] = get_current_user_id() === $zoom_author_id ? true : false;
			$meeting['meeting_start_time'] = new \DateTime( $meeting['start_time'], new \DateTimeZone( 'UTC' ) );
			wp_send_json_success( $meeting );
		}//end if
		wp_send_json_error( __( 'Access Denied', 'academy-pro' ) );
	}

	public function get_zoom_settings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}
		$settings = get_user_meta( get_current_user_id(), 'academy_pro_zoom_settings', true );
		wp_send_json_success( json_decode( $settings, true ) );
	}

	public function save_zoom_settings() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}
		$enforce_login = (bool) ( isset( $_POST['enforce_login'] ) ? \Academy\Helper::sanitize_checkbox_field( $_POST['enforce_login'] ) : false );
		$host_video = (bool) ( isset( $_POST['host_video'] ) ? \Academy\Helper::sanitize_checkbox_field( $_POST['host_video'] ) : false );
		$join_before_host = (bool) ( isset( $_POST['join_before_host'] ) ? \Academy\Helper::sanitize_checkbox_field( $_POST['join_before_host'] ) : false );
		$mute_participants = (bool) ( isset( $_POST['mute_participants'] ) ? \Academy\Helper::sanitize_checkbox_field( $_POST['mute_participants'] ) : false );
		$participants_video = (bool) ( isset( $_POST['participants_video'] ) ? \Academy\Helper::sanitize_checkbox_field( $_POST['participants_video'] ) : false );
		$recording_settings = ( isset( $_POST['recording_settings'] ) ? \Academy\Helper::sanitize_checkbox_field( $_POST['recording_settings'] ) : 'none' );
		$settings = [
			'join_before_host'      => $join_before_host,
			'host_video'            => $host_video,
			'participants_video'    => $participants_video,
			'mute_participants'     => $mute_participants,
			'enforce_login'         => $enforce_login,
			'recording_settings'    => $recording_settings,
		];
		update_user_meta( get_current_user_id(), 'academy_pro_zoom_settings', wp_json_encode( $settings ) );
		wp_send_json_success( $settings );
	}

	public function get_zoom_meeting_credentials() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}
		$user_id = get_current_user_id();
		$credentials = get_user_meta( $user_id, 'academy_pro_zoom_credentials', true );
		wp_send_json_success( json_decode( $credentials, true ) );
	}

	public function save_zoom_meeting_credentials() {
		check_ajax_referer( 'academy_nonce', 'security' );

		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$account_id = sanitize_text_field( $_POST['account_id'] );
		$client_id = sanitize_text_field( $_POST['client_id'] );
		$client_secret = sanitize_text_field( $_POST['client_secret'] );

		if ( empty( $account_id ) || empty( $client_id ) || empty( $client_secret ) ) {
			wp_send_json_error( __( 'Account ID, Client ID and Client Secret is required', 'academy-pro' ) );
		}

		$response = wp_remote_retrieve_body( wp_remote_get('https://api.zoom.us/v2/users/me', array(
			'method'     => 'GET',
			'headers' => array(
				'Authorization' => 'Bearer ' . Helper::generate_access_token( $account_id, $client_id, $client_secret ),
				'Content-Type' => 'application/json',
			),
			'sslverify'  => false,
		)) );
		$response = json_decode( $response, true );
		if ( count( $response ) && 'active' === $response['status'] ) {
			$user_id = get_current_user_id();
			$form_data = array(
				'account_id' => $account_id,
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'host_email' => $response['email'],
				'status' => $response['status']
			);
			update_user_meta( $user_id, 'academy_pro_zoom_credentials', wp_json_encode( $form_data ) );
			wp_send_json_success( $form_data );
		}
		wp_send_json_error( __( 'Wrong API Key and Secret Key', 'academy-pro' ) );
	}

	public function create_zoom_meeting() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$user_id = get_current_user_id();
		$meeting_title = ( isset( $_POST['meetingTitle'] ) ? sanitize_text_field( $_POST['meetingTitle'] ) : '' );
		$meeting_description = ( isset( $_POST['meetingSummary'] ) ? sanitize_text_field( $_POST['meetingSummary'] ) : '' );
		$meeting_date = ( isset( $_POST['meetingDate'] ) ? sanitize_text_field( $_POST['meetingDate'] ) : '' );
		$meeting_time = ( isset( $_POST['meetingTime'] ) ? sanitize_text_field( $_POST['meetingTime'] ) : '' );
		$meeting_duration = ( isset( $_POST['meetingDuration'] ) ? sanitize_text_field( $_POST['meetingDuration'] ) : '' );
		$meeting_duration_unit = ( isset( $_POST['meetingTimeUnit'] ) ? sanitize_text_field( $_POST['meetingTimeUnit'] ) : 'minute' );
		$timezone = ( isset( $_POST['meetingTimeZone'] ) ? sanitize_text_field( $_POST['meetingTimeZone'] ) : '' );
		$password = sanitize_text_field( $_POST['meetingPassword'] );

		$meeting_start_time    = \DateTime::createFromFormat( 'Y-m-d H:i', $meeting_date . ' ' . $meeting_time );
		if ( ! $meeting_start_time ) {
			wp_send_json_error( __( 'Sorry, Invalid Date Time Format', 'academy-pro' ) );
		}

		$request_body     = wp_json_encode(array(
			'topic'      => $meeting_title,
			'description' => $meeting_description,
			'type'       => 2,
			'start_time' => $meeting_start_time->format( 'Y-m-d\TH:i:s' ),
			'timezone'   => $timezone,
			'duration'   => ( 'hour' === $meeting_duration_unit ? $meeting_duration * 60 : $meeting_duration ),
			'password'   => $password,
			'settings'   => Helper::get_user_zoom_settings( $user_id ),
		));

		$response = wp_remote_retrieve_body(wp_remote_post('https://api.zoom.us/v2/users/me/meetings', array(
			'method'     => 'POST',
			'headers' => array(
				'Authorization' => 'Bearer ' . Helper::get_access_token( $user_id ),
				'Content-Type' => 'application/json',
			),
			'sslverify'  => false,
			'body'  => $request_body
		)));

		$response_array = json_decode( $response, true );
		if ( empty( $response ) || ! isset( $response_array['id'] ) ) {
			wp_send_json_error( __( 'Sorry, Failed to Create Zoom Meeting.', 'academy-pro' ) );
		}

		$args = array(
			'post_title'    => $meeting_title,
			'post_content'  => $meeting_description,
			'post_status'   => 'publish',
			'post_author'   => $user_id,
			'post_type'     => 'academy_zoom',
			'meta_input'    => array(
				'academy_zoom_request' => $request_body,
				'academy_zoom_response' => $response

			)
		);
		$is_post = wp_insert_post( $args );
		wp_send_json_success( [ 'post_id' => $is_post ] );

	}

	public function update_zoom_meeting() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}

		$user_id = get_current_user_id();
		$post_id = (int) ( isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : 0 );
		$meeting_id = (int) ( isset( $_POST['meeting_id'] ) ? sanitize_text_field( $_POST['meeting_id'] ) : 0 );
		$meeting_title = ( isset( $_POST['meetingTitle'] ) ? sanitize_text_field( $_POST['meetingTitle'] ) : '' );
		$meeting_description = ( isset( $_POST['meetingSummary'] ) ? sanitize_text_field( $_POST['meetingSummary'] ) : '' );
		$meeting_date = ( isset( $_POST['meetingDate'] ) ? sanitize_text_field( $_POST['meetingDate'] ) : '' );
		$meeting_time = ( isset( $_POST['meetingTime'] ) ? sanitize_text_field( $_POST['meetingTime'] ) : '' );
		$meeting_duration = ( isset( $_POST['meetingDuration'] ) ? sanitize_text_field( $_POST['meetingDuration'] ) : '' );
		$meeting_duration_unit = ( isset( $_POST['meetingTimeUnit'] ) ? sanitize_text_field( $_POST['meetingTimeUnit'] ) : 'minute' );
		$timezone = ( isset( $_POST['meetingTimeZone'] ) ? sanitize_text_field( $_POST['meetingTimeZone'] ) : '' );
		$password = sanitize_text_field( $_POST['meetingPassword'] );

		$meeting_start_time    = \DateTime::createFromFormat( 'Y-m-d H:i', $meeting_date . ' ' . $meeting_time );
		if ( ! $meeting_start_time ) {
			wp_send_json_error( __( 'Sorry, Invalid Date Time Format', 'academy-pro' ) );
		}

		$request_body     = wp_json_encode(array(
			'topic'      => $meeting_title,
			'type'       => 2,
			'start_time' => $meeting_start_time->format( 'Y-m-d\TH:i:s' ),
			'timezone'   => $timezone,
			'duration'   => ( 'hour' === $meeting_duration_unit ? $meeting_duration * 60 : $meeting_duration ),
			'password'   => $password,
			'settings'   => Helper::get_user_zoom_settings( $user_id ),
		));

			$response_code = wp_remote_retrieve_response_code(wp_remote_post('https://api.zoom.us/v2/meetings/' . $meeting_id, array(
				'method'     => 'PATCH',
				'headers' => array(
					'Authorization' => 'Bearer ' . Helper::get_access_token( $user_id ),
					'Content-Type' => 'application/json',
				),
				'sslverify'  => false,
				'body'  => $request_body
			)));

		if ( 204 !== (int) $response_code ) {
			wp_send_json_error( __( 'Sorry, Failed to update zoom meeting', 'academy-pro' ) );
		}

		// Update Existing Response
		$zoom_response = json_decode( get_post_meta( $post_id, 'academy_zoom_response', true ), true );
		$zoom_response['topic']   = $meeting_title;
		$zoom_response['start_time'] = $meeting_start_time->format( 'Y-m-d\TH:i:s' );
		$zoom_response['duration'] = ( 'hour' === $meeting_duration_unit ? $meeting_duration * 60 : $meeting_duration );
		$zoom_response['timezone'] = $timezone;
		$zoom_response['password'] = $password;
		$zoom_response['settings'] = array_merge( $zoom_response['settings'], Helper::get_user_zoom_settings( $user_id ) );

		$args = array(
			'ID' => $post_id,
			'post_title'    => $meeting_title,
			'post_content'  => $meeting_description,
			'post_status'   => 'publish',
			'post_author'   => $user_id,
			'post_type'     => 'academy_zoom',
			'meta_input'    => array(
				'academy_zoom_request' => $request_body,
				'academy_zoom_response' => wp_json_encode( $zoom_response )
			)
		);
		$is_post = wp_update_post( $args );
		wp_send_json_success( [ 'post_id' => $is_post ] );
	}

	public function delete_zoom_meeting() {
		check_ajax_referer( 'academy_nonce', 'security' );
		if ( ! current_user_can( 'manage_academy_instructor' ) ) {
			wp_die();
		}
		$user_id = get_current_user_id();
		$post_id = ( isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '' );
		$meeting_id = ( isset( $_POST['meeting_id'] ) ? sanitize_text_field( $_POST['meeting_id'] ) : '' );

		$request_body     = wp_json_encode(array(
			'meetingId' => $meeting_id,
		));

		$response_code = wp_remote_retrieve_response_code(wp_remote_post('https://api.zoom.us/v2/meetings/' . $meeting_id, array(
			'method'     => 'DELETE',
			'headers' => array(
				'Authorization' => 'Bearer ' . Helper::get_access_token( $user_id ),
				'Content-Type' => 'application/json',
			),
			'sslverify'  => false,
			'body'  => $request_body
		)));

		if ( 204 !== (int) $response_code && 404 !== $response_code ) {
			wp_send_json_error( __( 'Sorry, Failed to Delete Zoom Meeting.', 'academy-pro' ) );
		}

		wp_delete_post( $post_id, true );
		wp_send_json_success( [ 'post_id' => $post_id ] );
	}


}
