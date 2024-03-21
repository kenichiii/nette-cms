
function init_listeners() {

}

init_listeners();

class FormExtension {
    initialize(naja) {
        naja.addEventListener('complete', this.form.bind(this));
    }

    form(event) {
        qmandatagrid();
        let payload = event.detail.payload;

        if (payload === undefined || payload === null) {
            return;
        }

        if (payload.closeModal) {
            $(payload.closeModal).modal('hide');
        }

        if (payload.showModal) {
            $(payload.showModal).modal();
        }

        $('.datagrid').find('.datagridWrapper').show();
        $('.datagrid').find('.spinner').hide();

        init_listeners();
    }
}
naja.registerExtension(new FormExtension());