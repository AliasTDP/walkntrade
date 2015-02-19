var WTServices = (function() {
    /******************************* Private *********************************/
    var api_url = '/api2/';
    
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
    
    return {
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
    };
    
})();