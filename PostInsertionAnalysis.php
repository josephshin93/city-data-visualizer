<?php
    //time the script
    $startTime = microtime(true);
    //open up database
    class MyDB extends SQLite3 {
        function __construct() {
            $this->open('Test.db'); //need to change to Boston_Crime_Database.db once backend is complete
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
                if(!$ret){ continue; }
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
        $fp = fopen('monthly_totals.json', 'w');
        fwrite($fp, $monthlyTotalsjson);
        fclose($fp);
        $duplicatesjson = json_encode($duplicates);
        $fp = fopen('duplicates.json', 'w');
        fwrite($fp, $duplicatesjson);
        fclose($fp);
        $incidentFrequenciesjson = json_encode($incidentFrequencies);
        $fp = fopen('incident_frequency.json', 'w');
        fwrite($fp, $incidentFrequenciesjson);
        fclose($fp);
        $districtFrequenciesjson = json_encode($districtFrequencies);
        $fp = fopen('district_frequency.json', 'w');
        fwrite($fp, $districtFrequenciesjson);
        fclose($fp);
        $shootingFrequenciesjson = json_encode($shootingFrequencies);
        $fp = fopen('shooting_frequency.json', 'w');
        fwrite($fp, $shootingFrequenciesjson);
        fclose($fp);
        $dayWeekFrequenciesjson = json_encode($dayWeekFrequencies);
        $fp = fopen('day_week_frequency.json', 'w');
        fwrite($fp, $dayWeekFrequenciesjson);
        fclose($fp);
        $ucrFrequenciesjson = json_encode($ucrFrequencies);
        $fp = fopen('ucr_frequency.json', 'w');
        fwrite($fp, $ucrFrequenciesjson);
        fclose($fp);
        $streetFrequenciesjson = json_encode($streetFrequencies);
        $fp = fopen('street_frequency.json', 'w');
        fwrite($fp, $streetFrequenciesjson);
        fclose($fp);
    }
    function printResults(){
        //print all results of frequencies/totals
        global $monthlyTotals, $duplicates, $incidentFrequencies, $districtFrequencies, $shootingFrequencies, $dayWeekFrequencies, $ucrFrequencies, $streetFrequencies;
        echo "Monthly Totals:\n";
        foreach($monthlyTotals as $k_month => $v_total){
            echo " >>> ".$k_month." had ".$v_total." incidents\n";
        }
        echo "Duplicates:\n";
        echo " >>> total count: ".count($duplicates)."\n";
        echo "Incident Frequencies:\n";
        foreach($incidentFrequencies as $k_incident => $v_total){
            echo " >>> ".$k_incident." occurred ".$v_total." times\n";
        }
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
        foreach($streetFrequencies as $k_street => $v_total){
            echo " >>> ".$v_total." incidents occurred on ".$k_street."\n";
        }
    }

    createJsons();
    printResults();
    
    $timeElapsed = microtime(true) - $start;
    $timeElapsed = $timeElapsed/1000; //get the seconds of the script runtime
    echo "This script took ".$timeElasped."seconds to run\n"; 
?>