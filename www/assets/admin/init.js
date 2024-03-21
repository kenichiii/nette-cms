
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

function uploadFile(file, url, type = 'image') {
    $('.file-upload .spinner').show();
    var formData = new FormData();
    formData.append('file_to_upload', file);
    formData.append('action', 'fileUpload');
    if (type !== undefined) {
        formData.append('type', type);
    }
    naja.makeRequest('POST', url,formData)
        .then((payload) => { /* process payload */
            $('.file-upload .spinner').hide();
        })
        .catch((error) => { /* handle error */
            $('.file-upload .spinner').hide();
        });
}

function niceUrl(str) {
    str = trim(str);

    // UTF8 "Ä›ĹˇÄŤĹ™ĹľĂ˝ĂˇĂ­Ă©ĹĄĂşĹŻĂłÄŹĹ�ÄľÄş"
    convFromL = String.fromCharCode(283,353,269,345,382,253,225,237,233,357,367,250,243,271,328,318,314);
    // UTF8 "escrzyaietuuodnll"
    convToL = String.fromCharCode(101,115,99,114,122,121,97,105,101,116,117,117,111,100,110,108,108);

    // zmenseni a odstraneni diakritiky
    str = str.toLowerCase();
    str = strtr(str,convFromL,convToL);

    // jakejkoliv nealfanumerickej znak (nepouzit \W ci \w, protoze jinak tam necha treba "ÄŹĹĽËť")
    preg = /[^0-9A-Za-z]{1,}?/g;

    // odstraneni nealfanumerickych znaku (pripaddne je tolerovana tecka)
    str = trim(str.replace(preg, ' '));
    str = str.replace(/[\s]+/g, '-');

    return str;
}


function trim(string) {
    //var re= /^\s|\s$/g;
    var re= /^\s*|\s*$/g;
    return string.replace(re,"");
}


function strtr(s, from, to) {
    out = new String();
    // slow but simple :^)
    top:
        for(i=0; i < s.length; i++) {
            for(j=0; j < from.length; j++) {
                if(s.charAt(i) == from.charAt(j)) {
                    out += to.charAt(j);
                    continue top;
                }
            }
            out += s.charAt(i);
        }
    return out;
}