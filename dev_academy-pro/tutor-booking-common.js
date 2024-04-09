/* eslint-disable no-undef */
import { render } from '@wordpress/element';
import React from 'react';
import BookingWidget from '@Components/BookingWidget';
import './../assets/scss/frontendCommon.scss';
import { academyLogin } from '@Utils/frontendssr';
import {
	archiveBookingFilter,
	singleBookingCommentRating,
} from './tutor-booking/helper';

jQuery(document).ready(function () {
	academyLogin();
	archiveBookingFilter();
	singleBookingCommentRating();
});

jQuery(document).ready(function () {
	const academyProBookingCalendarWrap = document.getElementById(
		'academyProSingleBookingCalendar'
	);
	if (academyProBookingCalendarWrap) {
		render(
			<BookingWidget
				bookingId={academyProBookingCalendarWrap.getAttribute(
					'data-booking-id'
				)}
				settings={academyProBookingCalendarWrap.getAttribute(
					'data-calendar-settings'
				)}
			/>,
			document.getElementById('academyProSingleBookingCalendar')
		);
	}
});
