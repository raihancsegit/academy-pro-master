<?php
namespace AcademyProEmail;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hooks {
	private $mail;
	private $settings;

	public static function init() {
		$self = new self();
		$self->settings = Admin\Settings::get_settings_saved_data();
		$self->mail = new Email\Mail();

		// Course Enroll
		add_action( 'academy/course/after_enroll', array( $self, 'enrolled_course_email_to_admin' ), 10, 3 );
		add_action( 'academy/course/after_enroll', array( $self, 'enrolled_course_email_to_instructor' ), 10, 3 );
		add_action( 'academy/course/after_enroll', array( $self, 'enrolled_course_email_to_student' ), 10, 3 );

		// Finished Course
		add_action( 'academy/admin/course_complete_after', array( $self, 'finished_course_email_to_admin' ), 10, 2 );
		add_action( 'academy/admin/course_complete_after', array( $self, 'finished_course_email_to_instructor' ), 10, 2 );
		add_action( 'academy/admin/course_complete_after', array( $self, 'finished_course_email_to_student' ), 10, 2 );

		// Become an instructor
		add_action( 'academy/admin/after_instructor_registration', array( $self, 'become_an_instructor_request' ), 10, 1 );
		add_action( 'academy/admin/update_instructor_status', array( $self, 'become_an_instructor_accept' ), 10, 2 );
		add_action( 'academy/admin/update_instructor_status', array( $self, 'become_an_instructor_denied' ), 10, 2 );
	}

	private function get_settings( $template_name, $action_name ) {
		if ( isset( $this->settings[ $template_name ][ $action_name ] ) ) {
			return $this->settings[ $template_name ][ $action_name ];
		}
		return false;
	}

	private function get_global_setting( $setting_name ) {
		if ( isset( $this->settings[ $setting_name ] ) ) {
			return $this->settings[ $setting_name ];
		}
		return false;
	}

