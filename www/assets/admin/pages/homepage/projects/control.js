
function init_listeners() {
    $('#selectedLang').change(function(){
        window.location = $(this).val();
    })

    $('.newRecordButton').click(function() {
        $('#addFormModal').find('input[name="title"]').each(function(){
            $(this).val('');
        })
        $('#addFormModal').find('input[name="uri"]').each(function(){
            $(this).val('');
        });

        $('#addFormModal').find('input[name="description"]').each(function(){
            $(this).val('');
        });
        $('#addFormModal').modal();

        return false;
    });


    $("input[name='title']").unbind('keyup').keyup(function(){
        $("input[name='uri']").val(niceUrl($(this).val()));
    })

    if (document.getElementById('image_to_upload')) {
        document.getElementById('image_to_upload').addEventListener('change', (event) => {
            window.selectedFile = event.target.files[0];

            uploadFile(window.selectedFile, $('#foto-upload-url').val(), 'image');
        });
    }


}
function init_wysivig() {

    if ($('#content').tinymce() != null) {
        $('#content').tinymce().remove()
    }


    $('#content').tinymce({
        // Location of TinyMCE script
        script_url: '/'+$('#subdir').val()+'assets/admin/vendor/tinymce/tinymce.min.js',
        width: Math.round($('#editFormModal .modal-body').width()),
        height: 250,
        language: $('#lang').val() === 'cz' ? 'cs' : 'en',
        fullpage_default_encoding: "utf-8",
        entity_encoding: 'raw',
        // General options
        //theme : "advanced",
        plugins: "advlist,autolink,link,image,lists,charmap,preview,hr,anchor,pagebreak,searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table contextmenu directionality template paste textcolor",
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link image | code",
        menubar: "format insert table edit view" //tools


        //Example content CSS (should be your site CSS)
        //content_css : "/css/content.css"
        /*
                                // Drop lists for link/image/media/template dialogs
                                template_external_list_url : "lists/template_list.js",
                                external_link_list_url : "lists/link_list.js",
                                external_image_list_url : "lists/image_list.js",
                                media_external_list_url : "lists/media_list.js",

                                // Replace values for the template plugin
                                template_replace_values : {
                                        username : "Some User",
                                        staffid : "991234"
                                }
                                */
    });

    setTimeout(function () {
        setInterval(function () {
            if ($("#content_hidden") && $('#content').tinymce() && $('#content').tinymce().getContent instanceof Function) {
                $("#content_hidden").val($('#content').tinymce().getContent());
            }
        }, 100);
        $('body').addClass('modal-open');
    }, 1500);


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

        if (payload.showModal === '#editFormModal') {
            $('#editFormModal').on('shown.bs.modal', function() {
                $(document).off('focusin.modal');
            });
            init_wysivig()
        }

        $('.datagrid').find('.datagridWrapper').show();
        $('.datagrid').find('.spinner').hide();

        init_listeners();
    }
}
naja.registerExtension(new FormExtension());