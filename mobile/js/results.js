$(document).ready(function() {
    
    /** Constants **/
    var HEADER_SNAP_POINT = 7; // Measured in relative units
    var LAZY_LOAD_OFFSET = 66; // Measured in relative units

    /** State Variables **/
    var school = window.school; 
    delete window.school;
    
    var currentCategory = "all",
        currentQuery = "",
        inSortView = false,
        previousRelativeOffset = 0, // Previous scroll position of the window, in em's.
        inhibitUpdate = false, // Flag to be set if all results for school/category/query have been received from the server.
        $pageUpdate = $.Deferred(); // Track any asynchronous operations on the page.
    
    var poll; // Messaging Long Poll State Variable
    
    /** Cache **/
    var cache = {};
    
    /** Bootstrap the results page  **/
    initPage();
    
    /*********************************************************************************************************/
    
    function initPage() {
        history.replaceState("", document.title, window.location.pathname);
        var categoryFlag = getCategories();
        var resultsFlag = getPostsByCategory(school, 'all');

        $.when(categoryFlag, resultsFlag)
            .done(function() {
                $pageUpdate.resolve();
                bindDOM();
            })
            .fail(function() {
                $pageUpdate.reject();
            });
    }
    
    function bindDOM() {
        WTHelper.initSidebar().on('click', WTControllers.SidebarController);
        $('.results-category').on('click', WTControllers.ChangeCategoryController);;
        $('.results-searchtoggle').on('click', WTControllers.ResultsFilterController);
        $('.results-search').on('keyup click', WTControllers.SearchController);
        $(window).on('scroll', WTControllers.HeaderSnapController);
        $(window).on('scroll', WTControllers.ResultsScrollController);
    }
    
    function showConversationsView() {
        if (inSearchView) {
            WTControllers.ResultsFilterController(); // State Machine will return to Categories view.
        }

        window.location.hash = '#messages';
        $(window).off('scroll', WTControllers.ResultsScrollController);
        $(window).on('hashchange', WTControllers.LocationController);
        
        $('.wt-results').fadeOut({
            complete: function() {
                $(this).children().remove();
                $(this).parent().removeClass('wt-results-wrapper').addClass('wt-messager-wrapper');
                $(this).removeClass('wt-results').addClass('wt-messager');
                $(this).show();

                $('.wt-header').css({
                    'position': 'fixed',
                    'top': 0,
                    'z-index': 1
                });
                
                $('.results-filter').css({
                    'position': 'fixed',
                    'top': '3.5em',
                    'z-index': 0
                }).animate({ top: 0 }, {
                    duration: 500,
                    start: function() {
                        $('.wt-header .wt-header-nav').fadeOut();
                        $('#wt-header-menu > .fa-navicon')
                            .hide()
                            .removeClass('fa-navicon').addClass('fa-arrow-left')
                            .fadeIn()
                            .off('click')
                            .on('click', function(event) {
                                window.location.hash = '';
                            });
                    },
                    complete: function() {
                        $(this).css('z-index', -1).hide();
                        $('.wt-header').css('position', 'static');

                        WTServices.service_getUserMessageThreads()
                            .done(function(response) {
                                if (response.message === 'Threads for current user') {
                                    if (!response.payload.length) {
                                        $('.wt-messager').append('<span class="wt-content-info">No Messages.</span>');
                                        return;
                                    }

                                    for (i = 0; i < response.payload.length; i++) {
                                        thread = response.payload[i];
                                        var $message_div = $('.wt-messager')
                                            .append('\
                                                <div id="thread_'+thread.thread_id+'" class="wt-thread-wrapper">\
                                                    <div class="wt-thread">\
                                                        <div class="sender-image">\
                                                            <img class="pure-img" src="' + thread.associated_with_image + '"/>\
                                                        </div>\
                                                        <div class="thread-info">\
                                                            <h3>' + thread.post_title + '</h3>\
                                                            <span>' + thread.last_user_name + ': ' + thread.last_message + '</span>\
                                                            <span>' + thread.datetime + '</span>\
                                                        </div>\
                                                    </div>\
                                               </div>')
                                            .children('#thread_'+thread.thread_id);

                                        $message_div.data('thread_info', thread);
                                        $message_div.on('click', function(event) {
                                            event.preventDefault();
                                            event.stopImmediatePropagation();
                                            showMessageThread($(this).data('thread_info'));
                                        });
                                    }
                                }
                            }).fail(function() {
                                $('.wt-messager').append('<span class="wt-content-error">Failed to connect to server.</span>');
                            });
                    }
                });
            }
        });
    }

    function leaveConversationsView() {
        
        $('.wt-messager').fadeOut({
            complete: function() {
                $(this).children().remove();
                $(this).parent().removeClass('wt-messager-wrapper').addClass('wt-results-wrapper');
                $(this).removeClass('wt-messager').addClass('wt-results');
                $(this).show();
                
                $('.results-filter')
                    .show()
                    .animate({
                        'top': '3.5em'
                    }, {
                        duration: 400,
                        start: function() {
                            $('#wt-header-menu > .fa-arrow-left')
                                .hide()
                                .removeClass('fa-arrow-left').addClass('fa-navicon')
                                .fadeIn()
                                .off('click')
                                .on('click', WTHelper.animateSidebar);
                        },
                        complete: function() {
                            $('.wt-header .wt-header-nav').fadeIn();
                            $(this).css({
                                'position': 'static',
                                'z-index': 0
                            });
                            
                            /**
                            Since we are reloading the results from scratch, we
                            need to reset the 'inhibitUpdate' flag which is set
                            when there are no more results to load (the user
                            may have already triggered this).
                            **/
                            inhibitUpdate = false;

                            // Fetch results
                            $pageUpdate = $.Deferred();
                            getPostsByCategory(school, currentCategory, { resetOffset: true })
                                .done(function() {
                                    $pageUpdate.resolve();
                                })
                                .fail(function(error) {
                                    $('.wt-results').append('<span class="wt-content-error">'+error.detailed+'</span>');
                                    $('.wt-results').slideDown('slow');
                                    $pageUpdate.reject();
                                });
                        }
                    });
            }
        });
        
        $(window).off('hashchange', WTControllers.LocationController);
        $(window).on('scroll', WTControllers.ResultsScrollController);
    }
    
    function showMessageThread(thread_info) {
        if ($pageUpdate.state() === 'pending') return;

        $pageUpdate = $.Deferred();
        window.location.hash = '#messageThread';
        
        $('body > section.wt-content').css({
            'position': 'fixed',
            'box-shadow': '0 0.4em 1em rgba(0, 0, 0, 0.78)',
            'height': 'calc(100% - 3.5em)'
        }).animate({
            'left': '-99%'
        }, {
            duration: 400,
            complete: function() {
                $(this).children().css('opacity', 0.5);

                WTServices.service_getThreadByID(thread_info.thread_id, 10)
                    .done(function(thread) {
                        $('body').append('<section class="wt-message-thread-wrapper">\
                                              <div class="wt-message-thread">\
                                              </div>\
                                              <div class="wt-message-reply pure-form">\
                                                  <input type="text" class="wt-message-input" />\
                                                  <i class="wt-message-submit fa fa-lg fa-arrow-right"></i>\
                                              </div>\
                                          </section>');
                    
                        $('.wt-message-reply').on('keyup click', function(event) {
                            var event_type = event.type,
                                $event_target = $(event.target);

                            if (event_type === 'click' && $event_target.is('.wt-message-submit')) {
                                if ($('.wt-message-input').val() === '') {
                                    return;
                                }
                            } else if (event_type === 'keyup' && $event_target.is('.wt-message-input')) {
                                if (event.keyCode !== 13 || $('.wt-message-input').val() === '') {
                                    return;
                                }
                            } else {
                                return;
                            }

                            // All conditions met to submit a message.
                            var msg = $('.wt-message-input').val();
                            $('.wt-message-thread').append('\
                                <div class="wt-message" style="opacity: 0.5; color:white;">\
                                    <span class="wt-message-avatar" style="float:right;"><img class="pure-img" src="'+$('.user-info > .user-img > img').attr('src')+'"/></span>\
                                    <span class="wt-message-text" style="background-color:green; float:right;">'+msg+'</span>\
                                </div>');
                                var offset = $('.wt-message-thread').children('.wt-message:last-child').position().top;
                                $('.wt-message-thread').animate({
                                    scrollTop: '+='+offset
                                }, 100);
                                WTServices.service_appendMessageToThread(thread_info.thread_id, msg)
                                    .done(function(response) {
                                        $('.wt-message:last-child').append('<div class="wt-message-info" style="float:right;">Sent by You just now</div>');
                                        $('.wt-message:last-child').css('opacity', 1);
                                        var offset = $('.wt-message-thread').children('.wt-message:last-child').position().top;
                                        $('.wt-message-thread').animate({
                                            scrollTop: '+='+offset
                                        }, 100);
                                    }).fail(function() {
                                        $('.wt-message:last-child').append('<div class="wt-message-info" style="float:right;">There was an error sending this message</div>');
                                        var offset = $('.wt-message-thread').children('.wt-message:last-child').position().top;
                                        $('.wt-message-thread').animate({
                                            scrollTop: '+='+offset
                                        }, 100);
                                    });

                            $('.wt-message-input').val('');
                        });

                        for (var i = 0; i < thread.payload.length; i++) {
                            var msg = thread.payload[i];
                            var float = msg.sender_name === "You" ? "right": "left";
                            var bgcolor = msg.sender_name === "You" ? "green": "yellow";
                            var color = msg.sender_name === "You" ? "white": "black";
                            $('.wt-message-thread').append('\
                            <div class="wt-message" style="color:'+color+';">\
                                <span class="wt-message-avatar" style="float:'+float+';"><img class="pure-img" src="'+msg.avatar+'"/></span>\
                                <span class="wt-message-text" style="background-color:'+bgcolor+'; float:'+float+'">'+msg.message_content+'</span>\
                                <div class="wt-message-info" style="float:'+float+'">'+msg.sender_name+' at ' + msg.datetime + '</div>\
                            </div>');
                        }
                    
                        var offset = $('.wt-message-thread').children('.wt-message:last-child').position().top;
                        $('.wt-message-thread').scrollTop(offset);

                        $pageUpdate.resolve();
                    })
                    .fail(function() {
                        $('body').append('<section class="wt-message-thread-wrapper">\
                                              <div class="wt-message-thread">\
                                                  <span class="wt-content-info">Failed to connect to server.</span>\
                                              </div>\
                                         </section>');
                        $pageUpdate.reject();
                        });
                
                $('#wt-header-menu').off('click').on('click', function(event) {
                    window.location.hash = '#messages';
                });
           }
        });

        if (!poll) {
            poll = setInterval(function() {
                if ($pageUpdate.state() === 'pending') {
                    return;
                }

                WTServices.service_getNewMessagesInThread(thread_info.thread_id)
                    .done(function(response) {
                        if (response.payload.length) {
                            for (var i = 0; i < response.payload.length; i++) {
                                var msg = response.payload[i];
                                $('.wt-message-thread').append('\
                                <div class="wt-message" style="color:black;">\
                                    <span class="wt-message-avatar" style="float:left;"><img class="pure-img" src="'+msg.avatar+'"/></span>\
                                    <span class="wt-message-text" style="background-color:yellow; float:left;">'+msg.message_content+'</span>\
                                    <div class="wt-message-info" style="float:left;">'+msg.sender_name+' at ' + msg.datetime + '</div>\
                                </div>');
                            }

                            var offset = $('.wt-message-thread').children('.wt-message:last-child').position().top;
                            $('.wt-message-thread').animate({
                                scrollTop: '+='+offset
                            }, 100);
                        }
                    })
                    .fail(function() {
                        // TODO
                    });
            }, 5000);
        }
    }
    
    function leaveMessageThread() {
        if ($pageUpdate.state() === 'pending') return;

        $pageUpdate = $.Deferred();

        clearInterval(poll);
        poll = undefined;
        $('section.wt-message-thread-wrapper').remove();
        $('body > section.wt-content').css({
            'box-shadow': 'none',
            'height': 'initial'
        }).animate({
            'left': 0
        }, {
            duration: 400,
            complete: function() {
                $(this).css('position', 'static');
                $(this).children().css('opacity', 1);
                
                $('.wt-header > #wt-header-menu').off('click').on('click', function(event) {
                    leaveConversationsView();
                });

                $pageUpdate.resolve();
            }
        });
    }
    
    function createLoginDialog() {
        var $dialog;
                
        if (cache.hasOwnProperty('loginWindow')) {
            $dialog = WTHelper.factory_createDialog(cache['loginWindow'], '#login');
            $dialog.$el_promise
                .done(function($dialog_el) {
                    bindLoginDialog($dialog.$service, $dialog_el);
                })
                .fail(function(errorMessage) {
                    alert(errorMessage);
                });
        } else {
            $.get('/mobile/partials/login-window.html')
                .done(function(loginHTML) {
                    cache['loginWindow'] = loginHTML;
                    $dialog = WTHelper.factory_createDialog(cache['loginWindow'], '#login');
                    $dialog.$el_promise
                        .done(function($dialog_el) {
                            bindLoginDialog($dialog.$service, $dialog_el);
                        })
                        .fail(function(errorMessage) {
                            alert(errorMessage);
                        });
                })
                .fail(function() {
                    var errorMessage = '<span>There was a problem loading some data from the server. Please try again.</span>';
                    $dialog = WTHelper.factory_createDialog(errorMessage, '#login')
                    $dialog.$el_promise.fail(function(errorMessage) {
                        alert(errorMessage);
                    });
                });
        }

        // Assuming all async operations have succeeded so far, we end up here.
        function bindLoginDialog($dialog_service, $dialog_el) {

            if ($(window).width() < 600) {
                $dialog_el.find('a').eq(0).on('click', function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $dialog_el.find('.signup-form').hide();
                    $dialog_el.find('.login-form').show();
                });
                $dialog_el.find('a').eq(1).on('click', function(e) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $dialog_el.find('.login-form').hide();
                    $dialog_el.find('.signup-form').show();
                });
            }

            $dialog_el.find('.login-form input[type="submit"]').on({
                'click': checkLoginHandler
            }, {
                $dialog_service: $dialog_service,
                $dialog_el: $dialog_el
            });

            $dialog_el.find('.signup-form input[type="submit"]').on({
                'click': checkSignupHandler
            }, {
                $dialog_service: $dialog_service,
                $dialog_el: $dialog_el
            });
        }

        function bindVerificationDialog($dialog_service, $dialog_el, email, password) {
            $dialog_el.find('.verification-form input[type="submit"]').on({
                'click': checkVerificationHandler
            }, {
                $dialog_service: $dialog_service,
                $dialog_el: $dialog_el,
                email: email,
                password: password
            });
        }

        function checkLoginHandler(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var $dialog_service = event.data.$dialog_service,
                $dialog_el = event.data.$dialog_el;

            var email = $dialog_el.find('.login-form input[type="email"]').prop('value').trim(),
                password = $dialog_el.find('.login-form input[type="password"]').prop('value');

            var simple_email_regex = /^\S+@\S+\.\S+$/;
            if (!email.length) {
                $dialog_el.find('.login-form label')
                    .text('Please enter your email address.')
                    .css('color', 'red');
            } else if (simple_email_regex.test(email) === false) {
                $dialog_el.find('.login-form label')
                    .text('Please enter a valid email address.')
                    .css('color', 'red');
            } else if (!password.length) {
                $dialog_el.find('.login-form label')
                    .text('Please enter your password.')
                    .css('color', 'red');
            } else {
                checkLogin(email, password)
                    .done(function(responseText) {
                        if (responseText === 'success') {
                            $dialog_el.find('.login-form label')
                                .text('Success :)')
                                .css('color', 'green');
                            setTimeout(function() {
                                $dialog_service.$destroy();
                                loginUser();
                            }, 500);
                        } else if (responseText === 'verify') {
                            $dialog_service.$destroy();
                            $dialog = WTHelper.factory_createDialog('\
                            <h3>Verify Email</h3>\
                            <form class="verification-form">\
                                <input placeholder="Verification Code" type="text" autofocus tabindex=1/>\
                                <input class="pure-button" type="submit" tabindex=2 value="Verify"/>\
                                <label></label>\
                            </form>', '#verify');
                            
                            /** 
                            We're not checking for a fail condition here, because this cannot fail.
                            The modal content has to have been already cached to reach this point.
                            **/
                            $dialog.$el_promise.done(function($dialog_el) {
                                bindVerificationDialog($dialog.$service, $dialog_el, email, password);
                            });
                        } else {
                            $dialog_el.find('.login-form label')
                                .text(responseText)
                                .css('color', 'red');
                        }
                    })
                    .fail(function(errorText) {
                        $dialog_el.find('.login-form label')
                            .text('Failed to connect to server :(')
                            .css('color', 'red');
                    });
            }
        }

        function checkSignupHandler(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var $dialog_service = event.data.$dialog_service,
                $dialog_el = event.data.$dialog_el;

            var email = $dialog_el.find('.signup-form input[type="email"]').prop('value').trim(),
                username = $dialog_el.find('.signup-form input[type="text"]').prop('value').trim(),
                passwords = $dialog_el.find('.signup-form input[type="password"]');

            var simple_email_regex = /\S+@\S+\.edu/;
            if (!username.length) {
                $dialog_el.find('.signup-form label')
                    .text('Please enter a username.')
                    .css('color', 'red');
            } else if (!email.length) {
                $dialog_el.find('.signup-form label')
                    .text('Please enter an email address.')
                    .css('color', 'red');
            } else if (simple_email_regex.test(email) === false) {
                $dialog_el.find('.signup-form label')
                    .text('Please enter a valid .edu email.')
                    .css('color', 'red');
            } else if (!passwords[0].value.length && !passwords[1].value.length) {
                $dialog_el.find('.signup-form label')
                    .text('Please enter a password.')
                    .css('color', 'red');
            } else if ((!passwords[0].value.length && passwords[1].value.length) || 
                       (!passwords[1].value.length && passwords[0].value.length)) {
                $dialog_el.find('.signup-form label')
                    .text('Please confirm your password.')
                    .css('color', 'red');
            } else if (passwords[0].value !== passwords[1].value) {
                $dialog_el.find('.signup-form label')
                    .text('The two passwords provided must match.')
                    .css('color', 'red');
            } else {
                checkSignup(username, email, passwords[0].value)
                    .done(function(response) {
                        if (response.message === 'Success') {
                            $dialog_el.find('.signup-form label')
                                .text('Success :)')
                                .css('color', 'green');
                            $dialog_service.$destroy();
                            $dialog = WTHelper.factory_createDialog('\
                            <h3>Verify Email</h3>\
                            <form class="verification-form">\
                                <input placeholder="Verification Code" type="text" autofocus tabindex=1/>\
                                <input class="pure-button" type="submit" tabindex=2 value="Verify"/>\
                                <label></label>\
                            </form>', '#verify');
                            $dialog.$el_promise.done(function($dialog_el) {
                                bindVerificationDialog($dialog.$service, $dialog_el, email, passwords[0].value);
                            });
                        } else {
                            $dialog_el.find('.signup-form label')
                                .text(response.message)
                                .css('color', 'red');
                        }
                    })
                    .fail(function(errorText) {
                        $dialog_el.find('.signup-form label')
                            .text('Failed to connect to server :(')
                            .css('color', 'red');
                    });
            }
        }

        function checkVerificationHandler(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var $dialog_service = event.data.$dialog_service,
                $dialog_el = event.data.$dialog_el,
                email = event.data.email,
                password = event.data.password;

            var key = $dialog_el.find('.verification-form input[type="text"]').prop('value').trim();

            if (!key.length) {
                $dialog_el.find('.verification-form label')
                    .text('Please enter the verification code sent to you.')
                    .css('color', 'red');
            } else {
                checkVerification(key)
                    .done(function(responseText) {
                        if (responseText === 'Your email address has been verified!') {
                            $dialog_el.find('.verification-form label')
                                .text('Success :)')
                                .css('color', 'green');

                            checkLogin(email, password).done(function(responseText) {
                                if (responseText === 'success') {
                                    $dialog_service.$destroy();
                                    loginUser();
                                }
                            });
                        } else {
                            $dialog_el.find('.verification-form label')
                                .text(responseText)
                                .css('color', 'red');
                        }
                    })
                    .fail(function() {
                        $dialog_el.find('.verification-form label')
                            .text('Failed to connect to server :(')
                            .css('color', 'red'); 
                    });
            }
        }
    }
    
    function createMessagerDialog(post_id) {
        var $dialog;
        
        if (cache.hasOwnProperty('messagerHTML')) {
            $dialog = WTHelper.factory_createDialog(cache['messagerHTML'], '#messageUser');
                $dialog.$el_promise.done(function($dialog_el) {
                    bindMessagerDialog($dialog.$service, $dialog_el, post_id);
                });
        } else {
            $.get('/mobile/partials/messager-window.html').done(function(messagerHTML) {
                cache['messagerHTML'] = messagerHTML;
                $dialog = WTHelper.factory_createDialog(messagerHTML, '#messageUser');
                $dialog.$el_promise.done(function($dialog_el) {
                    bindMessagerDialog($dialog.$service, $dialog_el, post_id);
                });
            });
        }
        
        function bindMessagerDialog($dialog_service, $dialog_el, post_id) {
            $dialog_el.find('.messager-form input[type="submit"]').on({
                'click': checkMessagerHandler
            }, {
                $dialog_service: $dialog_service,
                $dialog_el: $dialog_el,
                post_id: post_id
            });
        }
        
        function checkMessagerHandler(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            $dialog_service = event.data.$dialog_service;
            $dialog_el = event.data.$dialog_el;
            message = $dialog_el.find('.messager-form textarea').prop('value');
            post_id = event.data.post_id;
            
            if (!message.length) {
                $dialog_el.find('.messager-form label')
                    .text('Enter a message.')
                    .css('color', 'red');
            } else {
                sendMessage(post_id, message).done(function(response) {
                    if (response.message === 'Message sent!') {
                        $dialog_el.find('.messager-form label')
                            .text('Success :)')
                            .css('color', 'green');
                        
                        $dialog_service.$destroy();
                    } else {
                        $dialog_el.find('.messager-form label')
                            .text(response.message)
                            .css('color', 'red');
                    }
                });
            }
            
        }
    }
    
    function sendMessage(post_id, message) {
        var $status = $.Deferred();
        WTServices.service_createMessageThread(post_id, message)
            .done(function(response) {
                $status.resolve(response);
            })
            .fail(function(request, errorText) {
                $status.reject(errorText);
            });
        return $status;
    }
    
    function checkLogin(email, password) {
        var $status = $.Deferred();
        WTServices.service_login(email, password)
            .done(function(response) {
                $status.resolve(response);
            })
            .fail(function(request, errorText) {
                $status.reject(errorText);
            });        
        return $status;
    }
    
    function checkSignup(username, email, password) {
        var $status = $.Deferred();
        WTServices.service_registerUser(username, email, password)
            .done(function(response) {
                $status.resolve(response);
            })
            .fail(function(request, errorText) {
                $status.reject(errorText);
            }); 
        return $status;
    }

    function checkVerification(key) {
        var $status = $.Deferred();
        WTServices.service_verifyUser(key)
            .done(function(response) {
                $status.resolve(response);
            })
            .fail(function(request, errorText) {
                $status.reject(errorText);
            });
        return $status;
    }
    
    function loginUser() {
        $pageUpdate = $.Deferred();
        
        $('.wt-sidebar > #LoginBtn').detach();
        
        if (cache.hasOwnProperty('sidebarSessionContent')) {
            attachSidebarSession(cache['sidebarSessionContent']);
        } else {
            $.get('/mobile/partials/sidebar-session.html')
                .done(function(sidebarHTML) {
                    cache['sidebarSessionContent'] = sidebarHTML;
                    attachSidebarSession(cache['sidebarSessionContent']);
                })
                .fail(function() {
                    // TODO: Change to use dialog.
                    alert('Lost the connection trying to load user info. Refresh the page to fix the problem.');
                });
        }

        function attachSidebarSession(sidebarHTML) {
            $('.wt-sidebar').prepend(sidebarHTML);
            
            var getUsername = WTServices.service_getUsername();
            var getUserAvatar = WTServices.service_getUserAvatar();
            
            $.when(getUsername, getUserAvatar)
                .done(function(response1, response2) {
                    $('.wt-sidebar > .user-info > .user-name')
                        .html(response1[0].message);
                    $('.wt-sidebar > .user-info > .user-img > img')
                        .attr('src', 'https://walkntrade.com/'+response2[0].message);

                    $pageUpdate.resolve();
                })
                .fail(function() {
                    // TODO: Change to use dialog.
                    alert('Lost the connection trying to load user info. Refresh the page to fix the problem.');
                });
        }
    }
    
    function logoutUser() {
        WTServices.service_logout()
            .done(function(response) {
                $('.wt-sidebar > .user-info').remove();
                $('.wt-sidebar > #LogoutBtn').detach();
                $('.wt-sidebar > #PostBtn').detach();
                $('.wt-sidebar > #MessageBtn').detach();
                //$('.wt-sidebar > #PanelBtn').detach();
                $('.wt-sidebar').prepend('<div id="LoginBtn" class="wt-sidebar-content"><a>Login</a></div>');
            })
            .fail(function(request) {
                alert('Lost the connection while trying to log you out. Refresh the page to ensure you are logged out correctly.');
            });
    }
    
    function populateCategories(categories) {
        $('.results-categories').before('<i style="display: none;" class="fa fa-2x fa-angle-left"></i>').prev()
            .on('click', function(event) {
                $('.results-categories').scrollLeft($('.results-categories').scrollLeft() - 25);
            });
        $('.results-categories').after('<i class="fa fa-2x fa-angle-right"></i>').next()
            .on('click', function(event) {
                $('.results-categories').scrollLeft($('.results-categories').scrollLeft() + 25);
            });
        
        $('.results-categories').on('scroll', function(event) {
            if ($(this).scrollLeft() < $(this).children(':first-child').width() / 2) {
                $(this).prev().css('display', 'none');
                $(this).next().css('display', 'inline');
            } else if ($(this).scrollLeft() > $(this).width() / 2) {
                $(this).prev().css('display', 'inline');
                $(this).next().css('display', 'none');
            } else {
                $(this).prev().css('display', 'inline');
                $(this).next().css('display', 'inline');
            }
        });
        
        for (var cat = 0; cat < categories.length; cat++) {
            var categoryID = categories[cat][0],
                categoryName = categories[cat][1],
                categoryDesc = categories[cat][2];

            var categoryEl = $('.results-categories')
                .append('<a class="results-category">')
                .children(':nth-child('+(cat+1)+')');
            
            if (cat === 0) { categoryEl.addClass('selected'); }
            categoryEl
                .addClass('pure-button color-'+categoryID)
                .data('category', categoryID)
                .text(categoryName);
        }
        
        if ($('.results-categories').get(0).scrollWidth <= $('.results-filter').width()) {
            $('.results-categories').next().css('display', 'none');
        } else {
            $('.results-categories').next().css('display', 'inline');
        }
        
        $(window).on('resize', function(event) {
            if ($('.results-categories').get(0).scrollWidth <= $('.results-filter').width()) {
                $('.results-categories').next().css('display', 'none');
            } else {
                $('.results-categories').next().css('display', 'inline');
            }
        });
    }
    
    function populateResults(results) {
        
        if (cache.hasOwnProperty('resultHTML')) {
            for (var item = 0; item < results.length; item++) {
                populateResult(cache['resultHTML'], results[item]);
            }
        } else {
            $.get('/mobile/partials/wt-result.html')
                .done(function(resultHTML) {
                    cache['resultHTML'] = resultHTML;
                    for (var item = 0; item < results.length; item++) {
                        populateResult(cache['resultHTML'], results[item]);
                    }
                });
        }
        
        function populateResult(resultHTML, result) {
            var obsId = result.obsId,
                userid = result.userid,
                category = result.category,
                title = result.title,
                seller = result.username,
                image_ref = result.image,
                details = result.details,
                price = result.price.length ? result.price : "";
            
            var $itemHTML = $(resultHTML);

            $itemHTML.find('.category')
                .addClass('color-'+category)
                .find('label')
                    .html(category);

            $itemHTML.find('.title > label')
                .html(title);

            $itemHTML.find('.visual > img')
                .attr('src', image_ref);

            $itemHTML.find('.price > span')
                .html(price);

            $itemHTML.find('.description > p')
                .html(details);

            $itemHTML.find('.seller > span')
                .html(seller)
                .attr('href', 'https://walkntrade.com/user.php?uid='+userid);

            $itemHTML.hide().appendTo('.wt-results').fadeIn('slow');
            
            $itemHTML.find('.messager').on('click', function(event) {
                createMessagerDialog(obsId);
            });
        }
    }
    
    function getCategories() {
        var status = $.Deferred();
        WTServices.service_getCategories().done(function(response) {
            populateCategories(response.payload.categories);
            status.resolve();
        })
        .fail(function(request) {
            status.reject();
        });
        
        return status;
    }
    
    function getPostsByCategory(schoolId, category, opts) {
        var status = $.Deferred();

        var opts = opts || {};

            opts['query'] = opts['query'] || '',
            opts['amount'] = opts['amount'] || 12,
            opts['sort'] = opts['sort'] || 0;

        WTServices.service_getPostsByCategory(schoolId, category, opts)
            .done(function(response) {
                currentCategory = category;
                currentQuery = opts.query || '';
                if (response.payload.length > 0) {
                    populateResults(response.payload);
                } else {
                    inhibitUpdate = true;
                }
                status.resolve();
            })
            .fail(function(request) {
                status.reject({
                    simple: 'Failed to connect to Walkntrade.',
                    detailed: 'Failed to connect to Walkntrade while retrieving the post catalog.'
                });
            });
        
        return status;
    }

    function createAddPostDialog() {
        var $dialog;

        if (cache.hasOwnProperty('addPostWindow')) {
            $dialog = WTHelper.factory_createDialog(cache['addPostWindow'], '#addPost');
            $dialog.$el_promise
                .done(function($dialog_el) {
                    WTServices.service_getCategories()
                        .done(function(response) {
                            var categories = response.payload.categories;
                            for (i = 0; i < categories.length; i++) {
                                if (categories[i][1] === 'Everything') {
                                    continue;
                                }
                                $dialog_el.find('.add-post-form .destination-category')
                                    .append('<option value="'+categories[i][1]+'">'+categories[i][1]+'</option>')
                                    .on('change', function(event) {
                                        if ($(this).val().indexOf('books') === -1) {
                                            $dialog_el.find('.add-post-form .post-author').val('').hide();
                                            $dialog_el.find('.add-post-form .post-isbn').val('').hide();
                                        } else {
                                            $dialog_el.find('.add-post-form .post-author').show();
                                            $dialog_el.find('.add-post-form .post-isbn').show();
                                        }
                                    });
                            }
                            bindCreatePostDialog($dialog.$service, $dialog_el);
                        })
                        .fail(function() {
                        });
                })
                .fail(function(errorMessage) {
                    alert(errorMessage);
                });
        } else {
            $.get('/mobile/partials/add-post-window.html')
                .done(function(addPostHTML) {
                    cache['addPostWindow'] = addPostHTML;
                    $dialog = WTHelper.factory_createDialog(cache['addPostWindow'], '#addPost');
                    $dialog.$el_promise
                        .done(function($dialog_el) {
                            WTServices.service_getCategories()
                                .done(function(response) {
                                    var categories = response.payload.categories;
                                    for (i = 0; i < categories.length; i++) {
                                        if (categories[i][1] === 'Everything') {
                                            continue;
                                        }
                                        $dialog_el.find('.add-post-form .destination-category')
                                            .append('<option value="'+categories[i][1]+'">'+categories[i][1]+'</option>')
                                            .on('change', function(event) {
                                                if ($(this).val().indexOf('books') === -1) {
                                                    $dialog_el.find('.add-post-form .post-author').val('').hide();
                                                    $dialog_el.find('.add-post-form .post-isbn').val('').hide();
                                                } else {
                                                    $dialog_el.find('.add-post-form .post-author').show();
                                                    $dialog_el.find('.add-post-form .post-isbn').show();
                                                }
                                            });
                                    }
                                    bindCreatePostDialog($dialog.$service, $dialog_el);
                                })
                                .fail(function() {
                                });
                        })
                        .fail(function(errorMessage) {
                            alert(errorMessage);
                        });
                })
                .fail(function() {
                    var errorMessage = '<span>There was a problem loading some data from the server. Please try again.</span>';
                    $dialog = WTHelper.factory_createDialog(errorMessage, '#addPost')
                    $dialog.$el_promise.fail(function(errorMessage) {
                        alert(errorMessage);
                    });
                });
        }

        function bindCreatePostDialog($dialog_service, $dialog_el) {
            $dialog_el.find('.add-post-form .post-submit').on({
                'click': checkNewPostHandler
            }, {
                $dialog_service: $dialog_service,
                $dialog_el: $dialog_el
            });
        }

        function checkNewPostHandler(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            var $dialog_service = event.data.$dialog_service,
                $dialog_el = event.data.$dialog_el;

            var category = $dialog_el.find('.destination-category').val().toLowerCase(),
                title = $dialog_el.find('.post-title').val().trim(),
                author = $dialog_el.find('.post-author').val().trim(),
                details = $dialog_el.find('.post-details').val().trim(),
                price = parseFloat($dialog_el.find('.post-price').val().trim()),
                tags = $dialog_el.find('.post-tags').val().trim(),
                isbn = $dialog_el.find('.post-isbn').val().trim();

            if (!title.length) {
                 $dialog_el.find('.add-post-form label')
                    .text('Please enter a title for your post.')
                    .css('color', 'red');
            } else if (!author.length && category.indexOf('book') > -1) {
                $dialog_el.find('.add-post-form label')
                    .text('Please enter the author of the textbook, or N/A if necessary.')
                    .css('color', 'red');
            } else if (!details.length) {
                 $dialog_el.find('.add-post-form label')
                    .text('Please enter some details about your post.')
                    .css('color', 'red');
            } else if (details.length && details.length < 10) {
                 $dialog_el.find('.add-post-form label')
                    .text('Your product details are too short.')
                    .css('color', 'red');
            } else if (isNaN(price)) {
                 $dialog_el.find('.add-post-form label')
                    .text('You\'ve entered an invalid price.')
                    .css('color', 'red');
            } else if (!tags.length) {
                 $dialog_el.find('.add-post-form label')
                    .text('Please enter some tags for your post.')
                    .css('color', 'red');
            } else {
                var obsId_regex = /^[0-9a-fA-f]{32}/;
                WTServices.service_createPost(category, title, author, details, price, tags, isbn)
                    .done(function(responseText) {
                        if (responseText.match(obsId_regex)) {
                            $dialog_el.find('.login-form label')
                                .text('Success :)')
                                .css('color', 'green');
                            setTimeout(function() {
                                window.location="./";
                            }, 1000);
                        } else {
                            // TODO
                        }
                    })
                    .fail(function(errorText) {
                        console.log(errorText);
                        $dialog_el.find('.add-post-form label')
                            .text('Failed to connect to server :(')
                            .css('color', 'red');
                    });
            }
        }
    }

    function ChangeResultsFilter(school, category, queryString) {
        inhibitUpdate = false;
        $pageUpdate = $.Deferred();
        queryString = queryString || '';

        $('.wt-results').slideUp('slow', function() {
            $(this).empty();
            getPostsByCategory(school, category, { query: queryString })
                .done(function() {
                    if (inhibitUpdate) {
                        $('.wt-results').append('<span class="wt-content-info">No Results</span>');
                    }
                    $('.wt-results').slideDown('slow');
                    $pageUpdate.resolve();
                })
                .fail(function(error) {
                    $('.wt-results').append('<span class="wt-content-error">'+error.detailed+'</span>');
                    $('.wt-results').slideDown('slow');
                    $pageUpdate.reject();
                });
        });
    }

    /** This is tightly coupled to code in here so it currently can't
        be factored out of this file. **/
    var inSearchView = false;
    var WTControllers = {
        SidebarController: function(event) {
            var $target = $(event.target);

            if ($target.is('#LoginBtn') || $target.is('#LoginBtn *')) {
                createLoginDialog();
            } else if ($target.is('#LogoutBtn') || $target.is('#LogoutBtn *')) {
                logoutUser();
            } else if ($target.is('#PostBtn') || $target.is('#PostBtn *')) {
                createAddPostDialog();
            } else if ($target.is('#ChangeSchoolBtn') || $target.is('#ChangeSchoolBtn *')) {
                window.location = "/selector.php";
            } else if ($target.is('#MessageBtn') || $target.is('#MessageBtn *')) {
                showConversationsView();
            }
        },

        ChangeCategoryController: function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();

            if ($pageUpdate.state() === 'pending') {
                return;
            }
            inhibitUpdate = false;

            var $category_el = $(this),
                category = $category_el.data('category');
            var $search_el = $('.results-searchfield'),
                search = $search_el.val();

            if (category === currentCategory && !search.length) {
                return;
            }

            $('.results-category').removeClass('selected');
            $category_el.addClass('selected');
            $search_el.val('');
            ChangeResultsFilter(school, category);
        },

        ResultsFilterController: function(event) {
            if (inSearchView) {
                $('.results-search').fadeOut('slow', function() {
                    $('.results-searchfield').prop('value', '');
                    $('.results-categories').fadeIn('fast', function() {
                        $(window).triggerHandler('resize');
                        inSearchView = false;
                    });
                });
            } else {
                $('.results-categories').fadeOut('slow', function() {
                    // Hide the overflow arrows for the results categories view
                    $('.results-categories').prev().css('display', 'none');
                    $('.results-categories').next().css('display', 'none');

                    $('.results-search').fadeIn('fast', function() {
                        inSearchView = true;
                    });
                });
            }
        },

        SearchController: function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            var event_type = event.type,
                $event_target = $(event.target);

            if (event_type === 'click' && $event_target.is('.results-searchbutton')) {
                var search_value = $('.results-searchfield').val();
                if (search_value === '' || search_value === currentQuery) {
                    return;
                }
            } else if (event_type === 'keyup' && $event_target.is('.results-searchfield')) {
                if (event.keyCode !== 13 || $('.results-searchfield').val() === '' || $('.results-searchfield').val() === currentQuery) {
                    return;
                }
            } else {
                return;
            }

            // All conditions met to conduct a search.
            ChangeResultsFilter(school, currentCategory, $('.results-searchfield').val());
        },

        HeaderSnapController: function(event) {
            var currentRelativeOffset = $(window).scrollTop() / parseFloat(WTHelper.const_fontSize);

            var currentlyPastSnapThreshold = (currentRelativeOffset > HEADER_SNAP_POINT),
                previouslyBeforeSnapThreshold = (previousRelativeOffset <= HEADER_SNAP_POINT);

            if (currentlyPastSnapThreshold && previouslyBeforeSnapThreshold) {

                $('.wt-header')
                    .css({
                        'position': 'fixed',
                        'top': "-3.5em"
                    })
                    .animate({ 'top': "+=3.5em" }, 600);

                $('.results-filter')
                    .css({
                        'position': 'fixed',
                        'top': "0"
                    })
                    .animate({ 'top': "+=3.5em" }, 600);

            } else if (!currentlyPastSnapThreshold && !previouslyBeforeSnapThreshold) {

                $('.results-filter').animate({ 'top': "-=3.5em" }, {
                    'duration': 50,
                    'complete': function() {
                        $(this).css({ 'position': 'static' });
                    }
                });

                $('.wt-header').animate({ 'top': "-=3.5em" }, {
                    'duration': 50,
                    'complete': function() {
                        $(this).css({ 'position': 'static' });
                    }
                });
            }

            previousRelativeOffset = currentRelativeOffset;
        },

        ResultsScrollController: function(event) {
            var currentRelativeOffset = $(window).scrollTop() / parseFloat(WTHelper.const_fontSize),
                relativeOffset_resultsContainer = currentRelativeOffset - HEADER_SNAP_POINT;

            var currentlyPastLazyLoadThreshold =
                (($('.wt-results').height() / parseFloat(WTHelper.const_fontSize)) - relativeOffset_resultsContainer < LAZY_LOAD_OFFSET);

            if (currentlyPastLazyLoadThreshold) {
                if ($pageUpdate.state() === 'pending' || inhibitUpdate === true) {
                    return;
                }

                $pageUpdate = $.Deferred();

                var queryString = inSearchView ? $('.results-searchfield').val() : '';
                getPostsByCategory(school, currentCategory, { query: queryString })
                    .done(function() {
                        $pageUpdate.resolve();
                    })
                    .fail(function() {
                        $pageUpdate.reject();
                    });
            }

            previousRelativeOffset = currentRelativeOffset;
        },

        LocationController: function(event) {
            if (this.location.hash === '') {
                leaveConversationsView();
            } else if (this.location.hash === '#messages') {
                leaveMessageThread();
            }
        }
    }
});
