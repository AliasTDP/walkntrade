$('document').ready(function() {
    var browser = window.navigator.userAgent,
        searchResults = {};
    
    if (browser.indexOf('MSIE') > -1 || browser.indexOf('Trident') > -1) {
        // Dirty IE hack
        $('.wt-content-landing-inner').css('vertical-align', 'middle');
    }
    
    function bindDOM() {
        
        $('#searchSchools').on('keyup', function(event) {
            if (event.keyCode !== 13) { // Enter
                getSchools($(this).val());
                return;
            }
            
            searchSchools($(this).val());
        });
        
        $('#submitSchool').on('click', function(event) {
            event.preventDefault();
            event.stopImmediatePropagation();
            searchSchools($('#searchSchools').val());
        });
    }
    
    function getSchools(query) {
        if (searchResults.query === undefined) {
            $.ajax({
                'type': 'POST',
                'crossDomain': true,
                'url': '/api2/',
                'data': {
                    'intent': 'getSchools',
                    'query': query
                },
                'dataType': 'JSON',
                'statusCode': {
                    200: function(response) {
                        $('#schools').empty();
                        for (var i = 0; i < response.payload.length; i++) {
                            var optionHTML = '<option id="' + response.payload[i].textId + '"' +
                                                     'value="' + response.payload[i].name + '">'; 
                            $(optionHTML).appendTo('#schools');
                            }
                        searchResults[query] = $('#schools').children();
                    }
                }
            });
        }
        else {
            $('#schools').append(searchResults[query]);
        }
    }
    
    function setSchool(id) {
        window.location.href='https://walkntrade.com/schools/' + id;
    }
    
    function searchSchools(query) {
        var $schools = $('#schools').children('option');
        $schools.filter(function() {
            if (query === $(this).attr('value')) {
                setSchool($(this).attr('id'));
            }
        });
    }
    
    bindDOM();
});