<?php

namespace PaymentPlugins\PPCP\WooFunnels\Upsell\PaymentGateways;

use PaymentPlugins\WooCommerce\PPCP\Constants;

class PayPal extends AbstractGateway {

	public $id = 'ppcp';

	protected $key = 'ppcp';

	public function __construct( ...$args ) {
		parent::__construct( ...$args );
		add_filter( 'wc_ppcp_get_paypal_flow', [ $this, 'get_paypal_flow' ], 10, 2 );
	}

	public function has_token( $order ) {
		$token = $order->get_meta( Constants::BILLING_AGREEMENT_ID );

		return ! empty( $token );
	}

	public function get_paypal_flow( $flow, $context ) {
		if ( $flow === 'checkout' && $this->should_tokenize() ) {
			$flow = 'vault';
		}

		return $flow;
	}

	public function get_payment_method_script_handles() {
		$this->assets->register_script( 'wc-ppcp-woofunnels', 'build/wc-ppcp-woofunnels-upsell.js' );

		return [ 'wc-ppcp-woofunnels' ];
	}

}