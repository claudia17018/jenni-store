<?php


namespace PaymentPlugins\WooCommerce\PPCP\Admin\MetaBoxes;


use PaymentPlugins\WooCommerce\PPCP\Assets\AssetsApi;
use PaymentPlugins\WooCommerce\PPCP\Main;
use PaymentPlugins\WooCommerce\PPCP\Payments\Gateways\AbstractGateway;
use PaymentPlugins\WooCommerce\PPCP\Payments\PaymentGateways;
use PaymentPlugins\WooCommerce\PPCP\Utilities\PayPalFee;

class Order {

	private $assets_api;

	public function __construct( AssetsApi $assets_api ) {
		$this->assets_api = $assets_api;
		$this->initialize();
	}

	private function initialize() {
		add_action( 'woocommerce_order_item_add_action_buttons', [ $this, 'add_action_buttons' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'woocommerce_admin_order_totals_after_total', [ $this, 'fee_details' ] );
	}

	public function register_scripts() {
		$this->assets_api->register_script( 'wc-ppcp-admin-commons', 'build/js/admin-commons.js' );
	}

	public function add_action_buttons( \WC_Order $order ) {
		$payment_methods = WC()->payment_gateways()->payment_gateways();
		if ( $order->get_type() === 'shop_order' && ! $order->has_status( [ 'pending', 'cancelled', 'draft', 'failed' ] )
		     && isset( $payment_methods[ $order->get_payment_method() ] )
		) {
			$payment_method = $payment_methods[ $order->get_payment_method() ];
			$transaction_id = $order->get_transaction_id();
			if ( $payment_method instanceof AbstractGateway && ! $transaction_id ) {
				$this->assets_api->enqueue_script( 'wc-ppcp-order-metabox', 'build/js/admin-order-metabox.js', [
					'wc-ppcp-admin-commons',
					'wc-backbone-modal'
				] );
				$this->assets_api->enqueue_style( 'wc-ppcp-admin', 'build/css/admin.css' );
				?>
                <button class="wc-ppcp-order-actions button button-secondary"
                        data-order="<?php echo esc_attr( $order->get_id() ) ?>"><?php esc_html_e( 'PayPal Actions', 'pymntpl-paypal-woocommerce' ) ?></button>
                <script type="text/template" id="tmpl-wc-ppcp-order-actions">
                    <div class="wc-backbone-modal">
                        <div class="wc-backbone-modal-content wc-transaction-data">
                            <section class="wc-backbone-modal-main" role="main">
                                <header class="wc-backbone-modal-header">
                                    <h1><?php esc_html_e( 'PayPal Order', 'pymntpl-paypal-woocommerce' ) ?>&nbsp;#{{ data.order.id }}</h1>
                                    <button
                                            class="modal-close modal-close-link dashicons dashicons-no-alt">
                                        <span class="screen-reader-text">Close modal panel</span>
                                    </button>
                                </header>
                                <article class="wc-ppcp-actions__article">
                                    <div class="wc-ppcp-actions-container">
                                        <div class="wc-ppcp-actions__actions">
                                            <#if(data.authorization && data.authorization.status == 'CREATED' && !data.capture){#>
                                            <input type="text" name="ppcp_capture_amount" value="{{data.authorization.amount.value}}">
                                            <button class="button button-secondary ppcp-capture"><?php esc_html_e( 'Capture', 'pymntpl-paypal-woocommerce' ); ?></button>
                                            <button class="button button-secondary ppcp-void"><?php esc_html_e( 'Void', 'pymntpl-paypal-woocommerce' ); ?></button>
                                            <#}else{#>
											<?php esc_html_e( 'There are no available actions.', 'pymntpl-paypal-woocommerce' ) ?>
                                            <#}#>
                                        </div>
                                    </div>
                                </article>
                                <footer>
                                    <div class="inner">

                                    </div>
                                </footer>
                            </section>
                        </div>
                    </div>
                    <div class="wc-backbone-modal-backdrop modal-close"></div>
                </script>
				<?php
			}
		}
	}

	public function fee_details( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( Main::container()->get( PaymentGateways::class )->has_gateway( $order->get_payment_method() ) ) {
			$fee = PayPalFee::display_fee( $order );
			$net = PayPalFee::display_net( $order );
			if ( $fee && $net ) {
				?>
                <tr>
                    <td class="label wc-ppcp-fee"><?php esc_html_e( 'PayPal Fee', 'pymntpl-paypal-woocommerce' ) ?>:</td>
                    <td width="1%"></td>
                    <td><?php echo $fee ?></td>
                </tr>
                <tr>
                    <td class="label wc-ppcp-net"><?php esc_html_e( 'Net payout', 'pymntpl-paypal-woocommerce' ) ?></td>
                    <td width="1%"></td>
                    <td class="total"><?php echo $net ?></td>
                </tr>
				<?php
			}
		}
	}

}