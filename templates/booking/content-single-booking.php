<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	do_action( 'academy_pro/templates/booking/before_single_booking' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

?>
	<div class="academy-col-lg-8">
	<?php
		/**
		 * @hook - academy_pro/templates/booking/single_booking_content
		 *
		 * @hooked -
		 */
		do_action( 'academy_pro/templates/booking/single_booking_content' );
	?>
	</div>
	<?php
	do_action( 'academy_pro/templates/booking/after_single_booking' );
