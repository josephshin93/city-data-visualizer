<?php
    header('Access-Control-Allow-Origin: *');

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
        //database opened up successfully
    }

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
        //table created successfully
    }

    //GET information from city of boston data api
    $jsonFile = file_get_contents("https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday");
    $jsonArray = json_decode($jsonFile, true);
    $insertSuccessCount = 0;
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
            $insertSuccessCount++;
        }
    }
    if($insertSuccessCount == 1116) {
        //inserts were successful
    } else {
        //inserts were not all successful
    }

    //query the entire thanksgivingcrimes table
    $dbh = new PDO('sqlite:test_database.db');
    $resultArray = array();
    foreach ($dbh->query('SELECT * FROM ThanksgivingCrimes') as $row) {
        $resultArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
    }
    $queryJson = json_encode($resultArray);

    //get post data and query database
    function parsePostData($postArray) {
        //parse post data and then form a valid WHERE statement
    }
    $queryResult = array();
    foreach ($dbh->query('SELECT * FROM ThanksgivingCrimes WHERE COMPNOS='.$_POST["compnos"]) as $row) {
        $queryResult[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
    }
    echo json_encode($queryResult);
?>
