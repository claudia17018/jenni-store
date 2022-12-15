<?php

namespace PaymentPlugins\WooCommerce\PPCP\Factories;

use PaymentPlugins\PayPalSDK\Collection;
use PaymentPlugins\PayPalSDK\Order;
use PaymentPlugins\PayPalSDK\PurchaseUnit;
use PaymentPlugins\WooCommerce\PPCP\Utilities\NumberUtil;

class OrderFactory extends AbstractFactory {

	/**
	 * @param \WC_Cart $cart
	 *
	 * @return \PaymentPlugins\PayPalSDK\Order
	 */
	public function from_cart( $intent ) {
		$needs_shipping = $this->cart->needs_shipping();
		$order          = ( new Order() )
			->setIntent( $intent )
			->setPayer( $this->factories->payer->from_customer() )
			->setApplicationContext( $this->factories->applicationContext->get( $needs_shipping ) )
			->setPurchaseUnits( ( new Collection() )->add( $this->factories->purchaseUnit->from_cart() ) );

		return $order;
	}

	/**
	 * @param \WC_Order $cart
	 *
	 * @return \PaymentPlugins\PayPalSDK\Order
	 */
	public function from_order( $intent ) {
		$needs_shipping = $this->order->needs_shipping_address();

		return ( new Order() )
			->setIntent( $intent )
			->setPayer( $this->factories->payer->from_order() )
			->setPurchaseUnits( new Collection( [ $this->factories->purchaseUnit->from_order() ] ) )
			->setApplicationContext( $this->factories->applicationContext->get( $needs_shipping, true ) );
	}

}