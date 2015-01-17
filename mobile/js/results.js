$(document).ready(function() {
    
    /** State Variables **/
    var school = window.school; delete window.school;
    
    var currentCategory = "all",
        filterSearch = false,
        filterSort = false,
        previousRelativeOffset = 0, // Previous scroll position of the window, in em's.
        inhibitUpdate = false, // Flag to be set if all results for school/category/query have been received from the server.
        $pageUpdate = $.Deferred(); // Track any asynchronous operations on the page.
    
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
                var loginHTML = '' +
                    '<div class="body-overlay" style="top:'+$(window).scrollTop()+'px;">' +
                        '<div class="pure-form login-window">' +
                            '<div class="login-window-content-wrapper">' +
                                '<h3 style="text-align: center;">Login</h3>' +
                                '<input style="display: block; margin: 0.25em auto; width:75%;" placeholder="Email" type="email"/>' + 
                                '<input style="display: block; margin: 0.25em auto; width:75%;" placeholder="Password" type="password"/>' +
                                '<div style="margin: 0 auto; text-align: center;">' +
                                    '<input class="pure-button pure-button-primary" style="margin: 0.25em;" type="submit" value="Log In"/>' +
                                '</div>' +
                                '<label></label>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                
                var $loginContainer = $(loginHTML);
                var $loginDialog = $loginContainer.find('.pure-form');
                
                // Unbind scroll events
                $('body').css('overflow', 'hidden')
                    .on('mousewheel.freezeDOM touchmove.freezeDOM', function(e) {
                        e.preventDefault();
                    });
                
                $loginContainer.hide().appendTo('body').fadeIn({
                    duration: 'fast', 
                    start: function() {
                        $loginDialog.animate({ top: "25%" }, 250);
                    },
                    complete: function() {
                        $(this).on('click', function(event) {
                            event.preventDefault();
                            event.stopImmediatePropagation();

                            var $target = $(event.target);
                            if (!$target.is($loginDialog) && !$target.is($loginDialog.find('*'))) {
                                // Re-bind scroll events
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
                        });
                    }
                });
                
                $loginDialog.find('input[type="submit"]').on('click', checkLoginHandler);
                $loginDialog.find('input[type="password"]').on('keyup', checkLoginHandler);
                
                function checkLoginHandler(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    
                    var email = $loginDialog.find('input[type="email"]').prop('value').trim(),
                        password = $loginDialog.find('input[type="password"]').prop('value'),
                        $target = $(event.target);
                    
                    if ($target.is($loginDialog.find('input[type="password"]'))) {
                        if (event.keyCode !== 13) {
                            return;
                        }
                    }
                    
                    checkLogin(email, password).done(function(responseText) {
                        if (responseText.indexOf("incorrect") !== -1) {
                            $loginDialog.find('label')
                                .text("Username or Password Incorrect.")
                                .css('color', 'red');
                        } else if (responseText === 'success') {
                            $loginDialog.find('label')
                                .text('Success :)')
                                .css('color', 'green');
                            
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
                    })
                    .fail(function(errorText) {
                        $loginDialog.find('label')
                            .text('Failed to connect to server :(')
                            .css('color', 'magenta');
                    });
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
    
    function loginUser() {
        $pageUpdate = $.Deferred();
        
        $('.wt-sidebar > #LoginBtn').detach();
        $('.wt-sidebar > #RegisterBtn').detach();
        $('.wt-sidebar').prepend('\
                <div id="LogoutBtn" class="wt-sidebar-content">\
                    <a>Logout</a>\
               </div>\
               <div id="PostBtn" class="wt-sidebar-content">\
                    <a>Add a Post</a>\
               </div>\
               <div id="MessageBtn" class="wt-sidebar-content">\
                    <a>Messages</a>\
               </div>\
               <div id="PanelBtn" class="wt-sidebar-content">\
                    <a>User CP</a>\
               </div>');
        var $userInfo = $('.wt-sidebar').prepend('<div class="user-info"></div>').children(':first-child');
        
        var getUsername = WTHelper.service_getUsername();
        var getUserAvatar = WTHelper.service_getUserAvatar();
        
        $.when(getUsername, getUserAvatar).done(function(response1, response2) {
            $userInfo.append('\
            <div class="user-name">'+response1[0].message+'</div>\
            <div class="user-img">\
                <img class="pure-img" src="https://walkntrade.com/'+response2[0].message+'"/>\
            </div>');
            $pageUpdate.resolve();
        });
    }
    
    function logoutUser() {
        WTHelper.service_logout().done(function(response) {
            $('.wt-header > .wt-header-nav').remove();
            $('.wt-sidebar > .user-info').remove();
            $('.wt-sidebar > #LogoutBtn').detach();
            $('.wt-sidebar > #PostBtn').detach();
            $('.wt-sidebar > #MessageBtn').detach();
            $('.wt-sidebar > #PanelBtn').detach();
            $('.wt-sidebar').prepend('<div id="RegisterBtn" class="wt-sidebar-content"><a>Sign Up</a></div>');
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
        for (var item = 0; item < results.length; item++) {
            var obsId = results[item].obsId,
                userid = results[item].userid,
                category = results[item].category,
                title = results[item].title,
                seller = results[item].username,
                image_ref = results[item].image,
                desc = results[item].details,
                price = results[item].price;

            price = (price.length) ? price : "";

            var itemHTML = '<div class="wt-result-wrapper">' +
                               '<div class="wt-result">' +
                               '</div>' +
                           '</div>';
            var $itemHTML = $(itemHTML);
            var $item = $itemHTML.find('.wt-result');

            $item.append('<div>', '<div>', '<div>', '<div>', '<div>');
            var $category_container = $item.children(':nth-child(1)');
            var $title_container = $item.children(':nth-child(2)');
            var $visual_container = $item.children(':nth-child(3)');
            var $desc_and_price_wrapper = $item.children(':nth-child(4)');
            var $seller_price_container = $item.children(':nth-child(5)');

            var $category_element = $category_container
                .addClass('category ' + 'color-'+category)
                .append('<label>')
                .children(':first-child');
            $category_element
                .html(category);

            var $title_element = $title_container
                .addClass('title')
                .append('<label>')
                .children(':first-child');
            $title_element
                .html(title);

            var $visual_element = $visual_container
                .addClass('visual')
                .append('<img>')
                .children(':first-child');
            $visual_element
                .addClass('pure-img')
                .attr('src', 'https://walkntrade.com/' + image_ref);
            
            $desc_and_price_wrapper
                .addClass('desc_price_wrapper')
                .append('<div>', '<div>');
            
            var $price_container = $desc_and_price_wrapper.children(':first-child');
            var $price_element = $price_container
                .addClass('price')
                .append('<label>')
                .children(':first-child');
            $price_element.html(price);
            
            var $desc_container = $desc_and_price_wrapper.children(':nth-child(2)');
            var $desc_element = $desc_container
                .addClass('description')
                .append('<p>')
                .children(':first-child');
            $desc_element.html(desc);

            $seller_price_container
                .addClass('seller')
                .css({ 'font-weight': 'bold' })
                .append('<div><a></a><label></label></div>');
            
            var $seller_element = $seller_price_container.children(':first-child').children(':nth-child(1)');
            $seller_element
                .html('Posted By: ' + seller)
                .attr('href', 'https://walkntrade.com/user.php?uid=' + userid);
            
            var $message_element = $seller_price_container.children(':first-child').children(':nth-child(2)');
            $message_element
                .html("Message User");

            $itemHTML.hide().appendTo('.wt-results').fadeIn('slow')
                .on('click', function(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    window.location="/show?" + obsId;
                });
        }
    }
    
    function ChangeFilterEventHandler(school, category, query) {
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
