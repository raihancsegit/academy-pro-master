<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-bookings__header academy-col-12">
	<?php
	/**
	 * Hook: academy_pro/templates/booking/archive_booking_description.
	 *
	 * @Hooked: academy_archive_booking_header_filter - 10
	 */
	do_action( 'academy_pro/templates/booking/archive_booking_description' );
	?>	
</div>
