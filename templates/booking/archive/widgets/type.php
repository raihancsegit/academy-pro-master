<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div class="academy-archive-booking-widget academy-archive-booking-widget--type">
	<h4 class="academy-archive-booking-widget__title"><?php esc_html_e( 'Price Type', 'academy-pro' ); ?>
	</h4>
	<div class="academy-archive-booking-widget__body">
		<?php
		foreach ( $type as $key => $type_name ) :
			?>
		<label>
			<?php echo esc_html( $type_name ); ?>
			<input class="academy-archive-booking-filter" type="checkbox" name="type"
				value="<?php echo esc_attr( $key ); ?>" />
			<span class="checkmark"></span>
		</label>
			<?php
			endforeach;
		?>
	</div>
</div>
