/*!
 * Tiny Scrollbar 1.66
 * http://www.baijs.nl/tinyscrollbar/
 *
 * Copyright 2010, Maarten Baijs
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/gpl-2.0.php
 *
 * Date: 13 / 11 / 2011
 * Depends on library: jQuery
 * 
 */

(function($){
	$.tiny = $.tiny || { };
	
	$.tiny.scrollbar = {
		options: {	
			axis: 'y', // vertical or horizontal scrollbar? ( x || y ).
			wheel: 40,  //how many pixels must the mouswheel scroll at a time.
			scroll: true, //enable or disable the mousewheel;
			size: 'auto', //set the size of the scrollbar to auto or a fixed number.
			sizethumb: 'auto' //set the size of the thumb to auto or a fixed number.
		}
	};	
	
	$.fn.tinyscrollbar = function(options) { 
		var options = $.extend({}, $.tiny.scrollbar.options, options); 		
		this.each(function(){ $(this).data('tsb', new Scrollbar($(this), options)); });
		return this;
	};
	$.fn.tinyscrollbar_update = function(sScroll) { return $(this).data('tsb').update(sScroll); };
	
	function Scrollbar(root, options){
		var oSelf = this;
		var oWrapper = root;
		var oViewport = { obj: $('.viewport', root) };
		var oContent = { obj: $('.overview', root) };
		var oScrollbar = { obj: $('.scrollbar', root) };
		var oTrack = { obj: $('.track', oScrollbar.obj) };
		var oThumb = { obj: $('.thumb', oScrollbar.obj) };
		var sAxis = options.axis == 'x', sDirection = sAxis ? 'left' : 'top', sSize = sAxis ? 'Width' : 'Height';
		var iScroll, iPosition = { start: 0, now: 0 }, iMouse = {};

		function initialize() {	
			oSelf.update();
			setEvents();
			return oSelf;
		}
		this.update = function(sScroll){
			oViewport[options.axis] = oViewport.obj[0]['offset'+ sSize];
			oContent[options.axis] = oContent.obj[0]['scroll'+ sSize];
			oContent.ratio = oViewport[options.axis] / oContent[options.axis];
			oScrollbar.obj.toggleClass('disable', oContent.ratio >= 1);
			oTrack[options.axis] = options.size == 'auto' ? oViewport[options.axis] : options.size;
			oThumb[options.axis] = Math.min(oTrack[options.axis], Math.max(0, ( options.sizethumb == 'auto' ? (oTrack[options.axis] * oContent.ratio) : options.sizethumb )));
			oScrollbar.ratio = options.sizethumb == 'auto' ? (oContent[options.axis] / oTrack[options.axis]) : (oContent[options.axis] - oViewport[options.axis]) / (oTrack[options.axis] - oThumb[options.axis]);
			iScroll = (sScroll == 'relative' && oContent.ratio <= 1) ? Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll)) : 0;
			iScroll = (sScroll == 'bottom' && oContent.ratio <= 1) ? (oContent[options.axis] - oViewport[options.axis]) : isNaN(parseInt(sScroll)) ? iScroll : parseInt(sScroll);
			setSize();
		};
		function setSize(){
			oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
			oContent.obj.css(sDirection, -iScroll);
			iMouse['start'] = oThumb.obj.offset()[sDirection];
			var sCssSize = sSize.toLowerCase(); 
			oScrollbar.obj.css(sCssSize, oTrack[options.axis]);
			oTrack.obj.css(sCssSize, oTrack[options.axis]);
			oThumb.obj.css(sCssSize, oThumb[options.axis]);		
		};		
		function setEvents(){
			oThumb.obj.bind('mousedown', start);
			oThumb.obj[0].ontouchstart = function(oEvent){
				oEvent.preventDefault();
				oThumb.obj.unbind('mousedown');
				start(oEvent.touches[0]);
				return false;
			};	
			oTrack.obj.bind('mouseup', drag);
			if(options.scroll && this.addEventListener){
				oWrapper[0].addEventListener('DOMMouseScroll', wheel, false);
				oWrapper[0].addEventListener('mousewheel', wheel, false );
			}
			else if(options.scroll){oWrapper[0].onmousewheel = wheel;}
		};
		function start(oEvent){
			iMouse.start = sAxis ? oEvent.pageX : oEvent.pageY;
			var oThumbDir = parseInt(oThumb.obj.css(sDirection));
			iPosition.start = oThumbDir == 'auto' ? 0 : oThumbDir;
			$(document).bind('mousemove', drag);
			document.ontouchmove = function(oEvent){
				$(document).unbind('mousemove');
				drag(oEvent.touches[0]);
			};
			$(document).bind('mouseup', end);
			oThumb.obj.bind('mouseup', end);
			oThumb.obj[0].ontouchend = document.ontouchend = function(oEvent){
				$(document).unbind('mouseup');
				oThumb.obj.unbind('mouseup');
				end(oEvent.touches[0]);
			};
			return false;
		};		
		function wheel(oEvent){
			if(!(oContent.ratio >= 1)){
				var oEvent = oEvent || window.event;
				var iDelta = oEvent.wheelDelta ? oEvent.wheelDelta/120 : -oEvent.detail/3;
				iScroll -= iDelta * options.wheel;
				iScroll = Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll));
				oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
				oContent.obj.css(sDirection, -iScroll);
				
				oEvent = $.event.fix(oEvent);
				oEvent.preventDefault();
			};
		};
		function end(oEvent){
			$(document).unbind('mousemove', drag);
			$(document).unbind('mouseup', end);
			oThumb.obj.unbind('mouseup', end);
			document.ontouchmove = oThumb.obj[0].ontouchend = document.ontouchend = null;
			return false;
		};
		function drag(oEvent){
			if(!(oContent.ratio >= 1)){
				iPosition.now = Math.min((oTrack[options.axis] - oThumb[options.axis]), Math.max(0, (iPosition.start + ((sAxis ? oEvent.pageX : oEvent.pageY) - iMouse.start))));
				iScroll = iPosition.now * oScrollbar.ratio;
				oContent.obj.css(sDirection, -iScroll);
				oThumb.obj.css(sDirection, iPosition.now);
			}
			return false;
		};
		
		return initialize();
	};
})(jQuery);

