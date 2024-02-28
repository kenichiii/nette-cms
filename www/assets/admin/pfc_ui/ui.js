
/*
 * 
 * CENTER HORIZONTALY
 */
(function ( $ ) {
$.fn.centerHorizontaly = function(autophminus) {
  //alert('teseeet');  
  
  
  
  this.each(function(){      
      
         var ph = parseInt($(this).parent().css('width'));
         
         if(ph)
         {
            var th = parseInt($(this).css('width'));
            var thpt = parseInt($(this).css('padding-left'));
            var thpb = parseInt($(this).css('padding-right'));
            var thmt = parseInt($(this).css('margin-left'));
            var thmb = parseInt($(this).css('margin-right'));
                        
            if(autophminus) ph -= autophminus;
            
            var hh = (ph - th - thpt - thpb - thmt - thmb)/2;
            
            $(this).css('margin-left',(hh+thmt)+'px');            
         }
     
    });     
    return this;
};//end centerHorizontaly
}( jQuery ));



/**
 * 
 * UI TABS
 */
(function ( $ ) {
$.fn.uiTabs = function() {
        
    var that = this;

    that.find('.tabs-head a').removeClass('active');
    that.find('.tab').hide();
    that.find('.tab:first').show();
    that.find('.tabs-head a:first').addClass('active');
    
    that.find('.tabs-head a').unbind('click').click(function(){
        that.find('.tabs-head a').removeClass('active');
        that.find('.tab').fadeOut('fast');
        $($(this).attr('href')).fadeIn('fast');
        $(this).addClass('active');
        return false;
    });
                
    return {
        openTab:function(htmlid) {
                that.find('.tabs-head a').removeClass('active');
                that.find('.tab').hide();
                $('#'+htmlid).fadeIn('fast');
                $('#'+htmlid).addClass('active');
        }
    };
};//end $.fn.uiTabs
}( jQuery ));



/**
 * 
 * PFC AJAX FORM
 */
(function ( $ ) {
$.fn.pfcAjaxForm = function( options ) {
        
        var that = this;
        
        var settings = $.extend({            
                ajaxFormOptions: {
                    success: success,
                    dataType:  'json'
                },               
                onforminit: $.fn.pfcAjaxForm.forminit,
                succ: $.fn.pfcAjaxForm.succ,
                onerror: $.fn.pfcAjaxForm.error,
                onexception: $.fn.pfcAjaxForm.exception
            }, options );


    this.ajaxForm(settings.ajaxFormOptions);

    settings.onforminit(that);

    function success(json)
    {
        that.find(".form_err").remove();   
        that.find('.error').hide();

        if( json.succ == 'yes' ) {
                settings.succ(json,that);
        }
        else {                                    
            if ( typeof(json.errors) == 'object' ) 
            {                                    
              if(json.errors[0] && json.errors[0].el=='exception')
                   {
                      settings.onexception(json.errors[0].mess,that); 
                   }
                   else {
                      settings.onerror(json.errors,that); 
                    }
            }
            else settings.onexception('error',that);  
        }
 
    }


                
    return this;
};//end $.fn.pfcAjaxForm

$.fn.pfcAjaxForm.forminit = function(form) {
        
}

$.fn.pfcAjaxForm.succ = function(json,f) {orm
        showAlert(json.succMsg);
}
    
$.fn.pfcAjaxForm.error = function(errors,form) {
                                    form.find('.error').fadeIn();
                                    
                                      for ( var i=0;i<errors.length;i++ ) 
                                      {
                                        if ( errors[i].el ) 
                                        {
                                          form.find("input[name='" + errors[i].el +"']")                                            
                                            .after('<div class="form_err">' + errors[i].mess + '</div>');
                                          form.find("select[name='" + errors[i].el +"']")                                            
                                            .after('<div class="form_err">' + errors[i].mess + '</div>');
                                          form.find(".err-" + errors[i].el )                                            
                                            .after('<div class="form_err">' + errors[i].mess + '</div>');
                                         }
                                       } //end for      
}

$.fn.pfcAjaxForm.exception = function(text,form) {
        showAlert(text,{mtype:'err'});        
}

}( jQuery ));

/*
 * 
 * UI DIALOG
 */
(function ( $ ) {
$.uiDialog = function(url,options) {

    var settings = {
        title:'uiDialog',
        width:1000,
        modal:true,
        onload:function(){}
    }
    
    $.extend(settings,options);
    
            
            var dialog = $('<div style="display:hidden"></div>').appendTo('body');
            // load remote content
            dialog.load(
                url,
                {},
                function (responseText, textStatus, XMLHttpRequest) {
                    dialog.dialog({ 
                            title: settings.title,
                            modal: settings.modal,
                            width: settings.width,
                            close: function(event, ui) {
                                $(this).remove();
                                //tinymce
                            }
                    });

                    settings.onload();
                });
    
}
}( jQuery ));



