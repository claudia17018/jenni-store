import $ from 'jquery';
import BaseGateway from "./class-base-gateway";
import {
    submitErrorMessage,
    getSetting
} from "@ppcp/utils";

class MiniCartGateway extends BaseGateway {

    constructor(cart, props) {
        super(props);
        this.cart = cart;
        this.initialize();
    }

    initialize() {
        this.cart.on('fragmentsChanged', super.initialize.bind(this));
        super.initialize();
    }

    needsShipping() {
        return this.cart.needsShipping();
    }

    getButtonContainer() {
        const el = document.getElementById(`wc-ppcp-minicart-${this.id}`);
        if (!el) {
            const location = getSetting('ppcp_advanced')?.miniCartLocation;
            $('.woocommerce-mini-cart__buttons').append(`<a id="wc-ppcp-minicart-${this.id}"></a>`);
        }
        return document.getElementById(`wc-ppcp-minicart-${this.id}`);
    }

    getPage() {
        return 'minicart';
    }

    handleBillingToken(response) {
        super.handleBillingToken(response);
        this.processCartCheckout();
    }

    createOrder(data, actions) {
        return this.cart.createOrder({payment_method: this.id}).then(orderId => {
            return orderId;
        });
    }
}

export {MiniCartGateway}