/* Start cs code */

updateScroll = function() {
    jQuery('.widget-liquid-right').tinyscrollbar_update('relative');
}



scrollSetUp = function($){
    $('#widgets-right').addClass('overview').wrap('<div class="viewport" />');
    $('.viewport').height($(window).height() - 60);
    $('.widget-liquid-right').height($(window).height()).prepend('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>').tinyscrollbar();
    $(window).resize(function() {
      $('.widget-liquid-right').height($(window).height());
      $('.viewport').height($(window).height() - 60);
      $('.widget-liquid-right').tinyscrollbar_update('relative');
    });
    $('#widgets-right').resize(function(){
        $('.widget-liquid-right').tinyscrollbar_update('relative');
    });
    
    $('.widget-liquid-right').click(function(){
        setTimeout("updateScroll()",300);
    });
    $('.widget-liquid-right').hover(function(){
        $('.scrollbar').fadeIn();
    }, function(){
        $('.scrollbar').fadeOut();
    });
}

addCSControls = function($){
    $('#cs-title-options').detach().prependTo('#widgets-right').show();
}

showCreateSidebar = function($){
    $('.create-sidebar-button').click(function(){
       var ajaxdata = {
           action: 'cs-wpnonce',
           nonce_action: 'cs-create-sidebar',
           nonce_nonce: $('#_nonce_nonce').val()
       };
       $('#cs-options').find('.ajax-feedback').css('visibility', 'visible');
       if($('#new-sidebar-holder').length == 0){ //If there is no form displayed
          $.post(ajaxurl, ajaxdata, function(response){
               $('#_nonce_nonce').val(response.nonce_nonce);
               $('#_create_nonce').val(response.nonce);
               var holder = $('#cs-new-sidebar').clone(true, true)
                    .attr('id', 'new-sidebar-holder')
                    .hide()
                    .insertAfter('#cs-title-options');
               holder.find('.widgets-sortables').attr('id', 'new-sidebar');
               holder.find('.sidebar-form').attr('id', 'new-sidebar-form');
               holder.find('.sidebar_name').attr('id', 'sidebar_name');
               holder.find('.sidebar_description').attr('id', 'sidebar_description');
               holder.find('.cs-create-sidebar').attr('id', 'cs-create-sidebar');
               holder.slideDown();
               var sbname = holder.children(".sidebar-name");
               sbname.click(function(){
                   var h=$(this).siblings(".widgets-sortables"),g=$(this).parent();if(!g.hasClass("closed")){h.sortable("disable");g.addClass("closed")}else{g.removeClass("closed");h.sortable("enable").sortable("refresh")}
               });
                    
               setCreateSidebar($);
               $('#cs-options').find('.ajax-feedback').css('visibility', 'hidden');
           }, 'json');
       }
       else
        $('#cs-options').find('.ajax-feedback').css('visibility', 'hidden');
       
       return false;
    });
}

