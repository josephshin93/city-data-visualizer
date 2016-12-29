<?php
    //time the script
    $startTime = microtime(true);
    //open up database
    class MyDB extends SQLite3 {
        function __construct() {
            $this->open('Boston_Crime_Database.db');
        }
    }
    $db = new MyDB();
    if (!$db) { echo $db->lastErrorMsg(); }
    
    $yearsArray = array(
        "2012" => "Twelve",
        "2013" => "Thirteen",
        "2014" => "Fourteen",
        "2015" => "Fifteen",
        "2016" => "Sixteen"
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
    $monthlyTotals = array();
    $duplicates = array();
    $incidentFrequencies = array();
    //next four values are only printed, not exported as json or inserted into table
    $maxLatitude = 0;
    $maxLongitude = -72;
    $minLatitude = 43;
    $minLongitude = 0;
    $districtFrequencies = array();
    $shootingFrequencies = array();
    $dayWeekFrequencies = array();
    $ucrFrequencies = array();
    $streetFrequencies = array();
    
    //iterate through all database tables
    foreach ($yearsArray as $keyy => $y) {
        foreach ($monthsArray as $keym => $m) {
            $monthTotal = 0;
            if($keyy == "2012" && ($keym == "1" || $keym == "2" || $keym == "3" || $keym == "4" || $keym == "5" || $keym == "6")){
                continue; //no data for months before 2012 July   
            }else{
                //handle data from entire database
                $ret = $db->query("SELECT * FROM ".$y.$m);
                if(!$ret){ continue; } //skip if month doesn't exist
                while($retArray = $ret->fetchArray(SQLITE3_ASSOC)){
                    $monthTotal++;
                    if(strpos($retArray['id'], ".") !== false){
                        array_push($duplicates, $retArray['id']);
                    }
                    if(isset($incidentFrequencies[$retArray['incident']])){
                        $incidentFrequencies[$retArray['incident']]++;
                    }else{
                        $incidentFrequencies[$retArray['incident']] = 1;
                    }
                    if($retArray['latitude'] > $maxLatitude){
                        $maxLatitude = $retArray['latitude'];
                    }
                    if($retArray['longitude'] > $maxLongitude){
                        $maxLongitude = $retArray['longitude'];
                    }
                    if($retArray['latitude'] < $minLatitude){
                        $minLatitude = $retArray['latitude'];
                    }
                    if($retArray['longitude'] < $minLongitude){
                        $minLongitude = $retArray['longitude'];
                    }
                    if(isset($districtFrequencies[$retArray['district']])){
                        $districtFrequencies[$retArray['district']]++;
                    }else{
                        $districtFrequencies[$retArray['district']] = 1;
                    }
                    if(isset($shootingFrequencies[$retArray['shooting']])){
                        $shootingFrequencies[$retArray['shooting']]++;
                    }else{
                        $shootingFrequencies[$retArray['shooting']] = 1;
                    }
                    if(isset($dayWeekFrequencies[$retArray['day_of_week']])){
                        $dayWeekFrequencies[$retArray['day_of_week']]++;
                    }else{
                        $dayWeekFrequencies[$retArray['day_of_week']] = 1;
                    }
                    if(isset($ucrFrequencies[$retArray['ucr']])){
                        $ucrFrequencies[$retArray['ucr']]++;
                    }else{
                        $ucrFrequencies[$retArray['ucr']] = 1;
                    }
                    if(isset($streetFrequencies[$retArray['street']])){
                        $streetFrequencies[$retArray['street']]++;
                    }else{
                        $streetFrequencies[$retArray['street']] = 1;
                    }
                }
                $monthlyTotals[$y.$m] = $monthTotal;
            }
        }
    }

    function createJsons(){
        //create json files of all results
        global $monthlyTotals, $duplicates, $incidentFrequencies, $districtFrequencies, $shootingFrequencies, $dayWeekFrequencies, $ucrFrequencies, $streetFrequencies;
        $monthlyTotalsjson = json_encode($monthlyTotals);
        $fp = fopen('freqtot/monthly_totals.json', 'w');
        fwrite($fp, $monthlyTotalsjson);
        fclose($fp);
        $duplicatesjson = json_encode($duplicates);
        $fp = fopen('freqtot/duplicates.json', 'w');
        fwrite($fp, $duplicatesjson);
        fclose($fp);
        $incidentFrequenciesjson = json_encode($incidentFrequencies);
        $fp = fopen('freqtot/incident_frequency.json', 'w');
        fwrite($fp, $incidentFrequenciesjson);
        fclose($fp);
        $districtFrequenciesjson = json_encode($districtFrequencies);
        $fp = fopen('freqtot/district_frequency.json', 'w');
        fwrite($fp, $districtFrequenciesjson);
        fclose($fp);
        $shootingFrequenciesjson = json_encode($shootingFrequencies);
        $fp = fopen('freqtot/shooting_frequency.json', 'w');
        fwrite($fp, $shootingFrequenciesjson);
        fclose($fp);
        $dayWeekFrequenciesjson = json_encode($dayWeekFrequencies);
        $fp = fopen('freqtot/day_week_frequency.json', 'w');
        fwrite($fp, $dayWeekFrequenciesjson);
        fclose($fp);
        $ucrFrequenciesjson = json_encode($ucrFrequencies);
        $fp = fopen('freqtot/ucr_frequency.json', 'w');
        fwrite($fp, $ucrFrequenciesjson);
        fclose($fp);
        $streetFrequenciesjson = json_encode($streetFrequencies);
        $fp = fopen('freqtot/street_frequency.json', 'w');
        fwrite($fp, $streetFrequenciesjson);
        fclose($fp);
    }
    function addTables(){
        //create tables for frequencies/totals and add values
        global $db, $monthlyTotals, $duplicates, $incidentFrequencies, $districtFrequencies, $shootingFrequencies, $dayWeekFrequencies, $ucrFrequencies, $streetFrequencies;
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS MonthTotals (month TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($monthlyTotals as $k_month => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO MonthTotals (month, total) VALUES('$k_month', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS Duplicates (id INT PRIMARY KEY)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($duplicates as $ids){
            $ret = $db->exec("INSERT OR IGNORE INTO Duplicates (id) VALUES('$ids')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS IncidentFrequency (name TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($incidentFrequencies as $k_incident => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO IncidentFrequency (name, total) VALUES('$k_incident', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS DistrictFrequency (district TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($districtFrequencies as $k_district => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO DistrictFrequency (district, total) VALUES('$k_district', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS ShootingFrequency (shooting TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($shootingFrequencies as $k_shot => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO ShootingFrequency (shooting, total) VALUES('$k_shot', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS DayWeekFrequency (day TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($dayWeekFrequencies as $k_day => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO DayWeekFrequency (day, total) VALUES('$k_day', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS UCRFrequency (ucr TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($ucrFrequencies as $k_ucr => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO UCRFrequency (ucr, total) VALUES('$k_ucr', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
        $ret = $db->exec("CREATE TABLE IF NOT EXISTS StreetFrequency (street TEXT PRIMARY KEY, total INT)");
        if(!$ret){ echo $db->lastErrorMsg(); }
        foreach($streetFrequencies as $k_street => $v_total){
            $ret = $db->exec("INSERT OR IGNORE INTO StreetFrequency (street, total) VALUES('$k_street', '$v_total')");
            if(!$ret){ echo $db->lastErrorMsg(); }
        }
    }
    function printResults(){
        //print all results of frequencies/totals
        global $monthlyTotals, $duplicates, $incidentFrequencies, $maxLatitude, $maxLongitude, $minLatitude, $minLongitude, $districtFrequencies, $shootingFrequencies, $dayWeekFrequencies, $ucrFrequencies, $streetFrequencies;
        echo "Monthly Totals:\n";
        foreach($monthlyTotals as $k_month => $v_total){
            echo " >>> ".$k_month." had ".$v_total." incidents\n";
        }
        echo "Duplicates:\n";
        echo " >>> total count: ".count($duplicates)."\n";
        echo "Incident Frequencies:\n";
        echo " >>> there are a total of ".count($incidentFrequencies)." incident categories\n";
        foreach($incidentFrequencies as $k_incident => $v_total){
            echo " >>> ".$k_incident." occurred ".$v_total." times\n";
        }
        echo "Location Bounds:\n";
        echo " >>> Max Latitude: ".$maxLatitude." Max Longitude: ".$maxLongitude."\n";
        echo " >>> Min Latitude: ".$minLatitude." Min Longitude: ".$minLongitude."\n";
        echo "District Frequencies:\n";
        echo "there are ".count($districtFrequencies)." districts\n";
        foreach($districtFrequencies as $k_district => $v_total){
            echo " >>> there were ".$v_total." incidents in district ".$k_district."\n";
        }
        echo "Shooting Frequencies:\n";
        foreach($shootingFrequencies as $k_shot => $v_total){
            echo " >>> ".$v_total." ".$k_shot."\n";
        }
        echo "Day of Week Frequencies:\n";
        foreach($dayWeekFrequencies as $k_day => $v_total){
            echo " >>> there were a total of ".$v_total." ".$k_day." incidents\n";
        }
        echo "UCR Frequencies:\n";
        foreach($ucrFrequencies as $k_ucr => $v_total){
            echo " >>> there were a total of ".$v_total." ".$k_ucr." crimes\n";
        }
        echo "Street Frequencies:\n";
        echo count($streetFrequencies)." streets were reported\n";
//        foreach($streetFrequencies as $k_street => $v_total){
//            echo " >>> ".$v_total." incidents occurred on ".$k_street."\n";
//        }
    }

    createJsons();
    addTables();
    printResults();
    
    $timeElapsed = microtime(true) - $startTime; //get the seconds of the script runtime
//    $timeElapsed = $timeElapsed*1000; //get the milliseconds of the script runtime
    echo "================ This script took ".$timeElapsed."seconds to run ================\n"; 
?>