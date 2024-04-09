<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	get_header( 'booking' );

?>

<?php
	/**
	 * @hook - academy/templates/before_main_content
	 */
	do_action( 'academy/templates/before_main_content', 'archive-booking.php' );
?>

<div class="academy-bookings">
	<div class="academy-container">
		<div class="academy-row">
			<div class="academy-col-12">
				<?php do_action( 'academy_pro/templates/booking/archive_booking_header' ); ?>
			</div>
			<div class="academy-col-md-9">
				<?php do_action( 'academy_pro/templates/booking/archive_booking_content' ); ?>
			</div>
			<div class="academy-col-md-3">
				<?php do_action( 'academy_pro/templates/booking/archive_booking_sidebar' ); ?>
			</div>
			<div class="academy-col-12">
				<?php do_action( 'academy_pro/templates/booking/archive_booking_footer' ); ?>
			</div>
		</div>
	</div>
</div>

<?php
	/**
	 * @hook - academy/templates/after_main_content
	 */
	do_action( 'academy/templates/after_main_content', 'archive-booking.php' );
?>

<?php
get_footer( 'booking' );