/**
 * 
 * PFC GRID ADMIN
 */
(function ( $ ) {    
     
$.fn.pfcFilesGridAdmin = function(options) {

    var that = this;
    var listUrl = this.find("input[name='list']").val();

    var fu = that.find("input[name='upload']").first();
    
    fu.unbind('change').change(function() {     
            upload(this.files);
    });
    
    fu.hide();

            that.find('a.button').unbind('click').click(function(){    
                fu.trigger('click');
                return false;
            });            

    var sett = {
        btnAdd: function() {
            return;
        } 
    };
    
    $.extend(sett,options);
    
    return that.pfcGridAdmin(sett);
        
        
        
function upload(files)
    {
        var formData = new FormData();
        var xhr = new XMLHttpRequest();
        
        var pr = that.find(".progress-holder-holder").html();
        
        that.find('output').html(pr);
        
        for(var i=0;i<files.length;i++)
        addFile(files[i]); 
           
        that.find(".form-holder select").each(function(){
            formData.append($(this).attr('name'),$(this).val());
        });   

         that.find(".form-holder input").each(function(){
            formData.append($(this).attr('name'),$(this).val());
        });      
    
          that.find(".form-holder textarea").each(function(){
            formData.append($(this).attr('name'),$(this).val());
        });     

    
                    // Update progress bar
                xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                                that.find('.progress-bar').css('width', (evt.loaded / evt.total) * 100 + "%");
                        }
                        else {
                            // No data to calculate on
                        }
                }, false);

                // File uploaded
                xhr.addEventListener("load", function () {
                        
                        var json = JSON.parse(xhr.responseText);
                        
                        if(json.succ=='yes')
                        {                            
                            showAlert(json.succMsg);
                        }
                        else {
                            showAlert('Během nahrávání došlo k problémům a né všechny soubory byly nahrány na server',{mtype:'err'}); 
                            
                                     for ( var i=0;i<json.errors.length;i++ ) 
                                      {
                                        if ( json.errors[i].el ) 
                                        {
                                            that.find('output .file-upload-preview').each(function(){
                                               if( $(this).find('.name').first().text() == json.errors[i].el )
                                               {
                                                   $(this).addClass('error');
                                                   $(this).find('.error-container').html(json.errors[i].mess+' <button>OK</button>');
                                                   var pi = $(this);
                                                   $(this).find('button').click(function(){
                                                        pi.fadeOut().remove();
                                                    })                                                   
                                               }
                                            });
                                        }
                                      } //end for  
                        }
                        
                            that.find('output .file-upload-preview').each(function(){
                               if(!$(this).hasClass('error')) $(this).fadeOut().remove(); 
                            });
                            that.find('output .progress-holder').fadeOut().remove();                             
                            that.pfcFilesGridAdmin(sett).load();
                        
                                         
                }, false);
    
    
           xhr.onerror = function(e){
               showAlert(e,{mtype:'err'});
           };
            
            xhr.open("post",that.find("input[name='action-add-action']").val());

            // Set appropriate headers
            xhr.send(formData);
       
       
        function addFile(file)
        {
            var preview;
        
                function createpreview(file)
                {
                    preview = $(that.find('.template').html());
                    if(file.name) preview.find('.name').html(file.name);
                    if(file.type) preview.find('.type').html(file.type);
                    if(file.size) preview.find('.filesize').html(parseInt(file.size / 1024, 10) + " kb");
                    
                                var str = file.name;
                                var pies = str.split('.');
                                var ext = pies[pies.length-1];
                    
                    
                        if (typeof FileReader !== "undefined" && (/image/i).test(file.type)) {
                                img = document.createElement("img");
                                $(img).attr('width',80).attr('height',80);
                                preview.find('.icon').html(img);
                                reader = new FileReader();
                                reader.onload = (function (theImg) {
                                                    return function (evt) {
                                                        theImg.src = evt.target.result;
                                                    };
                                                 }(img));

                                reader.readAsDataURL(file);
                         }
                         else preview.find('.icon').html(ext);
                    
                    that.find('output').find('.progress-holder').after(preview);
                }
        
            //create preview
            createpreview(file);
        
            //validate
            var val = validate(file);
        
            //add to form
            if(val)
            {
              formData.append('files[]',file,file.name);
                              
              return true; 
            }
            else
            {
                preview.addClass('error');
                preview.find('.error-container').html('Neplatná přípona souboru <button>OK</button>');
                preview.find('button').click(function(){
                    preview.fadeOut().remove();
                })
                return false;
            }    
               
            }
            
    }
    
        function validate(file)
        {
            var str = that.find("input[name='allowed']").val();
            
            if(str=='allfiles') return true;
            
            var allowed = str.split(",");
            
            str = file.name;
            var pies = str.split('.');
            var ext = pies[pies.length-1];
            var valid = false;
            for(var i=0;i<allowed.length;i++)
            {
                if(allowed[i]==ext) valid = true;
            }
            
            return valid;
        }            
        
        
        
} //end filesgrid admin

}( jQuery ));