setCreateSidebar = function($){
   $('#cs-create-sidebar').click(function(){
      var ajaxdata = {
           action: 'cs-create-sidebar',
           nonce: $('#_create_nonce').val(),
           sidebar_name: $('#sidebar_name').val(),
           sidebar_description: $('#sidebar_description').val()
       };
       $('#new-sidebar-form').find('.ajax-feedback').css('visibility', 'visible');
       $.post(ajaxurl, ajaxdata, function(response){
           if(response.success){
               var holder = $('#new-sidebar-holder');
               holder.removeAttr('id')
                    .find('.sidebar-name h3').html(response.name + '<span><img src="http://local.wp33/wp-admin/images/wpspin_dark.gif" class="ajax-feedback" title="" alt=""></span>');
               holder.find('#new-sidebar').fadeOut(function(){
                   holder.find('#new-sidebar').html('<p class="sidebar-description description">' + response.description + '</p>')
                                    .attr('id', response.id)
                                    .fadeIn();
               });
               holder = $('#' + response.id);
               reSort(holder, $);
               //holder.find('.widgets-sortables').droppable().sortable();
               //$('.widget').draggable('option', 'connectToSortable', 'div.widgets-sortables').draggable("enable");
           }
               showMessage(response.message, ! response.success);
               $('#new-sidebar-form').find('.ajax-feedback').css('visibility', 'hidden');
               
       }, 'json');
      
      return false;
   });
}

var showMessage = function(message, error){
   var msgclass = 'cs-update';
   if(error)
       msgclass = 'cs-error';
   var html = '<div id="cs-message" class="cs-message ' + msgclass + '">' + message + '</div>';
   jQuery(html).hide().prependTo('#widgets-left').fadeIn().slideDown();
   setTimeout('hideMessage()', 5000);
}

var hideMessage = function(){
    var msg = jQuery('#cs-message');
    msg.fadeTo('fast', 0.1, function(){
       msg.slideUp('fast', function(){
          msg.remove(); 
       });
    });
}


jQuery(function($){
    scrollSetUp($);
    addCSControls($);
    showCreateSidebar($);
});

function reSort(a, $){
  a.sortable({
                placeholder: "widget-placeholder",
                items: "> .widget",
                handle: "> .widget-top > .widget-title",
                cursor: "move",
                distance: 2,
                containment: "document",
                start: function (h, g) {
                    g.item.children(".widget-inside").hide();
                    g.item.css({
                        margin: "",
                        width: ""
                    })
                },
                stop: function (i, g) {
                    if (g.item.hasClass("ui-draggable") && g.item.data("draggable")) {
                        g.item.draggable("destroy")
                    }
                    if (g.item.hasClass("deleting")) {
                        wpWidgets.save(g.item, 1, 0, 1);
                        g.item.remove();
                        return
                    }
                    var h = g.item.find("input.add_new").val(),
                        l = g.item.find("input.multi_number").val(),
                        k = b,
                        j = a(this).attr("id");
                    g.item.css({
                        margin: "",
                        width: ""
                    });
                    b = "";
                    if (h) {
                        if ("multi" == h) {
                            g.item.html(g.item.html().replace(/<[^<>]+>/g, function (n) {
                                return n.replace(/__i__|%i%/g, l)
                            }));
                            g.item.attr("id", k.replace("__i__", l));
                            l++;
                            a("div#" + k).find("input.multi_number").val(l)
                        } else {
                            if ("single" == h) {
                                g.item.attr("id", "new-" + k);
                                f = "div#" + k
                            }
                        }
                        wpWidgets.save(g.item, 0, 0, 1);
                        g.item.find("input.add_new").val("");
                        g.item.find("a.widget-action").click();
                        return
                    }
                    wpWidgets.saveOrder(j)
                },
                receive: function (i, h) {
                    var g = a(h.sender);
                    if (!a(this).is(":visible") || this.id.indexOf("orphaned_widgets") != -1) {
                        g.sortable("cancel")
                    }
                    if (g.attr("id").indexOf("orphaned_widgets") != -1 && !g.children(".widget").length) {
                        g.parents(".orphan-sidebar").slideUp(400, function () {
                            a(this).remove()
                        })
                    }
                }
            }).sortable("option", "connectWith", "div.widgets-sortables").parent();
            $('.widget').draggable('option', 'connectToSortable', 'div.widgets-sortables').draggable("enable");
}