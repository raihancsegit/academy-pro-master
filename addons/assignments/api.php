<?php
namespace AcademyProAssignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AcademyProAssignments\Classes\Query;

class API {
	public static function init() {
		$self = new self();
		add_filter( 'academy/api/user/meta_values', array( $self, 'user_assignments_analytics' ) );
		add_filter( 'rest_prepare_academy_assignments', array( $self, 'add_author_name_to_rest_response' ), 10, 3 );
	}
	public function user_assignments_analytics( $values ) {
		$values['total_assignments'] = Query::get_total_number_of_assignments_by_instructor_id( get_current_user_id() );
		return $values;
	}
	public function add_author_name_to_rest_response( $item, $post, $request ) {
		$author_data = get_userdata( $item->data['author'] );
		$item->data['author_name'] = $author_data->display_name;
		return $item;
	}
}
