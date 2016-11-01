<!doctype html>
<html class="no-js" lang="">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="apple-touch-icon" href="apple-touch-icon.png">
        <!-- Place favicon.ico in the root directory -->

        <link rel="stylesheet" href="css/normalize.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="js/vendor/modernizr-2.8.3.min.js"></script>
        
        <!-- leaflet files -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.0-rc.3/dist/leaflet.css"/>
        <script src="https://unpkg.com/leaflet@1.0.0-rc.3/dist/leaflet.js"></script>
        
        <!-- mapbox files -->
        <script src='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.standalone.js'></script>
        <link href='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.css' rel='stylesheet' />
    </head>
    <body>
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <!-- Add your site or application content here -->
        <h2>Boston Crime Report Data Visualizer</h2>
        <div id="mapid"></div>
        <h4 class="subheader">Boston 2012 Thanksgiving Crime Report</h4>
        <p id="rspTxt"></p>
        <?php
            echo "<h4>--- php code output ---</h4>";
            class MyDB extends SQLite3 {
                function __construct() {
                    $this->open('CDV_Database.db');
                }
            }
            $db = new MyDB();
            if(!$db){
                echo $db->lastErrorMsg();
            } else {
                echo "Opened database successfully\n";
            }
            echo "<h4>--- end of php code output ---</h4>";
        ?>
        
        

        <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
        <script>window.jQuery || document.write('<script src="js/vendor/jquery-1.12.0.min.js"><\/script>')</script>
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>

        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
            function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
            e=o.createElement(i);r=o.getElementsByTagName(i)[0];
            e.src='https://www.google-analytics.com/analytics.js';
            r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
            ga('create','UA-XXXXX-X','auto');ga('send','pageview');
        </script>
    </body>
</html>
