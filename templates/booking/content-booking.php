<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	$column_per_row = array(
		'desktop' => 3,
		'tablet' => 2,
		'mobile' => 1,
	);
	$grid_class = Academy\Helper::get_responsive_column( $column_per_row );
	?>
<div class="<?php echo esc_attr( $grid_class ); ?>">
	<div class="academy-booking">
		<?php
			do_action( 'academy_pro/templates/booking/before_booking_loop' );
			/**
			 * @hook - academy_pro/templates/booking/booking_loop_header
			 *
			 * @Hooked -
			 */
			do_action( 'academy_pro/templates/booking/booking_loop_header' );
			/**
			 * @hook - academy_pro/templates/booking/booking_loop_content
			 *
			 * @Hooked -
			 */
			do_action( 'academy_pro/templates/booking/booking_loop_content' );
			/**
			 * @hook - academy_pro/templates/booking/booking_loop_footer
			 *
			 * @Hooked -
			 */
			do_action( 'academy_pro/templates/booking/booking_loop_footer' );

			do_action( 'academy_pro/templates/booking/after_booking_loop_item' );
		?>
	</div>
</div>
