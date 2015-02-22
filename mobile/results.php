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
    </head>
    <body>
        <header class="wt-header">
            <div id="wt-header-menu" class="wt-header-content-wrapper">
                <i class="wt-header-content fa fa-2x fa-navicon"></i>
            </div>
            <div class="wt-header-content-wrapper wt-header-offset-logo">
                <img class="wt-header-content pure-img" style="max-width: 100%;" src="/mobile/img/emblem/wtlogo_dark.png" />
            </div>
            <nav class="wt-header-content-wrapper wt-header-nav">
                <div class="wt-header-navitem-wrapper results-searchtoggle">
                    <i class="wt-header-content fa fa-lg fa-search"></i>
                </div>
            </nav>
        </header>
        <section class="pure-form results-filter">
            <div class="results-categories">
            </div>
            <div class="results-search" style="display: none">
                <input class="results-searchfield" type="search" placeholder="Search for an item..." /><!--
             --><button class="results-searchbutton pure-button">Go!</button>
            </div>
        </section>
        <nav class="wt-sidebar" style="overflow-y: scroll;">
            <?php if($loggedIn) {
                if(file_exists(ROOTPATH."user_images/uid_".$_SESSION["user_id"].".jpg")) {
                    $image = ROOTPATH.'/user_images/uid_'.$_SESSION["user_id"].'.jpg';
                } else {
                    $image = ROOTPATH.'/colorful/Anonymous_User.jpg';
                }
                
               echo('
               <div class="user-info">
                   <div class="user-name">'.$_SESSION["username"].'</div>
                   <div class="user-img">
                       <img class="pure-img" src="'.$image.'"/>
                   </div>
               </div>
               <div id="LogoutBtn" class="wt-sidebar-content">
                    <a>Logout</a>
               </div>
               <div id="PostBtn" class="wt-sidebar-content">
                    <a>Add a Post</a>
               </div>
               <div id="MessageBtn" class="wt-sidebar-content">
                    <a>Messages</a>
               </div>
               <!--
               <div id="PanelBtn" class="wt-sidebar-content">
                    <a>User CP</a>
               </div>
               -->');
            } else {
               echo('
               <div id="LoginBtn" class="wt-sidebar-content">
                   <a>Login</a>
               </div>');
            } ?>
            <div id="ChangeSchoolBtn" class="wt-sidebar-content">
                <a>Change School</a>
            </div>
            <!--
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
            </div>
            -->
        </nav>
        <section class="wt-content">
            <div class="wt-results-wrapper">
                <main class="wt-results">
                </main>
            </div>
        </section>
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', 'UA-42896980-1', 'auto');
          ga('send', 'pageview');
	     </script>
        <script>window.school="<?php echo $schoolTextId; ?>";</script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/mobile/js/vendor/jquery-1.10.2.min.js"><\/script>')</script>
        <script src="/mobile/js/helper.js"></script>
        <script src="/mobile/js/services.js"></script>
        <script src="/mobile/js/results.js"></script>
    </body>
</html>
