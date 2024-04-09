<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Academy\Helper;

class Integration {
	public static function init() {
		$self = new self();
		$self->add_woocommerce();
	}
	public function add_woocommerce() {
		if ( Helper::is_active_woocommerce() ) {
			Integration\Woocommerce::init();
		}
	}

}
