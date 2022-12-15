<?php

namespace PaymentPlugins\WooCommerce\PPCP\Rest\Routes\Admin;

class DeactivationRoute extends AbstractRoute {

	private $api_url = 'https://crm.paymentplugins.com/v1/feedback/paypal';

	public function get_path() {
		return 'deactivate';
	}

	public function get_routes() {
		return [
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => [ $this, 'handle_request' ],
			'permission_callback' => [ $this, 'get_admin_permission_check' ],
			'args'                => [
				'reason_code' => [
					'required' => true
				]
			]
		];
	}

	public function handle_post_request( \WP_REST_Request $request ) {
		$reason_code = $request['reason_code'];
		$reason_text = isset( $request['reason_text'] ) ? $request['reason_text'] : '';
		$website     = site_url();
		$result      = wp_safe_remote_post( $this->api_url, [
			'method'      => 'POST',
			'timeout'     => 30,
			'httpversion' => 1,
			'blocking'    => true,
			'headers'     => [
				'Content-Type' => 'application/json'
			],
			'body'        => wp_json_encode( compact( 'website', 'reason_code', 'reason_text' ) ),
			'cookies'     => []
		] );
		if ( is_wp_error( $result ) ) {
			return new \WP_Error( 'feedback-error', $result->get_error_message(), array( 'status' => 200 ) );
		}
		if ( wp_remote_retrieve_response_code( $result ) !== 200 ) {
			$body = json_decode( wp_remote_retrieve_body( $result ), true );

			return new \WP_Error( 'contact-error', $body['message'], array( 'status' => 200 ) );
		}

		return [ 'success' => true ];
	}

}