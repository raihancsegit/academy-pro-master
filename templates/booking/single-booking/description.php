<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-single-booking__content-item academy-single-booking__content-item--description">
	<h2 class="academy-single-booking__content-item--description-title"><?php esc_html_e( 'Booking Overview', 'academy-pro' ); ?></h2>
	<?php
		the_content();
	?>
</div>
