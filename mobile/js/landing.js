$('document').ready(function() {
    var browser = window.navigator.userAgent,
        searchResults = {};
    
    function bindDOM() {
        
        $('body').on('click', function(event) {
            if ($('ul#schools').css('display') !== 'none') { $('ul#schools').slideUp(200); }
        });
        
        $('input#searchSchools').on('keyup click', function(event) {
            /* Prevent the event bubbling up to body click handler, 
               which will hide the search results. */
            event.stopImmediatePropagation();

            var search_value = $(this).prop('value');
            
            if (event.type !== 'click' && event.keyCode !== 13) {
                // user pressed a key other than Return/Enter
                if (search_value !== '') {
                    $('.wt-content-landing-inner > h2').slideUp(100);
                    $('input#searchSchools').blur();
                    $('input#searchSchools').focus();
                } else {
                    $('.wt-content-landing-inner > h1').fadeIn();
                    $('.wt-content-landing-inner > h2').slideDown(100);
                    $('input#searchSchools').blur();
                    $('input#searchSchools').focus();
                }
                
                if ($(this).hasClass('input-option-selected')) {
                    $(this).removeClass('input-option-selected');
                }
                if ($('button#submitSchool').prop('disabled') === false) {
                    $('button#submitSchool').prop('disabled', true)
                        .removeClass('button-option-selected');
                }
            }
            if (event.type === 'click' || event.keyCode !== 13) {
                if (search_value !== '') {
                    $('#clearSearch').css('display', 'inline');
                    getSchools(search_value);
                } else {
                    $('ul#schools').slideUp();
                    $('#clearSearch').css('display', 'none');
                }
                return; // Exit the function before value can be submitted
            }

            // Enter key was pressed or search performed, submit the value if not blank
            if (search_value !== '') {
                searchSchools(search_value);
            }
        });

        $('ul#schools').on('click', 'li', function(event) {
            event.stopImmediatePropagation();
            $(this).addClass('button-option-selected');
            
            /* Have to force js to asynchronously tie the trigger
               of the body event handler to the completion of the
               css property change, otherwise the change will occur the
               next time the li element appears on screen. */
            var $that = $(this);
            setTimeout(function() {
                $.Deferred(function() {
                    $that.removeClass('button-option-selected');
                    this.resolve();
                })
                .done(function() {
                    $('body').trigger('click');
                });
            }, 50);
            
            $('input#searchSchools').prop('value', $(this).text())
                                    .addClass('input-option-selected');
            $('button#submitSchool').prop('disabled', false)
                                    .addClass('button-option-selected');
        });

        $('#clearSearch').on('click', function(event) {
            $('input#searchSchools').prop('value', '');
            $('input#searchSchools').trigger('keyup');
        });
        
        $('button#submitSchool').on('click', function(event) {
            searchSchools($('input#searchSchools').prop('value'));
        });
    }
    
    function getSchools(query) {
        if (searchResults.query === undefined) {
            $.ajax({
                'type': 'POST',
                'url': '/api2/',
                'data': {
                    'intent': 'getSchools',
                    'query': query
                },
                'dataType': 'JSON',
                'statusCode': {
                    200: function(response) {
                        $('ul#schools').empty();
                        for (var i = 0; i < response.payload.length; i++) {
                            var optionHTML = '<li id="' + response.payload[i].textId + '">' +
                                                          response.payload[i].name + '</li>';
                            $(optionHTML).appendTo('ul#schools');
                        }
                        searchResults[query] = $('ul#schools').children();
                    }
                }
            });
        } else {
            $('ul#schools').empty();
            $('ul#schools').append(searchResults[query]);
        }
        
        if (searchResults[query] === 1 && searchResults[query].text() === query) {
            $('button#submitSchool').prop('disabled', false)
                                    .addClass('button-option-selected');
            $('ul#schools').slideUp();
            return;
        }
        
        $('ul#schools').slideDown();
    }

    function setSchool(id) {
        WTHelper.fn_setCookie('sPref', id, 30);
        window.location='./';
    }
    
    function searchSchools(query) {
        query = query.toLowerCase();
        var $schools = $('ul#schools').children('li');
        $schools.filter(function() {
            if (query === $(this).text().toLowerCase() || query === $(this).attr('id').toLowerCase()) {
                setSchool($(this).attr('id'));
                return;
            }
            $(this).remove();
        });
    }
    
    bindDOM();
});
