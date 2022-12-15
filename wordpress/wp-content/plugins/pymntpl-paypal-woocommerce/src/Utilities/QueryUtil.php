<?php

namespace PaymentPlugins\WooCommerce\PPCP\Utilities;

use PaymentPlugins\WooCommerce\PPCP\Constants;

class QueryUtil {

	/**
	 * @param \PaymentPlugins\PayPalSDK\Refund $refund
	 *
	 * @return void
	 */
	public static function get_wc_refund_from_paypal_refund( $refund ) {
		if ( FeaturesUtil::is_custom_order_tables_enabled() ) {
			$refund_ids = wc_get_orders( [
				'type'       => 'shop_order_refund',
				'limit'      => 1,
				'return'     => 'ids',
				'meta_query' => [
					[
						'key'   => Constants::PAYPAL_REFUND,
						'value' => $refund->id
					]
				]
			] );
			$refund_id  = ! empty( $refund_ids ) ? $refund_ids[0] : null;
		} else {
			global $wpdb;
			$refund_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} AS posts LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id WHERE posts.post_type = %s AND meta.meta_key = %s AND meta.meta_value = %s LIMIT 1",
				'shop_order_refund', Constants::PAYPAL_REFUND, $refund->id ) );
		}

		return $refund_id;
	}

}