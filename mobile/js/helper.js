var WTHelper = (function() {
    
    /******************************* Private *********************************/
    
    // Used by service_initSidebar for menu state / animation.
    var sidebarState = 'hidden';
    
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
          $.get('/mobile/partials/modal-window.html')
              .done(function(modalHTML) {
                  cache['modalHTML'] = modalHTML;
                  attachDialog(cache['modalHTML'], dialogHTML, locationHash);
                  $status.resolve($dialog);
              })
              .fail(function() {
                  $status.reject('There was a problem connecting to the website. Please try again.');
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
        factory_createDialog: createDialog
    };

})();
