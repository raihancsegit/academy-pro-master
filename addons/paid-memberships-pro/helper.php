<?php
namespace AcademyProPaidMembershipsPro;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Helper {
	public static function is_active_pmpro() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' );
	}
	public static function required_levels( $term_ids, $check_full = false ) {
		global $wpdb;
		$query = $check_full ? "meta.meta_value='full_website_membership'" : '';
		if ( count( $term_ids ) ) {
			if ( $check_full ) {
				$query .= ' OR ';
			}
			$query .= "(meta.meta_value='category_wise_membership' AND cat_table.category_id IN (" . implode( ',', $term_ids ) . '))';
		}
		$query = ! empty( $query ) ? 'AND (' . $query . ')' : '';
		$labels = $wpdb->get_results(
			"SELECT DISTINCT level_table.*
            FROM {$wpdb->pmpro_membership_levels} level_table 
                LEFT JOIN {$wpdb->pmpro_memberships_categories} cat_table ON level_table.id=cat_table.membership_id
                LEFT JOIN {$wpdb->pmpro_membership_levelmeta} meta ON level_table.id=meta.pmpro_membership_level_id 
            WHERE 
                meta.meta_key='academy_pmpro_membership_model' " . $query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $labels;
	}
	public static function has_any_full_site_level() {
		global $wpdb;
		$results = $wpdb->get_var(
			"SELECT level_table.id
            FROM {$wpdb->pmpro_membership_levels} level_table 
                INNER JOIN {$wpdb->pmpro_membership_levelmeta} meta ON level_table.id=meta.pmpro_membership_level_id 
            WHERE 
                meta.meta_key='academy_pmpro_membership_model' AND 
                meta.meta_value='full_website_membership'"
		);
		return (int) $results;
	}
	public static function has_course_access( $course_id ) {
		$user_id = get_current_user_id();
		$has_access_flag = false;

		// Get all membership levels by user id
		$levels = pmpro_getMembershipLevelsForUser( $user_id );
		if ( ! is_array( $levels ) ) {
			$levels = array();
		}

		// Get course categories by course id
		$terms = get_the_terms( $course_id, 'academy_courses_category' );
		$term_ids = wp_list_pluck( $terms, 'term_id' );

		$required_levels = self::required_levels( $term_ids );
		if ( is_array( $required_levels ) && ! count( $required_levels ) && ! self::has_any_full_site_level() ) {
			// Can access only when the site level is not full and the course category is empty
			return true;
		}

		// Verify that the course is accessible to some level
		foreach ( $levels as $level ) {

			// Check expired or not
			$endtime = (int) $level->enddate;
			if ( 0 < $endtime && $endtime < \Academy\Helper::get_time() ) {
				continue;
			}

			if ( $has_access_flag ) {
				// already has access then ignore
				continue;
			}

			$model = get_pmpro_membership_level_meta( $level->id, 'academy_pmpro_membership_model', true );
			if ( 'full_website_membership' === $model ) {
				// Any full site model of the user grants them membership access
				$has_access_flag = true;

			} elseif ( 'category_wise_membership' === $model ) {
				// Confirm whether this membership has a category that includes this course
				$member_cats = pmpro_getMembershipCategories( $level->id );
				$member_cats = array_map(function( $member ) {
					return (int) $member;
				}, ( is_array( $member_cats ) ? $member_cats : array() ));

				// Check if the course id in the level category
				foreach ( $term_ids as $term_id ) {
					if ( in_array( $term_id, $member_cats, true ) ) {
						$has_access_flag = true;
						break;
					}
				}
			}
		}//end foreach
		return $has_access_flag ? true : self::required_levels( $term_ids, true );
	}
}
