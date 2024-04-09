<?php
namespace AcademyProTutorBooking\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class PermalinkSettings {

	/**
	 * Permalink settings.
	 *
	 * @var array
	 */
	private $permalinks = array();

	public static function init() {
		$self = new self();
		$self->settings_init();
		$self->settings_save();
	}

	/**
	 * Init our settings.
	 */
	public function settings_init() {
		\add_settings_section( 'academy-tutor-booking-permalink', __( 'Academy Tutor Booking permalinks', 'academy-pro' ), array( $this, 'settings' ), 'permalink' );

		\add_settings_field(
			'academy_tutor_booking_category_slug',
			__( 'Tutor Booking category base', 'academy-pro' ),
			array( $this, 'tutor_booking_category_slug_input' ),
			'permalink',
			'optional'
		);
		\add_settings_field(
			'academy_tutor_booking_tag_slug',
			__( 'Tutor Booking tag base', 'academy-pro' ),
			array( $this, 'tutor_booking_tag_slug_input' ),
			'permalink',
			'optional'
		);
		$this->permalinks = \AcademyProTutorBooking\Helper::get_permalink_structure();
	}

	/**
	 * Show a slug input box.
	 */
	public function tutor_booking_category_slug_input() {
		?>
		<input name="academy_tutor_booking_category_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['category_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'booking-category', 'slug', 'academy-pro' ); ?>" />
		<?php
	}

	/**
	 * Show a slug input box.
	 */
	public function tutor_booking_tag_slug_input() {
		?>
		<input name="academy_tutor_booking_tag_slug" type="text" class="regular-text code" value="<?php echo esc_attr( $this->permalinks['tag_base'] ); ?>" placeholder="<?php echo esc_attr_x( 'booking-tag', 'slug', 'academy-pro' ); ?>" />
		<?php
	}

