var WTServices = (function() {
    /******************************* Private *********************************/
    var api_url = '/api2/';
    
    // General purpose cache
    var cache = {};

    // Used by service_getPostsByCategory for state tracking.
    var previousCategory = '', 
        previousQuery = '', 
        offset = 0;
    
    /******************************** Public **********************************/
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
        if (!cache.hasOwnProperty('WTCategories')) {
            return $.ajax({
                type: 'POST',
                url: api_url,
                data: {
                    intent: 'getCategories'
                },
                dataType: 'JSON'
            }).done(function(response) {
                cache['WTCategories'] = response;
            });
        } else {
            return $.Deferred().resolve(cache['WTCategories']);
        }
    };
    
    var getPostsByCategory = function(schoolId, category, opts) {
        var school = schoolId,
            category = category,
            query = opts.query || '',
            amount = opts.amount || undefined,
            sort = opts.sort || undefined,
            resetFlag = opts.resetOffset || false;

        var filterChanged = (
            category !== previousCategory ||
            query !== previousQuery ||
            (previousQuery.length === 0 && query.length > 0)
        );

        if (resetFlag || filterChanged) { offset = 0; }
        
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
    
    var createPost = function(category, title, author, details, price, location, tags, isbn) {
        return $.ajax({
            type: 'POST',
            url: api_url,
            data: {
                intent: 'addPost',
                cat: category,
                title: title,
                author: author,
                details: details,
                price: price,
                location: location,
                tags: tags,
                isbn: isbn
            },
            dataType: 'HTML'
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
    
    return {
        service_login: login,
        service_registerUser: registerUser,
        service_verifyUser: verifyUser,
        service_logout: logout,
        service_getUsername: getUsername,
        service_getUserAvatar: getUserAvatar,
        service_getCategories: getCategories,
        service_getPostsByCategory: getPostsByCategory,
        service_createPost: createPost,
        service_createMessageThread: createMessageThread,
        service_getUserMessageThreads: getUserMessageThreads,
        service_getThreadByID: getThreadByID,
        service_appendMessageToThread: sendMessage,
        service_getNewMessagesInThread: getNewMessagesInThread
    };
    
})();