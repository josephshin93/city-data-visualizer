<?php
    $qDate = array();
    $qCompnos = "";
    $qIncident = "";
    $qCoordinates = array();
    $qTime = array();

    $p = array(
        "date" => array("2012", "11"),
        // "comp" => "120749398",
        "inc" => "PubDrink",
        // "coor" => array("-71.4", "41.2"),
        // "time" => array("12", "34", "56")
    );

	$yearsArray = array(
        "2012" => "Thanksgiving",
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
        "11" => "Crimes",
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
        global $p;
        $whereClause = "";
        if (isset($p["comp"])) {
            $qCompnos = $p["comp"];
            $whereClause = " WHERE compnos=".$qCompnos." ";
        } else {
            if (isset($p["inc"])) {
                $qIncident = $p["inc"];
                if ($whereClause == "") {
                    $whereClause .= " WHERE INCIDENT='".$qIncident."' ";
                }
            }
            if (isset($p["coor"])) {
                $qCoordinates = $p["coor"];
                if ($whereClause == "") {
                    $whereClause .= " WHERE longitude=".$qCoordinates[0]." AND latitude=".$qCoordinates[1]." ";
                } else {
                    $whereClause .= "AND WHERE longitude=".$qCoordinates[0]." AND latitude=".$qCoordinates[1]." ";
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
    if (isset($p["date"])) {
        $qDate = $p["date"];
        $table .= $yearsArray[$qDate[0]].$monthsArray[$qDate[1]];
        $queryString = "SELECT * FROM ".$table.obtainWhereClause();
    } else {
        $queryString = queryAllTablesString(obtainWhereClause());
    }


    echo $table."<br><br><br>";
    echo $queryString."<br><br><br>";

    class MyDB extends SQLite3 {
        function __construct() {
            $this->open('test_database.db');
        }
    }
    $db = new MyDB();
    if (!$db) { echo $db->lastErrorMsg(); }

    $queryResults = $db->query($queryString);
    $queryArray = array();
    while ($row = $queryResults->fetchArray()) {
        echo "---- ".$row[0]." ".$row[1]." ".$row[2]." ".$row[3]." ".$row[4]." ".$row[5]." ".$row[6]."<br>";
        $queryArray[] = array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);
    }
    echo json_encode($queryArray);

?>