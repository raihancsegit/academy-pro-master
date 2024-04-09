<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-archive-booking-widget academy-archive-booking-widget--levels">
	<h4 class="academy-archive-booking-widget__title"><?php esc_html_e( 'Level', 'academy-pro' ); ?>
	</h4>
	<div class="academy-archive-booking-widget__body">
		<?php
		foreach ( $levels as $key => $label ) :
			?>
		<label>
			<?php echo esc_html( $label ); ?>
			<input class="academy-archive-booking-filter" type="checkbox" name="levels"
				value="<?php echo esc_attr( $key ); ?>">
			<span class="checkmark"></span>
		</label>
			<?php
		endforeach;
		?>
	</div>
</div>
