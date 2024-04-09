<?php
/**
 * The Template for displaying all single bookings
 *
 * This template can be overridden by copying it to yourtheme/academy/single-course.php.
 *
 * the readme will list any important changes.
 *
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

		get_header( 'booking' );

		/**
		 * @hook - academy_pro/templates/booking/before_main_content
		 */
		do_action( 'academy_pro/templates/booking/before_main_content', 'single-booking.php' );
?>

		<div class="academy-single-booking">
			<div class="academy-container">
				<div class="academy-row">
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>

						<?php AcademyPro\Helper::get_template_part( 'booking/content', 'single-booking' ); ?>


					<?php endwhile; // end of the loop. ?>

					<?php
						/**
						 * @hook - academy_pro/templates/booking/single_booking_sidebar
						 *
						 * @hooked
						 */
						do_action( 'academy_pro/templates/booking/single_booking_sidebar' );
					?>
				</div>
			</div>
		</div>
	<?php
		/**
		 * @hook - academy/templates/after_main_content
		 */
		do_action( 'academy/templates/after_main_content', 'single-booking.php' );
	?>

<?php
	get_footer( 'booking' );
