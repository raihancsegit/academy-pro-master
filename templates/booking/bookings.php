<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-bookings__body">
	<div class="academy-row">
		<?php
			do_action( 'academy_pro/templates/booking/before_booking_loop' );
		if ( have_posts() ) {
			// Load posts loop.
			while ( have_posts() ) {
				the_post();
				/**
				 * Hook: academy/templates/course_loop.
				 */
				do_action( 'academy_pro/templates/booking/booking_loop' );

				AcademyPro\Helper::get_template_part( 'booking/content', 'booking' );
			}

			/**
			 * Hook: academy/templates/after_course_loop
			 *
			 * @Hooked: academy_course_pagination - 10
			 */
			do_action( 'academy_pro/templates/booking/after_booking_loop' );

		} else {
			// If no content, include the "No posts found" template.
			/**
			 * Hook: academy/templates/no_course_found
			 *
			 * @Hooked: academy_no_course_found - 10
			 */
			do_action( 'academy_pro/templates/booking/no_booking_found' );
		}//end if
		?>
	</div>
</div>
