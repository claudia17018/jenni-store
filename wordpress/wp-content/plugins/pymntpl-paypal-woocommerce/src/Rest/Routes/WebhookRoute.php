<?php


namespace PaymentPlugins\WooCommerce\PPCP\Rest\Routes;


use PaymentPlugins\PayPalSDK\PayPalClient;
use PaymentPlugins\PayPalSDK\WebhookEvent;
use PaymentPlugins\WooCommerce\PPCP\Admin\Settings\APISettings;
use PaymentPlugins\WooCommerce\PPCP\Logger;

class WebhookRoute extends AbstractRoute {

	private $client;

	private $api_settings;

	private $log;

	public function __construct( PayPalClient $client, APISettings $api_settings, Logger $log ) {
		$this->client       = $client;
		$this->api_settings = $api_settings;
		$this->log          = $log;
	}

	public function get_path() {
		return 'webhook/(?P<environment>[\w]+)';
	}

	public function get_routes() {
		return [
			[
				'methods'  => \WP_REST_Server::CREATABLE,
				'callback' => [ $this, 'handle_request' ]
			]
		];
	}

	public function handle_request( \WP_REST_Request $request ) {
		// authenticate the request
		$environment = $request->get_param( 'environment' );
		try {
			$this->validate_headers();
			// get the webhook ID
			$webhook_id = $this->api_settings->get_webhook_id( $environment );
			$payload    = \json_decode( $request->get_body(), true );
			$params     = [
				'auth_algo'         => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'],
				'cert_url'          => $_SERVER['HTTP_PAYPAL_CERT_URL'],
				'transmission_id'   => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'],
				'transmission_sig'  => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'],
				'transmission_time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'],
				'webhook_id'        => $webhook_id,
				'webhook_event'     => $payload
			];

			$result = $this->client->webhooks->verifySignature( $params );

			if ( $result->verification_status === 'SUCCESS' ) {
				return parent::handle_request( $request );
			}
			throw new \Exception( __( 'Verification of Webhook signature failed.', 'pymntpl-paypal-woocommerce' ) );
		} catch ( \Exception $e ) {
			$this->log->error( sprintf( 'Webhook failed. Reason: %s', $e->getMessage() ) );

			return new \WP_Error( 'INVALID_WEBHOOK', $e->getMessage(), [ 'status' => 400 ] );
		}
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		$payload = \json_decode( $request->get_body(), true );
		$event   = new WebhookEvent( $payload );
		try {
			do_action( 'wc_ppcp_webhook_event_' . strtolower( $event->event_type ), $event->resource, $event, $request );

			return [];
		} catch ( \Exception $e ) {
			$status = $e->getCode() ? $e->getCode() : 200;
			$this->log->error( sprintf( 'Error processing event %s. Reason: %s', $event->event_type, $e->getMessage() ) );

			return new \WP_Error( 'WEBHOOK_ERROR', $e->getMessage(), [ 'status' => $status ] );
		}
	}

	/**
	 * @throws \Exception
	 */
	private function validate_headers() {
		//$this->log->info( print_r( $_SERVER, true ) );
		$headers = [
			'HTTP_PAYPAL_TRANSMISSION_SIG',
			'HTTP_PAYPAL_AUTH_ALGO',
			'HTTP_PAYPAL_CERT_URL',
			'HTTP_PAYPAL_TRANSMISSION_ID',
			'HTTP_PAYPAL_TRANSMISSION_TIME'
		];
		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				throw new \Exception( sprintf( 'The %s header cannot be empty.', $header ) );
			}
		}
	}

}