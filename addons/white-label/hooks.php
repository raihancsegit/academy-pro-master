<?php
namespace AcademyProWhiteLabel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Hooks {
	private $settings;
	public static function init() {
		$self = new self();
		$self->settings = Admin\Settings::get_settings_saved_data();
		add_filter( 'academy/admin/toplevel_menu_title', array( $self, 'menu_title' ) );
		add_filter( 'academy/customizer/panel_title', array( $self, 'menu_title' ) );
		add_filter( 'academy_pro/admin/settings_sub_menu_title', array( $self, 'pro_menu_title' ) );
		add_filter( 'academy/admin/toplevel_active_menu_icon', array( $self, 'toplevel_active_menu_icon' ) );
		add_filter( 'academy/admin/toplevel_inactive_menu_icon', array( $self, 'toplevel_inactive_menu_icon' ) );
		add_filter( 'academy/admin/logo_url', array( $self, 'logo_url' ) );
		add_filter( 'academy/assets/backend_scripts_data', array( $self, 'set_scripts_data' ) );
	}
	public function menu_title( $title ) {
		if ( isset( $this->settings['title'] ) && ! empty( $this->settings['title'] ) ) {
			return $this->settings['title'];
		}
		return $title;
	}
	public function pro_menu_title( $title ) {
		if ( isset( $this->settings['title'] ) && ! empty( $this->settings['title'] ) ) {
			return $this->settings['title'] . ' ' . __( 'Pro', 'academy-pro' );
		}
		return $title;
	}
	public function toplevel_active_menu_icon( $active_icon ) {
		if ( isset( $this->settings['active_menu_icon'] ) && ! empty( $this->settings['active_menu_icon'] ) ) {
			return wp_get_attachment_url( (int) $this->settings['active_menu_icon'] );
		}
		return $active_icon;
	}
	public function toplevel_inactive_menu_icon( $inactive_icon ) {
		if ( isset( $this->settings['inactive_menu_icon'] ) && ! empty( $this->settings['inactive_menu_icon'] ) ) {
			return wp_get_attachment_url( $this->settings['inactive_menu_icon'] );
		}
		return $inactive_icon;
	}
	public function logo_url( $logo_url ) {
		if ( isset( $this->settings['logo'] ) && ! empty( $this->settings['logo'] ) ) {
			return wp_get_attachment_url( $this->settings['logo'] );
		}
		return $logo_url;
	}
	public function set_scripts_data( $scripts_data ) {
		if ( isset( $this->settings['is_hide_settings'] ) ) {
			$scripts_data['is_hide_white_label_settings'] = (bool) $this->settings['is_hide_settings'];
		}
		return $scripts_data;
	}
}
