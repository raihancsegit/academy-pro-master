<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<td>
	<?php
	if ( $recommend_badge ) :
		?>
	<span class="dashicons dashicons-star-filled"></span>
		<?php
		endif;
	?>
</td>
<td>
	<?php
	if ( 'full_website_membership' === $membership_model ) :
		?>
	<strong><?php esc_html_e( 'Full Site Membership', 'academy-pro' ); ?></strong>
		<?php
		elseif ( 'category_wise_membership' === $membership_model ) :
			?>
	<strong><?php esc_html_e( 'Category Wise Membership', 'academy-pro' ); ?></strong>
	<div>
			<?php
			$category = pmpro_getMembershipCategories( $level->id );
			if ( is_array( $category ) && count( $category ) ) {
				?>
					<ul>
						<?php
						foreach ( $category as $term_id ) {
							$term_item = get_term_by( 'id', $term_id );
							if ( $term_item ) :
								?>
									<li><?php echo esc_html( $term_item->name ); ?></li>
								<?php
							endif;
						}
						?>
					</ul>
				<?php
			}
			?>
	</div>
			<?php
		endif;
		?>
</td>
