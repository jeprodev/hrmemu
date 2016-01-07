(function($){
    $.fn.HrMenuFancySlider = function(opts){
        var defaults = {
            fancyTransition: 'linear',
            fancyTime: 500,
            fancyBg: 'black_bg.png'
        };

        var options = $.extend(defaults, opts);
        var hrmenuFancyObj = this;
        var fancyItem, currentItem;
        return hrmenuFancyObj.each(function(){
            fancyHrMenuInit();
        });

        /** fancy hrmenu **/
        function fancyHrMenuInit(){
            if($('li.active', hrmenuFancyObj).length){
                currentItem = $('li.active', hrmenuFancyObj);
            } else {
                currentItem = $('li.hover_bg_active', hrmenuFancyObj);
            }

            if(!currentItem.length){
                $('li.level1', hrmenuFancyObj).each(function(i, el){
                    el = $(el);
                    el.mouseenter(function(){
                        if(!$('li.hover_bg_active', hrmenuFancyObj).length){
                            el.addClass('hover_bg_active');
                            hrmenuFancyObj.HrMenuFancySlider({fancyTransition: options.fancyTransition, fancyTime: options.fancyTime });
                        }
                    });
                });
            }

            if(!$('.active', hrmenuFancyObj).length && !$('.hover_bg_active', hrmenuFancyObj).length) return false;

            $('ul.nav_hrmenu', hrmenuFancyObj).append('<li class="hrmenu_fancy_bg" ><div class="hrmenu_fancy center"><div class="hrmenu_fancy left" ><div class="hrmenu_fancy right" ></div></div></div></li>');
            fancyItem = $('.hrmenu_fancy_bg',hrmenuFancyObj);

            if(currentItem){ setCurrent(currentItem); }

            $('li.level1', hrmenuFancyObj).each(function(i, el){
                el = $(el);
                el.mouseenter(function(){ moveHrMenuFancy(el); });
                el.mouseleave(function(){
                    if(!$('li.active', hrmenuFancyObj).length){
                        $(fancyItem).stop(false, false).animate({left:0, width: 0}, { duration: options.fancyTime, easing: options.fancyTransition });
                    }else{
                        moveHrMenuFancy($(currentItem));
                    }
                });
            });
        }

        function moveHrMenuFancy(el){
            $(fancyItem).stop(false, false).animate({left: el.position().left, width: el.outerWidth()}, { duration: options.fancyTime, easing: options.fancyTransition});
        }

        function setCurrent(el){
            el = $(el);
            var defaultLeft = Math.round(el.position().left);
            var defaultWidth = el.outerWidth();

            $(fancyItem).stop(false, false).animate({left: defaultLeft, width: defaultWidth}, { duration: options.fancyTime, easing: options.fancyTransition});
        }
    };
})(jQuery);