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
    $qDateYears = array();
    $qDateMonths = array();
    $qCompnos = "";
    $qIncident = "";
    $qCoordinates = array();
    $qDay = "";
    $qTime = array();
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
    function parseCrimeQueryData () {
        global $qDateYears, $qDateMonths, $yearsArray, $monthsArray;
        function queryMultipleTablesString ($whereClause, $selectYear, $selectMonth) {
            global $yearsArray, $monthsArray, $qDateYears, $qDateMonths;
            $masterQueryString = "";
            foreach ($yearsArray as $keyy => $y) {
                foreach ($monthsArray as $keym => $m) {
                    if ($y == "Twelve" && ($m == "January" || $m == "February" || $m == "March" || $m == "April" || $m == "May" || $m == "June")) {
                        continue;
                    } elseif ($y == "Fifteen" && ($m == "September" || $m == "October" || $m == "November" || $m == "December")) {
                        continue;
                    } else {
                        if ($selectYear == "" && $selectMonth == "") {
                            $masterQueryString .= "SELECT * FROM ".$y.$m.$whereClause." UNION ";
                        } elseif ($selectMonth == "" && $keyy == $selectYear) {
                            $masterQueryString .= "SELECT * FROM ".$y.$m.$whereClause." UNION ";
                        } elseif ($selectYear == "" && $keym == $selectMonth) {
                            $masterQueryString .= "SELECT * FROM ".$y.$m.$whereClause." UNION ";
                        }
                    }
                }
            }
            if (substr($masterQueryString, -7, -1) == " UNION") {
                $masterQueryString = substr($masterQueryString, 0, -7);
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
                if (isset($_POST["coor"])) { //does not handle cases where only one coordinate is entered
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
                if (isset($_POST["time"])) { //does not handle cases where one or two parts of the time is entered
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
        $tables = array();
        $query = "";
        if (isset($_POST["year"]) && isset($_POST["month"])) { //both year and month given
            array_push($qDateYears, $_POST["year"]);
            array_push($qDateMonths, $_POST["month"]);
            array_push($tables, $yearsArray[$qDateYears[0]].$monthsArray[$qDateMonths[0]]);
            $query = "SELECT * FROM ".$tables[0].obtainWhereClause();
        } elseif (isset($_POST["year"])) { //only year is given
            array_push($qDateYears, $_POST["year"]);
            foreach ($monthsArray as $key => $m) {
                if ($_POST["year"] == "2012" && ($key == "1" || $key == "2" || $key == "3" || $key == "4" || $key == "5" || $key == "6")) {
                    continue;
                } elseif ($_POST["year"] == "2015" && ($key == "9" || $key == "10" || $key == "11" || $key == "12")) {
                    continue;
                } else {
                    array_push($qDateMonths, $key);
                    array_push($tables, $yearsArray[$qDateYears[0]].$m);
                }
            } 
            $query = queryMultipleTablesString(obtainWhereClause(), $qDateYears[0], "");
        } elseif (isset($_POST["month"])) {
            array_push($qDateMonths, $_POST["month"]);
            foreach ($yearsArray as $key => $y) { //only month is given
                if ($key == "2012" && ($_POST["month"] == "1" || $_POST["month"] == "2" || $_POST["month"] == "3" || $_POST["month"] == "4" || $_POST["month"] == "5" || $_POST["month"] == "6")) {
                    continue;
                } elseif ($key == "2015" && ($_POST["month"] == "9" || $_POST["month"] == "10" || $_POST["month"] == "11" || $_POST["month"] == "12")) {
                    continue;
                } else {
                    array_push($qDateYears, $key);
                    array_push($tables, $y.$monthsArray[$qDateMonths[0]]);
                }
            }
            $query = queryMultipleTablesString(obtainWhereClause(), "", $qDateMonths[0]);
        } else { //neither year nor month is given
            foreach ($yearsArray as $keyy => $y) {
                array_push($qDateYears, $keyy);
                foreach ($monthsArray as $m) {
                    array_push($tables, $y.$m);
                }
            }
            foreach ($monthsArray as $keym => $m) {
                array_push($qDateMonths, $keym);
            }
            $query = queryMultipleTablesString(obtainWhereClause(), "", "");
        }
        return array("tables" => $tables, "query" => $query);
    }
    $query = parseCrimeQueryData();
//    echo "tables: <br>";
//    foreach ($query["tables"] as $t) {
//        echo $t."<br>";
//    }
//    echo "<br>";
//    echo "where clause: ";
//    echo $query["query"]."<br>";
//    echo "<br>";
//    echo "years: <br>";
//    foreach ($qDateYears as $qy) {
//        echo $qy."<br>";   
//    }
//    echo "months: <br>";
//    foreach ($qDateMonths as $qm) {
//        echo $qm."<br>";
//    }
//    echo "<br>";

    //loop through all necessary tables for the query
    foreach ($qDateYears as $years) {
        foreach ($qDateMonths as $months) {
            //add the table if it has not yet been created and skip months not in the crime database
            if (($years == "2012" && ($months == "1" || $months == "2" || $months == "3" || $months == "4" || $months == "5" || $months == "6")) || ($years == "2015" && ($months == "9" || $months == "10" || $months == "11" || $months == "12"))) {
                continue;
            }
        
            //add in a way to check if a table exists or not to make code more efficient
            
            $ret = $db->exec(
                "CREATE TABLE IF NOT EXISTS ".$yearsArray[$years].$monthsArray[$months]." (
                    compnos INT PRIMARY KEY,
                    incident TEXT,
                    longitude REAL,
                    latitude REAL,
                    year INT,
                    month INT,
                    day INT,
                    hour INT,
                    minute INT,
                    second INT
                )"
            );
            if (!$ret) { echo $db->lastErrorMsg(); }
            //get data for all months necessary for query ******* may be inefficent look into creating a more efficient method
            //limit set at 1000 (lowest month - Aug2015:1860, highest month - May2014:8222)
            $dataJson = file_get_contents('https://data.cityofboston.gov/resource/ufcx-3fdn.json?year='.$years.'&month='.$months.'&$limit=1000');
            $dataArray = json_decode($dataJson, true);
            //enter all relevent data into table
            foreach ($dataArray as $entry) { 
                $comp = $entry['compnos'];
                $inc = $entry['incident_type_description'];
                $lx = $entry['location']['coordinates'][0];
                $ly = $entry['location']['coordinates'][1];
                $ty = substr($entry['fromdate'], 0, 4);
                $tmonth = substr($entry['fromdate'], 5, 2);
                $td = substr($entry['fromdate'], 8, 2);
                $th = substr($entry['fromdate'], 11, 2);
                $tmin = substr($entry['fromdate'], 14, 2);
                $ts = substr($entry['fromdate'], 17, 2);
                $ret = $db->exec(
                    "INSERT OR IGNORE INTO ".$yearsArray[$years].$monthsArray[$months]."(compnos, incident, longitude, latitude, year, month, day, hour, minute, second) VALUES('$comp', '$inc', '$lx', '$ly', '$ty', '$tmonth', '$td', '$th', '$tmin', '$ts')"
                );
                if (!$ret) { echo $db->lastErrorMsg(); }
            }
        }
    }

    
    //query the database
    $dbh = new PDO('sqlite:CDV_Database.db'); //we have to use PDO object to query for some reason - need to research...
    $queryResults = $dbh->query($query["query"]);
    $queryArray = array();
    foreach ($queryResults as $row) {
        $queryArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]);
    }
    echo json_encode($queryArray);   

?>