<?php
namespace  AcademyPro\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoginRegister {
	public static function init() {
		$self = new self();
		if ( \Academy\Helper::get_settings( 'is_enabled_recaptcha', false ) ) {
			// recaptcha
			add_action( 'academy/templates/login_form_before_submit', [ $self, 'add_google_recaptcha_markup' ] );
			add_action( 'academy/templates/instructor_reg_form_before_submit', [ $self, 'add_google_recaptcha_markup' ] );
			add_action( 'academy/templates/student_reg_form_before_submit', [ $self, 'add_google_recaptcha_markup' ] );
			// verify captcha
			add_action( 'academy/shortcode/before_login_signon', array( $self, 'verify_recaptcha_registration' ) );
			add_action( 'academy/shortcode/before_student_registration', array( $self, 'verify_recaptcha_registration' ) );
			add_action( 'academy/shortcode/before_instructor_registration', array( $self, 'verify_recaptcha_registration' ) );
		}
		// Check Email Verification
		if ( \Academy\Helper::get_settings( 'required_register_email_verification', false ) ) {
			add_action( 'academy/shortcode/before_student_registration', array( $self, 'email_verification_registration' ) );
			add_action( 'academy/shortcode/before_instructor_registration', array( $self, 'email_verification_registration' ) );
			add_filter( 'wp_authenticate', array( $self, 'restrict_unverified_users_login' ), 10 );
		}
	}
	public function add_google_recaptcha_markup() {
		$recaptcha_type = \Academy\Helper::get_settings( 'recaptcha_type', 'v2' );
		if ( 'v3' === $recaptcha_type ) {
			?>
			<div class="academy-form-group">
				<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response">
			</div>
			<?php
		} else {
			?>
			<div class="academy-form-group">
				<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( \Academy\Helper::get_settings( 'recaptcha_v2_site_key', false ) ); ?>"></div>
			</div>
			<?php
		}
	}

	public function verify_recaptcha_registration( $user_data ) {
		$recaptcha_type   = \Academy\Helper::get_settings( 'recaptcha_type', 'v2' );
		$recaptcha_secret = '';
		if ( 'v2' === $recaptcha_type ) {
			$recaptcha_secret = \Academy\Helper::get_settings( 'recaptcha_v2_secret_key', '' );
		} else {
			$recaptcha_secret = \Academy\Helper::get_settings( 'recaptcha_v3_secret_key', '' );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$verify               = $this->verify_recaptcha( sanitize_text_field( $_POST['g-recaptcha-response'] ), $_SERVER['REMOTE_ADDR'], $recaptcha_secret );
		if ( false === $verify ) {
			wp_send_json_error( [ __( 'Captcha is not matching, please try again.', 'academy-pro' ) ] );
		}
	}

	public function verify_recaptcha( $form_recaptcha_response, $server_remoteip, $recaptcha_secret_key ) {
		$google_url             = 'https://www.google.com/recaptcha/api/siteverify';
		$google_response        = add_query_arg(
			array(
				'secret'   => $recaptcha_secret_key,
				'response' => $form_recaptcha_response,
				'remoteip' => $server_remoteip,
			),
			$google_url
		);
		$google_response        = wp_remote_get( $google_response );
		$decode_google_response = json_decode( $google_response['body'] );
		return $decode_google_response->success;
	}

	public function email_verification_registration( $user_data ) {
		// Generate a verification token
		$verification_token = md5( uniqid() );
		if ( isset( $user_data['role'] ) ) {
			$user_data['role'] = 'pending';
		}
		$user_data['user_activation_key'] = $verification_token;
		$user_id = wp_insert_user( $user_data );
		if ( $user_id ) {
			// Store Verification token to verify user
			update_user_meta( $user_id, 'academy_email_verified', false );
			// if has role then it will be student
			if ( isset( $user_data['role'] ) ) {
				update_user_meta( $user_id, 'is_academy_student', \Academy\Helper::get_time() );
			} else {
				update_user_meta( $user_id, 'is_academy_instructor', \Academy\Helper::get_time() );
				update_user_meta( $user_id, 'academy_instructor_status', apply_filters( 'academy/admin/registration_instructor_status', 'pending' ) );
			}
			// Prepare email Template
			$user_name = $user_data['user_login'];
			$user_email = $user_data['user_email'];
			$site_url = site_url();
			$query_args = array(
				'email' => $user_email,
				'token' => $verification_token,
				'nonce'  => wp_create_nonce( 'academy_email_verification_' . $user_email ),
			);
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['redirect_to'] ) && ! empty( $_POST['redirect_to'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$query_args['redirect_to'] = sanitize_text_field( $_POST['redirect_to'] );
			}
			$verify_link = add_query_arg( $query_args, \Academy\Helper::get_page_permalink( 'frontend_dashboard_page' ) );
			// Compose the verification email
			$subject = 'Verify your email address';
			$body = "Hello $user_name,\n\n";
			$body .= "Thank you for registering $site_url\n\n";
			$body .= "Please click on the link below to verify your email address:\n\n";
			$body .= '<a href=' . $verify_link . " target='_blank'>$verify_link</a>\n\n";
			$body .= "If you did not register on our site, please ignore this email.\n\n";
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			$mail = new \AcademyProEmail\Email\Mail();
			$mail->send_mail( $user_email, $subject, $body, $headers );

			wp_send_json_success([
				'message'           => __( 'Thank you for submitting registration form. Please check your email inbox for a verification email from us. In order to complete your registration, please click the verification link provided in the email. If you do not receive the email within a few minutes, please check your spam or junk folder. If you still do not see the email, please contact us for assistance.', 'academy-pro' ),
				'redirect_url'      => false,
			]);
		}//end if
		wp_send_json_error( [ __( 'Failed to registered.', 'academy-pro' ) ] );
	}

	public function restrict_unverified_users_login( $user_login ) {
		$user = get_user_by( is_email( $user_login ) ? 'email' : 'login', $user_login );
		if ( ! $user || ! metadata_exists( 'user', $user->ID, 'academy_email_verified' ) ) {
			return $user_login;
		}
		// Check if the user's email is verified
		$email_verified = get_user_meta( $user->ID, 'academy_email_verified', true );
		if ( ! $email_verified ) {
			// If the email is not verified, prevent login and show an error message
			new \WP_Error( 'email_not_verified', __( 'Please verify your email before logging in.', 'academy-pro' ) );
			wp_send_json_error( __( 'Please verify your email before logging in.', 'academy-pro' ) );
		}
		return $user_login;
	}
}
