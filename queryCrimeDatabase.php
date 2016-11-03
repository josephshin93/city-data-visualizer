<?php
    header('Access-Control-Allow-Origin: *'); //prevent server from blocking requests

    //open up database
    class MyDB extends SQLite3 {
        function __construct() {
            $this->open('CDV_Database.db'); //need to change to Boston_Crime_Database.db once backend is complete
        }
    }
    $db = new MyDB();
    if (!$db) { echo $db->lastErrorMsg(); }
    

    //receive POST data and parse it
    $qDateYear = "";
    $qDateMonth = "";
    $qCompnos = "";
    $qIncident = "";
    $qCoordinates = array();
    $qDay = "";
    $qTime = array();
    function parseCrimeQueryData () {
        global $qDateYear, $qDateMonth;
        $yearsArray = array(
            "2012" => "Twelve",
            "2013" => "Thirteen",
            "2014" => "Fourteen",
            "2015" => "Fifteen"
        );
        $monthsArray = array(
            "1" => "January",
            "2" => "February",
            "3" => "March",
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December"
        );
        function queryAllTablesString ($whereClause) {
            global $yearsArray, $monthsArray;
            $masterQueryString = "";
            foreach ($yearsArray as $y) {
                foreach ($monthsArray as $m) {
                    if ($y == "Twelve" && ($m == "January" || $m == "February" || $m == "March" || $m == "April" || $m == "May" || $m == "June")) {
                        continue;
                    } elseif ($y == "Fifteen" && ($m == "September" || $m == "October" || $m == "November" || $m == "December")) {
                        continue;
                    } elseif ($y == "Fifteen" && $m == "August") {
                        $masterQueryString .= "SELECT * FROM ".$y.$m.$whereClause;
                    } else {
                        $masterQueryString .= "SELECT * FROM ".$y.$m.$whereClause." UNION ";
                    }
                }
            }
            return $masterQueryString;
        }
        function obtainWhereClause () {
            $whereClause = "";
            if (isset($_POST["comp"])) {
                $qCompnos = $_POST["comp"];
                $whereClause = " WHERE compnos=".$qCompnos." ";
            } else {
                if (isset($_POST["inc"]) || isset($_POST["coor"]) || isset($_POST["day"]) || isset($_POST["time"])) {
                    $whereClause .= " WHERE ";
                }
                if (isset($_POST["inc"])) {
                    $qIncident = $_POST["inc"];
                    if ($whereClause == " WHERE ") {
                        $whereClause .= "incident='".$qIncident."' ";
                    }
                }
                if (isset($_POST["coor"])) {
                    $qCoordinates = $_POST["coor"];
                    if ($whereClause == " WHERE ") {
                        $whereClause .= "longitude=".$qCoordinates[0]." AND latitude=".$qCoordinates[1]." ";
                    } else {
                        $whereClause .= "AND longitude=".$qCoordinates[0]." AND latitude=".$qCoordinates[1]." ";
                    }
                }
                if (isset($_POST["day"])) {
                    $qDay = $_POST["day"];
                    if ($whereClause == " WHERE ") {
                        $whereClause .= "day=".$qDay." ";
                    } else {
                        $whereClause .= "AND day=".$qDay." ";
                    }
                }
                if (isset($_POST["time"])) {
                    $qTime = $_POST["time"];
                    if ($whereClause == " WHERE ") {
                        $whereClause .= "hour=".$qTime[0]." AND minute=".$qTime[1]." AND second=".$qTime[2]." ";
                    } else {
                        $whereClause .= "AND hour=".$qTime[0]." AND minute=".$qTime[1]." AND second=".$qTime[2]." ";
                    }
                }
            }
            return $whereClause;
        }
        $table = "";
        $queryString = "";
        if (isset($_POST["date"][0]) && isset($_POST["date"][1])) {
            $qDateYear = $_POST["date"][0];
            $qDateMonth = $_POST["date"][1];
            $table .= $yearsArray[$qDateYear].$monthsArray[$qDateMonth];
            $queryString = "SELECT * FROM ".$table.obtainWhereClause();
        } else {
            $queryString = queryAllTablesString(obtainWhereClause());
        }
        $replacement = "";
        $queryString = substr($queryString, 0, -1).$replacement;   
        return array("table" => $table, "string" => $queryString);
    }
    $query = parseCrimeQueryData();
    echo $query["table"]."<br><br>".$query["string"]."<br><br>";


    //create table if it doesn't already exist
    $ret = $db->exec(
        "CREATE TABLE IF NOT EXISTS ".$query["table"]." (
            compnos INT,
            incident TEXT,
            longitude REAL,
            latitude REAL,
            day INT,
            hour INT,
            minute INT,
            second INT
        )"
    );
    if (!$ret) { echo $db->lastErrorMsg(); }
    //get data from open data api **** need to fix so that it only GETs if the table is empty
    $dataJson = file_get_contents("https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=".$qDateYear."&month=".$qDateMonth);
    $dataArray = json_decode($dataJson, true);
    foreach ($dataArray as $entry) { //enter relevent data into the table
        $comp = $entry['compnos'];
        $inc = $entry['incident_type_description'];
        $lx = $entry['location']['coordinates'][0];
        $ly = $entry['location']['coordinates'][1];
        $td = substr($entry['fromdate'], 8, 2);
        $th = substr($entry['fromdate'], 11, 2);
        $tm = substr($entry['fromdate'], 14, 2);
        $ts = substr($entry['fromdate'], 17, 2);
        //inserting all gathered info into sqlite db
        $ret = $db->exec("INSERT INTO ".$query["table"]."(compnos, incident, longitude, latitude, day, hour, minute, second) VALUES('$comp', '$inc', '$lx', '$ly', '$td', '$th', '$tm', '$ts')");
        if (!$ret) { echo $db->lastErrorMsg(); }
    }
    
    
    //query the database
    $dbh = new PDO('sqlite:CDV_Database.db'); //we have to use PDO object to query for some reason - need to research...
    $queryResults = $dbh->query($query["string"]);
    $queryArray = array();
    foreach ($queryResults as $row) {
        $queryArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
//        echo $row[0]." - ".$row[1]." - ".$row[2]." - ".$row[3]." - ".$row[4]." - ".$row[5]." - ".$row[6]." - ".$row[7]."<br>";
    }
    echo json_encode($queryArray);
    

?>
