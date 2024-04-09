<?php
namespace AcademyPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class License {
	private $white_label_settings;
	public static function init() {
		$self = new self();
		add_action( 'admin_menu', array( $self, 'add_freemius_submenu' ) );
		$self->load_freemius_sdk();
		$self->dispatch_hooks();
	}
	public function add_freemius_submenu() {
		$page_title = apply_filters( 'academy_pro/admin/settings_sub_menu_title', __( 'Academy Pro', 'academy-pro' ) );

		add_submenu_page(
			'options-general.php',
			$page_title,
			$page_title,
			'manage_options',
			'academy-pro',
			'__return_false'
		);
	}

	public function load_freemius_sdk() {
		if ( ! function_exists( 'academy_pro_license' ) ) {
			/**
			 *   Create a helper function for easy SDK access
			 * */
			function academy_pro_license() {
				global $academy_pro_license;

				if ( ! isset( $academy_pro_license ) ) {
					// Activate multisite network integration.
					if ( ! defined( 'WP_FS__PRODUCT_11123_MULTISITE' ) ) {
						define( 'WP_FS__PRODUCT_11123_MULTISITE', true );
					}

					// Include Freemius SDK.
					require_once ACADEMY_PRO_LIBRARY_DIR_PATH . 'freemius/start.php';

					$is_active_white_label = \Academy\Helper::get_addon_active_status( 'white-label' );

					$academy_pro_license = fs_dynamic_init( array(
						'id'                  => '11123',
						'slug'                => 'academy-pro',
						'premium_slug'        => 'academy-pro',
						'type'                => 'plugin',
						'public_key'          => 'pk_70ab0af4689d43c866bf3815aa419',
						'is_premium'          => true,
						'is_premium_only'     => true,
						'has_addons'          => false,
						'has_paid_plans'      => true,
						'has_affiliation'     => 'selected',
						'menu'                => array(
							'slug'           => 'academy-pro',
							'override_exact' => true,
							'first-path'     => 'admin.php?page=academy-license',
							'network'        => true,
							'support'        => false,
							'contact'        => $is_active_white_label ? false : true,
							'pricing'        => false,
							'affiliation'    => $is_active_white_label ? false : true,
							'parent'         => array(
								'slug' => 'options-general.php',
							),
						),
					) );
				}//end if

				return $academy_pro_license;
			}
			// Init Freemius.
			academy_pro_license();

			// Signal that SDK was initiated.
			do_action( 'academy_pro_license_loaded' );
			academy_pro_license()->add_filter( 'connect_url', array( $this, 'academy_pro_license_settings_url' ) );
			academy_pro_license()->add_filter( 'after_skip_url', array( $this, 'academy_pro_license_settings_url' ) );
			academy_pro_license()->add_filter( 'after_connect_url', array( $this, 'academy_pro_license_settings_url' ) );
			academy_pro_license()->add_filter( 'after_pending_connect_url', array( $this, 'academy_pro_license_settings_url' ) );
			academy_pro_license()->add_filter( 'pricing_url', array( $this, 'academy_pro_purchase_url' ) );
			academy_pro_license()->add_filter( 'checkout_url', array( $this, 'academy_pro_purchase_url' ) );
			// White label license
			$is_active_white_label = \Academy\Helper::get_addon_active_status( 'white-label' );
			if ( $is_active_white_label ) {
				$settings = get_option( 'academy_pro_white_label_settings' );
				if ( $settings ) {
					$this->white_label_settings = json_decode( $settings, true );
				}
				academy_pro_license()->add_filter( 'plugin_title', array( $this, 'white_label_title' ) );
				academy_pro_license()->add_filter( 'hide_plan_change', '__return_true' );
				academy_pro_license()->add_filter( 'connect-message_on-premium', function () {
					return sprintf( __( 'Welcome to %s! To get started, please enter your license key:', 'academy-pro' ), '<b>' . $this->white_label_title( 'Academy Pro' ) . '</b>' );
				} );
				academy_pro_license()->add_filter( 'show_admin_notice', function ( $show, $msg ) {
					return false;
				}, 10, 2 );
				add_action( 'admin_head', array( $this, 'add_freemius_custom_css' ) );
			}
		}//end if
	}

	public function academy_pro_license_settings_url() {
		return admin_url( 'options-general.php?page=academy-pro' );
	}

	public function academy_pro_purchase_url() {
		return 'https://academylms.net/pricing/';
	}

	public function white_label_title( $title ) {
		if ( isset( $this->white_label_settings['title'] ) && ! empty( $this->white_label_settings['title'] ) ) {
			return $this->white_label_settings['title'];
		}
		return $title;
	}

	public function dispatch_hooks() {
		add_action( 'academy/admin_menu_list', array( $this, 'add_license_menu' ) );
		add_action( 'current_screen', array( $this, 'registered_user_direct_to_account_page' ) );
	}

	public function registered_user_direct_to_account_page() {
		global $academy_pro_license;
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		if ( strpos( $screen->base, 'page_academy-license' ) ) {
			wp_safe_redirect( esc_url( admin_url( 'options-general.php?page=academy-pro' ) ) );
			exit;
		} elseif ( 'settings_page_academy-pro' === $screen->base && $academy_pro_license->can_use_premium_code() && $academy_pro_license->is_registered() ) {
			wp_safe_redirect( esc_url( admin_url( 'options-general.php?page=academy-pro-account' ) ) );
			exit;
		}
	}

	public function add_license_menu( $menu ) {
		$menu[ ACADEMY_PLUGIN_SLUG . '-license' ]    = [
			'parent_slug' => ACADEMY_PLUGIN_SLUG,
			'title'      => __( 'License', 'academy-pro' ),
			'capability' => 'manage_options',
		];
		return $menu;
	}

	public function add_freemius_custom_css() {
		$logo_url = '';
		if ( isset( $this->white_label_settings['logo'] ) && ! empty( $this->white_label_settings['logo'] ) ) {
			$logo_url = wp_get_attachment_url( $this->white_label_settings['logo'] );
		}
		echo '<style>
			.settings_page_academy-pro #fs_connect .fs-header .fs-plugin-icon img  {
				display: none;
			}
			.settings_page_academy-pro #fs_connect .fs-header .fs-plugin-icon {
				background-image: url(' . esc_url( $logo_url ) . ');
				background-size: 50px 50px;
				background-position: center center;
				background-repeat: no-repeat;
			}
		</style>';
	}
}
