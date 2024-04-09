<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Frontend {
	public static function init() {
		$self = new self();
		$self->dispatch_hooks();
	}
	public function dispatch_hooks() {
		if ( \Academy\Helper::get_settings( 'required_register_email_verification', false ) ) {
			add_action( 'academy/templates/single_course_content', array( $this, 'show_verify_email_message' ), 05 );
			add_action( 'academy/shortcode/before_academy_dashboard', array( $this, 'show_verify_email_message' ) );
			add_action( 'template_redirect', array( $this, 'verify_email_address' ) );
		}
		if ( \Academy\Helper::get_settings( 'is_expire_course_enrollment', false ) ) {
			add_action( 'academy/course/is_enrolled_before', array( $this, 'cancel_enrollment' ), 10, 2 );
		}
		add_filter( 'academy/get_course_filter_types', array( $this, 'course_filter_by_type_args' ) );
	}
	public function verify_email_address() {
		// verify email
		if ( isset( $_GET['nonce'] ) && isset( $_GET['email'] ) && wp_verify_nonce( $_GET['nonce'], 'academy_email_verification_' . $_GET['email'] ) && ! is_user_logged_in() ) {
			global $wpdb;
			$verify_status = 'failed';
			$email = isset( $_GET['email'] ) ? sanitize_text_field( $_GET['email'] ) : '';
			$token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';
			$redirect_url = ( isset( $_GET['redirect_to'] ) ? sanitize_text_field( $_GET['redirect_to'] ) : \Academy\Helper::get_page_permalink( 'frontend_dashboard_page' ) );
			$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->users} WHERE user_email = %s AND user_activation_key = %s", $email, $token ) );
			if ( $user ) {
				$role = get_user_meta( $user->ID, 'is_academy_student' ) ? 'academy_student' : get_option( 'default_role' );
				update_user_meta( $user->ID, 'academy_email_verified', true );
				wp_update_user( array(
					'ID' => $user->ID,
					'role' => $role,
					'user_activation_key' => ''
				) );
				$verify_status = 'success';
				// Log in the user
				if ( $user ) {
					wp_set_current_user( $user->ID, $user->user_login );
					wp_set_auth_cookie( $user->ID );
				}
			}
			set_transient( 'academy_verify_email_status', $verify_status, MINUTE_IN_SECONDS );
			wp_safe_redirect( $redirect_url );
			exit;
		}//end if
		return false;
	}

	public function show_verify_email_message() {
		// Show Message
		$status = get_transient( 'academy_verify_email_status' );
		if ( $status ) {
			$message = __( 'Oops! Your email verification failed. Please try again or contact support for assistance.', 'academy-pro' );
			// message
			if ( 'success' === $status ) {
				$message = __( 'Congratulations! Your email has been successfully verified.', 'academy-pro' );
			}
			?>
			<div class="academy-verify-registration academy-verify-registration--<?php echo 'success' === $status ? 'success' : 'error'; ?>">
				<?php
					echo esc_html( $message );
				?>
			</div>
			<?php
			delete_transient( 'academy_verify_email_status' );
		}
		return false;
	}
	public function cancel_enrollment( $course_id, $user_id ) {
		$course_expire_enrollment = (int) get_post_meta( $course_id, 'academy_course_expire_enrollment', true );
		if ( ! $course_expire_enrollment ) {
			return;
		}
		global $wpdb;
		$expired_date = \Academy\Helper::get_time() - ( ( 60 * 60 * 24 ) * $course_expire_enrollment );

		$course_ids = $wpdb->get_col(
			$wpdb->prepare("SELECT ID FROM {$wpdb->posts}
            WHERE post_author=%d
                AND post_parent=%d
                AND post_type='academy_enrolled'
                AND post_status='completed'
                AND UNIX_TIMESTAMP(post_date)<%d", $user_id, $course_id, $expired_date)
		);

		if ( is_array( $course_ids ) && count( $course_ids ) ) {
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status='cancel' WHERE ID IN (%s)", implode( ',', $course_ids ) ) );
			foreach ( $course_ids as $course_id ) {
				do_action( 'academy_pro/course/after_expired', $course_id );
			}
		}

	}

	public function course_filter_by_type_args( $filter ) {
		$filter['public'] = __( 'Public', 'academy-pro' );
		return $filter;
	}
}
