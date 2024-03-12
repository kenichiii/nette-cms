function customMenu() {
    return            {"create" : {
            "separator_before"	: false,
            "separator_after"	: true,
            "label"				: "New subpage",
            "action"			: function (obj) { this.create(obj);
                return false;
            }
        },
        "remove" : {
            "separator_before"	: false,
            "icon"				: false,
            "separator_after"	: false,
            "label"				: "Delete page",
            "action"			: function (obj) {
                var that = this;

                if ( window.confirm('Really delete this page?')) {
                    if(that.is_selected(obj)) { that.remove(); } else { that.remove(obj); }
                };
                return false;
            }
        }
    }
}


$(function(){

    $("#newrootpage").unbind('click').click(function() {
        $('#tree').jstree("deselect_all");
        $("#tree").jstree("create", null, "last", { "attr" : { "rel" : 'page' } });
    });


    $("#view-lang").change(function(){
        clearInterval(contentInterval)
        $("#tree").jstree('refresh',-1);
        $("#page-detail").html('Choose page to edit');
    });


    $("#tree")
        .jstree({
            // List of active plugins
            "plugins" : [
                "themes","json_data","ui","crrm","dnd","search","types","contextmenu"
            ],
            "contextmenu": {items: customMenu},
            // I usually configure the plugin that handles the data first
            // This example uses JSON as it is most common
            "json_data" : {
                // This tree is ajax enabled - as this is most common, and maybe a bit more complex
                // All the options are almost the same as jQuery's AJAX (read the docs)
                "ajax" : {
                    // the URL to fetch the data
                    "url" : $('#admin-pages-tree-url').val(),
                    // the `data` function is executed in the instance's scope
                    // the parameter is the node being loaded
                    // (may be -1, 0, or undefined when loading the root nodes)
                    "data" : function (n) {
                        // the result is fed to the AJAX request `data` option
                        return {
                            "operation" : "getChildren",
                            "lang" : $("#view-lang").val(),
                            "id" : n.attr ? n.attr("id").replace("node_","") : 0
                        };
                    }
                }
            },

            // Using types - most of the time this is an overkill
            // read the docs carefully to decide whether you need types
            "types" : {
                // I set both options to -2, as I do not need depth and children count checking
                // Those two checks may slow jstree a lot, so use only when needed
                "max_depth" : -2,
                "max_children" : -2,
                // I want only `drive` nodes to be root nodes
                // This will prevent moving or creating any other type as a root node
                "valid_children" : [ "page" ],
                "types" : {
                    // The default type
                    "default" : {
                        // I want this type to have no children (so only leaf nodes)
                        // In my case - those are files
                        "valid_children" : "page"

                    },
                    "page" : {
                        // I want this type to have no children (so only leaf nodes)
                        // In my case - those are files
                        "valid_children" : "page"

                    }
                }
            },
            // UI & core - the nodes to initially select and open will be overwritten by the cookie plugin
            /*
                    // the UI plugin - it handles selecting/deselecting/hovering nodes
                    "ui" : {
                        // this makes the node with ID node_4 selected onload
                        "initially_select" : [ "node_4" ]
                    },
            */
            // the core plugin - not many options here
            "core" : {
                // just open those two nodes up
                // as this is an AJAX enabled tree, both will be downloaded from the server
//			"initially_open" : [ "node_2" , "node_3" ]
            }
        })
        .bind("create.jstree", function (e, data) {

            if ($('#content').tinymce() !== null) {
                $('#content').tinymce().remove()
            }

            naja.makeRequest('POST', $("#admin-pages-add-url").val(),{
                "operation" : "createNode",
                "parent" : data.rslt.parent.attr ? data.rslt.parent.attr("id").replace("node_","") : 0,
                "position" : data.rslt.position,
                "title" : data.rslt.name,
                "type" : data.rslt.obj.attr("rel"),
                "lang" : $("#view-lang").val()
            })
                .then((payload) => { /* process payload */
                    $(data.rslt.obj).attr("id", "node_" + payload.id).attr("rel", "page");
                    $("#node_" + payload.id).find('a').trigger('click');
                })
                .catch((error) => { /* handle error */
                });
        })
        .bind("move_node.jstree", function (e, data) {
            data.rslt.o.each(function (i) {

                var prev =  $(this).prev().is('li') ? $(this).prev().attr("id").replace("node_","") : 'none';
                var next = $(this).next().is('li') ? $(this).next().attr("id").replace("node_","") : 'none';

                naja.makeRequest('POST', $("#admin-pages-update-url").val(), {
                        "operation" : "move_node",
                        "currItemId" : $(this).attr("id").replace("node_",""),
                        "prevItemId" : prev,
                        "nextItemId" :  next,
                        "parentId" : data.rslt.np.attr("id").replace("node_","")
                    })
                    .then((payload) => { /* process payload */

                    })
                    .catch((error) => { /* handle error */
                    });;
            });
        })
        .bind("remove.jstree", function (e, data) {
            data.rslt.obj.each(function () {
                if($('#content').tinymce()!=null) {
                    $('#content').tinymce().remove()
                }

                naja.makeRequest('POST', $('#admin-pages-delete-url').val(),{"pageId" : this.id.replace("node_","")})
                    .then((payload) => { /* process payload */
                        $("#page-detail").html('Choose page to edit');
                    })
                    .catch((error) => { /* handle error */
                    });
            });
        })
        .bind("select_node.jstree", function (event, data) {
            // `data.rslt.obj` is the jquery extended node that was clicked

            if ($('#content').tinymce()!=null) {
                $('#content').tinymce().remove()
            }

            if (data.rslt.obj.attr("id") == undefined) {
                $("#page-detail").html('Page not exists in database');
            }
            else {
                loadPage(data.rslt.obj.attr("id").replace("node_", ""));
            }
        });
}); //end jquery ready


function loadPage(id) {

    if ($('#content').tinymce() != null) {
        $('#content').tinymce().remove()
    }
    $('.page-content-wrraper').hide();
    $('.spinner').show();
    naja.makeRequest('GET', $("#admin-pages-page-url").val(),{page:id})
        .then((payload) => { /* process payload */
            activate_page_listeners();
            $('.spinner').hide();
            $('.page-content-wrraper').show();
        })
        .catch((error) => { /* handle error */
        });
}
let contentInterval;
function activate_page_listeners() {
    //activateFotoFormListener();
    //gallerypageadmin();

    if ($('#content').tinymce() != null) {
        $('#content').tinymce().remove()
    }

    $('#content').tinymce({
        // Location of TinyMCE script
        script_url: '/'+$('#subdir').val()+'assets/admin/vendor/tinymce/tinymce.min.js',
        width: 800,
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
    $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');
    setTimeout(function () {
        contentInterval = setInterval(function () {
            if ($("#content_hidden") && $('#content').tinymce() && $('#content').tinymce().getContent instanceof Function) {
                $("#content_hidden").val($('#content').tinymce().getContent());
            }
        }, 100);
    }, 1500);
}

if ($('#content')) {
    activate_page_listeners();

}

class PageFormExtension {
    initialize(naja) {
        naja.addEventListener('complete', this.form.bind(this));
        naja.addEventListener('before', this.before.bind(this));
    }
    before(event) {
        clearInterval(contentInterval);
    }
    form(event) {
        let payload = event.detail.payload;

        if (payload === undefined || payload === null || !payload.hasOwnProperty('afterPageForm')) {
            return;
        }
        if (payload.selectTab) {
            $('a.nav-link[href="' + payload.selectTab + '"]').trigger('click');
        }

        activate_page_listeners();
    }
}
naja.registerExtension(new PageFormExtension());




