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
    $incidentFrequency = array();

    $ret = $db->exec(
        "CREATE TABLE IF NOT EXISTS IncidentFrequency (
            name TEXT,
            occurances INT,
            color TEXT
        )"
    );
    if (!$ret) { echo $db->lastErrorMsg(); }
    
    foreach ($yearsArray as $keyy => $y) {
        foreach ($monthsArray as $keym => $m) {
            if ($y == "Twelve" && ($m == "January" || $m == "February" || $m == "March" || $m == "April" || $m == "May" || $m == "June")) {
                continue;
            } elseif ($y == "Fifteen" && ($m == "September" || $m == "October" || $m == "November" || $m == "December")) {
                continue;
            } else {
                $dataJson = file_get_contents("https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=".$keyy."&month=".$keym.'&$limit=8300');
                $dataArray = json_decode($dataJson, true);
                foreach ($dataArray as $entry) {
                    if (isset($incidentFrequency[$entry["incident_type_description"]])) {
                        $incidentFrequency[$entry["incident_type_description"]]++;
                    } else {
                        $incidentFrequency[$entry["incident_type_description"]] = 1;
                    }
                }
            }
        }
    }

    foreach ($incidentFrequency as $keyname => $occurvalue) {
        echo $keyname." occured ".$occurvalue." times<br>";
        $ret = $db->exec(
            "INSERT OR IGNORE INTO IncidentFrequency (name, occurances) VALUES('$keyname', '$occurvalue')"
        );
        if (!$ret) { echo $db->lastErrorMsg(); }
    }

    $incfreqjson = json_encode($incidentFrequency);

    $fp = fopen('incident_freq.json', 'w');
    fwrite($fp, $incfreqjson);
    fclose($fp);
    
    echo $incfreqjson;

?>