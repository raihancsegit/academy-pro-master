<?php
namespace AcademyPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Addons {
	public static function init() {
		$self = new self();
		// Load all addons
		$self->addons_loader();
	}

	private function addons_loader() {
		$Autoload = Autoload::get_instance();
		$addons = apply_filters('academy_pro/addons/loader_args', [
			'email' => 'Email',
			'assignments' => 'Assignments',
			'content-drip' => 'ContentDrip',
			'enrollment' => 'Enrollment',
			'woocommerce-subscriptions' => 'WoocommerceSubscriptions',
			'zoom' => 'Zoom',
			'advanced-analytics' => 'AdvancedAnalytics',
			'tutor-booking' => 'TutorBooking',
			'paid-memberships-pro' => 'PaidMembershipsPro',
			'course-prerequisites' => 'CoursePrerequisites',
			'white-label' => 'WhiteLabel',
			'scorm' => 'Scorm',
		]);
		foreach ( $addons as $addon_name => $addon_class_name ) {
			$addon_root_path = ACADEMY_PRO_ADDONS_DIR_PATH . $addon_name . '/';
			// Register the addon's root namespace and path.
			$addon_namespace = 'AcademyPro' . $addon_class_name;
			$Autoload->add_namespace_directory( $addon_namespace, $addon_root_path );
			// Initialize the addon's main class.
			$class = $addon_namespace . '\\' . $addon_class_name;
			$class::init();
		}
	}
}
