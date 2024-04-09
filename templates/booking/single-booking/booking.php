<div class="academy-widget-booking academy-sticky-widget ">
	<div class="academy-widget-booking__head">
	<?php
	if ( $is_paid ) {
		if ( $price ) {
			echo '<div class="academy-booking-price">' . wp_kses_post( $price ) . '</div>';
		} else {
			echo '<div class="academy-booking-type">' . esc_html__( 'Paid', 'academy-pro' ) . '</div>';
		}
	} else {
		echo '<div class="academy-booking-type">' . esc_html__( 'Free', 'academy-pro' ) . '</div>';
	}
	?>
	</div>
	<div class="academy-widget-booking__content">
		<ul class="academy-widget-booking__content-lists">
			<li>
				<span class="label">
					<span class="academy-icon academy-icon--skill"></span>
					<?php esc_html_e( 'Class Type', 'academy-pro' ); ?>
				</span>
				<span class="data"><?php echo esc_html( $class_types ); ?></span>
			</li>
			<li>
				<span class="label">
					<span class="academy-icon academy-icon--add"></span>
					<?php esc_html_e( 'Schedule Types', 'academy-pro' ); ?>
				</span>
				<span class="data"><?php echo esc_html( $schedule_type ); ?></span>
			</li>
			<li>
				<span class="label">
					<span class="academy-icon academy-icon--clock"></span>
					<?php esc_html_e( 'Duration', 'academy-pro' ); ?>
				</span>
				<span class="data"><?php echo esc_html( $duration ); ?></span>
			</li>
		</ul>
	</div>
	<div class="academy-widget-booking__footer">
		<?php
		if ( is_user_logged_in() ) :
			?>
		<a href="<?php echo esc_url( $my_booking_page_url ); ?>" class="academy-btn academy-btn--preset-purple"><?php esc_html_e( 'My Booking', 'academy-pro' ); ?></a>
			<?php
			endif;
		?>
		<a href="#booknow" class="academy-btn academy-btn--preset-purple"><?php esc_html_e( 'Book Now', 'academy-pro' ); ?></a>
	</div>
</div>
