import $ from 'jquery';
import {getRestRoute, getSetting, setSetting} from "@ppcp/utils";
import apiFetch from "@wordpress/api-fetch";
import Event from './event';

class Cart extends Event {

    constructor() {
        super();
        this.data = getSetting('cart');
        this.page = getSetting('generalData').page;
        this.processing = false;
        $(document.body).on('updated_wc_div', this.onCartUpdated.bind(this));
        $(document.body).on('updated_cart_totals', this.onCartUpdated.bind(this));
        $(document.body).on('updated_checkout', this.onUpdatedCheckout.bind(this));
        $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', this.onCartFragmentsChanged.bind(this));
    }

    async onCartUpdated(e) {
        if (window.wcPPCPCartData) {
            await this.refreshData(window.wcPPCPCartData);
        } else {
            await this.refreshData(null);
        }
        setSetting('queryParams', this.data.queryParams);
        this.trigger('cartUpdated', this);
    }

    async onUpdatedCheckout(e) {
        await this.refreshData(window.wcPPCPCartData ? window.wcPPCPCartData : null);
        setSetting('queryParams', this.data.queryParams);
        this.trigger('updatedCheckout', this);
    }

    onCartFragmentsChanged() {
        // fetch updated cart data
        setTimeout(() => {
            if (window.wcPPCPMiniCartUpdate) {
                this.data = {...this.data, ...wcPPCPMiniCartUpdate};
            }
            this.trigger('fragmentsChanged', this);
        }, 250);
    }

    getData() {
        return getSetting('cart');
    }

    needsShipping() {
        return this.data?.needsShipping;
    }

    async refreshData(data = null) {
        if (data) {
            this.data = {...this.data, ...data};
        } else {
            if (!this.processing) {
                try {
                    this.processing = true;
                    const response = await apiFetch({
                        method: 'POST',
                        url: getRestRoute('cart/refresh'),
                        data: {page: this.page},
                    });
                    this.data = {...this.data, ...response.cart};
                    this.data.queryParams = response.queryParams;
                } catch (error) {
                    console.log(error);
                } finally {
                    this.processing = false;
                }
            }
        }
    }

    async addToCart(data) {
        try {
            return apiFetch({
                method: 'POST',
                url: getRestRoute('cart/item'),
                data
            })
        } catch (error) {
            throw error;
        }
    }

    async createOrder(data) {
        try {
            return apiFetch({
                method: 'POST',
                url: getRestRoute('cart/order'),
                data
            })
        } catch (error) {
            throw error;
        }
    }

    async doOrderPay(payment_method) {
        const order = getSetting('order');
        try {
            return apiFetch({
                method: 'POST',
                url: getRestRoute('order/pay'),
                data: {
                    payment_method,
                    ...order
                }
            })
        } catch (error) {
            throw error;
        }
    }

    getTotal() {
        return this.data?.total;
    }
}

export default new Cart();