	public function enrolled_course_email_to_admin( $course_id, $enroll_id, $user_id ) {
		$settings = $this->get_settings( 'enrolled_course', 'admin' );
		if ( ! $settings['is_enable'] ) {
			return;
		}

		$student     = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$course = get_post( $course_id );
		$course_title = $course->post_title;
		$student_profile = get_edit_user_link( $student->ID );
		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}', '{course_name}' ],
			[ $user_display_name, $site_name, $site_url, $course_title ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/enrolled-course-admin.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{student_profile}' ],
			[ $user_display_name, $user_email, $course_title, $student_profile ],
			$body
		);
		foreach ( Helper::get_users_email_by_role() as $email ) {
			$this->mail->send_mail( $email, $subject, $body, $headers );
		}
	}

	public function enrolled_course_email_to_instructor( $course_id, $enroll_id, $user_id ) {
		$settings = $this->get_settings( 'enrolled_course', 'instructor' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$course = get_post( $course_id );
		$course_title = $course->post_title;
		$student_profile = get_edit_user_link( $student->ID );
		$dashboard = $site_url . '/dashboard';

		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}', '{course_name}' ],
			[ $user_display_name, $site_name, $site_url, $course_title ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/enrolled-course-instructor.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{instructor_dashboard}', '{student_profile}' ],
			[ $user_display_name, $user_email, $course_title, $dashboard, $student_profile ],
			$body
		);
		foreach ( Helper::get_instructors_email_by_course_id( $course_id ) as $email ) {
			$this->mail->send_mail( $email, $subject, $body, $headers );
		}
	}

	public function enrolled_course_email_to_student( $course_id, $enroll_id, $user_id ) {
		$settings = $this->get_settings( 'enrolled_course', 'user' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$course = get_post( $course_id );
		$course_title = $course->post_title;
		$permalink = get_permalink( $course_id );

		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}', '{course_name}' ],
			[ $user_display_name, $site_name, $site_url, $course_title ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/enrolled-course-user.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{course_url}' ],
			[ $user_display_name, $user_email, $course_title, $permalink ],
			$body
		);
		$this->mail->send_mail( $user_email, $subject, $body, $headers );

	}

	public function finished_course_email_to_admin( $course_id, $user_id ) {
		$settings = $this->get_settings( 'finished_course', 'admin' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$course = get_post( $course_id );
		$course_title = $course->post_title;
		$student_profile = get_edit_user_link( $student->ID );
		$to = get_option( 'admin_email' );
		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}' ],
			[ $user_display_name, $site_name, $site_url ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . strip_tags( $settings['email_content'], '<br>' ) . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/finished-course-admin.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{student_profile}' ],
			[ $user_display_name, $user_email, $course_title, $student_profile ],
			$body
		);
		foreach ( Helper::get_users_email_by_role() as $email ) {
			$this->mail->send_mail( $email, $subject, $body, $headers );
		}
	}
	public function finished_course_email_to_instructor( $course_id, $user_id ) {
		$settings = $this->get_settings( 'finished_course', 'instructor' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$course = get_post( $course_id );
		$course_title = $course->post_title;
		$student_profile = get_edit_user_link( $student->ID );

		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}' ],
			[ $user_display_name, $site_name, $site_url ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\r\n\r\nIf" . strip_tags( $settings['email_content'], '<br>' ) . "\r\n\r\nIf" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/finished-course-instructor.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{student_profile}' ],
			[ $user_display_name, $user_email, $course_title, $student_profile ],
			$body
		);
		foreach ( Helper::get_instructors_email_by_course_id( $course_id ) as $email ) {
			$this->mail->send_mail( $email, $subject, $body, $headers );
		}
	}
	public function finished_course_email_to_student( $course_id, $user_id ) {
		$settings = $this->get_settings( 'finished_course', 'user' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$course = get_post( $course_id );
		$course_title = $course->post_title;
		$permalink = get_permalink( $course_id );

		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}' ],
			[ $user_display_name, $site_name, $site_url ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/finished-course-user.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{user_display_name}', '{user_email}', '{course_name}', '{course_url}' ],
			[ $user_display_name, $user_email, $course_title, $permalink ],
			$body
		);
		$this->mail->send_mail( $user_email, $subject, $body, $headers );
	}

	public function become_an_instructor_request( $user_id ) {
		$settings = $this->get_settings( 'become_an_instructor', 'request' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;

		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}' ],
			[ $user_display_name, $user_display_name, $site_url ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/become-an-instructor-request.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{request_email}', '{site_title}', '{admin_user_manager}' ],
			[ $user_email, $site_name, esc_url( admin_url( 'admin.php?page=academy-instructors' ) ) ],
			$body
		);
		foreach ( Helper::get_users_email_by_role() as $email ) {
			$this->mail->send_mail( $email, $subject, $body, $headers );
		}
	}

	public function become_an_instructor_accept( $user_id, $status ) {
		if ( 'approved' !== $status ) {
			return;
		}
		$settings = $this->get_settings( 'become_an_instructor', 'accept' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_email = $student->user_email;
		$user_display_name = $student->display_name;
		$login_url = $site_url . '/wp-login.php';
		$login_url = '<a href="' . $login_url . '">Login</a>';
		$subject = str_replace(
			[ '{site_title}' ],
			[ $user_display_name ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/become-an-instructor-accept.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{site_title}', '{login_url}' ],
			[ $site_name, $login_url ],
			$body
		);
		$this->mail->send_mail( $user_email, $subject, $body, $headers );

	}

	public function become_an_instructor_denied( $user_id, $status ) {
		if ( 'remove' !== $status ) {
			return;
		}
		$settings = $this->get_settings( 'become_an_instructor', 'denied' );
		if ( ! $settings['is_enable'] ) {
			return;
		}
		$student            = get_userdata( $user_id );
		$site_url    = get_bloginfo( 'url' );
		$site_name   = get_bloginfo( 'name' );
		$user_display_name = $student->display_name;
		$user_email = $student->user_email;
		$subject = str_replace(
			[ '{user_display_name}', '{site_title}', '{site_url}' ],
			[ $user_display_name, $site_name, $site_url ],
			$settings['email_subject']
		);
		$footer = $this->get_global_setting( 'footer_text' );
		$email_type = $this->get_global_setting( 'email_content_type' );
		if ( 'plainText' === $email_type ) {
			$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
			$body = strip_tags( $settings['email_heading'], '<br>' ) . "\n" . strip_tags( $settings['email_content'], '<br>' ) . "\n" . strip_tags( $footer, '<br>' );
		} else {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			ob_start();
			\AcademyPro\Helper::get_template('email/become-an-instructor-accept.php', array(
				'heading' => $settings['email_heading'],
				'content' => $settings['email_content'],
				'footer' => $footer,
			));
			$body = ob_get_clean();
		}

		$body = str_replace(
			[ '{site_title}' ],
			[ $site_name ],
			$body
		);
		$this->mail->send_mail( $user_email, $subject, $body, $headers );
	}
}
