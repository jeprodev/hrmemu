(function($){
    $.fn.DropDownHrMenu = function(opts){
        //setting default options
        var defaults = {
            fx_transition: 'linear',
            fx_duration : 500,
            menuId: 'hrmenu',
            item_width : 160,
            item_height: 30,
            level1_font_size: 18,
            test_overflow: '0',
            orientation : 'horizontal',
            behavior : 'mouseover',
            open_type: 'open',
            direction: 'normal',
            direction_offset1 : '30',
            direction_offset2: '30',
            time_in:0,
            time_out: 500,
            is_mobile:false,
            menu_position: '0',
            effect_type : 'drop_down',
            show_active_sub_items : '0'
        };

        /** calling default options **/
        var options = $.extend(defaults, opts);
        var hrMenuObj = this;
        var status = new Array();
        //var slide_container;

        /** act upon the element that is passed into the design **/
        return hrMenuObj.each(function(){
            hrMenuInit();

            if(defaults.menu_position == 'top_fixed'){
                var menu_y = $(this).offset().top;
                $(document.body).attr('data-margintop', $(document.body).css('margin-top'));
                hrMenuObj.menu_height = $(this).height();
                $(window).bind('scroll', function(){
                    if($(window).scrollTop() > menu_y){
                        hrMenuObj.addClass('hrmenu_fixed');
                        $(document.body).css('margin-top', parseInt(hrMenuObj.menu_height))
                    }else{
                        $(document.body).css('margin-top', $(document.body).attr('data-margintop'));
                        hrMenuObj.removeClass('hrmenu_fixed')
                    }
                });
            }else if(defaults.menu_position == 'bottom_fixed'){
                $(this).addClass('hrmenu_fixed').find('ul.nav_hrmenu').css('position', 'static');
            }
        });

        function hrMenuInit(){
            var menu = $('#hrmenu_logo');
            var menuList = $('.hrmenu_wrapper_menu', hrMenuObj);
            menu.data('status', 'closed');
            if(options.effect_type == 'push_down'){
                $('li.hrmenu.level1', hrMenuObj).each(function(index, elt){
                    if(!$(elt).hasClass('parent')){
                        $(elt).mouseenter(function(){
                            $('li.hrmenu.level1.parent', hrMenuObj).each(function(j, elt2){
                                elt2 = $(elt2);
                                if($(elt).prop('class') != elt2.prop('class')){
                                    elt2.submenu = $('> .hrmenu_push_down > .hrmenu_float', hrMenuObj).eq(j);
                                    hideSubHrMenu(elt2);
                                }
                            });
                        });
                    }
                });
                elts = $('li.hrmenu.level1.parent', hrMenuObj);
            }else{
                elts = $('li.hrmenu.parent', hrMenuObj);
            }

            elts.each(function(i, el){
                el = $(el);
                if(el.hasClass('no_drop_down')){ return true; }

                // manage item level
                if(el.hasClass('level1')){ el.data('level', 1); }
                $('li.hrmenu.parent', el).each(function(j, child){
                    $(child).data('level', el.data('level') + 1);
                });

                // manage sub menus
                if(options.effect_type == 'push_down'){
                    el.submenu = $('> .hrmenu_push_down > .hrmenu_float', hrMenuObj).eq(i);
                    el.submenu.find('> .hrmenu_drop_main').css('width', 'inherit').css('overflow', 'hidden');
                }else{
                    el.submenu = $('> .hrmenu_float', el);
                    el.submenu.css('position', 'absolute');
                    /*el.addClass('hrmenu_animation'); */
                }

                el.submenu_height = el.submenu.height();
                el.submenu.css('width', options.item_width + 'px');
                el.submenu_width = el.submenu.width();

                if(options.open_type == 'no_effect' || options.open_type == 'open' || options.open_type == 'slide'){
                    el.submenu.css('display', 'none');
                }else{
                    el.submenu.css('display', 'block');
                    el.submenu.hide();
                }

                //manage active sub menus
                if((options.show_active_sub_items == '1' && el.hasClass('active')) || el.hasClass('hrmenu_open')){
                    if(el.hasClass('full_width')){
                        el.submenu.css('display', 'block').css('left', '0');
                    }else{
                        el.submenu.css('display', 'block');
                    }
                    el.submenu.css('max-height', '');
                    el.submenu.show();
                }

                // manage inverse direction
                if(options.fx_direction == 'inverse' && el.hasClass('level1') && options.orientation == 'horizontal'){
                    el.submenu.css('bottom', options.direction_offset1 + 'px');
                }
                if(options.fx_direction == 'inverse' && el.hasClass('level1') && options.orientation == 'vertical'){
                    el.submenu.css('right', options.direction_offset1 + 'px');
                }
                if(options.fx_direction == 'inverse' && el.hasClass('level1') && options.orientation == 'vertical'){
                    el.submenu.css('right', options.direction_offset2 + 'px');
                }

                if(options.behavior == 'click_close'){
                    el.mouseenter(function(){
                        if(options.test_overflow == '1'){ testHrMenuOverflow(); }
                        $('li.hrmenu.parent.level' + el.data('level'), hrMenuObj).each(function(j, el2){
                            el2 = $(el2);
                            if(el.prop('class') != el2.prop('class')){
                                if(option.effect_type == 'push_down'){
                                    el2.submenu = $('> .hrmenu_push_down > .hrmenu_float', hrMenuObj).eq(j);
                                }else{
                                    el2.submenu = $('> .hrmenu_float', el2);
                                }
                                hideSubHrMenu(el2);
                            }
                        });
                        showSubHrMenu(el);
                    });

                    $('> div >.hrmenu_close', el).click(function(){ hideSubHrMenu(el); });
                }else if(options.behavior == 'click'){
                    if(el.hasClass('parent') && $('> a.hrmenu', el).length){
                        el.redirection = $('> a.hrmenu', el).prop('href');
                        $('> a.hrmenu', el).prop('href', 'javascript:void(0)');
                        el.has_been_clicked = false;
                    }

                    $('> a.hrmenu, > div.separator, > div.nav_header', el).click(function(){
                        $('li.hrmenu.level' + $(el).attr('data-level'), hrMenuObj).removeClass('hrmenu_clicked').removeClass('hrmenu_open');
                        el.addClass('hrmenu_clicked');
                        if(options.test_overflow == '1'){ testHrMenuOverflow(el); }
                        if(el.data('status') == 'opened'){
                            $('li.hrmenu', $(el)).removeClass('hrmenu_clicked').removeClass('hrmenu_open');
                            $(el).removeClass('hrmenu_clicked').removeClass('hrmenu_open');
                            hideSubHrMenu(el);
                            $('li.hrmenu.parent:not(.no_drop_down)', el).each(function(j, el2){
                                el2 = $(el2);
                                if(el.prop('class') != el2.prop('class')){
                                    if(options.effect_type == 'push_down'){
                                        el2.submenu = $('> .hrmenu_push_down > .hrmenu_float', hrMenuObj).eq(j);
                                    }else{
                                        el2.submenu = $('> .hrmenu_float', el2);
                                    }
                                    hideSubHrMenu(el2);
                                }
                            });
                        }else{
                            $('li.hrmenu.parent.level' + el.data('level'), hrMenuObj).each(function(j, el2){
                                el2 = $(el2);
                                if(el.prop('class') != el2.prop('class')){
                                    if(options.effect_type == 'push_down'){
                                        el2.submenu = $('> .hrmenu_push_down > .hrmenu_float', hrMenuObj).eq(j);
                                    }else{
                                        el2.submenu = $('> .hrmenu_float', el2);
                                    }
                                    hideSubHrMenu(el2);
                                }
                            });
                            showSubHrMenu(el);
                        }
                    });
                }else{
                    el.mouseenter(function(){
                        if(options.effect_type == 'push_down'){
                            $('li.hrmenu.level1.parent', hrMenuObj).each(function(j, el2) {
                                el2 = $(el2);
                                if(el.prop('class') != el2.prop('class')){
                                    el2.submenu = $('> .hrmenu_push_down > .hrmenu_float', hrMenuObj).eq(j);
                                    hideSubHrMenu(el2);
                                }
                            });
                        }else{
                            if(options.test_overflow == '1'){
                                testHrMenuOverflow(el);
                            }
                        }
                        showSubHrMenu(el);
                    });

                    if(options.effect_type == 'push_down'){
                        hrMenuObj.mouseleave(function(){ hideSubHrMenu(el); });
                    }else{
                        el.mouseleave(function(){ hideSubHrMenu(el); });
                    }
                }
            });

            /**--- Manage mobile effect ---**/
            if(options.is_mobile){
                menuList.slideUp("fast");
                menu.click(function(){
                    if(menu.data('status') == 'opened'){
                        menu.data('status', 'closing');
                        menuList.slideUp("slow");
                        menu.data('status', 'closed');
                    }else if(menu.data('status') == 'closed'){
                        menu.data('status', 'opening');
                        menuList.slideDown("slow");
                        menu.data('status', 'opened');
                    }
                });
                /*
                 var menu_elts = $('li.hrmenu');
                 menu_elts.each(function(i, elt){
                 elt = $(elt);
                 if(!elt.hasClass('parent')){
                 elt.click(function(){
                 //$('#hrmenu_text_link').text(this.text());
                 });
                 }
                 });*/
            }
        }

        function closeHrMenu(el){
            var fxDuration = options.fx_duration;
            var fxTransition = options.fx_transition;
            el.submenu.stop(true, true);
            status[el.data('level')] = '';
            el.data('status', 'closing');
            switch(options.open_type){
                case 'no_effect' :
                    el.submenu.css('display', 'none');
                    status[el.data('level')] = '';
                    el.data('status', 'closed');
                    break;
                case 'fade' :
                    el.submenu.fadeOut(fxDuration, fxTransition, {
                        complete:function(){
                            status[el.data('level')] = '';
                            el.data('status', 'closed');
                        }
                    });
                    el.data('status', 'closed');
                    break;
                case 'slide' :
                    if(el.hasClass('level1') && options.orientation == 'horizontal'){
                        el.submenu.css('max-height', '');
                    }else{
                        el.submenu.css('max-width', '');
                    }
                    el.submenu.css('display', 'none');
                    el.submenu.css('position', 'absolute');
                    status[el.data('level')] = '';
                    el.data('status', 'closed');
                    break;
                case 'open' :
                    el.submenu.stop();
                    el.submenu_height = el.submenu.height();
                    status[el.data('level')] = '';
                    el.submenu.css('overflow', 'hidden');
                    el.data('status', 'closing');
                    if(el.hasClass('level1') && options.orientation =='horizontal'){
                        el.submenu.css('overflow', 'hidden').css('max-height', el.submenu.height()).animate({ 'max-height' : 0 }, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                el.submenu.css('max-height', '');
                                el.submenu.css('display', 'none');
                                el.submenu.css('position', 'absolute');
                                status[el.data('level')] = '';
                                el.data('status', 'closed');
                            }
                        });
                    }else{
                        el.submenu.css('max-width', '');
                        el.submenu.css('display', 'none');
                        el.submenu.css('position', 'absolute');
                        status[el.data('level')] = '';
                        el.data('status', 'closed');
                    }
                    break;
                case 'drop' :
                default :
                    el.submenu.hide(0, {
                        complete: function(){
                            status[el.data('level')] = '';
                            el.data('status', 'closed');
                        }
                    });
                    el.data('status', 'closed');
                    break;
            }
        }

        function openHrMenu(el){
            var subMenu = $(el.submenu);
            var fxDuration = options.fx_duration;
            var fxTransition = options.fx_transition;
            var slideContainer;
            if(el.data('status') === 'opened' || (status[el.data('level') - 1] === 'showing' && options.open_type === 'drop')) return;
            subMenu.stop(true, true);
            subMenu.css('left', 'auto');
            el.submenu.css('display', 'block');

            if(options.effect_type == 'push_down'){ el.submenu.css('position', 'relative'); }
            if(options.open_type !== 'no_effect'){ status[el.data('level')] = 'showing'; }
            switch(options.open_type){
                case 'slide' :
                    if(el.data('status') == 'opening'){ break; }
                    el.data('status', 'opening');
                    el.submenu.css('overflow', 'hidden');
                    el.submenu.stop(true, true);
                    slideContainer = $('.hrmenu_2', el);
                    if(el.hasClass('level1') && options.orientation == 'horizontal'){
                        slideContainer.css('marginTop', -el.submenu_height);
                        slideContainer.animate({ marginTop:0}, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                status[el.data('level')] = '';
                                el.submenu.css('overflow', 'visible');
                                el.data('status', 'opened');
                            }
                        });
                        el.submenu.animate({ 'max-height' : el.submenu_height }, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                $(this).css('max-height', '');
                                status[el.data('level')] = '';
                                el.submenu.css('overflow', 'visible');
                                el.data('status', 'opened');
                            }
                        });
                    }else{
                        slideContainer.css('marginLeft', -el.submenu.width());
                        slideContainer.animate({ marginLeft : 0 }, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                status[el.data('level')] = '';
                                el.submenu.css('overflow', 'visible');
                                el.data('status', 'opened');
                            }
                        });
                        el.submenu.animate({'max-height' : el.submenu.width()}, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                status[el.data('level')] = '';
                                el.submenu.css('overflow', 'visible');
                                el.data('status', 'opened');
                            }
                        });
                    }
                    break;
                case 'fade':
                    el.data('status', 'opening');
                    el.submenu.hide();
                    el.submenu.stop(true, true);
                    el.submenu.fadeIn(fxDuration, fxTransition, {
                        complete: function(){
                            status[el.data('level')] = '';
                            el.data('status', 'opened');
                        }
                    });
                    el.data('status', 'opened');
                    break;
                case 'show':
                    el.data('status', 'opening');
                    el.submenu.hide();
                    el.submenu.stop(true, true);
                    el.submenu.show(fxDuration, fxTransition, {
                        complete: function(){
                            status[el.data('level')] = '';
                            el.data('status', 'opened');
                        }
                    });
                    el.data('status', 'opened');
                    break;
                case 'scale':
                    el.data('status', 'opening');
                    if(!el.hasClass('status') || options.orientation == 'vertical'){
                        el.submenu.css('margin-left', el.submenu.width());
                    }
                    el.submenu.hide();
                    el.submenu.stop(true, true);
                    el.submenu.show("scale", {
                        duration:fxDuration, easing: fxTransition, complete : function(){
                            status[el.data('level')] = '';
                            el.data('status', 'opened');
                        }
                    });
                    el.data('status', 'opened');
                    break;
                case 'no_effect':
                    status[el.data('level')] = '';
                    el.data('status', 'opened');
                    break;
                case 'puff':
                    el.data('status', 'opening');
                    if(!el.hasClass('level1') || options.orientation == 'vertical'){
                        el.submenu.css('margin-left', el.submenu.width());
                    }
                    el.submenu.stop(true, true);
                    el.submenu.show("puff", {
                        duration: fxDuration, easing: fxTransition, complete: function(){
                            status[el.data('level')] = '';
                        }

                    });
                    el.data('status', 'opened');
                    break;
                case 'open' :
                default:
                    el.data('status', 'opening');
                    el.submenu.stop();
                    el.submenu.css('overflow', 'hidden');
                    if(el.hasClass('level1') && options.orientation == 'horizontal'){
                        el.submenu.animate({ 'max-height' : el.submenu_height }, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                $(this).css('max-height', '');
                                status[el.data('level')] = '';
                                if(options.effect_type == 'drop_down'){ el.submenu.css('overflow', 'visible'); }
                                el.data('status', 'opened');
                            }
                        });
                    }else{
                        el.submenu.animate({'max-width' : el.submenu.width() }, {
                            duration: fxDuration, queue: false, easing: fxTransition, complete: function(){
                                $(this).css('max-width', '');
                                status[el.data('level')] = '';
                                if(options.effect_type == 'drop_down'){ el.submenu.css('overflow', 'visible'); }
                                el.data('status', 'opened')
                            }
                        })
                    }
                    break;
            }
        }

        function showSubHrMenu(el){
            el.css('z-index', 15000);
            el.submenu.css('z-index', 15000)
            clearTimeout(el.time_out);
            el.time_out = setTimeout(function(){ openHrMenu(el); }, options.time_in);
        }

        function hideSubHrMenu(el){
            if(options.effect_type == 'push_down' && el.data('status') != 'closing'){
                closeHrMenu(el);
            }else if(options.effect_type != 'push_down'){
                el.css('z-index', 12001);
                el.submenu.css('z-index', 12001)
                clearTimeout(el.time_out);
                el.time_out = setTimeout(function(){ closeHrMenu(el); }, options.time_out);
            }
        }

        function testHrMenuOverflow(el){
            if(el.hasClass('full_width')){ return; }
            var pageWidth = $(document.body).outerWidth();
            var eltPosition = el.offset().left + el.outerWidth() + el.submenu.width();

            if(eltPosition > pageWidth){
                if(el.data('level') === 1){
                    el.submenu.css('right', '0px');
                }else{
                    el.submenu.css('right', el.submenuWidth + 'px');
                }
                el.submenu.css('marginRight', '0px');
                el.submenu.addClass('fix_right');
            }
        }
    };
})(jQuery);