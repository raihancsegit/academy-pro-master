<?php
namespace AcademyProZoom;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {

	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'create_academy_zoom_post_type' ] );
		add_action( 'rest_api_init', [ $self, 'register_academy_zoom_meta' ] );
	}

	public function create_academy_zoom_post_type() {
		$post_type = 'academy_zoom';
		register_post_type(
			$post_type,
			array(
				'labels'                => array(
					'name'                  => esc_html__( 'Zoom', 'academy-pro' ),
					'singular_name'         => esc_html__( 'zoom', 'academy-pro' ),
					'search_items'          => esc_html__( 'Search Zoom', 'academy-pro' ),
					'parent_item_colon'     => esc_html__( 'Parent Zoom:', 'academy-pro' ),
					'not_found'             => esc_html__( 'No zoom found.', 'academy-pro' ),
					'not_found_in_trash'    => esc_html__( 'No zoom found in Trash.', 'academy-pro' ),
					'archives'              => esc_html__( 'zoom archives', 'academy-pro' ),
				),
				'public'                => true,
				'publicly_queryable'    => true,
				'show_ui'               => false,
				'show_in_menu'          => false,
				'hierarchical'          => true,
				'rewrite'               => array( 'slug' => 'zoom' ),
				'query_var'             => true,
				'has_archive'           => true,
				'delete_with_user'      => false,
				'supports'              => array( 'title', 'editor', 'custom-fields', 'comments', 'post-formats', 'author' ),
				'show_in_rest'          => true,
				'rest_base'             => $post_type,
				'rest_namespace'        => ACADEMY_PLUGIN_SLUG . '/v1',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'capability_type'           => 'post',
				'capabilities'              => array(
					'edit_post'             => 'edit_academy_zoom',
					'read_post'             => 'read_academy_zoom',
					'delete_post'           => 'delete_academy_zoom',
					'delete_posts'          => 'delete_academy_zooms',
					'edit_posts'            => 'edit_academy_zooms',
					'edit_others_posts'     => 'edit_others_academy_zooms',
					'publish_posts'         => 'publish_academy_zooms',
					'read_private_posts'    => 'read_private_academy_zooms',
					'create_posts'          => 'edit_academy_zooms',
				),
			)
		);
	}

	public function register_academy_zoom_meta() {
		$course_meta = [
			'academy_zoom_request'          => 'string',
			'academy_zoom_response'         => 'string',
		];

		foreach ( $course_meta as $meta_key => $meta_value_type ) {
			register_meta(
				'post',
				$meta_key,
				array(
					'object_subtype' => 'academy_zoom',
					'type'           => $meta_value_type,
					'single'         => true,
					'show_in_rest'   => true,
				)
			);
		}
	}

}
