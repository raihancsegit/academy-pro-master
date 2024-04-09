import debounce from 'just-debounce-it';
import { ajaxurl, academy_nonce } from '@Utils/helper';

export const archiveBookingFilter = function () {
	if (jQuery('.academy-bookings__sidebar').length === 0) {
		return;
	}
	const filterData = {
		category: [],
		tags: [],
		levels: [],
		type: [],
		classType: [],
		search: '',
		orderby: '',
		paged: 1,
	};

	filterObserver('input.academy-archive-booking-filter', 'change');
	// sorting by order
	jQuery('.academy-bookings__header-ordering select').removeAttr('onchange');
	jQuery('.academy-bookings__header-ordering').on('change', function (e) {
		e.preventDefault();
		filterData.orderby = e.target.value;
		dispatchFilter(filterData);
	});
	// search
	jQuery('input.academy-archive-booking-search').on(
		'input',
		debounce(function (e) {
			filterData.search = e.target.value;
			dispatchFilter(filterData);
		}, 500)
	);

	// paged
	jQuery(document).on(
		'click',
		'.academy-bookings__pagination .page-numbers',
		function (e) {
			e.preventDefault();
			const that = jQuery(this);
			let paginationNumber = filterData.paged;
			if (that.hasClass('prev')) {
				paginationNumber--;
			} else if (that.hasClass('next')) {
				paginationNumber++;
			} else {
				paginationNumber = parseInt(that[0].innerHTML);
			}
			if (!that.hasClass('current')) {
				filterData.paged = paginationNumber;
				dispatchFilter(filterData);
			}
		}
	);

	function filterObserver(selector, eventType = 'change') {
		jQuery(selector).on(eventType, function () {
			setVariableData(
				jQuery(this).attr('name'),
				jQuery(this).val(),
				jQuery(this).is(':checked')
			);
			dispatchFilter(filterData);
		});
	}
	function setVariableData(selectorName, data, isUpdate) {
		if (isUpdate) {
			filterData[selectorName].push(data);
		} else {
			filterData[selectorName] = filterData[selectorName].filter(
				(item) => item !== data
			);
		}
	}
	function dispatchFilter(data = {}) {
		const responseWrap = jQuery(
			'.academy-bookings .academy-bookings__body'
		);
		const timeStart = Date.now();
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'academy_pro/booking/frontend/archive_booking_filter',
				security: academy_nonce,
				...data,
			},
			beforeSend() {
				responseWrap.css({ width: responseWrap.width() });
				jQuery('.academy-bookings').addClass(
					'academy-bookings--filter-overlay'
				);
				jQuery('html,body').css('cursor', 'progress');
			},
			success(response) {
				if (response.success) {
					setTimeout(
						function () {
							responseWrap.html(response.data.markup);
							jQuery(
								'.academy-bookings__header-result-count span'
							).html(response.data.found_posts);
							jQuery('.academy-bookings').removeClass(
								'academy-bookings--filter-overlay'
							);
							jQuery('html,body').css('cursor', 'inherit');
						},
						Date.now() - timeStart < 500 ? 500 : 0
					);
				}
			},
		});
	}
};

export const singleBookingCommentRating = () => {
	if (!jQuery('.academy-single-booking').length) return;
	jQuery('.academy-btn-add-review').on('click', () => {
		jQuery('.academy-review-form').toggleClass(
			'academy-review-form--open-form'
		);
	});
	// Star ratings for comments
	jQuery('#academy_rating')
		.hide()
		.before(
			`<p class="stars">\
                    <span>\
                        <a class="star-1" href="#">1</a>\
                        <a class="star-2" href="#">2</a>\
                        <a class="star-3" href="#">3</a>\
                        <a class="star-4" href="#">4</a>\
                        <a class="star-5" href="#">5</a>\
                    </span>\
                </p>`
		);
	jQuery('body')
		.on('click', '#respond p.stars a', function () {
			const jQuerystar = jQuery(this),
				jQueryrating = jQuery(this)
					.closest('#respond')
					.find('#academy_rating'),
				jQuerycontainer = jQuery(this).closest('.stars');

			jQueryrating.val(jQuerystar.text());
			jQuerystar.siblings('a').removeClass('active');
			jQuerystar.addClass('active');
			jQuerycontainer.addClass('selected');

			return false;
		})
		.on('click', '#respond #submit', function () {
			const jQueryrating = jQuery(this)
					.closest('#respond')
					.find('#academy_rating'),
				rating = jQueryrating.val();

			if (jQueryrating.length > 0 && !rating) {
				window.alert( 'Please select a rating' ); // eslint-disable-line
				return false;
			}
		});
};
