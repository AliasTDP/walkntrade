var WTHelper = (function() {
    
    /******************************* Private *********************************/
    var api_url = '/api2/';
    
    // Used by service_initSidebar for menu state / animation.
    var sidebarState = 'hidden',
        animateSidebar = function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            var $sidebar = $('.wt-sidebar');
            if (sidebarState === 'hidden') { 
                $sidebar.animate({ "width": $sidebar.css('max-width') }, {
                    "duration": 250,
                    "start": function() {
                        $sidebar.children().fadeIn();
                    },
                    "complete": function() {
                        sidebarState = 'visible';
                    }
                });
            } else if (sidebarState === 'visible') {
                $sidebar.animate({ "width": "0" }, {
                    "duration": 250,
                    "start": function() {
                        $sidebar.children().fadeOut('fast');
                    },
                    "complete": function() {
                        sidebarState = 'hidden';
                    }
                });
            }
        };
    
    // Used by service_getPostsByCategory for state tracking.
    var previousCategory = '', 
        previousQuery = '', 
        offset = 0;
    
    /******************************** Public **********************************/
    
    var fontSize = window
        .getComputedStyle($('body')[0], null)
        .getPropertyValue('font-size');
    
    var initSidebar = function() {
        var $menu = $('.wt-header').find('#wt-header-menu'),
            $sidebar = $('.wt-sidebar');
        
        $menu.on('click', animateSidebar);
        $("body").on('click', function(event) {
            $target = $(event.target);
            if ($target.is($sidebar)) {
                // An empty area in the sidebar.
                return;
            }
            
            if (sidebarState === 'visible') { animateSidebar(event); }
        });
        $(window).on('resize', function(event) {
            if (sidebarState === 'visible') { animateSidebar(event); }
        });
        $(window).on('scroll', function(event) {
            if (sidebarState === 'visible') { animateSidebar(event); }
        });
        
        return $sidebar;
    };
    
    var login = function(email, password, rememberMe) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'login',
                email: email,
                password: password,
                rememberMe: rememberMe || true
            },
            dataType: 'HTML'
        });
    };
    
    var registerUser = function(username, email, password) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'addUser',
                username: username,
                email: email,
                password: password
            },
            dataType: 'JSON'
        });
    };
    
    var logout = function() {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'logout',
            },
            dataType: 'HTML'
        });
    };
    
    
    var getUsername = function() {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'getUserName'
            },
            dataType: 'JSON'
        });
    };
    
    var getUserAvatar = function() {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'getAvatar'
            },
            dataType: 'JSON'
        });
    };
    
    var getCategories = function() {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'getCategories'
            },
            dataType: 'JSON'
        });
    };
    
    var getPostsByCategory = function(schoolId, category, query, amount, sort) {
        query = query || '';
        if (category !== previousCategory || query !== previousQuery || query.length > 0) { offset = 0; }
        previousCategory = category;
        previousQuery = query;
        
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'getPosts',
                school: schoolId,
                cat: category,
                query: query,
                offset: offset,
                amount: amount,
                sort: sort,
                ellipse: 1
            },
            dataType: 'JSON'
        }).done(function() {
            offset += amount;
        });
    };
    
    var setCookie = function(c_name,value,exdays) {
        var exdate=new Date();
        exdate.setDate(exdate.getDate() + exdays);
        var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
        document.cookie=c_name + "=" + c_value +"; path=/";
    };

    return {
        const_fontSize: fontSize,
        fn_setCookie: setCookie,
        fn_initSidebar: initSidebar,
        service_login: login,
        service_registerUser: registerUser,
        service_logout: logout,
        service_getUsername: getUsername,
        service_getUserAvatar: getUserAvatar,
        service_getCategories: getCategories,
        service_getPostsByCategory: getPostsByCategory
    }

})();
