<!doctype html>
<html>
    <head>
    </head>
    <body>
        <h2>TEST PAGE</h2>
        <div id="data" style="font-size: 8px; column-count: 3"></div>
         <script>
             var cdRequest = new XMLHttpRequest();
             cdRequest.open("GET", "https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday", false);
             cdRequest.send();
             var bosTGCrimesJSON = JSON.parse(cdRequest.responseText),
                 bosTGCrimesText = "",
                 i;
             for (i = 0; i < bosTGCrimesJSON.length; i = i + 1) {
                bosTGCrimesText = bosTGCrimesText + bosTGCrimesJSON[i].incident_type_description + "@ location: " + bosTGCrimesJSON[i].location.coordinates + "<br>";
             }
             document.getElementById("data").innerHTML = bosTGCrimesText;
         </script>
        
        <?php
            echo "<h4>--- php start ---</h4>";
             
            //open up database
            class MyDB extends SQLite3 {
                function __construct() {
                    $this->open('test_database.db');
                }
            }
            $db = new MyDB();
            if (!$db) {
                echo $db->lastErrorMsq();
            } else {
                echo "Opened database successfully";
                echo "<br />";
            }
            echo "<br />";
         
            //create a table for the 2012 thanksgiving crime data
            $db->exec('DROP TABLE IF EXISTS ThanksgivingCrimes');
            $ret = $db->exec(
                'CREATE TABLE ThanksgivingCrimes (
                    COMPNOS INT PRIMARY KEY,
                    INCIDENT TEXT,
                    LOCATION_X REAL,
                    LOCATION_Y REAL,
                    TIME_HOUR INT,
                    TIME_MIN INT,
                    TIME_SEC INT
                )'
            );
            if(!$ret) {
                echo $db->lastErrorMsg();
            } else {
                echo "Table Created Successfully";
                echo "<br />";
            }
            echo "<br />";
         
            //GET information from city of boston data api
            $jsonFile = file_get_contents("https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday");
            $jsonArray = json_decode($jsonFile, true);
            foreach ($jsonArray as $value) {
                //gathering  and echoing all useful info from crime reports
                $comp = $value['compnos'];
                $inc = $value['incident_type_description'];
                $lx = $value['location']['coordinates'][0];
                $ly = $value['location']['coordinates'][1];
                $th = substr($value['fromdate'], 11, 2);
                $tm = substr($value['fromdate'], 14, 2);
                $ts = substr($value['fromdate'], 17, 2);
                //inserting all gathered info into sqlite db
                $ret2 = $db->exec("INSERT INTO ThanksgivingCrimes(COMPNOS, INCIDENT, LOCATION_X, LOCATION_Y, TIME_HOUR, TIME_MIN, TIME_SEC) VALUES('$comp', '$inc', '$lx', '$ly', '$th', '$tm', '$ts')");
                if(!$ret2) {
                    echo $db->lastErrorMsg();
                } else {
                    
                }
            }
            echo "Insert Successful";
            echo "<br />";
            
            //query the entire thanksgivingcrimes table
            $dbh = new PDO('sqlite:test_database.db');
            $resultArray = array();
            foreach ($dbh->query('SELECT * FROM ThanksgivingCrimes') as $row) {
                $resultArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
            }
            $queryJson = json_encode($resultArray);
        
            echo "<h4>--- php end ---</h4>";
         ?>
        
        <h4>QUERY RESULTS WILL RENDER HERE</h4>
        <div id="results" style="font-size: 10px; color: green">right here...</div>
        
        <script>
            //get query from php code and then put it into a comprehensible string
            //method example - "http://p2p.wrox.com/php-faqs/11606-q-how-do-i-pass-php-variables-javascript.html"
            var query = <?php echo $queryJson ?>;
            var parsedQuery = "",
                i;
            for (i = 0; i < query.length; i++) {
                parsedQuery += "COMPNOS: " + query[i][0] + " - Incident \'" + query[i][1] + "\' occured at coordinates (" + query[i][2] + ", " + query[i][3] + ") and at time " + query[i][4] + ":" + query[i][5] + ":" + query[i][6] + "<br />";
            }
            document.getElementById("results").innerHTML = parsedQuery;
        </script>
        
        <h2>END OF TEST PAGE</h2>
        
        
        
    </body>
</html>