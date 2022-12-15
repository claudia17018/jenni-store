<?php

namespace PaymentPlugins\PPCP\WooFunnels\Upsell;

use PaymentPlugins\WooCommerce\PPCP\Constants;
use PaymentPlugins\WooCommerce\PPCP\Integrations\PluginIntegrationsRegistry;
use PaymentPlugins\WooCommerce\PPCP\Integrations\PluginIntegrationType;
use PaymentPlugins\WooCommerce\PPCP\Payments\Gateways\AbstractGateway;
use PaymentPlugins\WooCommerce\PPCP\WPPayPalClient;

class WooFunnelsIntegration implements PluginIntegrationType {

	public $id = 'woofunnels_upsell';

	private $active;

	private $client;

	public function __construct( $active, WPPayPalClient $client ) {
		$this->active = $active;
		$this->client = $client;
		$this->initialize();
	}

	public function is_active() {
		return $this->active;
	}

	public function initialize() {
		add_filter( 'woocommerce_ppcp_plugin_integration_registration', [ $this, 'register' ] );
		add_filter( 'wc_ppcp_process_payment_result', [ $this, 'process_payment' ], 10, 3 );
	}

	public function register( PluginIntegrationsRegistry $registry ) {
		$registry->register( $this );
	}

	/**
	 * @param                                                                    $result
	 * @param \WC_Order                                                          $order
	 * @param \PaymentPlugins\WooCommerce\PPCP\Payments\Gateways\AbstractGateway $payment_method
	 *
	 * @return mixed
	 */
	public function process_payment( $result, \WC_Order $order, AbstractGateway $payment_method ) {
		$funnels_payment_method = WFOCU_Core()->gateways->get_integration( $payment_method->id );
		if ( $funnels_payment_method->should_tokenize() ) {
			$billing_token = $payment_method->get_billing_token_from_request();
			if ( $billing_token ) {
				$billing_agreement = $this->client->billingAgreements->create( [ 'token_id' => $billing_token ] );
				if ( is_wp_error( $billing_agreement ) ) {
					return $billing_agreement;
				}
				$token = $payment_method->get_payment_method_token_instance();
				$token->initialize_from_payer( $billing_agreement->payer->payer_info );
				$order->set_payment_method_title( $token->get_payment_method_title() );
				$order->update_meta_data( Constants::BILLING_AGREEMENT_ID, $billing_agreement->id );
				$order->update_meta_data( Constants::PPCP_ENVIRONMENT, $this->client->getEnvironment() );
				$order->update_meta_data( Constants::PAYER_ID, $token->get_payer_id() );
				$order->save();
				$payment_method->payment_handler->set_use_billing_agreement( true );
				$result = false;
			}
		}

		return $result;
	}

}