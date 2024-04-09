<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

	global $pmpro_currencies, $pmpro_currency;
	$current_currency = $pmpro_currency ? $pmpro_currency : '';
	$currency = 'USD' === $current_currency ?
							array( 'symbol' => '$' ) :
							( isset( $pmpro_currencies[ $current_currency ] ) ? $pmpro_currencies[ $current_currency ] : null );

	$currency_symbol = ( is_array( $currency ) && isset( $currency['symbol'] ) ) ? $currency['symbol'] : '';
	$currency_position = ( is_array( $currency ) && isset( $currency['position'] ) ) ? strtolower( $currency['position'] ) : 'left';

?>
<form class="academy-widget-enroll__pmpro-pricing">
	<h3 class="academy-pmpro-pricing__entry-title"><?php esc_html_e( 'Pick a plan', 'academy-pro' ); ?></h3>
	<?php
		$no_commitment = get_option( 'pmpro_no_commitment_message' );
		$money_back = get_option( 'pmpro_moneyback_day' );
		$money_back = ( is_numeric( $money_back ) && $money_back > 0 ) ? $money_back : false;
		$level_page_id = apply_filters( 'academy_pmpro_checkout_page_id', pmpro_getOption( 'checkout_page_id' ) );
		$level_page_url = get_the_permalink( $level_page_id );

	if ( $no_commitment ) {
		?>
			<small><?php echo esc_html( $no_commitment ); ?></small>
			<?php
	}

		$level_count = count( $required_levels );
	?>

	<div class="academy-pmpro-pricing__entry-body">
		<?php foreach ( $required_levels as $level ) :
			$highlight = (int) get_pmpro_membership_level_meta( $level->id, 'academy_pmpro_label_recommend_badge', true );
			?>
			<label class="academy-pmpro-pricing__item" data-id="<?php echo esc_attr( $level->id ); ?>">
				<?php
				if ( $highlight ) :
					?>
				<div class="academy-pmpro-pricing-highlight">
					<span class="academy-icon academy-icon--star"></span>
				</div>
					<?php
					endif;
				?>
				<div class="academy-pmpro-pricing__item-title">
					<span class="academy-pmpro-pricing-label">
						<input type="radio" name="academy_pmpro_level_radio" value="<?php echo esc_attr( $level->id ); ?>" <?php checked( $highlight, 1 ); ?> />
						<span class="academy-label"><?php echo esc_html( $level->name ); ?></span>
					</span>
					<span class="academy-pmpro-pricing-price-label">
						<?php
							$billing_amount = round( $level->billing_amount );
							$initial_payment = round( $level->initial_payment );

							$billing_text = '';
								'left' === $currency_position ? $billing_text .= $currency_symbol : 0;
									$billing_text .= ( $level->cycle_period ? $billing_amount : $initial_payment );
								'right' === $currency_position ? $billing_text .= $currency_symbol : 0;
							$billing_text .= '';

							echo esc_html( $billing_text );
						if ( $level->cycle_period ) :
							?>
						<span class="academy-pmpro-pricing-duration">/<?php echo esc_html( substr( $level->cycle_period, 0, 2 ) ); ?></span>
						<?php endif; ?>
					</span>
				</div>
				<div class="academy-pmpro-pricing__item-body">
					<p class="academy-pmpro-pricing-description"><?php echo wp_kses_post( $level->description ); ?></p>
					<a href="<?php echo esc_url( add_query_arg( array( 'level' => $level->id ), $level_page_url ) ); ?>" class="academy-btn academy-btn--preset-purple">
						<?php esc_html_e( 'Buy Now', 'academy-pro' ); ?>
					</a>
					<?php if ( $money_back ) : ?>
						<p class="academy-pmpro-pricing-info"><?php
							/* translators: This is a placeholder for the number of days in a money-back guarantee */
							echo sprintf( esc_html__( '%d-day money-back guarantee', 'academy-pro' ), esc_html( $money_back ) );
						?></p>
					<?php endif; ?>
				</div>
			</label>
		<?php endforeach; ?>
	</div>
</form>
