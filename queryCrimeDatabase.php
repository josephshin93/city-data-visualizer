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
                $masterQueryString = substr($masterQueryString, 0, -8);
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
        $tables = array();
        $query = "";
        if (isset($_POST["year"]) && isset($_POST["month"])) {
            array_push($qDateYears, $_POST["year"]);
            array_push($qDateMonths, $_POST["month"]);
            array_push($tables, $yearsArray[$qDateYears[0]].$monthsArray[$qDateMonths[0]]);
            $query = "SELECT * FROM ".$tables[0].obtainWhereClause();
        } elseif (isset($_POST["year"])) {
            array_push($qDateYears, $_POST["year"]);
            foreach ($monthsArray as $key => $m) {
                array_push($qDateMonths, $key);
                array_push($tables, $yearsArray[$qDateYears[0]].$m);
            } 
            $query = queryMultipleTablesString(obtainWhereClause(), $qDateYears[0], "");
        } elseif (isset($_POST["month"])) {
            array_push($qDateMonths, $_POST["month"]);
            foreach ($yearsArray as $key => $y) {
                array_push($qDateYears, $key);
                array_push($tables, $y.$monthsArray[$qDateMonths[0]]);
            }
            $query = queryMultipleTablesString(obtainWhereClause(), "", $qDateMonths[0]);
        } else {
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
            $ret = $db->exec(
                "CREATE TABLE IF NOT EXISTS ".$yearsArray[$years].$monthsArray[$months]." (
                    compnos INT PRIMARY KEY,
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
            //get data for all months necessary for query ******* may be inefficent look into creating a more efficient metho
            $dataJson = file_get_contents("https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=".$years."&month=".$months);
            $dataArray = json_decode($dataJson, true);
            //enter all relevent data into table
            foreach ($dataArray as $entry) { 
                $comp = $entry['compnos'];
                $inc = $entry['incident_type_description'];
                $lx = $entry['location']['coordinates'][0];
                $ly = $entry['location']['coordinates'][1];
                $td = substr($entry['fromdate'], 8, 2);
                $th = substr($entry['fromdate'], 11, 2);
                $tm = substr($entry['fromdate'], 14, 2);
                $ts = substr($entry['fromdate'], 17, 2);
                $ret = $db->exec(
                    "INSERT OR IGNORE INTO ".$yearsArray[$years].$monthsArray[$months]."(compnos, incident, longitude, latitude, day, hour, minute, second) VALUES('$comp', '$inc', '$lx', '$ly', '$td', '$th', '$tm', '$ts')"
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
        $queryArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
    }
    echo json_encode($queryArray);
//    

?>
