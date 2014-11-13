var WTHelper = (function() {
    var menuState = 0;
    
    var initHeaderCondensedMenu = function() {
        if (menuState === 1) { return; }
        menuState = 1;
        
        var $menu = $('.wt-header').find('.wt-header-content-condensed');
        
        // Perform a deep copy of matched elements.
        var $menuOptions = $('.wt-header').find('.wt-header-content-full').clone(); 
        
        var $menuWrapper = $menu
            .find('.wt-header-menu-wrapper')
            .empty()
            .append($menuOptions);

        $menu.click(function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            if ($menuWrapper.css('display') === 'none') {
                $menuWrapper.css('display', 'initial');
            } else {
                $menuWrapper.css('display', 'none');
            }
            return null;
        });
        $menu.mouseleave(function(event) {
            $('body').click(function() {
                event.preventDefault();
                event.stopImmediatePropagation();
                $menuWrapper.css('display', 'none');
            });
        });
    }

    return {
        initHeaderMenu: initHeaderCondensedMenu
    }

})();