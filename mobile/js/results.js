$(document).ready(function() {
    
    /** State Variables **/
    var school = window.school; 
    delete window.school;
    
    var currentCategory = "all",
        filterSearch = false,
        filterSort = false,
        previousRelativeOffset = 0, // Previous scroll position of the window, in em's.
        inhibitUpdate = false, // Flag to be set if all results for school/category/query have been received from the server.
        $pageUpdate = $.Deferred(); // Track any asynchronous operations on the page.
    
    /** Cache **/
    var cache = {
    };
    
    /** Bootstrap the results page  **/
    initPage();
    
    /*********************************************************************************************************/
    
    function initPage() {
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
        var $sidebar = WTHelper.fn_initSidebar();
        
        $sidebar.on('click', function(event) {
            var $target = $(event.target);
            if ($target.is('#LoginBtn') || $target.is('#LoginBtn *')) {
                var $loginContainer, $loginDialog;
                
                if (cache.hasOwnProperty('loginWindow')) {
                    attachLoginWindow(cache['loginWindow']);
                } else {
                    $.get('/mobile/partials/login-window.html').done(function(loginHTML) {
                        cache['loginWindow'] = loginHTML;
                        attachLoginWindow(cache['loginWindow']);
                    });
                }
                
                function attachLoginWindow(loginHTML) {
                    $loginContainer = $(loginHTML);
                    $loginDialog = $loginContainer.find('.modal-window');
                    $loginContainer.css({
                        'display': 'none',
                        'top': $(window).scrollTop()+'px'
                    });

                    // Unbind scroll events
                    $('body').css('overflow', 'hidden')
                        .on('mousewheel.freezeDOM touchmove.freezeDOM', function(e) {
                            e.preventDefault();
                        });

                    $loginContainer.appendTo('body').fadeIn({
                        duration: 'fast', 
                        start: function() {
                            $loginDialog.animate({ top: "20%" }, 250);
                        },
                        complete: function() {
                            $(this).on('click', function(event) {
                                event.preventDefault();
                                event.stopImmediatePropagation();

                                var $target = $(event.target);
                                if (!$target.is($loginDialog) && !$target.is($loginDialog.find('*'))) {
                                    destroyLoginWindow();
                                }
                            });
                        }
                    });
                    
                    if ($(window).width() < 600) {
                        $loginContainer.find('a').eq(0).on('click', function(e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            $loginDialog.find('.signup-form').hide();
                            $loginDialog.find('.login-form').show();
                        });
                        $loginContainer.find('a').eq(1).on('click', function(e) {
                            e.preventDefault();
                            e.stopImmediatePropagation();
                            $loginDialog.find('.login-form').hide();
                            $loginDialog.find('.signup-form').show();
                        });
                    }
                    
                    $loginDialog.find('.login-form input[type="submit"]').on('click', checkLoginHandler);
                    $loginDialog.find('.signup-form input[type="submit"]').on('click', checkSignupHandler);
                }
                
                function destroyLoginWindow() {
                    // Re-bind scroll event
                    $('body').css('overflow', 'visible')
                        .off('.freezeDOM');

                    $loginContainer.fadeOut({
                        start: function() {
                            $loginDialog.animate({ top: "0" }, 250);
                        },
                        complete: function() {
                            $(this).remove();
                        }
                    });
                }
                
                function checkLoginHandler(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    
                    var email = $loginDialog.find('.login-form input[type="email"]').prop('value').trim(),
                        password = $loginDialog.find('.login-form input[type="password"]').prop('value');
                    
                    checkLogin(email, password).done(function(responseText) {
                        if (responseText === 'success') {
                            $loginDialog.find('.login-form label')
                                .text('Success :)')
                                .css('color', 'green');
                            destroyLoginWindow();
                        } else {
                            $loginDialog.find('.login-form label')
                                .text(responseText)
                                .css('color', 'red');
                        }
                    })
                    .fail(function(errorText) {
                        $loginDialog.find('.login-form label')
                            .text('Failed to connect to server :(')
                            .css('color', 'magenta');
                    });
                }
                
                function checkSignupHandler(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    
                    var email = $loginDialog.find('.signup-form input[type="email"]').prop('value').trim(),
                        username = $loginDialog.find('.signup-form input[type="text"]').prop('value').trim(),
                        passwords = $loginDialog.find('.signup-form input[type="password"]');
                    
                    if (!username.length) {
                        $loginDialog.find('.signup-form label')
                            .text('Please enter a username.')
                            .css('color', 'red');
                    } else if (!email.length) {
                        $loginDialog.find('.signup-form label')
                            .text('Please enter an email address.')
                            .css('color', 'red');
                    } else if (!passwords[0].value.length && !passwords[1].value.length) {
                        $loginDialog.find('.signup-form label')
                            .text('Please enter a password.')
                            .css('color', 'red');
                    } else if ((!passwords[0].value.length && passwords[1].value.length) || 
                               (!passwords[1].value.length && passwords[0].value.length)) {
                        $loginDialog.find('.signup-form label')
                            .text('Please confirm your password.')
                            .css('color', 'red');
                    } else if (passwords[0].value !== passwords[1].value) {
                        $loginDialog.find('.signup-form label')
                            .text('The two passwords provided must match.')
                            .css('color', 'red');
                    } else {
                        checkSignup(username, email, passwords[0].value).done(function(response) {
                            if (response.message === 'Success') {
                                $loginDialog.find('.signup-form label')
                                    .text('Success :)')
                                    .css('color', 'green');
                                destroyLoginWindow();
                            } else {
                                $loginDialog.find('.signup-form label')
                                    .text(response.message)
                                    .css('color', 'red');
                            }
                        })
                        .fail(function(errorText) {
                            $loginDialog.find('.signup-form label')
                                .text('Failed to connect to server :(')
                                .css('color', 'magenta');
                        });
                    }
                }
                
            } else if ($target.is('#LogoutBtn') || $target.is('#LogoutBtn *')) {
                logoutUser();
            } else if ($target.is('#ChangeSchoolBtn') || $target.is('#ChangeSchoolBtn *')) {
                window.location = "/selector.php";
            }
        });
        
        $('.results-categories > a').on('click', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            
            if ($pageUpdate.state() === 'pending') {
                return;
            }
            
            inhibitUpdate = false;
            var $category = $(this);
            var category = $category.data('category');
            if (category === currentCategory && $('.results-searchfield > input').prop('value') === '') {
                return;
            }
            
            $('.results-searchfield > input').prop('value', '');
            $('.results-categories > a').removeClass('selected');
            $category.addClass('selected');
            ChangeFilterEventHandler(school, category);
        });
        
        $('.results-search').on('click', function(event) {
            if (filterSearch) {
                $('.results-filter > .results-searchfield').fadeOut('slow', function() {
                    $('.results-categories').fadeIn('fast', function() {
                        $(window).triggerHandler('resize');
                        filterSearch = false;
                    });
                });
            } else {
                $('.results-categories').fadeOut('slow', function() {
                    $('.results-categories').prev().css('display', 'none');
                    $('.results-categories').next().css('display', 'none');
                    $('.results-filter > .results-searchfield').fadeIn('fast', function() {
                        filterSearch = true;
                    });
                });
            }
        });
        
        $('.results-searchfield > input').on('keyup', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            if (event.keyCode !== 13 || $(this).prop('value') === '') { return; }
            ChangeFilterEventHandler(school, currentCategory, $(this).prop('value'));
        });
        
        $('.results-searchfield > button').on('click', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            ChangeFilterEventHandler(school, currentCategory, $('.results-searchfield > input').prop('value'));
        });
        
        $(window).on('scroll', function(event) {
            var relativeOffset = $(window).scrollTop() / parseFloat(WTHelper.const_fontSize);
            
            if (relativeOffset > 7 && previousRelativeOffset <= 7) {
                $('.wt-header').css({
                    'position': 'fixed',
                    'top': "-3.5em"
                }).animate({ 'top': "+=3.5em" }, 600);
                $('.results-filter').css({
                    'position': 'fixed',
                    'top': "0"
                }).animate({ 'top': "+=3.5em" }, 600);
            } else if (relativeOffset < 7 && previousRelativeOffset >= 7) {
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
            previousRelativeOffset = relativeOffset;
            
            var relativeHeight_resultsContainer = $('.wt-results').height() / parseFloat(WTHelper.const_fontSize);
            var relativeOffset_resultsContainer = relativeOffset - 7; // Due to sticky header / results filter
            if (relativeHeight_resultsContainer - relativeOffset_resultsContainer < 66) {
                if ($pageUpdate.state() === 'pending' || inhibitUpdate === true) {
                    return;
                }
                
                $pageUpdate = $.Deferred();
                getPostsByCategory(school, currentCategory).done(function() {
                    $pageUpdate.resolve();
                })
                .fail(function() {
                    $pageUpdate.reject();
                });
            }
        });
    }
    
    function checkLogin(email, password, $callback) {
        var $status = $.Deferred();
        WTHelper.service_login(email, password).done(function(response) {
            console.log(response);
            if (response === 'success') {
                loginUser();
            } else if (response.indexOf("incorrect")) {
            } else if (response === 'verify') {
            } else if (response === 'reset') {
            }
            
            $status.resolve(response);
        })
        .fail(function(request, errorText) {
            console.log(errorText);
            $status.reject(errorText);
        });
        
        return $status;
    }
    
    function checkSignup(username, email, password) {
        var $status = $.Deferred();
        WTHelper.service_registerUser(username, email, password).done(function(response) {
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
            $.get('/mobile/partials/sidebar-session.html').done(function(sidebarHTML) {
                cache['sidebarSessionContent'] = sidebarHTML;
                attachSidebarSession(cache['sidebarSessionContent']);
            });
        }

        function attachSidebarSession(sidebarHTML) {
            $('.wt-sidebar').prepend(sidebarHTML);
            
            var getUsername = WTHelper.service_getUsername();
            var getUserAvatar = WTHelper.service_getUserAvatar();
            
            $.when(getUsername, getUserAvatar).done(function(response1, response2) {
                $('.wt-sidebar > .user-info > .user-name')
                    .html(response1[0].message);
                $('.wt-sidebar > .user-info > .user-img > img')
                    .attr('src', 'https://walkntrade.com/'+response2[0].message);
                
                $pageUpdate.resolve();
            });
        }
    }
    
    function logoutUser() {
        WTHelper.service_logout().done(function(response) {
            $('.wt-sidebar > .user-info').remove();
            $('.wt-sidebar > #LogoutBtn').detach();
            $('.wt-sidebar > #PostBtn').detach();
            $('.wt-sidebar > #MessageBtn').detach();
            $('.wt-sidebar > #PanelBtn').detach();
            $('.wt-sidebar').prepend('<div id="LoginBtn" class="wt-sidebar-content"><a>Login</a></div>');
        })
        .fail(function(request) {
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
                .append('<a>')
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
            var $flag = $.get('/mobile/partials/wt-result.html')
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

            $itemHTML.find('.price > label')
                .html(price);

            $itemHTML.find('.description > p')
                .html(details);

            $itemHTML.find('.seller a')
                .html('Posted By: ' + seller)
                .attr('href', 'https://walkntrade.com/user.php?uid='+userid);

            $itemHTML.find('.seller label')
                .html('Message User');

            $itemHTML.hide().appendTo('.wt-results').fadeIn('slow')
                .on('click', function(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    window.location="/show?" + obsId;
                });
        }
    }
    
    function ChangeFilterEventHandler(school, category, query) {
        inhibitUpdate = false;
        $pageUpdate = $.Deferred();
        query = query || '';
        
        $('.wt-results').slideUp('slow', function() {
            $(this).empty();
            getPostsByCategory(school, category, query).done(function() {
                if (inhibitUpdate) {
                    $('.wt-results').append('<p>no results :(</p>');
                }
                $('.wt-results').slideDown('slow');
                $pageUpdate.resolve();
            })
            .fail(function() {
                $('.wt-results').append('<p>wow cant connect to wt :( </p>');
                $('.wt-results').slideDown('slow');
                $pageUpdate.reject();
            });
        });
    }
    
    function getCategories() {
        var status = $.Deferred();
        WTHelper.service_getCategories().done(function(response) {
            console.log(response);
            populateCategories(response.payload.categories);
            status.resolve();
        })
        .fail(function(request) {
            console.log(request);
            status.reject();
        });
        
        return status;
    }
    
    function getPostsByCategory(schoolId, category, query) {
        var status = $.Deferred();
        WTHelper.service_getPostsByCategory(schoolId, category, query, 12, 0).done(function(response) {
            console.log(response);
            currentCategory = category;
            if (response.payload.length > 0) {
                populateResults(response.payload);
            } else {
                inhibitUpdate = true;
            }
            status.resolve();
        })
        .fail(function(request) {
            console.log(request);
            status.reject();
        });
        
        return status;
    }
});