/**
 * 
 * PFC GRID ADMIN
 */
(function ( $ ) {    
    
$.fn.pfcGridAdmin = function(options) {

    var that = this;
    var listUrl = this.find("input[name='list']").val();

    var settings = {
        add_title:'Nový',
        add_onforminit:function(form){},
        edit_title:'Upravit',
        edit_onforminit:function(form){},        
        delete_text:'Opravdu smazat?',
        loadParams:{},
        sortable:true,
        add_form_id:'#grid-admin-add-form',
        edit_form_id:"#grid-admin-edit-form",
        btnAdd: function() {
            that.find('.action-add').click(function(){
                $.uiDialog(this.href,{
                    title:settings.add_title,
                    onload:function() {
                      $('#'+settings.add_form_id).pfcAjaxForm({
                            onforminit:settings.add_onforminit,
                            succ:function(json,form) {
                                showAlert(json.succMsg);
                                load();
                                loadEditAction(json.id);
                            }                    
                      })
                    }
                })
                return false;
            });            
        } 
    };
    
    $.extend(settings,options);
    
    return {
      init:function() {                                     
          
            load();

            //static action-add
            settings.btnAdd();      
      },  
      load:load
    };
    
    
    function load()
    {
            $.get(listUrl,settings.loadParams,function(html){
                that.find('.list-holder').fadeOut().html(html).fadeIn();
                bindListActions();
            });    
    }
    
    function loadFromPaging(url,href)
    {
            $.get(url,{},function(html){
                $(href).parent().fadeOut().replaceWith(html).fadeIn();
                bindListActions();
            });    
    }
    
    function loadEditAction(id)
    {
            $.get(that.find("input[name='edit-action']").val(),{id:id},function(html){
                $('#'+settings.add_form_id).fadeOut().replaceWith(html).fadeIn();
                bindEditActions();
            });        
    }
    
    function bindListActions()
    {
        that.find(".grid-list-item").removeClass('odd');
        that.find(".grid-list-item:odd").addClass('odd');
    
                //paging next href
            that.find('.paging-holder a, .paging-previos-holder a').unbind('click').click(function(){
                loadFromPaging(this.href,this);
                return false;
            });

                //action-edit
            that.find('.action-edit').unbind('click').click(function(){
                $.uiDialog(this.href,{
                    title:settings.edit_title,
                    onload:function() {
                        bindEditActions();
                    }
                });
                
                return false;
            });

        //action-delete
            that.find('.action-delete').unbind('click').click(function(){
                var action = this.href;
                showConfirm(settings.delete_text,function(){
                    $.get(action,{},function(json){
                        if(json.succ=='yes')
                        {
                            showAlert(json.succMsg);
                            load();
                        }
                        else showAlert(json.errors[0].mess,{mtype:'err'});
                    })
                });
               return false; 
            });
            
        
        //if rank sortable    
        if(settings.sortable)
        {   
		that.find(".grid-admin-list").sortable({
                    
                    update : function (event, ui) {

                                var id = ui.item.attr('id');
                                var neib = ui.item.prev().attr('id');
                                
                                var RE = /^gridadminlistitem/;
                                if( ! RE.test(neib) ) {
                                    neib = 'gridadminlistitem_0';
                                }
                                
                                var p;
                                /*
                                if(settings.loadParams.ownerid) {
                                    p = {
                                        id: id.replace('gridadminlistitem_',''),
                                        neib: neib.replace('gridadminlistitem_',''),                                        
                                        ownerid:settings.loadParams.ownerid
                                    };
                                }
                                
                                else if(settings.loadParams.parentid) {
                                    p = {
                                        id: id.replace('gridadminlistitem_',''),
                                        neib: neib.replace('gridadminlistitem_',''),                                        
                                        parentid:settings.loadParams.parentid
                                    };                                    
                                }
                                
                                else {
                                    */
                                    p = {
                                        id: id.replace('gridadminlistitem_',''),
                                        neib: neib.replace('gridadminlistitem_','')
                                    };
                                //}
                                
                                $.get(that.find("input[name='action-sort-action']").val(),
                                    p, function(result) {
                                        if( result.succ == 'yes' )
                                        {
                                            that.find(".grid-list-item").removeClass('odd');
                                            that.find(".grid-list-item:odd").addClass('odd');
                                            showAlert(result.succMsg);
                                            
                                        }
                                        else    
                                        {
                                            showAlert(json.errors[0].mess,{mtype:'err'});
                                            load();
                                        }
                                        
                                    },'json'
                                 );

                           }
                });
		that.find(".grid-admin-list").disableSelection();
            }
    }
    
    function bindEditActions()
    {
                      $('#'+settings.edit_form_id).pfcAjaxForm({
                            onforminit:settings.edit_onforminit,
                            succ:function(json,form) {
                                showAlert(json.succMsg);
                                load();
                            }                    
                      })    
    }
    
    
}

}( jQuery ));









