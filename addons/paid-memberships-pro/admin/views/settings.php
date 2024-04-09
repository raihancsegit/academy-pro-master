<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<div id="academy-pmpro-setting">
	<h2 class="academy-pmpro-setting__title"><?php esc_html_e( 'Academy LMS Content Settings', 'academy-pro' ); ?></h2>
	<input type="hidden" value="pmpro_settings" name="academy_action"/>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row" valign="top"><label><?php esc_html_e( 'Membership Model', 'academy-pro' ); ?></label></th>
				<td>
					<?php
						$membership_model = get_pmpro_membership_level_meta( $level->id, 'academy_pmpro_membership_model', true );
					?>
					<select id="academy_pmpro_membership_model" name="academy_pmpro_membership_model">
						<option value=""><?php esc_html_e( 'Select a membership model', 'academy-pro' ); ?></option>
						<option value="full_website_membership" <?php selected( 'full_website_membership', $membership_model ); ?> ><?php esc_html_e( 'Full website membership', 'academy-pro' ); ?></option>
						<option value="category_wise_membership" <?php selected( 'category_wise_membership', $membership_model ); ?>><?php esc_html_e( 'Category wise membership', 'academy-pro' ); ?></option>
					</select>
				</td>
			</tr>
			<tr id="category_wise_membership" style="display: <?php echo 'category_wise_membership' === $membership_model ? '' : 'none'; ?>;">
				<th scope="row" valign="top">
					<label><?php esc_html_e( 'Course Categories', 'academy-pro' ); ?></label>
				</th>
				<td>
					<ul>
						<?php
						foreach ( $categories as $parent_category ) :
							$parent_name = 'membershipcategory_' . $parent_category->term_id;
							?>
							<li>
								<label class="parent-term">
									<input 
										type="checkbox" 
										name="<?php echo esc_attr( $parent_name ); ?>"
										value='yes'
										<?php checked( in_array( $parent_category->term_id, $level->categories, true ), true, true ); ?>
									/>
									<?php echo esc_html( $parent_category->name ); ?>
								</label>
								<?php
								if ( count( $parent_category->children ) ) :
									?>
									<ul>
									<?php
									foreach ( $parent_category->children as $child_category ) :
										$child_name = 'membershipcategory_' . $child_category->term_id;
										?>
										<li>
											<label class="child-term">
												<input
													type="checkbox" 
													name="<?php echo esc_attr( $child_name ); ?>"
													value='yes'
													<?php checked( in_array( $child_category->term_id, $level->categories, true ), true, true ); ?> 
												/>
												<?php echo esc_html( $child_category->name ); ?>
											</label>
										</li>
										<?php
									endforeach;
									?>
									</ul>
									<?php
								endif;
								?>
							</li>
							<?php
						endforeach;
						?>
					</ul>
				</td>
			</tr>
			<tr>
				<th scope="row" valign="top"><label>
					<?php esc_html_e( 'Add Recommend badge', 'academy-pro' ); ?>
				</label></th>
				<td>
					<?php
						$recommend_badge = get_pmpro_membership_level_meta( $level->id, 'academy_pmpro_label_recommend_badge', true );
					?>
					<input type="checkbox"  value="1" name="academy_pmpro_label_recommend_badge" <?php echo $recommend_badge ? 'checked="checked"' : ''; ?>/>
				</td>
			</tr>
		</tbody>
	</table>
</div>
