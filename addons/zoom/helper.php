<?php
namespace AcademyProZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {
	public static function get_zoom_api_settings( $user_id ) {
		$zoom_meta_key = get_user_meta( $user_id, 'academy_pro_zoom_credentials', true );
		return json_decode( $zoom_meta_key, true );
	}

	public static function generate_access_token( $account_id, $client_id, $client_secret ) {
		$url = 'https://zoom.us/oauth/token?grant_type=account_credentials';

		if ( ! $account_id || ! $client_id || ! $client_secret ) {
			return false;
		}

		$url = $url . '&account_id=' . $account_id;
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encode   = base64_encode( $client_id . ':' . $client_secret );
		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'ContentType'   => 'application/x-www-form-urlencoded',
					'Authorization' => 'Basic' . $encode,
				),
				'timeout' => 60,
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$generate_token = json_decode( $response['body'] );
			if ( is_object( $generate_token ) && property_exists( $generate_token, 'access_token' ) ) {
				set_transient( 'academy_pro_zoom_access_token_' . get_current_user_id(), $generate_token->access_token, $generate_token->expires_in );
				return $generate_token->access_token;
			}
			return false;
		}
		return false;
	}
	public static function get_access_token( $user_id ) {
		$access_token = get_transient( 'academy_pro_zoom_access_token_' . $user_id );
		// if not have access token then create new one
		if ( false === $access_token ) {
			$settings = self::get_zoom_api_settings( $user_id );
			$access_token = self::generate_access_token( $settings['account_id'], $settings['client_id'], $settings['client_secret'] );
		}
		return $access_token;
	}

	public static function get_user_zoom_settings( $user_id ) {
		$settings = get_user_meta( $user_id, 'academy_pro_zoom_settings', true );
		if ( ! empty( $settings ) ) {
			return json_decode( $settings, true );
		}
		return array(
			'join_before_host'  => true,
			'host_video'        => false,
			'participant_video' => false,
			'mute_upon_entry'   => false,
			'auto_recording'    => false,
			'enforce_login'     => false,
		);
	}
}
