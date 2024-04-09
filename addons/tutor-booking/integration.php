<?php
namespace AcademyProTutorBooking;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integration {
	public static function init() {
		$self = new self();
		$self->add_woocommerce();
	}
	public function add_woocommerce() {
		if ( \Academy\Helper::is_active_woocommerce() ) {
			Integration\Woocommerce::init();
		}
	}

}
