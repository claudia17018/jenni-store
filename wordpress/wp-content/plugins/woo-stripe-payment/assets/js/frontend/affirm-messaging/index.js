import {BaseGateway} from '@paymentplugins/wc-stripe';
import ProductMessaging from './affirm-product';
import CartMessaging from './affirm-cart';
import CheckoutMessaging from './affirm-checkout';

class AffirmGateway extends BaseGateway {
    constructor(params) {
        super(params);
    }
};

if (typeof wc_stripe_affirm_cart_params !== 'undefined') {
    new CartMessaging(new AffirmGateway(wc_stripe_affirm_cart_params));
}
if (typeof wc_stripe_affirm_product_params !== 'undefined') {
    new ProductMessaging(new AffirmGateway(wc_stripe_affirm_product_params));
}
if (typeof wc_stripe_local_payment_params !== 'undefined') {
    if (wc_stripe_local_payment_params?.gateways?.stripe_affirm) {
        new CheckoutMessaging(new AffirmGateway(wc_stripe_local_payment_params.gateways.stripe_affirm));
    }
}