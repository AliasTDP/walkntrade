$(document).ready(function() {
    var currentCategory = "all",
        filterSearch = false,
        filterSort = false,
        previousRelativeOffset = 0, // previous scroll position of the window, in em's.
        inhibitUpdate = false, // flag to be set if a response payload is empty (no more results to load).
        $pageUpdate = $.Deferred(); // promise object to track any asynchronous operations on the page.
    
    function bindDOM() {
        var $sidebar = WTHelper.fn_initSidebar();
        
        $sidebar.on('click', function(event) {
            var $target = $(event.target);
            if ($target.is('#LoginBtn') || $target.is('#LoginBtn *')) {
                var loginHTML = '' +
                    '<div style="position: absolute; width: 100%; height: 100%;\
                                 top: 0; left: 0; background-color: grey; opacity: 0.95;">' +
                        '<div class="pure-form" style="position: relative; border-radius: 1em;\
                                                       margin: 0 auto; padding: 1em; z-index: 3;\
                                                       top: 25%; background-color: white;\
                                                       width: 50%; height: 50%;">' +
                            '<h3 style="text-align: center;">Login</h3>' +
                            '<input style="display: block; margin: 0.5em auto;" placeholder="Email" type="email"/>' + 
                            '<input style="display: block; margin: 0.5em auto;" placeholder="Password" type="password"/>' +
                            '<div style="margin: 0 auto; text-align: center;">' +
                                '<input class="pure-button" style="margin: 0.5em;" type="button" value="Log In"/>' +
                                '<input class="pure-button" style="margin: 0.5em;" type="button" value="Cancel"/>' +
                            '</div>' +
                            '<label></label>' +
                        '</div>' +
                    '</div>';
                
                var $loginContainer = $(loginHTML);
                var $loginDialog = $loginContainer.find('.pure-form');
                $loginContainer.hide().appendTo('body').fadeIn().on('click', function(event) {
                    event.preventDefault();
                    event.stopImmediatePropagation();

                    var $target = $(event.target);
                    if (!$target.is($loginDialog) && !$target.is($loginDialog.find('*'))) {
                        $loginContainer.fadeOut({
                            complete: function() {
                                $(this).remove();
                            }
                        });
                    }
                });
                
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
                            $loginContainer.fadeOut({
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
                
                $loginDialog.find('input[type="button"][value="Log In"]').on('click', checkLoginHandler);
                $loginDialog.find('input[type="password"]').on('keyup', checkLoginHandler);
            } else if ($target.is('#LogoutBtn') || $target.is('#LogoutBtn *')) {
                logoutUser();
            } else if ($target.is('#ChangeSchoolBtn') || $target.is('#ChangeSchoolBtn *')) {
                window.location = "index.html";
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
            ChangeFilterEventHandler('spsu', category);
        });
        
        $('.results-search').on('click', function(event) {
            if (filterSearch) {
                $('.results-filter > .results-searchfield').fadeOut('slow', function() {
                    $('.results-categories').fadeIn('fast', function() {
                        filterSearch = false;
                    });
                });
            } else {
                $('.results-categories').fadeOut('slow', function() {
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
            ChangeFilterEventHandler('spsu', currentCategory, $(this).prop('value'));
        });
        
        $('.results-searchfield > button').on('click', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            ChangeFilterEventHandler('spsu', currentCategory, $('.results-searchfield > input').prop('value'));
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
                getPostsByCategory('spsu', currentCategory).done(function() {
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
        $('.wt-sidebar').prepend('<div id="LogoutBtn" class="wt-sidebar-content"><a>Logout</a></div>');
        
        var flag1 = WTHelper.service_getUsername().done(function(response) {
            console.log(response);
        })
        .fail(function(request) {
        });
        
        var flag2 = WTHelper.service_getUserAvatar().done(function(response) {
            console.log(response);
            $('.wt-sidebar').prepend('<div class="user-heading" style="width:100%";>\
                                        <img class="pure-img" style="margin: 0 auto;" src="https://walkntrade.com/'+response.message+'"/>\
                                     </div>');
        })
        .fail(function(request) {
        });
        
        $.when(flag1, flag2).then(function() {
            $pageUpdate.resolve();
        });
    }
    
    function logoutUser() {
        WTHelper.service_logout().done(function(response) {
            $('.wt-sidebar > #LogoutBtn').detach();
            $('.wt-sidebar > .user-heading').remove();
            $('.wt-sidebar').prepend('<div id="LoginBtn" class="wt-sidebar-content"><a>Login</a></div>');
        })
        .fail(function(request) {
        });
    }
    
    function populateCategories(categories) {
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
            
            var $desc_container = $desc_and_price_wrapper.children(':first-child');
            var $desc_element = $desc_container
                .addClass('description')
                .append('<p>')
                .children(':first-child');
            $desc_element.html(desc);
            
            var $price_container = $desc_and_price_wrapper.children(':nth-child(2)');
            var $price_element = $price_container
                .addClass('price')
                .append('<label>')
                .children(':first-child');
            $price_element.html(price);

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
                    window.location.href="";
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
    
    var categoryFlag = getCategories();
    var resultsFlag = getPostsByCategory('spsu', 'all');
    
    $.when(categoryFlag, resultsFlag).then(function() {
        $pageUpdate.resolve();
        bindDOM();
    }, function() {
        $pageUpdate.reject();
    });
});
