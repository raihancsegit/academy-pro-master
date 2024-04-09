<?php
namespace AcademyProZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class API {

	public static function init() {
		$self = new self();
		add_filter( 'rest_prepare_academy_zoom', array( $self, 'prepare_academy_zoom' ), 10, 3 );
	}

	public function prepare_academy_zoom( $item, $post, $request ) {
		$author_data = get_userdata( $item->data['author'] );
		$item->data['author_name'] = $author_data->display_name;
		$item->data['meta'] = array(
			'academy_zoom_request' => json_decode( $item->data['meta']['academy_zoom_request'], true ),
			'academy_zoom_response' => json_decode( $item->data['meta']['academy_zoom_response'], true )
		);
		return $item;
	}
}
