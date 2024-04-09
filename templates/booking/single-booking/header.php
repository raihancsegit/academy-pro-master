<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="academy-single-booking__preview">
	<img class="academy-booking__thumbnail-image" src="<?php echo esc_url( AcademyProTutorBooking\Helper::get_the_booking_thumbnail_url( 'academy_thumbnail' ) ); ?>" alt="<?php esc_html_e( 'thumbnail', 'academy-pro' ); ?>">
</div>

<?php
	$categories = \AcademyProTutorBooking\Helper::get_the_booking_category( get_the_ID() );
if ( ! empty( $categories ) ) {
	echo '<span class="academy-single-booking__category"><a href="' . esc_url( get_term_link( $categories[0]->term_id ) ) . '">' . esc_html( $categories[0]->name ) . '</a></span>';
}
?>
<h1 class="academy-single-booking__title"><?php the_title(); ?></h1>
