<?php

namespace PaymentPlugins\WooCommerce\PPCP\Factories;

use PaymentPlugins\PayPalSDK\Collection;
use PaymentPlugins\PayPalSDK\Item;
use PaymentPlugins\PayPalSDK\Money;
use PaymentPlugins\WooCommerce\PPCP\Utilities\NumberUtil;

class ItemsFactory extends AbstractFactory {

	/**
	 * @param \WC_Cart $cart
	 *
	 * @return \PaymentPlugins\PayPalSDK\Collection
	 */
	public function from_cart() {
		$incl_tax = $this->display_prices_including_tax();
		$items    = new Collection();
		foreach ( $this->cart->get_cart() as $key => $cart_item ) {
			/**
			 * Calculate the individual item price using the line_subtotal since that takes into
			 * consideration things like discounts, order bumps etc. We divide by quantity so we know
			 * the per unit price since PayPal does their own unit_price * quantity.
			 */
			if ( $incl_tax ) {
				$total = ( $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'] ) / (int) $cart_item['quantity'];
			} else {
				$total = $cart_item['line_subtotal'] / (int) $cart_item['quantity'];
			}
			$qty  = $cart_item['quantity'];
			$name = $cart_item['data']->get_name();
			$items->add( $this->get_cart_item( $total, $name, $qty, $cart_item ) );
		}
		if ( 0 < $this->cart->get_fee_total() ) {
			$fees = $this->cart->get_fees();
			/**
			 * 1.0.6 - There is a chance the fee total is greater than $0 but the fees array is not populated.
			 * Make sure the fee total gets added either as one value or all the fees
			 */
			if ( count( $fees ) > 0 ) {
				foreach ( $fees as $fee ) {
					if ( $fee->total > 0 ) {
						$total = $incl_tax ? $fee->total + $fee->tax : $fee->total;
						$items->add( $this->get_cart_item( $total, $fee->name, 1, null ) );
					}
				}
			} else {
				$items->add( $this->get_cart_item( $this->cart->get_fee_total(), __( 'Fees', 'pymntpl-paypal-woocommerce' ), 1, null ) );
			}
		}

		return $items;
	}

	/**
	 * @return \PaymentPlugins\PayPalSDK\Collection
	 */
	public function from_order() {
		$items = new Collection();
		$total = 0;
		foreach ( $this->order->get_items() as $item ) {
			$item = $this->get_order_item( $item );
			$items->add( $item );
			$total = $total + $item->getUnitAmount()->getValue() * $item->getQuantity();
		}
		if ( 0 < $this->get_order_total_fees() ) {
			$items->add( $this->get_order_fees() );
		}

		return $items;
	}

	/**
	 * @param $total
	 * @param $name
	 * @param $qty
	 * @param $cart_item
	 *
	 * @return Item
	 */
	public function get_cart_item( $total, $name, $qty, $cart_item ) {
		return apply_filters( 'wc_ppcp_get_cart_item',
			( new Item() )->setName( substr( $name, 0, 127 ) )
			              ->setQuantity( $qty )
			              ->setUnitAmount( ( new Money() )->setCurrencyCode( $this->currency )
			                                              ->setValue( (string) $this->round( $total ) ) ), $cart_item );
	}

	public function get_order_item( $item ) {
		return ( new Item() )->setName( substr( $item->get_name(), 0, 127 ) )
		                     ->setQuantity( $item->get_quantity() )
		                     ->setUnitAmount( ( new Money() )->setCurrencyCode( $this->currency )
		                                                     ->setValue( $this->round( $item->get_subtotal() / $item->get_quantity() ) ) );
	}

	protected function get_order_fees() {
		return ( new Item() )
			->setName( __( 'Fees', 'pymntpl-paypal-woocommerce' ) )
			->setUnitAmount( ( new Money() )->setCurrencyCode( $this->currency )->setValue( $this->round( $this->get_order_total_fees() ) ) )
			->setQuantity( 1 );
	}

}