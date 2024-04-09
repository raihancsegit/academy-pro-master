<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="academy-review-form">
	<div class="academy-review-form__add-review">
		<button class="academy-btn academy-btn--bg-purple academy-btn-add-review"><?php esc_html_e( 'Add Review', 'academy-pro' ); ?></button>
	</div>
	<?php
	$comment_form = array(
		/* translators: %s is product title */
		'title_reply'         => '',
		/* translators: %s is product title */
		'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'academy-pro' ),
		'title_reply_before'  => '<span id="reply-title" class="academy-review-reply-title">',
		'title_reply_after'   => '</span>',
		'comment_notes_after' => '',
		'label_submit'        => esc_html__( 'Submit', 'academy-pro' ),
		'class_submit'        => 'academy-btn academy-btn--bg-purple',
		'logged_in_as'        => '',
		'comment_field'       => '',
	);

	$name_email_required = true;
	$fields              = array(
		'author' => array(
			'label'    => __( 'Name', 'academy-pro' ),
			'type'     => 'text',
			'value'    => '',
			'required' => $name_email_required,
		),
		'email'  => array(
			'label'    => __( 'Email', 'academy-pro' ),
			'type'     => 'email',
			'value'    => '',
			'required' => $name_email_required,
		),
	);

	$comment_form['fields'] = array();

	foreach ( $fields as $key => $field ) {
		$field_html  = '<p class="academy-review-form-' . esc_attr( $key ) . '">';
		$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

		if ( $field['required'] ) {
			$field_html .= '&nbsp;<span class="required">*</span>';
		}

		$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

		$comment_form['fields'][ $key ] = $field_html;
	}

	$login_page_url = wp_login_url( get_permalink() );
	if ( $login_page_url ) {
		/* translators: %s opening and closing link tags respectively */
		$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'academy-pro' ), '<a href="' . esc_url( $login_page_url ) . '">', '</a>' ) . '</p>';
	}

	$comment_form['comment_field'] = '<div class="academy-review-form-rating"><select name="academy_rating" id="academy_rating" required>
		<option value="">' . esc_html__( 'Rate&hellip;', 'academy-pro' ) . '</option>
		<option value="5">' . esc_html__( 'Perfect', 'academy-pro' ) . '</option>
		<option value="4">' . esc_html__( 'Good', 'academy-pro' ) . '</option>
		<option value="3">' . esc_html__( 'Average', 'academy-pro' ) . '</option>
		<option value="2">' . esc_html__( 'Not that bad', 'academy-pro' ) . '</option>
		<option value="1">' . esc_html__( 'Very poor', 'academy-pro' ) . '</option>
	</select></div>';

	$comment_form['comment_field'] .= '<p class="academy-review-form-review"><textarea id="academy_comment" name="comment" cols="45" rows="8" placeholder="' . esc_html__( 'Enter your feedback', 'academy-pro' ) . '" required></textarea></p>';

	comment_form( apply_filters( 'academy_pro/templates/booking/booking_review_comment_form_args', $comment_form ) );
	?>
</div>
