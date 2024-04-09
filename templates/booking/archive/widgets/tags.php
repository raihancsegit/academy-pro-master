<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( count( $tags ) ) :
	?>
<div class="academy-archive-booking-widget academy-archive-booking-widget--tags">
	<h4 class="academy-archive-booking-widget__title"><?php esc_html_e( 'Tag', 'academy-pro' ); ?>
	</h4>
	<div class="academy-archive-booking-widget__body">
	<?php
	foreach ( $tags as $tag_item ) :
		?>
		<label>
		<?php echo esc_html( $tag_item->name ); ?>
			<input class="academy-archive-booking-filter" type="checkbox" name="tags"
				value="<?php echo esc_attr( $tag_item->slug ); ?>" />
			<span class="checkmark"></span>
		</label>
		<?php
		endforeach;
	?>
	</div>
</div>
<?php endif;
