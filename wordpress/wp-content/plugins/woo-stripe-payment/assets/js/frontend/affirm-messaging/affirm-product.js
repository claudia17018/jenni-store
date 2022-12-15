import $ from 'jquery';
import AffirmBaseMessage from './base';

class AffirmProduct extends AffirmBaseMessage {

    constructor(...params) {
        super(...params);
        this.initialize();
    }

    initialize() {
        $(document.body).on('change', '[name="quantity"]', this.createMessage.bind(this, true));
        this.createMessage();
    }

    getElementContainer() {
        const $el = $('#wc-stripe-affirm-product-msg');
        if (!$el.length) {
            if ($('.summary .price').length) {
                $('.summary .price').append('<div id="wc-stripe-affirm-product-msg"></div>');
            } else {
                if ($('.price').length) {
                    $($('.price')[0]).append('<div id="wc-stripe-affirm-product-msg"></div>');
                }
            }
        }
        return document.getElementById('wc-stripe-affirm-product-msg');
    }

    getTotalPriceCents() {
        if (this.gateway.has_gateway_data()) {
            return this.gateway.get_gateway_data()?.product?.price_cents * this.getQuantity();
        }
        return 0;
    }

    getQuantity() {
        let qty = $('[name="quantity"]').val();
        if (isNaN(qty)) {
            qty = 0;
        }
        return parseInt(qty);
    }
}

export default AffirmProduct;