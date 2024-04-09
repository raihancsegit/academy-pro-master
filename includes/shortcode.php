<?php
namespace  AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcode {
	public static function init() {
		$self = new self();
		Shortcode\LoginRegister::init();
	}
}
