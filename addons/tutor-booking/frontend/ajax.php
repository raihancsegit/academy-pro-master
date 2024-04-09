<?php
namespace AcademyProTutorBooking\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AcademyProTutorBooking\Helper;

class Ajax {
	public static function init() {
		$self = new self();
		add_action( 'wp_ajax_academy_pro/booking/frontend/do_booked', array( $self, 'do_booked' ) );
		add_action( 'wp_ajax_academy_pro/booking/frontend/get_my_booking', array( $self, 'get_my_booking' ) );
		add_action( 'wp_ajax_academy_pro/booking/frontend/get_booking', array( $self, 'get_booking' ) );
		add_action( 'wp_ajax_academy_pro/booking/frontend/get_booked_schedule_details', array( $self, 'get_booked_schedule_details' ) );
		add_action( 'wp_ajax_academy_pro/booking/frontend/archive_booking_filter', array( $self, 'archive_booking_filter' ) );
		add_action( 'wp_ajax_nopriv_academy_pro/booking/frontend/archive_booking_filter', array( $self, 'archive_booking_filter' ) );
		add_action( 'wp_ajax_academy_pro/booking/frontend/cancel_booking', array( $self, 'cancel_booked_schedule' ) );
		add_action( 'wp_ajax_academy_pro/booking/frontend/render_booking', array( $self, 'render_booking' ) );
	}
	public function do_booked() {
		check_ajax_referer( 'academy_nonce', 'security' );

		$booking_id = (int) sanitize_text_field( $_POST['booking_id'] );
		$selected_date = (string) sanitize_text_field( $_POST['selectedDate'] );
		$selected_time_slot = (string) sanitize_text_field( $_POST['selectedTimeSlot'] );
		$time_zone = (string) sanitize_text_field( $_POST['timeZone'] );
		// Generate date time.
		$schedule_date_time = $selected_date . ' ' . $selected_time_slot . ' ' . $time_zone;
		$user_id = get_current_user_id();
		$booking_type = Helper::get_booking_type( $booking_id );

		// Run booking process
		if ( 'paid' === $booking_type ) {
			if ( \Academy\Helper::is_active_woocommerce() ) {
				global $woocommerce;
				$product_id = Helper::get_booked_product_id( $booking_id );
				$woocommerce->cart->add_to_cart( $product_id, 1, 0, array(), array( 'booked_schedule_date_time' => $schedule_date_time ) );
				wp_send_json_success(array(
					'message' => __( 'Congratulations, successfully Added to cart and redirecting...', 'academy-pro' ),
					'booking_type'   => $booking_type,
					'redirect_url' => esc_url( wc_get_checkout_url() )
				));
			}
		} else {
			$is_booked = Helper::do_booked( $booking_id, $user_id, $schedule_date_time );
			if ( $is_booked ) {
				wp_send_json_success(array(
					'message' => __( 'Congratulations, successfully booked.', 'academy-pro' ),
					'booking_type'   => $booking_type,
					'redirect_url' => esc_url( get_the_permalink( $booking_id ) )
				));
			}
		}//end if

		wp_send_json_error( array( 'message' => __( 'Sorry, Failed to booked!!', 'academy-pro' ) ) );
	}

	public function get_my_booking() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$user_id = get_current_user_id();

		$args = array(
			'post_type'      => 'academy_booked',
			'post_status'    => 'completed',
			'numberposts'    => 100,
			'author'         => $user_id
		);

