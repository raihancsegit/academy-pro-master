<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-booking__price">
	<?php
	if ( ! empty( $price ) ) {
		echo wp_kses_post( $price );
	} elseif ( empty( $price ) && $is_paid ) {
		esc_html_e( 'Paid', 'academy-pro' );
	} else {
		esc_html_e( 'Free', 'academy-pro' );
	}
	?>
</div>
