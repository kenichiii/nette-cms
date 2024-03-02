
function init_listeners() {
    $('.newRecordButton').click(function() {
        $('#addNewSettingModal').find('input[name="pointer"]').each(function(){
            $(this).val('');
        })
        $('#addNewSettingModal').find('input[name="info"]').each(function(){
            $(this).val('');
        })
        $('#addNewSettingModal').find('input[name="value"]').each(function(){
            $(this).val('');
        })
        $('#addNewSettingModal').find('ul.error').each(function(){
            $(this).html('');
        })
        $('#addNewSettingModal').modal();

        return false;
    });

}

init_listeners();

class FormExtension {
    initialize(naja) {
        naja.addEventListener('complete', this.form.bind(this));
    }

    form(event) {
        qmandatagrid();
        init_listeners();
        let payload = event.detail.payload;

        if (payload === undefined || payload === null) {
            return;
        }
        if (payload.showModal) {
            $(payload.showModal).modal();
        }
        if (payload.closeModal) {
            if (payload.closeModal === '#addNewSettingModal') {

            }
            $(payload.closeModal).modal('hide');

        }
        $('.datagrid').find('.datagridWrapper').show();
        $('.datagrid').find('.spinner').hide();
    }
}
naja.registerExtension(new FormExtension());