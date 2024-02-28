


(function ( $ ) {
$.fn.autofillscreengallery = function( options ) {
        
        var that = this;
        
        var is_chrome = window.chrome;
        
        var ani = 0;
        
        var settings = $.extend({            
                paths:[],
                GALLERY_SPEED:750
            }, options );
            
       var paths = settings.paths;     
            
    function link_more()
    {
                that.find(".img-footer a").unbind('click').click(function(){
                    var link = $(this).attr('href');
                    var test = link.substring(0,4);
                    
                    if(test == 'http') window.open(link);
                    else if( test == 'www.') window.open('http://'+link);
                    else window.open($("#napistenamurl").val()+link);
                    
                    return false;
                });
    }


if(paths.length>0)    
    {    

        var iwidth = parseInt(that.find(".img-third").first().css('width'));    
        
        function galleryHeight()
        {
            var iwidth = parseInt(that.find(".img-third").first().css('width'));    
            
                    that.find("div .img-holder").css('height',(iwidth-50)+"px");
                    that.find(".autofillscreen-gallery-holder").css('height',(iwidth+45)+"px");         
                
        }     
    
    
    
    var index = 0;        
    
    if($(window).width()>2000)
        that.find(".autofillscreen-gallery-holder").css('width',"1900px");
    else if($(window).width()>1100)
        that.find(".autofillscreen-gallery-holder").css('width',($(window).width())+"px");
    else    that.find(".autofillscreen-gallery-holder").css('width',"1100px");
        
        $(window).resize(function() {
            that.find(".autofillscreen-gallery-holder").css('width',($(window).width()>1000?$(window).width():'1100')+"px");
        });

               
         

galleryHeight();
$( window ).resize(function(){
    galleryHeight();
});

                var index2 = index-1;
                if( index2 == -1 ) index2 = paths.length-1;
                var index1 = index2-1;
                if( index1 == -1 ) index1 = paths.length-1;
                var index0 = index1-1;
                if( index0 == -1 ) index0 = paths.length-1;                

                var index4 = index+1;
                if( paths[index4] == undefined ) index4 = 0;
                var index5 = index4+1;
                if( paths[index5] == undefined ) index5 = 0;
                var index6 = index5+1;
                if( paths[index6] == undefined ) index6 = 0;
                
                
        
        
                 that.find(".img-holder").eq(0).css('background-image','url('+paths[index0].small+')').attr('rel',index0);
                 that.find(".img-title").eq(0).html(paths[index0].title);                 
                 that.find(".img-footer").eq(0).html('<a href="'+paths[index0].link+'">chcete vědět více</a>');   
                 that.find(".img-holder").eq(1).css('background-image','url('+paths[index1].small+')').attr('rel',index1);
                 that.find(".img-title").eq(1).html(paths[index1].title);
                 that.find(".img-footer").eq(1).html('<a href="'+paths[index1].link+'">chcete vědět více</a>');   
                 that.find(".img-holder").eq(2).css('background-image','url('+paths[index2].small+')').attr('rel',index2);
                 that.find(".img-title").eq(2).html(paths[index2].title);
                 that.find(".img-footer").eq(2).html('<a href="'+paths[index2].link+'">chcete vědět více</a>');   
                 that.find(".img-holder").eq(3).css('background-image','url('+paths[index].small+')').attr('rel',index);
                 that.find(".img-title").eq(3).html(paths[index].title);
                 that.find(".img-footer").eq(3).html('<a href="'+paths[index].link+'">chcete vědět více</a>');   
                 that.find(".img-holder").eq(4).css('background-image','url('+paths[index4].small+')').attr('rel',index4);
                 that.find(".img-title").eq(4).html(paths[index4].title);                 
                 that.find(".img-footer").eq(4).html('<a href="'+paths[index4].link+'">chcete vědět více</a>');   
                 that.find(".img-holder").eq(5).css('background-image','url('+paths[index5].small+')').attr('rel',index5);
                 that.find(".img-title").eq(5).html(paths[index5].title);
                 that.find(".img-footer").eq(5).html('<a href="'+paths[index5].link+'">chcete vědět více</a>');   
                 that.find(".img-holder").eq(6).css('background-image','url('+paths[index6].small+')').attr('rel',index6);
                 that.find(".img-title").eq(6).html(paths[index6].title);
                 that.find(".img-footer").eq(6).html('<a href="'+paths[index6].link+'">chcete vědět více</a>');   

            
                that.find(".img-footer, .autofillscreen-gallery-info, .autofillscreen-gallery-nav-holder").mouseover(function(){
                    that.find("div .img-holder").html('');                    
                });            

                link_more();
            
                that.find(".img-title").click(function(){
                    lightGallery(parseInt($(this).parent().find('.img-holder').first().attr('rel')));
                    return false;
                }).mouseover(function(){
                    that.find("div .img-holder").html('');                    
                });            
            
            

       that.find(".goright").click(function(e){                  
            that.find(".snav").css('color','gray').unbind('click').click(function(){               
               return false;               
           });   
           
           animater();    
           
           return false;
        });
        
        that.find(".goleft").click(function(e){
                              
            that.find(".snav").css('color','gray').unbind('click').click(function(e){
               return false;
           });   
           
           animatel();
           
           return false; 
        });
            
        that.find("div .img-holder").mouseover(function(){
            
            var self = $(this);
            var over = $('<a href="#kontakt" class="img-overlay"></a>');
                                                
            that.find("div .img-holder").html('');                        
            
            if(!is_chrome) 
            self.html(over);

            if(iwidth>360)                 
                    that.find(".img-overlay").css('height',(iwidth-50)+"px");            
            
                   
            that.find(".img-overlay").unbind('click').click(function(){
                    
                    lightGallery(parseInt($(this).parent().attr('rel')));
                    return false;
                })
             .mouseout(function(){
                 that.find("div .img-holder").html('');
             })
            
            
        }).click(function(){
                    lightGallery(parseInt($(this).attr('rel')));
                    return false;
                });
        
        
        
        $('.autofillscreen-outtop,.autofillscreen-outbot').mouseover(function(){
             that.find("div .img-holder").html('');                        
        });
   
    
    
    
    } //end paths.length>0
    else {
        that.find(".autofillscreen-gallery-nav-holder").hide();
    }
    
    
    
    function animater() {

                index--;
                if( paths[index] == undefined  ) index = paths.length-1;

                var index2 = index-1;
                if( paths[index2] == undefined ) index2 = paths.length-1;
                var index1 = index2-1;
                if( paths[index1] == undefined ) index1 = paths.length-1;
                var index0 = index1-1;
                if( paths[index0] == undefined ) index0 = paths.length-1;                

                var index4 = index+1;
                if( paths[index4] == undefined ) index4 = 0;
                var index5 = index4+1;
                if( paths[index5] == undefined ) index5 = 0;
                var index6 = index5+1;
                if( paths[index6] == undefined ) index6 = 0; 
           
                 
                that.find('.goright').fadeOut('fast',function(){
                    $(this).delay(settings.GALLERY_SPEED-200).fadeIn('fast').unbind('click').click(function(){                       
                                   that.find(".snav").css('color','gray').unbind('click').click(function(){
                                        return false;
                                    });                          
                       animater();                       
                       return false;
                   });       
                });
                   
                 that.find('.goleft').fadeOut('fast',function(){
                    $(this).delay(settings.GALLERY_SPEED-200).fadeIn('fast').unbind('click').click(function(){                       
                 
                                   that.find(".snav").css('color','gray').unbind('click').click(function(){
                                        return false;
                                    });                          
                       animatel();                       
                       return false;
                   });   
                 });
                
                
                
                
           
           that.find(".autofillscreen-gallery-holder div").animate({

            left: "+=25%"
            
           }, settings.GALLERY_SPEED , function() {
   
                
              
    
                that.find(".img-six").remove();
                that.find(".img-fifth").first().addClass('img-six').removeClass('img-fifth'); 
                that.find(".img-four").first().addClass('img-fifth').removeClass('img-four');
                that.find(".img-third").first().addClass('img-four').removeClass('img-third');
                that.find(".img-sec").first().addClass('img-third').removeClass('img-sec');
                that.find(".img-first").first().addClass('img-sec').removeClass('img-first');
                that.find(".img-zero").first().addClass('img-first').removeClass('img-zero');
          
    
    
                that.find('.autofillscreen-gallery-holder').prepend('<div class="img-zero"><div class="img-holder"></div><div class="img-title"></div><div class="img-footer"></div></div>');
    
                galleryHeight()
    
                that.find(".img-zero .img-footer").mouseover(function(){
                    that.find("div .img-holder").html('');                    
                }); 
                
                that.find(".img-zero .img-title").click(function(){
                    lightGallery(parseInt($(this).parent().find('.img-holder').first().attr('rel')));
                    return false;
                }).mouseover(function(){
                    that.find("div .img-holder").html('');                    
                });                    
                
                
                that.find(".img-zero .img-holder").mouseover(function(){
                            var self = $(this);
                            var over = $('<a href="#" class="img-overlay"></a>').mouseout(function(){
                                            that.find("div .img-holder").html('');
                                        }).click(function(){
                                            lightGallery(parseInt($(this).parent().attr('rel')));
                                            return false;
                                        });
                                
                            that.find("div .img-holder").html('');    
                            
                            if(!is_chrome) 
                            self.html(over);                            
                            
                            if(iwidth>360)                 
                                that.find(".img-overlay").css('height',(iwidth-50)+"px"); 
                            

                        }).click(function(){
                                            lightGallery(parseInt($(this).attr('rel')));
                                            return false;
                                        });      ;
                        
                        
                        
                 that.find(".img-holder").eq(0).css('background-image','url('+paths[index0].small+')').attr('rel',index0);
                 that.find(".img-title").eq(0).html(paths[index0].title);                                  
                 that.find(".img-footer").eq(0).html('<a href="'+paths[index0].link+'">chcete vědět více</a>');                                                  
                 that.find(".img-holder").eq(1).css('background-image','url('+paths[index1].small+')').attr('rel',index1);
                 that.find(".img-title").eq(1).html(paths[index1].title);
                 that.find(".img-footer").eq(1).html('<a href="'+paths[index1].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(2).css('background-image','url('+paths[index2].small+')').attr('rel',index2);
                 that.find(".img-title").eq(2).html(paths[index2].title);
                 that.find(".img-footer").eq(2).html('<a href="'+paths[index2].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(3).css('background-image','url('+paths[index].small+')').attr('rel',index);
                 that.find(".img-title").eq(3).html(paths[index].title);
                 that.find(".img-footer").eq(3).html('<a href="'+paths[index].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(4).css('background-image','url('+paths[index4].small+')').attr('rel',index4);
                 that.find(".img-title").eq(4).html(paths[index4].title);    
                 that.find(".img-footer").eq(4).html('<a href="'+paths[index4].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(5).css('background-image','url('+paths[index5].small+')').attr('rel',index5);
                 that.find(".img-title").eq(5).html(paths[index5].title);
                 that.find(".img-footer").eq(5).html('<a href="'+paths[index5].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(6).css('background-image','url('+paths[index6].small+')').attr('rel',index6);
                 that.find(".img-title").eq(6).html(paths[index6].title);    
                 that.find(".img-footer").eq(6).html('<a href="'+paths[index6].link+'">chcete vědět více</a>');                 
                 
                 link_more();
            });      
    }
    
    
    function animatel() {
    
           index++;
           
        
           if( paths[index] == undefined  ) index = 0;
         
                var index2 = index-1;
                if( paths[index2] == undefined  ) index2 = paths.length-1;
                var index1 = index2-1;
                if( paths[index1] == undefined  ) index1 = paths.length-1;
                var index0 = index1-1;
                if( paths[index0] == undefined ) index0 = paths.length-1;                

                var index4 = index+1;
                if( paths[index4] == undefined ) index4 = 0;
                var index5 = index4+1;
                if( paths[index5] == undefined ) index5 = 0;
                var index6 = index5+1;
                if( paths[index6] == undefined ) index6 = 0;
               
                that.find('.goright').fadeOut('fast',function(){
                    $(this).delay(settings.GALLERY_SPEED-200).fadeIn('fast').unbind('click').click(function(){                       
                                   that.find(".snav").css('color','gray').unbind('click').click(function(){
                                        return false;
                                    });                          
                       animater();                       
                       return false;
                   });       
                });
                   
                 that.find('.goleft').fadeOut('fast',function(){
                    $(this).delay(settings.GALLERY_SPEED-200).fadeIn('fast').unbind('click').click(function(){                       
                 
                                   that.find(".snav").css('color','gray').unbind('click').click(function(){
                                        return false;
                                    });                          
                       animatel();                       
                       return false;
                   });   
                 });     
               
           that.find(".autofillscreen-gallery-holder div").animate({

            left: "-=25%",

            }, settings.GALLERY_SPEED , function() {
            
                
                
                that.find(".img-zero").remove();
                that.find(".img-first").first().addClass('img-zero').removeClass('img-first');
                that.find(".img-sec").first().addClass('img-first').removeClass('img-sec');
                that.find(".img-third").first().addClass('img-sec').removeClass('img-third');
                that.find(".img-four").first().addClass('img-third').removeClass('img-four');
                that.find(".img-fifth").first().addClass('img-four').removeClass('img-fifth');                                                                 
                that.find(".img-six").first().addClass('img-fifth').removeClass('img-six');
          
    
    
                that.find(".img-fifth").after('<div class="img-six"><div class="img-holder"></div><div class="img-title"></div><div class="img-footer"></div></div>');
                
                galleryHeight();
                

                that.find(".img-six .img-footer").mouseover(function(){
                    that.find("div .img-holder").html('');                    
                });            
            
            
                that.find(".img-six .img-title").click(function(){
                    lightGallery(parseInt($(this).parent().find('.img-holder').first().attr('rel')));
                    return false;
                }).mouseover(function(){
                    that.find("div .img-holder").html('');                    
                }); 
                
                
                that.find('.img-six .img-holder').mouseover(function(){
                            var self = $(this);
                            var over = $('<a href="#" class="img-overlay"></a>').mouseout(function(){
                                    that.find("div .img-holder").html('');
                                }).click(function(){
                                        lightGallery(parseInt($(this).parent().attr('rel')));
                                        return false;
                                    });
                                
                                that.find("div .img-holder").html('');  
                                
                               if(!is_chrome) 
                                self.html(over);
                                
                            if(iwidth>360)                 
                                that.find(".img-overlay").css('height',(iwidth-50)+"px"); 
                                
                                

                        }).click(function(){
                                        lightGallery(parseInt($(this).attr('rel')));
                                        return false;
                                    });
                                
                 that.find(".img-holder").eq(0).css('background-image','url('+paths[index0].small+')').attr('rel',index0);
                 that.find(".img-title").eq(0).html(paths[index0].title);                 
                 that.find(".img-footer").eq(0).html('<a href="'+paths[index0].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(1).css('background-image','url('+paths[index1].small+')').attr('rel',index1);
                 that.find(".img-title").eq(1).html(paths[index1].title);
                 that.find(".img-footer").eq(1).html('<a href="'+paths[index1].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(2).css('background-image','url('+paths[index2].small+')').attr('rel',index2);
                 that.find(".img-footer").eq(2).html('<a href="'+paths[index2].link+'">chcete vědět více</a>');                 
                 that.find(".img-title").eq(2).html(paths[index2].title);
                 that.find(".img-holder").eq(3).css('background-image','url('+paths[index].small+')').attr('rel',index);
                 that.find(".img-title").eq(3).html(paths[index].title);
                 that.find(".img-footer").eq(3).html('<a href="'+paths[index].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(4).css('background-image','url('+paths[index4].small+')').attr('rel',index4);
                 that.find(".img-title").eq(4).html(paths[index4].title);                 
                 that.find(".img-footer").eq(4).html('<a href="'+paths[index4].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(5).css('background-image','url('+paths[index5].small+')').attr('rel',index5);
                 that.find(".img-title").eq(5).html(paths[index5].title);
                 that.find(".img-footer").eq(5).html('<a href="'+paths[index5].link+'">chcete vědět více</a>');                 
                 that.find(".img-holder").eq(6).css('background-image','url('+paths[index6].small+')').attr('rel',index6);
                 that.find(".img-title").eq(6).html(paths[index6].title);                 
                 that.find(".img-footer").eq(6).html('<a href="'+paths[index6].link+'">chcete vědět více</a>');                 
                 
                 link_more();
            });      
    }



function lightGallery(imgindex)
    {
      $("body").append('<div id="lightGallery-overlay"></div>');
      $("body").append('<div id="lightGallery-holder" class="hidden"><div id="lightGallery-header"><a href="#" id="lightGallery-close"><img src="/assets/layouts/default/images/lightgallery/gallery_det_close.png"></a><span></span></div><div id="lightGallery-img"><img src=""><a id="lightGallery-goleft" href="#"></a><a id="lightGallery-goright" href="#"></a></div><div id="lightGallery-footer"></div></div>');
    
      $("#lightGallery-img img").attr('src',paths[imgindex].big);
      $("#lightGallery-header span").html(paths[imgindex].title);
      $("#lightGallery-footer").html($('<a href="'+paths[imgindex].link+'">chcete vědět více</a>').unbind('click').click(function(){
                    var link = $(this).attr('href');
                    var test = link.substring(0,4);
                    
                    if(test == 'http') window.open(link);
                    else if( test == 'www.') window.open('http://'+link);
                    else window.open($("#napistenamurl").val()+link);
                    
                    return false;
                }));
      
       var wwidth = $(window).width();  
       var wscrolltop = $(window).scrollTop();
      
       var pwidth = parseInt($('#lightGallery-holder').css('width'));               
       var pleft, pstarttop = (wscrolltop+15)+"px";
       
       if(wwidth>pwidth)
       {           
           //center
           pleft = ""+(wwidth/2-pwidth/2)+"px";           
       }
       else 
       {          
           pleft = "0px";
       }
      
      $('#lightGallery-holder').css({'top':pstarttop,'left':pleft}).show();
      
      
      $("#lightGallery-overlay").css({width:(($(document).width())+"px"),height:(($(document).height())+"px")})
    
      $("#lightGallery-close").click(function(){
        $("#lightGallery-holder").remove();      
        $("#lightGallery-overlay").remove();
        return false;
      }); 
    
      $("#lightGallery-goleft").click(function(){
        var newindex = (imgindex-1)>-1 ? (imgindex-1) : (paths.length-1);        
        $("#lightGallery-header span").html(paths[newindex].title);
        $("#lightGallery-footer").html($('<a href="'+paths[newindex].link+'">chcete vědět více</a>').unbind('click').click(function(){
                    var link = $(this).attr('href');
                    var test = link.substring(0,4);
                    
                    if(test == 'http') window.open(link);
                    else if( test == 'www.') window.open('http://'+link);
                    else window.open($("#napistenamurl").val()+link);
                    
                    return false;
                }));
        $("#lightGallery-img img").attr('src',paths[newindex].big);    
        imgindex = newindex;
        return false;
      }); 

      $("#lightGallery-goright").click(function(){
        var newindex = (imgindex+1) < paths.length ? (imgindex+1) : 0;        
        $("#lightGallery-header span").html(paths[newindex].title);
        $("#lightGallery-footer").html($('<a href="'+paths[newindex].link+'">chcete vědět více</a>').unbind('click').click(function(){
                    var link = $(this).attr('href');
                    var test = link.substring(0,4);
                    
                    if(test == 'http') window.open(link);
                    else if( test == 'www.') window.open('http://'+link);
                    else window.open($("#napistenamurl").val()+link);
                    
                    return false;
                }));
        $("#lightGallery-img img").attr('src',paths[newindex].big);    
        imgindex = newindex;
        return false;
      });       
      
      
            
    } //end function lightGallery
    
    

    
};//end $.fn.autofillscreengallery

}( jQuery ));