/**
 * 
 * PFC FILE ADMIN
 */
(function ( $ ) {
$.fn.pfcFileAdmin = function(options) {

    var that = this;
    
    var settings = {
        succ:function() {
            
        },
        succdelete: function() {
            
        }
    };
    
    $.extend(settings,options);
    
    loadFile();
    
    var fu = that.find("input[name='upload']").first();
    
    fu.change(function() {     
            upload(this.files);
    });
    
    fu.hide();
    
    that.find('a.button').unbind('click').click(function(){
        fu.trigger('click');
        return false;
    })
    
    function upload(files)
    {
        var formData = new FormData();
        var xhr = new XMLHttpRequest();
        
       if( addFile(files[0]) ) {
           
           xhr.onerror = function(e){
               showAlert(e,{mtype:'err'});
           };
            
            xhr.open("post",that.find("input[name='file-action-url']").val());

            // Set appropriate headers
            xhr.send(formData);
       }
       
        function addFile(file)
        {
            var preview;
        
                function createpreview(file)
                {
                    preview = $(that.find('.template').html());
                    preview.find('.name').html(file.name);
                    preview.find('.type').html(file.type);
                    preview.find('.filesize').html(parseInt(file.size / 1024, 10) + " kb");
                    
                                var str = file.name;
                                var pies = str.split('.');
                                var ext = pies[pies.length-1];
                    
                    
                        if (typeof FileReader !== "undefined" && (/image/i).test(file.type)) {
                                img = document.createElement("img");
                                $(img).attr('width',80).attr('height',80);
                                preview.find('.icon').html(img);
                                reader = new FileReader();
                                reader.onload = (function (theImg) {
                                                    return function (evt) {
                                                        theImg.src = evt.target.result;
                                                    };
                                                 }(img));

                                reader.readAsDataURL(file);
                         }
                         else preview.find('.icon').html(ext);
                    
                    that.find('output').html(preview);
                }
        
            //create preview
            createpreview(file);
        
            //validate
            var val = validate(file);
        
            //add to form
            if(val)
            {
               formData.append('file',file,file.name);
               
                // Update progress bar
                xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                                preview.find('.progress-bar').css('width', (evt.loaded / evt.total) * 100 + "%");
                        }
                        else {
                            // No data to calculate on
                        }
                }, false);

                // File uploaded
                xhr.addEventListener("load", function () {
                        if (xhr.responseText != "done") {                            
                            preview.find('.progress-bar-container').replaceWith('<div>'+xhr.responseText+' <button>OK</button></div>');
                            preview.find('button').click(function(){
                                preview.fadeOut().remove();
                            })
                            
                        }
                        else {
                            
                            showAlert('Soubor '+file.name+' byl nahrán na server')
                            preview.fadeOut();
                            loadFile(settings.succ);
                        
                            
                        }                    
                }, false);               
              return true; 
            }
            else
            {
                preview.find('.progress-bar-container').replaceWith('<div>Neplatná přípona souboru <button>OK</button></div>');
                preview.find('button').click(function(){
                    preview.fadeOut().remove();
                })
                return false;
            }    
               
            }
            
    }
    
        function validate(file)
        {
            var str = that.find("input[name='allowed']").val();
            var allowed = str.split(",");
            
            str = file.name;
            var pies = str.split('.');
            var ext = pies[pies.length-1];
            var valid = false;
            for(var i=0;i<allowed.length;i++)
            {
                if(allowed[i]==ext) valid = true;
            }
            
            return valid;
        }    
    
    function activateFileListeners()
    {
            //delete button
            that.find(".action-delete").unbind('click').click(function(){
                   var that = this; 
                   showConfirm('Opravdu smazat soubor?',function(){
                                    $.get( that.href, {}, function(res)
                                        {
                                            if( res == 'done' )                                                                                                         
                                            {
                                                showAlert("Soubor byl odstraněn.");
                                                loadFile(); 
                                                settings.succdelete();
                                             }
                                        else 
                                             showAlert('Error - zopakujte prosím akci',{mtype:'err'});    
                                      });                         
                             }); 

                return false;
            });
        }

function loadFile(callback)
{
    $.get(that.find("input[name='file-ajax-url']").val(),{},function(img){    
        that.find(".preview").fadeOut().html(img).fadeIn();
        activateFileListeners();
        if(callback!=undefined) callback();
    });
}
    
}
}( jQuery ));
