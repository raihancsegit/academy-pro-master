<?php
namespace AcademyProPaidMembershipsPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Settings {
	public static function init() {
		$self = new self();
		// add settings inside label
		add_action( 'pmpro_membership_level_after_other_settings', array( $self, 'add_settings' ) );
		add_action( 'pmpro_save_membership_level', array( $self, 'save_settings' ) );
		// label list table
		add_action( 'pmpro_membership_levels_table_extra_cols_header', array( $self, 'add_table_head' ) );
		add_action( 'pmpro_membership_levels_table_extra_cols_body', array( $self, 'add_table_body' ) );
		add_filter( 'pmpro_membership_levels_table', array( $self, 'unassigned_category_notice' ) );
	}

	public function add_settings() {
		global $wpdb;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$edit = sanitize_text_field( $_REQUEST['edit'] );
		$level = $wpdb->get_row( $wpdb->prepare( "
            SELECT * FROM $wpdb->pmpro_membership_levels
            WHERE id = %d LIMIT 1",
			$edit
		));
		if ( empty( $level ) ) {
			$level = new \stdClass();
			$level->id = null;
			$level->name = null;
			$level->description = null;
			$level->confirmation = null;
			$level->billing_amount = null;
			$level->trial_amount = null;
			$level->initial_payment = null;
			$level->billing_limit = null;
			$level->trial_limit = null;
			$level->expiration_number = null;
			$level->expiration_period = null;
			$level->categories = array();
		}

		if ( $level->id ) {
			$level->categories = $wpdb->get_col( $wpdb->prepare( "
                SELECT c.category_id
                FROM $wpdb->pmpro_memberships_categories c
                WHERE c.membership_id = %d",
				$level->id
			) );
		}

		$categories = get_terms(
			array(
				'taxonomy'   => 'academy_courses_category',
				'hide_empty' => false,
			)
		);
		$categories = \Academy\Helper::prepare_category_results( $categories );

		include ACADEMY_PRO_PMPRO_INCLUDES_DIR_PATH . 'admin/views/settings.php';
	}

	public function save_settings( $level_id ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['academy_action'] ) || 'pmpro_settings' !== $_POST['academy_action'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$membership_model = ( isset( $_POST['academy_pmpro_membership_model'] ) ? sanitize_text_field( $_POST['academy_pmpro_membership_model'] ) : '' );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$recommend_badge = (int) ( isset( $_POST['academy_pmpro_label_recommend_badge'] ) ? sanitize_text_field( $_POST['academy_pmpro_label_recommend_badge'] ) : false );

		if ( $membership_model ) {
			update_pmpro_membership_level_meta( $level_id, 'academy_pmpro_membership_model', $membership_model );
		}

		if ( 1 === $recommend_badge ) {
			update_pmpro_membership_level_meta( $level_id, 'academy_pmpro_label_recommend_badge', 1 );
		} else {
			delete_pmpro_membership_level_meta( $level_id, 'academy_pmpro_label_recommend_badge' );
		}
	}

	public function add_table_head() {
		include ACADEMY_PRO_PMPRO_INCLUDES_DIR_PATH . 'admin/views/list-table-head.php';
	}
	public function add_table_body( $level ) {
		$membership_model = get_pmpro_membership_level_meta( $level->id, 'academy_pmpro_membership_model', true );
		$recommend_badge = (int) get_pmpro_membership_level_meta( $level->id, 'academy_pmpro_label_recommend_badge', true );
		include ACADEMY_PRO_PMPRO_INCLUDES_DIR_PATH . 'admin/views/list-table-body.php';
	}
	public function unassigned_category_notice( $markup ) {
		global $wpdb;
		$level_cats = $wpdb->get_col(
			"SELECT cat.category_id 
            FROM {$wpdb->pmpro_memberships_categories} cat 
                INNER JOIN {$wpdb->pmpro_membership_levels} lvl ON lvl.id=cat.membership_id"
		);
		if ( ! is_array( $level_cats ) ) {
			$level_cats = array();
		}

		// Get all categories and check if exist in any level.
		$unassign_category = array();
		$categories = get_terms(
			array(
				'taxonomy'   => 'academy_courses_category',
				'hide_empty' => false,
			)
		);
		foreach ( $categories as $category ) {
			if ( ! in_array( $category->term_id, $level_cats, true ) ) {
				$unassign_category[] = $category;
			}
		}
		ob_start();

		include ACADEMY_PRO_PMPRO_INCLUDES_DIR_PATH . 'admin/views/list-table-category-notice.php';

		return $markup . ob_get_clean();
	}
}
