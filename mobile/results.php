<!DOCTYPE html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Walkntrade</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">

        <link rel="stylesheet" href="/mobile/css/vendor/pure-min.css">
        <link rel="stylesheet" href="/mobile/css/vendor/font-awesome.min.css">
        <link rel="stylesheet" href="/mobile/css/styles.css">
        <link rel="stylesheet" href="/mobile/css/component.css">
        <link rel="stylesheet" href="/mobile/css/results.css">
        <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Droid+Sans" type="text/css">
        <script src="/mobile/js/vendor/modernizr-2.6.2.min.js"></script>
    </head>
    <body>
        <header class="wt-header">
            <div id="wt-header-menu" class="wt-header-content-wrapper">
                <i class="wt-header-content fa fa-2x fa-navicon"></i>
            </div>
            <div class="wt-header-content-wrapper wt-header-offset-logo">
                <img class="wt-header-content pure-img" src="/mobile/img/emblem/wtlogo_dark.png" />
            </div>
            <!--
            <nav class="wt-header-content-wrapper wt-header-nav">
                <div class="wt-header-navitem-wrapper">
                    <i class="wt-header-content fa fa-2x fa-envelope"></i>
                </div>
            </nav>
            -->
        </header>
        <section class="wt-content pure-form results-filter">
            <div class="results-sort">
                <span class="fa fa-sort"></span>
            </div>
            <div class="results-categories">
            </div>
            <div class="results-searchfield" style="display: none">
                <input type="search" placeholder="Search for an item..." /><!--
             --><button class="pure-button">Go!</button>
            </div>
            <div class="results-search">
                <span class="fa fa-search"></span>
            </div>
        </section>
        <nav class="wt-sidebar">
            <div id="LoginBtn" class="wt-sidebar-content">
                <a>Login</a>
            </div>
            <div id="RegisterBtn" class="wt-sidebar-content">
                <a>Sign Up</a>
            </div>
            <div id="ChangeSchoolBtn" class="wt-sidebar-content">
                <a>Change School</a>
            </div>
            <div class="wt-sidebar-footer">
                <div>
                    <a>Terms of Service</a>
                </div>
                <div>
                    <a>Privacy Policy</a>
                </div>
                <div>
                    <a>Feedback</a>
                </div>
                <label>Copyright 2013 Walkntrade</label>
            </div>
        </nav>
        <section class="wt-content wt-content-results">
            <div class="wt-results-wrapper">
                <main class="wt-results">
                </main>
            </div>
        </section>
        <script>window.school="<?php echo $schoolTextId; ?>";</script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/mobile/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
        <script src="/mobile/js/helper.js"></script>
        <script src="/mobile/js/results.js"></script>
    </body>
</html>
