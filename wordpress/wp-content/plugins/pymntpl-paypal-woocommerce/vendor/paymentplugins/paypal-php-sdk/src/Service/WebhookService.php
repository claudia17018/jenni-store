<?php


namespace PaymentPlugins\PayPalSDK\Service;

use PaymentPlugins\PayPalSDK\Webhook;

/**
 * Class WebhookService
 * @package PaymentPlugins\PayPalSDK\Service
 */
class WebhookService extends BaseService {

	protected $path = 'v1/notifications';

	/**
	 * Create a webhook
	 *
	 * @param $params
	 * @param array $options
	 *
	 * @return Webhook
	 */
	public function create( $params, $options = [] ) {
		return $this->post( $this->buildPath( '/webhooks' ), Webhook::class, $params, $options );
	}

	public function delete( $id, $options = [] ) {
		return $this->request( 'delete', $this->buildPath( '/webhooks/%s', $id ), null, null, $options );
	}

	/**
	 * Verify the webhook signature
	 *
	 * @param $params
	 * @param array $options
	 *
	 * @return mixed|object
	 */
	public function verifySignature( $params, $options = [] ) {
		return $this->post( $this->buildPath( '/verify-webhook-signature' ), null, $params, $options );
	}
}