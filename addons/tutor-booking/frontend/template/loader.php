<?php
namespace  AcademyProTutorBooking\Frontend\Template;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Loader {
	public static function init() {
		$self = new self();
		add_filter( 'template_include', array( $self, 'template_loader' ) );
		add_filter( 'comments_template', array( $self, 'load_comments_template' ) );
	}

	public function template_loader( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		$default_file = $this->get_template_loader_default_file();

		if ( $default_file ) {
			/**
			 * Filter hook to choose which files to find before Academy does it's own logic.
			 *
			 * @var array
			 */
			$search_files = $this->get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );

			if ( ! $template ) {
				if ( false !== strpos( $default_file, 'academy_booking_category' ) || false !== strpos( $default_file, 'academy_booking_tag' ) ) {
					$cs_template = str_replace( '_', '-', $default_file );
					$template    = \AcademyPro\Helper::plugin_path() . 'templates/booking/' . $cs_template;
				} else {
					$template = \AcademyPro\Helper::plugin_path() . 'templates/booking/' . $default_file;
				}
			}
		}
		return $template;
	}

	/**
	 * Get the default filename for a template.
	 *
	 * @return string
	 */
	private function get_template_loader_default_file() {
		if ( is_singular( 'academy_booking' ) ) {
			$default_file = 'single-booking.php';
		} elseif ( \AcademyProTutorBooking\Helper::is_booking_taxonomy() ) {
			if ( is_tax( 'academy_booking_category' ) ) {
				$default_file = 'taxonomy-booking-category.php';
			} elseif ( is_tax( 'academy_booking_tag' ) ) {
				$default_file = 'taxonomy-booking-tag.php';
			} else {
				$default_file = 'archive-booking.php';
			}
		} elseif ( is_post_type_archive( 'academy_booking' ) ) {
			$default_file = 'archive-booking.php';
		} else {
			$default_file = '';
		}
		return $default_file;
	}

	private function get_template_loader_files( $default_file ) {
		$templates   = apply_filters( 'academy_pro_tutor_booking\frontend\template\loader_files', array(), $default_file );
		$templates[] = 'academy-pro.php';

		if ( is_page_template() ) {
			$page_template = get_page_template_slug();

			if ( $page_template ) {
				$validated_file = validate_file( $page_template );
				if ( 0 === $validated_file ) {
					$templates[] = $page_template;
				} else {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( "Academy Pro Booking: Unable to validate template path: \"$page_template\". Error Code: $validated_file." );
				}
			}
		}

		if ( is_singular( 'academy_booking' ) ) {
			$object       = get_queried_object();
			$name_decoded = urldecode( $object->post_name );
			if ( $name_decoded !== $object->post_name ) {
				$templates[] = "single-booking-{$name_decoded}.php";
			}
			$templates[] = "single-booking-{$object->post_name}.php";
		}

		if ( \AcademyProTutorBooking\Helper::is_booking_taxonomy() ) {
			$object = get_queried_object();
			if ( is_tax( 'academy_booking_category' ) ) {
				$templates[] = 'taxonomy-booking-category-' . $object->slug . '.php';
				$templates[] = \AcademyPro\Helper::template_path() . 'taxonomy-booking-category-' . $object->slug . '.php';
				$templates[] = 'taxonomy-booking-category.php';
				$templates[] = \AcademyPro\Helper::template_path() . 'taxonomy-booking-category.php';
			} elseif ( is_tax( 'academy_booking_tag' ) ) {
				$templates[] = 'taxonomy-booking-tag-' . $object->slug . '.php';
				$templates[] = \AcademyPro\Helper::template_path() . 'taxonomy-booking-tag-' . $object->slug . '.php';
				$templates[] = 'taxonomy-booking-tag.php';
				$templates[] = \AcademyPro\Helper::template_path() . 'taxonomy-booking-tag.php';
			}
			$cs_default  = str_replace( '_', '-', $default_file );
			$templates[] = $cs_default;
		}

		$templates[] = $default_file;
		if ( isset( $cs_default ) ) {
			$templates[] = \AcademyPro\Helper::template_path() . $cs_default;
		}
		$templates[] = \AcademyPro\Helper::template_path() . $default_file;

		return array_unique( $templates );
	}

	/**
	 * Load comments template.
	 *
	 * @param string $template template to load.
	 * @return string
	 */
	public function load_comments_template( $template ) {
		if ( get_post_type() !== 'academy_booking' ) {
			return $template;
		}

		$check_dirs = array(
			trailingslashit( get_stylesheet_directory() ) . \AcademyPro\Helper::plugin_path(),
			trailingslashit( get_template_directory() ) . \AcademyPro\Helper::plugin_path(),
			trailingslashit( get_stylesheet_directory() ),
			trailingslashit( get_template_directory() ),
			trailingslashit( \AcademyPro\Helper::plugin_path() ) . 'templates/booking/',
		);

		if ( ACADEMY_TEMPLATE_DEBUG_MODE ) {
			$check_dirs = array( array_pop( $check_dirs ) );
		}

		foreach ( $check_dirs as $dir ) {
			if ( file_exists( trailingslashit( $dir ) . 'single-booking-reviews.php' ) ) {
				return trailingslashit( $dir ) . 'single-booking-reviews.php';
			}
		}
	}
}
