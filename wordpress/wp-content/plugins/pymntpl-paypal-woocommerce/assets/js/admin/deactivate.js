import {useState, useCallback, useEffect, render} from '@wordpress/element';
import {Modal, Button, RadioControl, TextareaControl} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

window.addEventListener('load', () => {
    const app = document.createElement('div');
    app.id = 'ppcp-app';
    document.getElementById('wpbody')?.append(app);
    let el = document.getElementById('deactivate-payment-plugins-for-paypal-woocommerce');
    if (!el) {
        el = document.getElementById('deactivate-pymntpl-paypal-woocommerce');
    }
    render(<App el={el}/>, document.getElementById('ppcp-app'));
});

const App = ({el}) => {
    const [open, setOpen] = useState(false);
    const openModal = useCallback((e) => {
        e.preventDefault();
        setOpen(true)
    }, []);
    useEffect(() => {
        if (el) {
            el.addEventListener('click', openModal);
            return () => el.removeEventListener('click', openModal);
        }
    }, [openModal]);
    const submit = () => {
        el.removeEventListener('click', openModal);
        el.click();
        setOpen(false);
    }
    if (el) {
        return <DeactivationModal submit={submit} deactivateLink={el.href} open={open} setOpen={setOpen} data={wcPPCPModal}/>
    }
    return null;
}

const DeactivationModal = ({deactivateLink, open, setOpen, data, submit}) => {
    const [reasonCode, setReasonCode] = useState('found_better');
    const [reasonText, setReasonText] = useState('');
    const [processing, setProcessing] = useState();
    const [placeholder, setPlaceHolder] = useState('');
    const onClose = () => setOpen(false);
    const options = Object.keys(data.options).map(id => ({
        label: data.options[id],
        value: id
    }));
    const onSubmit = async () => {
        setProcessing(true);
        try {
            await apiFetch({
                method: 'POST',
                url: data.route,
                data: {
                    reason_code: reasonCode,
                    reason_text: reasonText
                }
            })
        } catch (error) {

        } finally {
            setProcessing(false);
            submit();
        }
    }

    useEffect(() => {
        if (data.placeholders.hasOwnProperty(reasonCode)) {
            setPlaceHolder(data.placeholders[reasonCode]);
        } else {
            setPlaceHolder('');
        }
    }, [reasonCode]);

    const props = {
        title: data.title,
        isDismissible: true,
        focusOnMount: true,
        isFullScreen: false,
        onRequestClose: onClose
    }
    if (open) {
        return (
            <Modal {...props}>
                <div className='ppcp-modal-content'>
                    <p>{data.description}</p>
                    <div className='options-container'>
                        <RadioControl selected={reasonCode} options={options} onChange={setReasonCode}/>
                    </div>
                    <div className='ppcp-deactivation__text'>
                        <TextareaControl placeholder={placeholder} value={reasonText} onChange={setReasonText}/>
                    </div>
                </div>
                <div className='ppcp-modal-actions'>
                    <Button variant='primary' onClick={onSubmit} isBusy={processing} disabled={processing}>{data.buttons.primary}</Button>
                    <Button href={deactivateLink} className='ppcp-skip-deactivate' variant='tertiary' onClick={onClose}>{data.buttons.secondary}</Button>
                </div>
            </Modal>
        )
    }
    return null;
}