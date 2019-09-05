<?php
/**
 * BSF Pre Publish Checklist page setups comment
 *
 * PHP version 7
 *
 * @category PHP
 * @package  Pre-Publish Checklist.
 * @author   Display Name <username@ShubhamW.com>
 * @license  http://brainstormforce.com
 * @link     http://brainstormforce.com
 */

/*
 * Main Frontpage.
 *
 * @since  1.0.0
 * @return void
 */

if ( ! class_exists( 'PPC_Pagesetups' ) ) :
	/**
	 * Pre Publish Checklist Loader Doc comment
	 *
	 * PHP version 7
	 *
	 * @category PHP
	 * @package  Pre Publish Check-list
	 * @author   Display Name <username@ShubhamW.com>
	 * @license  http://brainstormforce.com
	 * @link     http://brainstormforce.com
	 */
	class PPC_Pagesetups {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 *  Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'add_meta_boxes', array( $this, 'ppc_add_custom_meta_box' ) );
			add_action( 'admin_menu', array( $this, 'bsf_ppc_settings_page' ) );
			add_action( 'wp_ajax_ppc_ajax_add_change', array( $this, 'ppc_meta_box_ajax_add_handler' ), 1 );
			add_action( 'wp_ajax_nopriv_ppc_ajax_add_change', array( $this, 'ppc_meta_box_ajax_add_handler' ), 1 );
			add_action( 'wp_ajax_ppc_ajax_delete_change', array( $this, 'ppc_meta_box_ajax_delete_handler' ), 1 );
			add_action( 'wp_ajax_nopriv_ppc_ajax_delete_change', array( $this, 'ppc_meta_box_ajax_delete_handler' ), 1 );
			add_action( 'admin_footer', array( $this, 'ppc_markup' ) );
		}
		/**
		 * Function for HTML markup of notification.
		 *
		 * Shows the pop-up of warning a user or preventing a user.
		 *
		 * @since 1.0.0
		 */
		public function ppc_markup() {
			$ppc_screen = get_current_screen();
			// If not edit or add new page, post or custom post type window then return.
			if ( ! isset( $ppc_screen->parent_base ) || (isset( $ppc_screen->parent_base ) && 'edit' !== $ppc_screen->parent_base) ) {
				return;
			}
			wp_enqueue_script( 'ppc_backend_checkbox_js' );
			wp_enqueue_style( 'ppc_backend_css' );
			?>
			<div class = "ppc-modal-warn">
				<div id="ppc_notifications" class="ppc-popup-warn">
					<h2>Pre-Publish Checklist</h2>
					<p class="ppc-popup-description">Your Pre-Publish Checklist is still pending. What would you like to do?</p>
					<div class="ppc-button-wrapper">
						<div class="ppc-popup-option-dontpublish">Don't Publish</div>
						<div class="ppc-popup-options-publishanyway">Publish Anyway</div>
					</div>    
				</div>
			</div>
			<div class = "ppc-modal-prevent">
				<div id="ppc_notifications" class="ppc-popup-prevent">
					<h2>Pre-Publish Checklist</h2>
					<p class="ppc-popup-description"> Please check all the checklist items before publishing.</p>
					<div class="ppc-prevent-button-wrapper">
						<div class="ppc-popup-option-okay">Okay, Take Me to the List!</div>
					</div>  
					
					
				</div>
			</div>
			<?php
		}

		/**
		 * Function for adding settings page in admin area
		 *
		 * Displays our plugin settings page in the WordPress
		 *
		 * @since 1.0.0
		 */
		public function bsf_ppc_settings_page() {
			add_submenu_page(
				'options-general.php',
				'Pre-Publish Checklist',
				'Pre-Publish Checklist',
				'manage_options',
				'bsf_ppc',
				array( $this, 'bsf_ppc_page_html' )
			);
		}
		/**
		 * Tabs function
		 *
		 * All the tabs are managed in the file which is included.
		 *
		 * @since 1.0.0
		 */
		public function bsf_ppc_page_html() {
			include_once PPC_ABSPATH . 'includes/ppc-tabs.php';
		}
		/**
		 * Add custom meta box
		 *
		 * Display plugin's custom meta box in the meta settings side bar
		 *
		 * @since 1.0.0
		 */
		public function ppc_add_custom_meta_box() {
			$ppc_post_types_to_display = get_option( 'ppc_post_types_to_display' );
			if ( ! empty( $ppc_post_types_to_display ) ) {
				foreach ( $ppc_post_types_to_display as $screen ) {
					add_meta_box(
						'ppc_custom_meta_box', // Unique ID.
						'Pre-Publish Checklist', // Box title.
						array( $this, 'ppc_custom_box_html' ), // Content callback, must be of type callable.
						$screen,
						'side',
						'high'
					);
				}
			}
		}

		/**
		 * Call back function for HTML markup of meta box.
		 *
		 * This functions contains the markup to be displayed or the information to be displayed in the custom meta box
		 *
		 * @since 1.0.0
		 */
		public function ppc_custom_box_html() {
			wp_enqueue_script( 'ppc_backend_checkbox_js' );
			wp_enqueue_script( 'ppc_backend_tooltip_js' );
			wp_enqueue_style( 'ppc_backend_css' );
			global $post;
			$ppc_checklist_item_data = get_option( 'ppc_checklist_data' );
			$value                      = get_post_meta( $post->ID, '_ppc_meta_key', true );

			?>
			<div class="ppc-percentage-wrapper">
				<span class="ppc-percentage-value"></span>
				<div class="ppc-percentage-background">
					<div class="ppc-percentage"></div>
				</div>
			</div>
<!-- 			<div class="ppc-hide-checked-item-buttton-wrapper">
			<button class="components-button is-button is-default ppc-hide-checked-item-buttton" name="ppc-hide-checked-item">Hide Checked Items</button>
			</div> -->
			<?php
			if ( ! empty( $ppc_checklist_item_data ) ) {
				foreach ( $ppc_checklist_item_data as $key ) {
					?>
					<div class="ppc-checklist-item-wrapper">
						<input type="checkbox" name="checkbox[]" id="checkbox" class="ppc_checkboxes" value= "<?php echo esc_attr( $key ); ?>"

					<?php
					if ( ! empty( $value ) ) {
						foreach ( $value as $keychecked ) {
							checked( $keychecked, $key );
						}
					}
					?>
						>
					<?php

					?><div class="ppc-checklist"><?php echo esc_attr( $key );?></div></div><?php		
				}?><?php
			} else {
				echo 'Please create a list to display here from Settings->Pre-Publish-Checklist';
			}
		}

		/**
		 * Function for saving the meta box values
		 *
		 * Adds value from metabox chechbox to the wp_post_meta()
		 *
		 * @since 1.0.0
		 */
		public function ppc_meta_box_ajax_add_handler() {
			if ( isset( $_POST['ppc_field_value'] ) ) {//PHPCS:ignore:WordPress.Security.NonceVerification.Missing
				$ppcpost        = sanitize_text_field( $_POST['ppc_post_id'] );//PHPCS:ignore:WordPress.Security.NonceVerification.Missing
				$ppc_check_data = array( sanitize_text_field( $_POST['ppc_field_value'] ) );//PHPCS:ignore:WordPress.Security.NonceVerification.Missing
				$pre_data          = get_post_meta( $ppcpost, '_ppc_meta_key', true );
				if ( ! empty( $pre_data ) ) {
					$ppc_checklist_add_data = array_merge( $pre_data, $ppc_check_data );
				} else {
					$ppc_checklist_add_data = $ppc_check_data;
				}
				update_post_meta(
					$ppcpost,
					'_ppc_meta_key',
					$ppc_checklist_add_data
				);
				echo 'sucess';
			} else {
				echo 'failure';
			}
			die;
		}

		/**
		 * Function for deleting the meta box values
		 *
		 * Delete value from post meta using chechbox uncheck from wp_post_meta()
		 *
		 * @since 1.0.0
		 */
		public function ppc_meta_box_ajax_delete_handler() {
			if ( isset( $_POST['ppc_field_value'] ) ) {//PHPCS:ignore:WordPress.Security.NonceVerification.Missing
				$ppcpost    = sanitize_text_field( $_POST['ppc_post_id'] );//PHPCS:ignore:WordPress.Security.NonceVerification.Missing
				$ppc_delete = sanitize_text_field( $_POST['ppc_field_value'] );//PHPCS:ignore:WordPress.Security.NonceVerification.Missing
				$pre_data      = get_post_meta( $ppcpost, '_ppc_meta_key', true );
				$key           = array_search( $ppc_delete, $pre_data, true );
				if ( false !== $key ) {
					unset( $pre_data[ $key ] );
				}
				update_post_meta(
					$ppcpost,
					'_ppc_meta_key',
					$pre_data
				);
			} else {
				echo 'failure';
			}
			die;
		}

	}
	PPC_Pagesetups::get_instance();
endif;