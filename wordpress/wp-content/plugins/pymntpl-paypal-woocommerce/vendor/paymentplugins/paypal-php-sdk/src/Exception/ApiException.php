<?php


namespace PaymentPlugins\PayPalSDK\Exception;

use Throwable;

/**
 * Class APIException
 * @package PaymentPlugins\PayPalSDK\Exception
 */
class ApiException extends \Exception {

	private $errorCode;

	private $errorData;

	public function __construct( $code = 0, $data = [] ) {
		$this->code = $code;
		$this->initialize( $data );
		//parent::__construct( $message, $code, null );
	}

	private function initialize( $data = [] ) {
		$this->errorData = $data;
		$this->parseErrorMessage( $data );

		if ( isset( $data['name'] ) ) {
			$this->errorCode = $data['name'];
		} elseif ( isset( $data['error'] ) ) {
			$this->errorCode = $data['error'];
		}
	}

	protected function parseErrorMessage( $data ) {
		if ( isset( $data['error_description'] ) ) {
			$this->message = $data['error_description'];
		} elseif ( isset( $data['message'] ) ) {
			$this->message = $data['message'];
		}
	}

	public function getErrorCode() {
		return $this->errorCode;
	}

	public function getData() {
		return $this->errorData;
	}
}