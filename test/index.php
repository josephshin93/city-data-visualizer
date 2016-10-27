<!doctype html>
<html>
    <head>
    </head>
    <body>
        <h1>Test Code</h1>
        <h3>Boston Crime Reports (Thanksgiving 2012)</h3>
<!--        <p id="bostonThanksgivingCrime"></p>-->
        <?php 

            echo "report<br>";
        
            class MyDB extends SQLite3 {
                function __construct() {
                    $this->open('test_database.db');
                }
            }
            $db = new MyDB();
            if(!$db){
                echo $db->lastErrorMsg();
            } else {
                echo "Opened database successfully\n";
            }
        
            $sql =<<<EOF
                CREATE TABLE crimes (
                    compnos INT PRIMARY KEY,
                    incident_description TEXT,
                    longitude INT,
                    latitude INT,
                    address TEXT
                );
                SELECT * from crimes;
            EOF;
        
            $ret = $db->exec($sql);
            if(!$ret){
                echo $db->lastErrorMsg();
            } else {
                echo "Table created successfully\n";
            }
            $db->close();
        
            
        ?>
            
        
        <h3>Boston Crime Reports (near Peter Fan's current location)</h3>
        <p id="bostonCrimePjfan"></p>
<!--
        <h3>Mapbox API Key</h3>
        <p id="mbKEY"></p>
-->
        
        <script>
            var request = new XMLHttpRequest();
//            request.onreadystatechange = function() {
//                if (request.readyState == 4 && request.status == 200) {
//                    callback(request.responseText);
//                }
//            }
            request.open("GET", "https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday", false);
            request.send();
            var bosThanksgivingCrimesJSON = JSON.parse(request.responseText),
                bosThanksgivingCrimes = "",
                i;
            for (i = 0; i < bosThanksgivingCrimesJSON.length; i = i + 1) {
//                console.log(bosThanksgivingCrimesJSON[i].incident_type_description)
                bosThanksgivingCrimes = bosThanksgivingCrimes + bosThanksgivingCrimesJSON[i].incident_type_description + " @ location: " + bosThanksgivingCrimesJSON[i].location.coordinates + "<br>";
//                console.log(bosThanksgivingCrimesJSON[i].location.coordinates[0] + " - " + bosThanksgivingCrimesJSON[i].location.coordinates[1])
            }
//            document.getElementById("bostonThanksgivingCrime").innerHTML = request.responseText;
//            document.getElementById("bostonThanksgivingCrime").innerHTML = bosThanksgivingCrimes;
            
            
//            request.open("GET", "https://data.cityofboston.gov/resource/ufcx-3fdn.json?compnos=130295497", false);
//            request.send();
//            var testJSON = JSON.parse(request.responseText),
//                testJSONParse,
//                i;
//            for (i = 0; i < testJSON.length; i = i + 1) {
//                console.log(testJSON[i].incident_type_description);
//            }
//            
//            document.getElementById("bostonCrimePjfan").innerHTML = request.responseText;
            
        </script>
    
    </body>
</html>