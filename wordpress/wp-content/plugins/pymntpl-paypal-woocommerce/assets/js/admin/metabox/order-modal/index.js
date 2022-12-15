import $ from 'jquery';

export const OrderMetaBoxModal = $.WCBackboneModal.View.extend({
    events: {
        ...$.WCBackboneModal.View.prototype.events, ...{
            'click .ppcp-capture': 'handleCapture',
            'click .ppcp-void': 'handleVoid'
        }
    },
    initialize(data) {
        this.props = data.props;
        $.WCBackboneModal.View.prototype.initialize.call(this, data);
        this.captureButton = this.$el.find('.ppcp-capture');
        this.voidButton = this.$el.find('.ppcp-void');
    },
    disableButtons() {
        this.captureButton.prop('disabled', true);
        this.voidButton.prop('disabled', true);
    },
    enableButtons() {
        this.captureButton.prop('disabled', false);
        this.voidButton.prop('disabled', false);
    },
    handleCapture(e) {
        const amount = this.$el.find('[name="ppcp_capture_amount"]').val();
        this.props.displayLoader($('.wc-ppcp-actions__actions'));
        this.disableButtons();
        this.props.handleCapture(amount, this.props.order_id).then((response) => {
            if (response.code) {
                this.enableButtons();
                this.props.hideLoader();
                this.submitError(response.message);
            } else {
                window.location.reload();
            }
        }).catch(error => {
            this.props.hideLoader();
            this.submitError(error?.message);
        });
    },
    handleVoid(e) {
        const $button = $(e.currentTarget);
        this.props.displayLoader($('.wc-ppcp-actions__actions'));
        this.disableButtons();
        this.props.handleVoid(this.props.order_id).then((response) => {
            this.props.hideLoader();
            if (response.code) {
                this.enableButtons();
                this.submitError(response.message);
            } else {
                window.location.reload();
            }
        }).catch(error => {
            this.props.hideLoader();
            this.submitError(error?.message);
        });
    },
    submitError(message) {
        this.$el.find('.ppcp-order-actions-error').remove();
        this.$el.find('.wc-ppcp-actions__article').prepend(`<div class="ppcp-order-actions-error">${message}</div>`);
    }
});

export default OrderMetaBoxModal;