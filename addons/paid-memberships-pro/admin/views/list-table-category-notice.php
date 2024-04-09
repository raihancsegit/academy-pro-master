<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! count( $unassign_category ) ) {
	return;
}
?>
<div class="academy-pmpro-categories-notice">
	<div class="entry-left">
		<span class="dashicons dashicons-info-outline"></span>
	</div>
	<div class="entry-right">
		<h3><?php esc_html_e( 'Academy course categories that are not assigned to any level.', 'academy-pro' ); ?></h3>
		<p><?php esc_html_e( 'You need to assign a level to each course category in Academy LMS. Otherwise, they will be free for everyone. This is important if you want to earn money from your courses.', 'academy-pro' ); ?></p>
		<div class="unassign-categories">
			<?php
			foreach ( $unassign_category as $category ) {
				?>
					<strong><?php echo esc_html( $category->name ); ?></strong>
				<?php
			}
			?>
		</div>
	</div>
</div>
