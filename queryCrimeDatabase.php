<? php
    header('Access-Control-Allow-Origin: *'); //prevent server from blocking requests

    //open up database
    class MyDB extends SQLite3 {
        function __construct() {
            $this->open('Boston_Crime_Database.db');
        }
    }
    $db = new MyDB();
    if (!$db) { echo $db->lastErrorMsg(); }
    
    //receive POST data and parse it
    $qDate = array();
    $qCompnos = "";
    $qIncident = "";
    $qCoordinates = array();
    $qDay = "";
    $qTime = array();
    function parseCrimeQueryData () {
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
                if (isset($_POST["inc"])) {
                    $qIncident = $_POST["inc"];
                    if ($whereClause == "") {
                        $whereClause .= " WHERE INCIDENT='".$qIncident."' ";
                    }
                }
                if (isset($_POST["coor"])) {
                    $qCoordinates = $_POST["coor"];
                    if ($whereClause == "") {
                        $whereClause .= " WHERE longitude=".$qCoordinates[0]." AND latitude=".$qCoordinates[1]." ";
                    } else {
                        $whereClause .= "AND WHERE longitude=".$qCoordinates[0]." AND latitude=".$qCoordinates[1]." ";
                    }
                }
                if (isset($_POST["day"])) {
                    $qTime = $_POST["time"];
                    if ($whereClause == "") {
                        $whereClause .= " WHERE day=".$qDay." ";
                    } else {
                        $whereClause .= "AND WHERE day=".$qDay." ";
                    }
                }
                if (isset($p["time"])) {
                    $qTime = $p["time"];
                    if ($whereClause == "") {
                        $whereClause .= " WHERE hour=".$qTime[0]." AND minute=".$qTime[1]." AND second=".$qTime[2]." ";
                    } else {
                        $whereClause .= "AND WHERE hour=".$qTime[0]." AND minute=".$qTime[1]." AND second=".$qTime[2]." ";
                    }
                }
            }
            return $whereClause;
        }
        $table = "";
        $queryString = "";
        if (isset($_POST["date"])) {
            $qDate = $_POST["date"];
            $table .= $yearsArray[$qDate[0]].$monthsArray[$qDate[1]];
            $queryString = "SELECT * FROM ".$table.obtainWhereClause();
        } else {
            $queryString = queryAllTables(obtainWhereClause());
        }
        return array("table" => $table, "query" => $queryString);
    }
    $q = parseCrimeQueryData();

    //check if specified table exists, if not then create it
    $ret = $db->exec("SELECT name FROM sqlite_master WHERE type='table' AND name='".$q["table"]."'");
    if (!$ret) { //if specified table doesn't exist
        $ret = $db->exec(
            "CREATE TABLE IF NOT EXISTS ".$q["table"]." (
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
        //get data from open data api
        $dataJson = file_get_contents("https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=".$qDate[0]."&month=".$qDate[1]);
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
            $ret = $db->exec("INSERT INTO ".$q["table"]."(compnos, incident, longitude, latitude, day, hour, minute, second) VALUES('$comp', '$inc', '$lx', '$ly', '$td', '$th', '$tm', '$ts')");
            if (!$ret) { echo $db->lastErrorMsg(); }
        }
    }
    
    //query the database
    $queryResults = $db->query($queryString);
    $queryArray = array();
    while ($row = $queryResults->fetchArray()) {
        $queryArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7]);
    }
    echo json_encode($queryArray);
    

?>
