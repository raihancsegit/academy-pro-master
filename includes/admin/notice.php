<?php
namespace AcademyPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Notice {
	public static function init() {
		$self = new self();
		add_action( 'plugins_loaded', array( $self, 'dispatch_notice' ), 30 );
	}

	public function dispatch_notice() {
		if ( ! did_action( 'academy_loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'academy_pro_fail_load' ) );
			return;
		}

		if ( ! version_compare( ACADEMY_VERSION, ACADEMY_PRO_REQUIRED_CORE_VERSION, '>=' ) ) {
			add_action( 'admin_notices', array( $this, 'academy_pro_fail_load_out_of_date' ) );
			return;
		}
	}

	public function academy_pro_fail_load() {
		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		$plugin = 'academy/academy.php';

		if ( \AcademyPro\Helper::is_academy_installed() ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

			$message = '<h3>' . esc_html__( 'Activate the Academy LMS Plugin', 'academy-pro' ) . '</h3>';
			$message .= '<p>' . esc_html__( 'Before you can use all the features of Academy LMS Pro, you need to activate the Academy LMS Free plugin first.', 'academy-pro' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, esc_html__( 'Activate Now', 'academy-pro' ) ) . '</p>';
		} else {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=academy' ), 'install-plugin_academy' );

			$message = '<h3>' . esc_html__( 'Install and Activate the Academy LMS Free Plugin', 'academy-pro' ) . '</h3>';
			$message .= '<p>' . esc_html__( 'Before you can use all the features of Academy LMS Pro, you need to install and activate the Academy LMS Free plugin first.', 'academy-pro' ) . '</p>';
			$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, esc_html__( 'Install Academy LMS', 'academy-pro' ) ) . '</p>';
		}//end if

		$this->print_error( $message );
	}

	public function academy_pro_fail_load_out_of_date() {
		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		$file_path = 'academy/academy.php';

		$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
		$message = '<p>' . esc_html__( 'Academy Pro is not working because you are using an old version of Academy.', 'academy-pro' ) . '</p>';
		$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_link, esc_html__( 'Update Academy Now', 'academy-pro' ) ) . '</p>';

		$this->print_error( $message );
	}

	public function print_error( $message ) {
		if ( ! $message ) {
			return;
		}
		// PHPCS - $message should not be escaped
		echo '<div class="error">' . $message . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
