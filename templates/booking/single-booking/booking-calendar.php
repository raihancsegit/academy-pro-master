<div id="booknow" class="academy-single-booking__content-item academy-single-booking__content-item--booking-calendar">
	<h4><?php esc_html_e( 'Book Tutor', 'academy-pro' ); ?></h4>
	<div 
		id="academyProSingleBookingCalendar" 
		class="academy-single-booking-calendar"
		data-booking-id="<?php echo esc_attr( get_the_ID() ); ?>"
		data-calendar-settings="<?php echo esc_attr( \AcademyProTutorBooking\Helper::get_booking_calendar_settings( get_the_ID() ) ); ?>"
	></div>
</div>
