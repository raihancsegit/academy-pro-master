<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Installer {

	public $academy_version;
	public static function init() {
		$self = new self();
		$self->academy_version = get_option( 'academy_pro_version' );
		// Save option table data
		$self->save_option();
	}

	public function save_option() {
		if ( ! $this->academy_version ) {
			add_option( 'academy_pro_version', ACADEMY_PRO_VERSION );
		}
	}
}
