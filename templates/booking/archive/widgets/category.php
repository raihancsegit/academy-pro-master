<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( count( $categories ) ) :
	?>
<div class="academy-archive-booking-widget academy-archive-booking-widget--category">
	<h4 class="academy-archive-booking-widget__title"><?php esc_html_e( 'Category', 'academy-pro' ); ?>
	</h4>
	<div class="academy-archive-booking-widget__body">
		<?php
		foreach ( $categories as $parent_category ) :
			?>
		<label class="parent-term">
			<?php echo esc_html( $parent_category->name ); ?>
			<input class="academy-archive-booking-filter" type="checkbox" name="category"
				value="<?php echo esc_attr( $parent_category->slug ); ?>" />
			<span class="checkmark"></span>
		</label>
			<?php
			if ( count( $parent_category->children ) ) :
				foreach ( $parent_category->children as $child_category ) :
					?>
					<label class="child-term">
					<?php echo esc_html( $child_category->name ); ?>
						<input class="academy-archive-booking-filter" type="checkbox" name="category"
							value="<?php echo esc_attr( $child_category->slug ); ?>">
						<span class="checkmark"></span>
					</label>
					<?php
				endforeach;
		endif;
		endforeach;
		?>
	</div>
</div>
	<?php
endif;