		$response = array();
		$my_booked = get_posts( $args );
		foreach ( $my_booked as $booked_item ) {
			$response[] = Helper::prepare_booked_response( $booked_item );
		}
		wp_send_json_success( $response );
	}

	public function get_booking() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$booking_id = ( isset( $_POST['booking_id'] ) ? sanitize_text_field( $_POST['booking_id'] ) : '' );

		$args = array(
			'post_type'      => 'academy_booked',
			'post_status'    => 'completed',
			'post_parent'    => $booking_id,
		);

		$response = array();
		$my_booked = get_posts( $args );
		foreach ( $my_booked as $booked_item ) {
			$response[] = Helper::prepare_booked_response( $booked_item );
		}
		wp_send_json_success( $response );
	}

	public function get_booked_schedule_details() {
		check_ajax_referer( 'academy_nonce', 'security' );

		$booking_id = sanitize_text_field( $_POST['booking_id'] );
		$order_id = get_post_meta( $booking_id, '_academy_booked_by_order_id', true );

		$response = [];

		$user = get_user_by( 'id', get_current_user_id() );
		$first_name = $user->first_name;
		$last_name = $user->last_name;
		$full_name = $first_name . ' ' . $last_name;
		$email_address = $user->user_email;

		$response = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'full_name' => $full_name,
			'email_address' => $email_address,
		);

		if ( $order_id && \Academy\Helper::is_active_woocommerce() ) {
			$order = wc_get_order( $order_id );
			$response['payment_method'] = $order->get_payment_method_title();
			$response['payment_status'] = $order->get_status();
		}

		wp_send_json_success( $response );
	}

	public function archive_booking_filter() {
		check_ajax_referer( 'academy_nonce', 'security' );

		$search   = ( isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '' );
		$category = ( isset( $_POST['category'] ) ? \Academy\Helper::sanitize_text_or_array_field( $_POST['category'] ) : [] );
		$tags     = ( isset( $_POST['tags'] ) ? \Academy\Helper::sanitize_text_or_array_field( $_POST['tags'] ) : [] );
		$levels   = ( isset( $_POST['levels'] ) ? \Academy\Helper::sanitize_text_or_array_field( $_POST['levels'] ) : [] );
		$type     = ( isset( $_POST['type'] ) ? \Academy\Helper::sanitize_text_or_array_field( $_POST['type'] ) : [] );
		$classType     = ( isset( $_POST['classType'] ) ? \Academy\Helper::sanitize_text_or_array_field( $_POST['classType'] ) : [] );
		$orderby  = ( isset( $_POST['orderby'] ) ? sanitize_text_field( $_POST['orderby'] ) : '' );
		$paged    = ( isset( $_POST['paged'] ) ) ? absint( $_POST['paged'] ) : 1;

		$args = \AcademyProTutorBooking\Helper::prepare_booking_search_query_args(
			[
				'search'         => $search,
				'category'       => $category,
				'tags'           => $tags,
				'levels'         => $levels,
				'type'           => $type,
				'classType'      => $classType,
				'paged'          => $paged,
				'orderby'        => $orderby,
				'posts_per_page' => (int) \Academy\Helper::get_customizer_settings( 'course_per_page', 12 ),
			]
		);

		$courses = new \WP_Query( $args );
		ob_start();
		?>
		<div class="academy-row">
				<?php
				if ( $courses->have_posts() ) :
					while ( $courses->have_posts() ) :
						$courses->the_post();
						\AcademyPro\Helper::get_template_part( 'booking/content', 'booking' );
				endwhile;
					wp_reset_query(); else :
						?>
			<div class='academy-mybooking'>
				<h3 class='academy-not-found'><?php esc_html_e( 'Sorry no course found.', 'academy-pro' ); ?>
				</h3>
			</div>
				<?php endif; ?>
			<div class="academy-courses__pagination academy-col-md-12">
				<?php
				$big = 999999999;
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo paginate_links(
					array(
						'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format'    => '?paged=%#%',
						'current'   => max( 1, $paged ),
						'total'     => $courses->max_num_pages,
						'prev_text' => '<i class="academy-icon academy-icon--angle-left"></i>',
						'next_text' => '<i class="academy-icon academy-icon--angle-right"></i>',
					)
				);
				?>
			</div>
		</div>
		<?php
		$markup = ob_get_clean();
		wp_send_json_success(
			[
				'markup'      => $markup,
				'found_posts' => $courses->found_posts,
			]
		);
		wp_die();
	}
	public function cancel_booked_schedule() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$booked_id = sanitize_text_field( $_POST['booked_id'] );
		$user_id = (int) sanitize_text_field( $_POST['user_id'] );

		if ( get_current_user_ID() !== $user_id ) {
			wp_send_json_error( __( 'Sorry, you have no permission to cancel appointment', 'academy-pro' ) );
		}

		$is_update = wp_update_post( array(
			'ID'        => $booked_id,
			'post_status'   => 'cancel',
		), true, true );

		wp_send_json_success( $is_update );
	}
	public function render_booking() {
		check_ajax_referer( 'academy_nonce', 'security' );
		$course_id = (int) sanitize_text_field( $_POST['course_id'] );
		$booking_id = sanitize_text_field( $_POST['booking_id'] );
		$booking = get_post( $booking_id );
		if ( ! $booking ) {
			wp_send_json_error( esc_html__( 'Sorry, something went wrong!', 'academy-pro' ) );
		}

		do_action( 'academy_pro_tutor_booking/frontend/before_render_booking', $course_id, $booking_id );

		$response = [
			'ID' => $booking_id,
			'settings' => \AcademyProTutorBooking\Helper::get_booking_calendar_settings( $booking_id ),
		];
		wp_send_json_success( $response );
	}
}
