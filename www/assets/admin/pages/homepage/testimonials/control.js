
function init_listeners() {
    $('#selectedLang').change(function(){
        window.location = $(this).val();
    })

    $('.newRecordButton').click(function() {
        $('#addFormModal').find('input[name="name"]').each(function(){
            $(this).val('');
        })
        $('#addFormModal').find('input[name="position"]').each(function(){
            $(this).val('');
        })
        $('#addFormModal').find('[name="content"]').each(function(){
            $(this).val('');
        })
        $('#addFormModal').modal();

        return false;
    });
    if (document.getElementById('file_to_upload'))
    document.getElementById('file_to_upload').addEventListener('change', (event) => {
        window.selectedFile = event.target.files[0];

        uploadFile(window.selectedFile, $('#foto-upload-url').val());


    });
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