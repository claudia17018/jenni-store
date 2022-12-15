<?php
/**
 * Plugin Name: Orders Tracking for WooCommerce
 * Plugin URI: https://villatheme.com/extensions/woocommerce-orders-tracking
 * Description: Easily import/manage your tracking numbers, add tracking numbers to PayPal and send email notifications to customers.
 * Version: 1.2.1
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woo-orders-tracking
 * Domain Path: /languages
 * Copyright 2019-2022 VillaTheme.com. All rights reserved.
 * Tested up to: 6.0
 * WC tested up to: 6.9
 * Requires PHP: 7.0
 **/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VI_WOO_ORDERS_TRACKING_VERSION', '1.2.1' );
define( 'VI_WOO_ORDERS_TRACKING_PATH_FILE', __FILE__ );
define( 'VI_WOO_ORDERS_TRACKING_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'woo-orders-tracking' . DIRECTORY_SEPARATOR );
define( 'VI_WOO_ORDERS_TRACKING_INCLUDES', VI_WOO_ORDERS_TRACKING_DIR . 'includes' . DIRECTORY_SEPARATOR );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce-orders-tracking/woocommerce-orders-tracking.php' ) ) {
	return;
}
/*Required for register_activation_hook*/
if ( is_file( VI_WOO_ORDERS_TRACKING_INCLUDES . 'data.php' ) ) {
	require_once VI_WOO_ORDERS_TRACKING_INCLUDES . 'data.php';
}
if ( is_file( VI_WOO_ORDERS_TRACKING_INCLUDES . 'class-vi-woo-orders-tracking-trackingmore-table.php' ) ) {
	require_once VI_WOO_ORDERS_TRACKING_INCLUDES . 'class-vi-woo-orders-tracking-trackingmore-table.php';
}
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woo-orders-tracking" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
	require_once $init_file;
} else {
	add_action( 'admin_notices', array( 'WOO_ORDERS_TRACKING', 'global_note' ) );
}

if ( ! class_exists( 'WOO_ORDERS_TRACKING' ) ) {
	class WOO_ORDERS_TRACKING {
		protected $settings;

		public function __construct() {
			register_activation_hook( VI_WOO_ORDERS_TRACKING_PATH_FILE, array( __CLASS__, 'install' ) );
		}

		/**
		 * Notify if WooCommerce is not activated
		 */
		public static function global_note() {
			?>
            <div id="message" class="error">
                <p><?php esc_html_e( 'Please install and activate WooCommerce to use Orders Tracking for WooCommerce plugin.', 'woo-orders-tracking' ); ?></p>
            </div>
			<?php
		}

		public static function install() {
			/*Create custom table to store tracking data*/
			VI_WOO_ORDERS_TRACKING_TRACKINGMORE_TABLE::create_table();
			/*create tracking page*/
			if ( ! get_option( 'woo_orders_tracking_settings' ) ) {
				$current_user = wp_get_current_user();
				// create post object
				$page = array(
					'post_title'  => esc_html__( 'Orders Tracking', 'woo-orders-tracking' ),
					'post_status' => 'publish',
					'post_author' => $current_user->ID,
					'post_type'   => 'page',
					'post_name'   => 'orders-tracking',
				);
				// insert the post into the database
				$page_id = wp_insert_post( $page, true );
				if ( ! is_wp_error( $page_id ) ) {
					$settings                      = VI_WOO_ORDERS_TRACKING_DATA::get_instance();
					$args                          = $settings->get_params();
					$args['service_tracking_page'] = $page_id;
					update_option( 'woo_orders_tracking_settings', $args );
				}
			}
		}
	}
}
new WOO_ORDERS_TRACKING();