	/**
	 * Show the settings.
	 */
	public function settings() {
		/* translators: %s: Home URL */
		echo wp_kses_post( wpautop( sprintf( __( 'If you like, you may enter custom structures for your booking URLs here. For example, using <code>booking</code> would make your booking links like <code>%sbooking/sample-booking/</code>. This setting affects booking URLs only, not things such as booking categories.', 'academy-pro' ), esc_url( home_url( '/' ) ) ) ) );

		$tutor_booking_page_id = (int) \Academy\Helper::get_settings( 'tutor_booking_page' );
		$base_slug    = urldecode( ( $tutor_booking_page_id > 0 && get_post( $tutor_booking_page_id ) ) ? get_page_uri( $tutor_booking_page_id ) : _x( 'booking', 'default-slug', 'academy-pro' ) );
		$booking_base = _x( 'booking', 'default-slug', 'academy-pro' );

		$structures = array(
			0 => '',
			1 => '/' . trailingslashit( $base_slug ),
			2 => '/' . trailingslashit( $base_slug ) . trailingslashit( '%tutor_booking_category%' ),
		);

		?>
		<table class="form-table academy-tutor-booking-permalink-structure">
			<tbody>
				<tr>
					<th><label><input name="tutor_booking_permalink" type="radio" value="<?php echo esc_attr( $structures[0] ); ?>" class="academytbog" <?php checked( $structures[0], $this->permalinks['booking_base'] ); ?> /> <?php esc_html_e( 'Default', 'academy-pro' ); ?></label></th>
					<td><code class="default-example"><?php echo esc_html( home_url() ); ?>/?booking=sample-booking</code> <code class="non-default-example"><?php echo esc_html( home_url() ); ?>/<?php echo esc_html( $booking_base ); ?>/sample-booking/</code></td>
				</tr>
				<?php if ( $tutor_booking_page_id ) : ?>
					<tr>
						<th><label><input name="tutor_booking_permalink" type="radio" value="<?php echo esc_attr( $structures[1] ); ?>" class="academytbog" <?php checked( $structures[1], $this->permalinks['booking_base'] ); ?> /> <?php esc_html_e( 'Bookings base', 'academy-pro' ); ?></label></th>
						<td><code><?php echo esc_html( home_url() ); ?>/<?php echo esc_html( $base_slug ); ?>/sample-booking/</code></td>
					</tr>
				<?php endif; ?>
				<tr>
					<th><label><input name="tutor_booking_permalink" id="academy_tutor_booking_custom_selection" type="radio" value="custom" class="tog" <?php checked( in_array( $this->permalinks['booking_base'], $structures, true ), false ); ?> />
						<?php esc_html_e( 'Custom base', 'academy-pro' ); ?></label></th>
					<td>
						<input name="tutor_booking_permalink_structure" id="academy_tutor_booking_permalink_structure" type="text" value="<?php echo esc_attr( $this->permalinks['booking_base'] ? trailingslashit( $this->permalinks['booking_base'] ) : '' ); ?>" class="regular-text code"> <span class="description"><?php esc_html_e( 'Enter a custom base to use. A base must be set or WordPress will use default instead.', 'academy-pro' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php wp_nonce_field( 'academy-pro-tutor-booking-permalinks', 'academy-pro-tutor-booking-permalinks-nonce' ); ?>
		<script type="text/javascript">
			jQuery( function() {
				jQuery('input.academytbog').on( 'change', function() {
					jQuery('#academy_tutor_booking_permalink_structure').val( jQuery( this ).val() );
				});
				jQuery('.permalink-structure input').on( 'change', function() {
					jQuery('.academy-tutor-booking-permalink-structure').find('code.non-default-example, code.default-example').hide();
					if ( jQuery(this).val() ) {
						jQuery('.academy-tutor-booking-permalink-structure code.non-default-example').show();
						jQuery('.academy-tutor-booking-permalink-structure input').prop('disabled', false);
					} else {
						jQuery('.academy-tutor-booking-permalink-structure code.default-example').show();
						jQuery('.academy-tutor-booking-permalink-structure input:eq(0)').trigger( 'click' );
						jQuery('.academy-tutor-booking-permalink-structure input').attr('disabled', 'disabled');
					}
				});
				jQuery('.permalink-structure input:checked').trigger( 'change' );
				jQuery('#academy_tutor_booking_permalink_structure').on( 'focus', function(){
					jQuery('#academy_tutor_booking_custom_selection').trigger( 'click' );
				} );
			} );
		</script>
		<?php
	}

	/**
	 * Save the settings.
	 */
	public function settings_save() {
		if ( ! is_admin() ) {
			return;
		}

		// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
		if ( isset( $_POST['tutor_booking_permalink_structure'], $_POST['academy-pro-tutor-booking-permalinks-nonce'], $_POST['academy_tutor_booking_category_slug'], $_POST['academy_tutor_booking_tag_slug'] ) && wp_verify_nonce( wp_unslash( $_POST['academy-pro-tutor-booking-permalinks-nonce'] ), 'academy-pro-tutor-booking-permalinks' ) ) { // WPCS: input var ok, sanitization ok.

			$permalinks                   = (array) get_option( 'academy_pro_tutor_permalinks', array() );
			$permalinks['category_base']  = \Academy\Helper::sanitize_permalink( wp_unslash( $_POST['academy_tutor_booking_category_slug'] ) ); // WPCS: input var ok, sanitization ok.
			$permalinks['tag_base']       = \Academy\Helper::sanitize_permalink( wp_unslash( $_POST['academy_tutor_booking_tag_slug'] ) ); // WPCS: input var ok, sanitization ok.

			// Generate booking base.
			$booking_base = isset( $_POST['tutor_booking_permalink'] ) ? sanitize_text_field( wp_unslash( $_POST['tutor_booking_permalink'] ) ) : ''; // WPCS: input var ok, sanitization ok.

			if ( 'custom' === $booking_base ) {
				if ( isset( $_POST['tutor_booking_permalink_structure'] ) ) { // WPCS: input var ok.
					$booking_base = preg_replace( '#/+#', '/', '/' . str_replace( '#', '', trim( wp_unslash( $_POST['tutor_booking_permalink_structure'] ) ) ) ); // WPCS: input var ok, sanitization ok.
				} else {
					$booking_base = '/';
				}

				// This is an invalid base structure and breaks pages.
				if ( '/%booking_category%/' === trailingslashit( $booking_base ) ) {
					$booking_base = '/' . _x( 'booking', 'slug', 'academy-pro' ) . $booking_base;
				}
			} elseif ( empty( $booking_base ) ) {
				$booking_base = _x( 'booking', 'slug', 'academy-pro' );
			}

			$permalinks['booking_base'] = \Academy\Helper::sanitize_permalink( $booking_base );

			// Shop base may require verbose page rules if nesting pages.
			$tutor_booking_page_id = (int) \Academy\Helper::get_settings( 'tutor_booking_page' );
			$booking_permalink = ( $tutor_booking_page_id > 0 && get_post( $tutor_booking_page_id ) ) ? get_page_uri( $tutor_booking_page_id ) : _x( 'courses', 'default-slug', 'academy-pro' );

			if ( $tutor_booking_page_id && stristr( trim( $permalinks['booking_base'], '/' ), $booking_permalink ) ) {
				$permalinks['use_verbose_page_rules'] = true;
			}

			update_option( 'academy_pro_tutor_permalinks', $permalinks );
		}//end if
	}

}
