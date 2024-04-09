<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-booking__header">
	<?php
		do_action( 'academy_pro/templates/booking/before_booking_loop_header_inner' );
	?>
	<div class="academy-booking__thumbnail">
		<a href="<?php echo esc_url( get_the_permalink() ); ?>">
			<img class="academy-booking__thumbnail-image" src="<?php echo esc_url( Academy\Helper::get_the_course_thumbnail_url( 'academy_thumbnail' ) ); ?>" alt="<?php esc_html_e( 'thumbnail', 'academy-pro' ); ?>">
		</a>
	</div>
	<?php
		do_action( 'academy_pro/templates/booking/after_booking_loop_header_inner' );
	?>
</div>
