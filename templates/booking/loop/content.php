<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	global $authordata;
?>

<div class="academy-booking__body">
	<?php
		/**
		 * Hook - academy_pro/templates/booking/before_booking_loop_content_inner
		 */
		do_action( 'academy_pro/templates/booking/before_booking_loop_content_inner' );
		$categories = \AcademyProTutorBooking\Helper::get_the_booking_category( get_the_ID() );
	if ( ! empty( $categories ) ) {
		echo '<span class="academy-booking__meta academy-booking__meta--category"><a href="' . esc_url( get_term_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a></span>';
	}
	?>
	<h4 class="academy-booking__title"><a href="<?php echo esc_url( get_the_permalink() ); ?>"><?php the_title(); ?></a></h4>
	<div class="academy-booking__author-meta">
		<div class="academy-booking__author">
			<span class="author"><?php esc_html_e( 'BY -', 'academy-pro' ); ?>
				<?php
				if ( Academy\Helper::get_settings( 'is_show_public_profile' ) ) :
					?>
				<a href="<?php echo esc_url( home_url( '/author/' . $authordata->user_nicename ) ); ?>">
					<?php echo get_the_author(); ?>
				</a>
				<?php else : ?>
					<?php echo get_the_author(); ?>
				<?php endif; ?>
			</span>
		</div>
	</div>
	<?php
		do_action( 'academy/templates/booking/after_booking_loop_content_inner' );
	?>
</div>
