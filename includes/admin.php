<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Admin {
	public static function init() {
		$self = new self();
		$self->dispatch_hooks();
	}
	public function dispatch_hooks() {
		Admin\License::init();
		Admin\Settings::init();
		Admin\Export::init();
	}
}
