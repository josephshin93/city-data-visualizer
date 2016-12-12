<?php
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
    $standardsjson = file_get_contents("incident_standard.json");
    $standards = json_decode($standardsjson, true);

    //create month table
    function createTable($database, $year, $month){
        $ret = $database->exec(
            "CREATE TABLE IF NOT EXISTS ".$year.$month." (
                id INT PRIMARY KEY,
                incident TEXT,
                incident_description TEXT,
                district TEXT,
                longitude REAL, 
                latitude REAL,
                year INT, 
                month INT,
                day INT,
                hour INT,
                minute INT,
                day_of_week TEXT,
                ucr TEXT,
                street TEXT,
                shooting TEXT
            )"
        );
        if (!$ret) { echo $database->lastErrorMsg(); }
    }
    //insert valid entries into tables while handling duplicates (same ids)
    function insertEntryIntoTable($database, $y, $m, $id, $incident, $incidentDescription, $district, $longitude, $latitude, $year, $month, $day, $hour, $minute, $dayWeek, $ucr, $street, $shooting){
        //address duplicates
        $ret = $database->query("SELECT * FROM ".$y.$m." WHERE id LIKE '%".$id."%'");
        $unique = 0;
        while($retArray = $ret->fetchArray(SQLITE3_ASSOC)){ //iterate through matching entries
            if($retArray['incident_description'] != $incidentDescription){
                $unique++; //if entry is not a duplicate, then increment unique identifier
            }
        }
        if($unique > 0){
            $id = $id.".".$unique; //apply the unique identifier to id if it qualifies   
        }
        $ret = $database->exec(
            "INSERT OR IGNORE INTO ".$y.$m."(id, incident, incident_description, district, longitude, latitude, year, month, day, hour, minute, day_of_week, ucr, street, shooting) VALUES('$id', '$incident', '$incidentDescription', '$district', '$longitude', '$latitude', '$year', '$month', '$day', '$hour', '$minute', '$dayWeek', '$ucr', '$street', '$shooting')"
        );
        if(!$ret){ echo $database->lastErrorMsg(); }
    }
    //extract relevant data from old system (2012 July - 2015 May)
    function extractDataOld($entry, $year, $month){
        global $standards;
        $data = array();
        array_push($data, $entry['compnos']);
        if(isset($standards[$entry['incident_type_description']])){
            array_push($data, $standards[$entry['incident_type_description']]);
        }else{
            array_push($data, $entry['incident_type_description']);
        }
        array_push($data, $entry['incident_type_description']);
        if(isset($entry['reptdistrict'])){
            array_push($data, $entry['reptdistrict']);
        }else{
            array_push($data, "NA");
        }
        array_push($data, $entry['location']['coordinates'][0]);
        array_push($data, $entry['location']['coordinates'][1]);
        array_push($data, $year);
        array_push($data, $month);
        array_push($data, substr($entry['fromdate'], 8, 2));
        array_push($data, substr($entry['fromdate'], 11, 2));
        array_push($data, substr($entry['fromdate'], 14, 2));
        array_push($data, $entry['day_week']);
        if(isset($entry['ucrpart'])){
            if($entry['ucrpart'] == "Part three"){ //standardize ucr naming of part three
                array_push($data, "Part Three");
            }else{
                array_push($data, $standards[$entry['ucrpart']]);
            }
        }else{
            array_push($data, "NA");   
        }
        if(isset($entry['streetname'])){
            array_push($data, $entry['streetname']);
        }else{
            array_push($data, "NA"); 
        }
        array_push($data, $entry['shooting']);
        return $data;
    }
    //extract relevant data from new system (2015 July - 2016 December)
    function extractDataNew($entry, $year, $month){
        global $standards;
        $data = array();
        array_push($data, $entry['incident_number']);
        if(isset($standards[$entry['offense_code_group']])){
            array_push($data, $standards[$entry['offense_code_group']]);
        }else{
            array_push($data, $entry['offense_code_group']);
        }
        array_push($data, $entry['offense_description']);
        if(isset($entry['district'])){
            array_push($data, $entry['district']);
        }else{
            array_push($data, "NA");
        }
        array_push($data, $entry['location']['coordinates'][0]);
        array_push($data, $entry['location']['coordinates'][1]);
        array_push($data, $year);
        array_push($data, $month);
        array_push($data, substr($entry['occurred_on_date'], 8, 2));
        array_push($data, substr($entry['occurred_on_date'], 11, 2));
        array_push($data, substr($entry['occurred_on_date'], 14, 2));
        array_push($data, $entry['day_of_week']);
        if(isset($entry['ucr_part'])){
            if($entry['ucr_part'] == "Part three"){ //standardize ucr naming of part three
                array_push($data, "Part Three");
            }else{
                array_push($data, $standards[$entry['ucr_part']]);
            }
        }else{
            array_push($data, "NA");
        }
        if(isset($entry['street'])){
            $entry['street'] = str_replace("'", ".", $entry['street']); //apostrophe char was causing problems so it was turned into a period
            array_push($data, $entry['street']);
        }else{
            array_push($data, "NA");
        }
        if(isset($entry['shooting'])){
            array_push($data, "Yes");
        }else{
            array_push($data, "No");
        }
        return $data;
    }

    
    foreach ($yearsArray as $keyy => $y) {
        foreach ($monthsArray as $keym => $m) {
            $entryData = array();
            if($keyy == "2012" && ($keym == "1" || $keym == "2" || $keym == "3" || $keym == "4" || $keym == "5" || $keym == "6")){
                continue; //no data for months before 2012 July
            }elseif($keyy == "2015" && $keym == "6"){
                //handle data for 2015 June - new and old fields are both used here
                echo "Overlap > ".$y.$m."\n";
                createTable($db, $y, $m);
                $datajson = file_get_contents("june_overlap.json");
                $data = json_decode($datajson, true);
                $c = $nl = 0;
                foreach($data as $entry){
                    if(isset($entry['old'])){
                        if($entry['old']['location']['coordinates'][1] > 0.0){ //filter out entries with no location
                            $entryData = extractDataOld($entry['old'], ((int)$keyy), ((int)$keym));
                        }else{ $nl++; }
                    }else{
                        if(isset($entry['lat'])){ //filter out entries with no location
                            $entryData = extractDataNew($entry, ((int)$keyy), ((int)$keym));
                        }else{ $nl++; }
                    }
                    if(isset($entryData[14])){
                        insertEntryIntoTable($db, $y, $m, $entryData[0], $entryData[1], $entryData[2], $entryData[3], $entryData[4], $entryData[5], $entryData[6], $entryData[7], $entryData[8], $entryData[9], $entryData[10], $entryData[11], $entryData[12], $entryData[13], $entryData[14]);
                        $c++;
                    }
                }
                echo " >>> ".$nl." entries for ".$keyy." ".$m." did not have a valid location\n";
                echo " >>> ".$c." entries for ".$keyy." ".$m." has been submitted for insertion to its table\n\n";
            }elseif(($keyy == "2015" && ($keym == "7" || $keym == "8" || $keym == "9" || $keym == "10" || $keym == "11" || $keym == "12")) || ($keyy == "2016")){
                //handle data from new system (2015 July - 2016 December)
                echo "New System > ".$y.$m."\n";
                createTable($db, $y, $m);
                $datajson = file_get_contents('https://data.cityofboston.gov/resource/29yf-ye7n.json?year='.$keyy.'&month='.$keym.'&$limit=9000');
                $data = json_decode($datajson, true);
                $c = $nl = 0;
                foreach($data as $entry){
                    if(isset($entry['lat'])){ //filter out entries with no location
                        $entryData = extractDataNew($entry, ((int)$keyy), ((int)$keym));
                    }else{ $nl++; }
                    if(isset($entryData[14])){
                        insertEntryIntoTable($db, $y, $m, $entryData[0], $entryData[1], $entryData[2], $entryData[3], $entryData[4], $entryData[5], $entryData[6], $entryData[7], $entryData[8], $entryData[9], $entryData[10], $entryData[11], $entryData[12], $entryData[13], $entryData[14]);
                        $c++;
                    }
                }
                echo " >>> ".$nl." entries for ".$keyy." ".$m." did not have a valid location\n";
                echo " >>> ".$c." entries for ".$keyy." ".$m." has been submitted for insertion to its table\n\n";
            }else{
                //handle data from legacy(old) system (2012 July - 2015 May)
                echo "Old System > ".$y.$m."\n";
                createTable($db, $y, $m);
                $datajson = file_get_contents('https://data.cityofboston.gov/resource/ufcx-3fdn.json?year='.$keyy.'&month='.$keym.'&$limit=9000');
                $data = json_decode($datajson, true);
                echo " >>> data for ".$keyy." ".$m." received\n";
                $c = $nl = 0;
                foreach($data as $entry){
                    if($entry['location']['coordinates'][1] > 0.0){ //filter out entries with no location
                        $entryData = extractDataOld($entry, ((int)$keyy), ((int)$keym));
                    }else{ $nl++; }
                    if(isset($entryData[14])){
                        insertEntryIntoTable($db, $y, $m, $entryData[0], $entryData[1], $entryData[2], $entryData[3], $entryData[4], $entryData[5], $entryData[6], $entryData[7], $entryData[8], $entryData[9], $entryData[10], $entryData[11], $entryData[12], $entryData[13], $entryData[14]);
                        $c++;
                    }
                }
                echo " >>> ".$nl." entries for ".$keyy." ".$m." did not have a valid location\n";
                echo " >>> ".$c." entries for ".$keyy." ".$m." has been submitted for insertion to its table\n\n";
            }
        }
    }

    
?>