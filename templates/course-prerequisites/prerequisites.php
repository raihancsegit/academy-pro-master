<div class="academy-widget-enroll__prerequisites">
	<p class="academy-prerequisites-message"><?php esc_html_e( 'NOTE: You have to pass below courses before you can enroll this course.', 'academy-pro' ); ?></p>
	<ul class="academy-prerequisites-lists">
		<?php
		foreach ( $required_courses as $course_id ) :
			?>
		<li><a href="<?php echo esc_url( get_permalink( $course_id ) ); ?>"><?php echo esc_html( get_the_title( $course_id ) ); ?></a></li>
			<?php
			endforeach;
		?>
	</ul>
</div>
