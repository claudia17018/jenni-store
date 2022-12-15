<?php

namespace PaymentPlugins\WooCommerce\PPCP\Utilities;

use PaymentPlugins\WooCommerce\PPCP\Constants;

class PayPalFee {

	/**
	 * @param \WC_Order                                           $order
	 * @param \PaymentPlugins\PayPalSDK\SellerReceivableBreakdown $breakdown
	 */
	public static function add_fee_to_order( $order, $breakdown, $save = true ) {
		$order->update_meta_data( Constants::PAYPAL_FEE, $breakdown->paypal_fee->value );
		$order->update_meta_data( Constants::PAYPAL_NET, $breakdown->net_amount->value );
		if ( $save ) {
			$order->save();
		}
	}

	/**
	 * @param \WC_Order $order
	 */
	public static function display_fee( $order ) {
		$fee = $order->get_meta( Constants::PAYPAL_FEE );
		if ( is_numeric( $fee ) ) {
			return wc_price( - 1 * $fee, array( 'currency' => $order->get_currency() ) );
		}

		return null;
	}

	/**
	 * @param \WC_Order $order
	 */
	public static function display_net( $order ) {
		$net = $order->get_meta( Constants::PAYPAL_NET );
		if ( is_numeric( $net ) ) {
			return wc_price( $net, array( 'currency' => $order->get_currency() ) );
		}

		return null;
	}

	/**
	 * @param \PaymentPlugins\PayPalSDK\Refund $refund
	 * @param \WC_Order                        $order
	 */
	public static function update_net( $refund, $order, $save = true ) {
		if ( isset( $refund->seller_payable_breakdown ) ) {
			$fee = $order->get_meta( Constants::PAYPAL_FEE );
			$fee = is_numeric( $fee ) ? $fee : 0;
			$net = $order->get_total() - $fee - $refund->seller_payable_breakdown->total_refunded_amount->value;
			$order->update_meta_data( Constants::PAYPAL_NET, $net );
			if ( $save ) {
				$order->save();
			}
		}
	}

}