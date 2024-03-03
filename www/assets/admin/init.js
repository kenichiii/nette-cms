
//init Naja

document.addEventListener('DOMContentLoaded', naja.initialize.bind(naja));

naja.redirectHandler.addEventListener('redirect', (event) => event.detail.setHardRedirect(true))

class NavbarExtension {
    initialize(naja) {
        naja.addEventListener('complete', this.after_event.bind(this));
        naja.addEventListener('before', this.before_event.bind(this));
    }

    after_event(event) {
        let payload = event.detail.payload;

        if (payload === undefined || payload === null) {
            return;
        }
        $('.navbar-nav').find('.spinner').hide();
        $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');
    }
    before_event(event) {
        console.log(event.target)

        var target = $( event.target );

        if ( target.is( "a" ) ||target.is( "li" ) ) {
            alert('test')
        }
        $('.navbar-nav').find('.spinner').show();
    }
}
naja.registerExtension(new NavbarExtension());

function uploadFile(file, url) {
    $('.file-upload .spinner').show();
    var formData = new FormData();
    formData.append('file_to_upload', file);
    formData.append('action', 'fileUpload');
    naja.makeRequest('POST', url,formData)
        .then((payload) => { /* process payload */
            $('.file-upload .spinner').hide();
        })
        .catch((error) => { /* handle error */
            $('.file-upload .spinner').hide();
        });
}