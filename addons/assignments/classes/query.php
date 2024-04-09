<?php
namespace AcademyProAssignments\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Query {
	public static function get_total_number_of_assignments_by_instructor_id( $instructor_id ) {
		global $wpdb;
		$results = $wpdb->get_var(
			$wpdb->prepare("SELECT COUNT(ID) 
            FROM {$wpdb->posts} 
            WHERE post_type = %s 
            AND post_author = %d
			AND post_status = %s", 'academy_assignments', $instructor_id, 'publish')
		);
		return (int) $results;
	}
	public static function get_total_number_of_assignments() {
		global $wpdb;
		$results = $wpdb->get_var(
			$wpdb->prepare("SELECT COUNT(ID) 
            FROM {$wpdb->posts} 
            WHERE post_type = %s 
			AND post_status = %s", 'academy_assignments', 'publish')
		);
		return (int) $results;
	}
}
