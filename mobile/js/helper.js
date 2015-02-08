var WTHelper = (function() {
    
    /******************************* Private *********************************/
    var api_url = '/api2/';
    
    // Used by service_initSidebar for menu state / animation.
    var sidebarState = 'hidden';
    
    // Used by service_getPostsByCategory for state tracking.
    var previousCategory = '', 
        previousQuery = '', 
        offset = 0;
    
    // General purpose cache
    var cache = {
    };
    
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
    
    var animateSidebar = function(event) {
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
    
    var createDialog = function(dialogHTML, locationHash) {
      var $dialogContainer, $dialog, $status;
        
      $status = $.Deferred();
      if (cache.hasOwnProperty('modalHTML')) {
          attachDialog(cache['modalHTML'], dialogHTML, locationHash);
          $status.resolve($dialog);
      } else {
          $.get('/mobile/partials/modal-window.html').done(function(modalHTML) {
              cache['modalHTML'] = modalHTML;
              attachDialog(cache['modalHTML'], dialogHTML, locationHash);
              $status.resolve($dialog);
          });
      }
        
      function attachDialog(modalHTML, dialogHTML, locationHash) {
          $dialogContainer = $(modalHTML)
            .css({
              'display': 'none',
              'top': $(window).scrollTop()+'px'
            });
          
          $dialog = $(dialogHTML)
            .appendTo($dialogContainer.find('.modal-window-content-wrapper'))
            .parents('.modal-window');
          
          // Unbind viewport scrolling
          $('body').css('overflow', 'hidden')
            .on('mousewheel.freezeDOM touchmove.freezeDOM', function(scrollEvent) {
              scrollEvent.preventDefault();
            });
          
          $dialogContainer.appendTo('body').fadeIn({
              duration: 'fast',
              start: function() {
                  $dialog.animate({ top: "20%" }, 250);
              },
              complete: function() {
                  window.location.hash = locationHash;
                  $(window).on('hashchange', function() {
                      if (!this.location.hash) {
                          destroyDialog();
                      }
                  });
                  
                  $(this).on('click keyup', function(event) {
                      event.preventDefault();
                      event.stopImmediatePropagation();
                      
                      if (event.type === 'keyup' && event.keyCode === 27) {
                          destroyDialog();
                          return;
                      }
                      
                      var $target = $(event.target);
                      if (!($target.is($dialog) || $target.is($dialog.find('*')))) {
                          // User clicked somewhere on the modal backdrop.
                          destroyDialog();
                      }
                  });
              }
          });
      }
        
      function destroyDialog() {
          // Rebind viewport scrolling
          $('body').css('overflow', 'visible')
            .off('.freezeDOM');
          
          // Remove dialog and modal from the DOM
          $dialogContainer.fadeOut({
              duration: 'fast',
              start: function() {
                  $dialog.animate({ top: '0' }, 250);
              },
              complete: function() {
                  $(this).remove();
                  window.history.replaceState("", document.title, window.location.pathname);
              }
          });
      }
      
          return {
              $el_promise: $status,
              $service: {
                $destroy: destroyDialog
              }
          };
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
    
    var verifyUser = function(key) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'verifyKey',
                key: key
            },
            dataType: 'HTML'
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
    
    var getPostsByCategory = function(schoolId, category, opts) {
        var school = schoolId,
            category = category,
            query = opts.query || '',
            amount = opts.amount || undefined,
            sort = opts.sort || undefined,
            flag = opts.resetOffset || false;
            
        if (flag === true || category !== previousCategory || query !== previousQuery || query.length > 0) { 
            offset = 0;
        }
        
        previousCategory = category;
        previousQuery = query;
        
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'getPosts',
                school: school,
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
    }
    
    var createMessageThread = function(post_id, message) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'createMessageThread',
                message: message,
                post_id: post_id
            },
            dataType: 'JSON'
        });
    };

    var getUserMessageThreads = function(offset, amount) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'getMessageThreadsCurrentUser',
                offset: offset,
                amount: amount
            },
            dataType: 'JSON'
        });
    };

    var getThreadByID = function(thread_id, post_count) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'retrieveThread',
                thread_id: thread_id,
                post_count: post_count
            },
            dataType: 'JSON'
        });
    };
    
    var sendMessage = function(thread_id, message) {
        return $.ajax({ 
            type: 'POST',
            url: api_url,
            data: {
                intent: 'appendMessage',
                thread_id: thread_id,
                message: message
            },
            dataType: 'JSON'
        });
    };
    
    var getNewMessagesInThread = function(thread_id) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'retrieveThreadNew',
                thread_id: thread_id
            },
            dataType: 'JSON'
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
        fn_animateSidebar: animateSidebar,
        factory_createDialog: createDialog,
        service_login: login,
        service_registerUser: registerUser,
        service_verifyUser: verifyUser,
        service_logout: logout,
        service_getUsername: getUsername,
        service_getUserAvatar: getUserAvatar,
        service_getCategories: getCategories,
        service_getPostsByCategory: getPostsByCategory,
        service_createMessageThread: createMessageThread,
        service_getUserMessageThreads: getUserMessageThreads,
        service_getThreadByID: getThreadByID,
        service_appendMessageToThread: sendMessage,
        service_getNewMessagesInThread: getNewMessagesInThread
    }

})();
