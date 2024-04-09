<?php
namespace AcademyProAssignments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Database {

	public static function init() {
		$self = new self();
		add_action( 'init', [ $self, 'create_academy_assignments_post_type' ] );
		add_action( 'rest_api_init', [ $self, 'register_academy_assignments_meta' ] );
	}

	public function create_academy_assignments_post_type() {
		$post_type = 'academy_assignments';
		register_post_type(
			$post_type,
			array(
				'labels'                => array(
					'name'                  => esc_html__( 'Assignments', 'academy-pro' ),
					'singular_name'         => esc_html__( 'Assignment', 'academy-pro' ),
					'search_items'          => esc_html__( 'Search Assignment', 'academy-pro' ),
					'parent_item_colon'     => esc_html__( 'Parent Assignment:', 'academy-pro' ),
					'not_found'             => esc_html__( 'No Assignments found.', 'academy-pro' ),
					'not_found_in_trash'    => esc_html__( 'No Assignments found in Trash.', 'academy-pro' ),
					'archives'              => esc_html__( 'Assignment archives', 'academy-pro' ),
				),
				'public'                => true,
				'publicly_queryable'    => true,
				'show_ui'               => false,
				'show_in_menu'          => false,
				'hierarchical'          => true,
				'rewrite'               => array( 'slug' => 'assignments' ),
				'query_var'             => true,
				'has_archive'           => true,
				'delete_with_user'      => false,
				'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'comments', 'post-formats', 'author' ),
				'show_in_rest'          => true,
				'rest_base'             => $post_type,
				'rest_namespace'        => ACADEMY_PLUGIN_SLUG . '/v1',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'capability_type'           => 'post',
				'capabilities'              => array(
					'edit_post'             => 'edit_academy_assignment',
					'read_post'             => 'read_academy_assignment',
					'delete_post'           => 'delete_academy_assignment',
					'delete_posts'          => 'delete_academy_assignments',
					'edit_posts'            => 'edit_academy_assignments',
					'edit_others_posts'     => 'edit_others_academy_assignments',
					'publish_posts'         => 'publish_academy_assignments',
					'read_private_posts'    => 'read_private_academy_assignments',
					'create_posts'          => 'edit_academy_assignments',
				),
			)
		);
	}

	public function register_academy_assignments_meta() {
		register_meta(
			'post',
			'academy_assignment_attachment',
			array(
				'object_subtype' => 'academy_assignments',
				'type'           => 'integer',
				'single'         => true,
				'show_in_rest'   => true,
			)
		);
		register_meta(
			'post',
			'academy_assignment_settings',
			array(
				'object_subtype' => 'academy_assignments',
				'type'           => 'object',
				'single'         => true,
				'show_in_rest'   => [
					'schema' => array(
						'type'       => 'object',
						'properties' => [
							'submission_time'   => array(
								'type' => 'integer',
							),
							'submission_time_unit' => array(
								'type' => 'string',
							),
							'minimum_passing_points'   => array(
								'type' => 'integer',
							),
							'total_points'   => array(
								'type' => 'integer',
							),
						],
					),
				],
			)
		);
	}
}
