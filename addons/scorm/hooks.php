<?php
namespace AcademyProScorm;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Hooks {
	public static function init() {
		$self = new self();
		add_action( 'rest_api_init', array( $self, 'course_progress_endpoint' ) );
		add_filter( 'academy/assets/frontend_scripts_data', array( $self, 'set_scorm_data' ) );
		add_filter( 'academy/get_course_curriculums', array( $self, 'update_course_curriculums' ), 10, 2 );
		add_filter( 'academy/single/is_show_complete_form', array( $self, 'is_show_complete_form' ), 10, 3 );
	}

	public function get_scorm_file_url( $file_name ) {
		$upload_dir = wp_get_upload_dir();
		return trailingslashit( $upload_dir['baseurl'] ) . 'academy_uploads/' . $file_name;
	}

	public function get_scorm_file_path( $file_name ) {
		return ABSPATH . 'wp-content/uploads/academy_uploads/' . $file_name; // Replace with the actual path to your file
	}

	public function get_scorm_manifest( $path ) {
		$manifest_path = "{$path}/imsmanifest.xml";
		return ( file_exists( $manifest_path ) ) ? $manifest_path : false;
	}

	public function get_scorm_version( $scorm_file ) {
		$scorm_version = '1.2';
		if ( $scorm_file ) {
			$manifest = $this->get_scorm_manifest( $this->get_scorm_file_path( $scorm_file ) );
			if ( $manifest ) {
				$xml_file      = simplexml_load_file( $manifest );
				$scorm_version = '1.2';
				if ( ! empty( $xml_file->metadata ) && count( $xml_file->metadata ) >= 1 ) {
					$schema_version = $xml_file->metadata[0]->schemaversion;
					if ( ! empty( $schema_version ) && '1.2' != $schema_version ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
						$scorm_version = '2004';
					}
				} elseif ( ! empty( $xml_file['version'] ) ) {
					$scorm_version = (string) $xml_file['version'];
				}
				return $scorm_version;
			}
			return $scorm_version;
		}
		return $scorm_version;
	}

	public function get_scorm_iframe_url( $scorm_file ) {
		if ( $scorm_file ) {
			$scorm_file_url = $this->get_scorm_file_url( $scorm_file );
			$manifest = $this->get_scorm_manifest( $this->get_scorm_file_path( $scorm_file ) );
			if ( $manifest ) {
				$manifest      = simplexml_load_file( $manifest );
				if ( ! empty( $manifest )
					&& ! empty( $manifest->resources )
					&& ! empty( $manifest->resources->resource )
					&& ! empty( $manifest->resources->resource->attributes() )
				) {
					$atts = $manifest->resources->resource->attributes();
					if ( ! empty( $atts->href ) ) {
						return (string) $scorm_file_url . '/' . $atts->href;
					}
				}
			}
			return false;
		}
		return false;
	}

	public function course_progress_endpoint() {
		$namespace = ACADEMY_PRO_PLUGIN_SLUG . '/v1';
		register_rest_route(
			$namespace,
			'/scorm_course_progress/(?P<course_id>[\d-]+)/',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'scorm_get_course_progress' ),
			)
		);

		register_rest_route(
			$namespace,
			'/scorm_course_progress/(?P<course_id>[\d-]+)/',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => array( $this, 'scorm_update_course_progress' ),
			)
		);
	}
	public function scorm_get_course_progress( \WP_REST_Request $request ) {
		$params = $request->get_params();
		return apply_filters( 'academy_pro/scorm/get_course_progress', $params );
	}
	public function scorm_update_course_progress( \WP_REST_Request $request ) {
		$params = $request->get_params();
		$scorm_data         = $request->get_json_params();
		$scorm_url          = $request->get_url_params();
		return apply_filters( 'academy_pro/scorm/update_course_progress', false, $params, $scorm_data, $scorm_url );
	}
	public function set_scorm_data( $script_data ) {
		if ( is_singular( 'academy_courses' ) ) {
			$scorm_path = get_post_meta( get_the_ID(), '_academy_course_builder_scorm_file', true );
			$iframe_url = $this->get_scorm_iframe_url( $scorm_path );
			$version = $this->get_scorm_version( $scorm_path );
			$script_data['scrom_data'] = array(
				'url'       => $iframe_url,
				'version'   => $version
			);
		}
		return $script_data;
	}
	public function update_course_curriculums( $curriculums, $course_id ) {
		$scorm_path = get_post_meta( $course_id, '_academy_course_builder_scorm_file', true );
		if ( $scorm_path ) {
			return [];
		}
		return $curriculums;
	}
	public function is_show_complete_form( $is_show, $is_complete, $course_id ) {
		if ( ! $is_complete ) {
			$scorm_path = get_post_meta( $course_id, '_academy_course_builder_scorm_file', true );
			if ( $scorm_path ) {
				return false;
			}
			return $is_show;
		}
		return $is_show;
	}
}
