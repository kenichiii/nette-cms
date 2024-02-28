
//init Naja

class ModalExtension {
    initialize(naja) {
        naja.addEventListener('complete', this.modal.bind(this));
    }

    modal(event) {
        let payload = event.detail.payload;

        if (payload === undefined || payload === null || !payload.hasOwnProperty('modalId')) {
            return;
        }
        let modalId = payload.modalId;
        let hideModal = payload.hideModal;

        if (hideModal === true) {
            $('#' + modalId).hide()
            return;
        }
        $('#' + modalId).modal();
    }
}

naja.registerExtension(new ModalExtension());
document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

naja.redirectHandler.addEventListener('redirect', (event) => event.detail.setHardRedirect(true))
