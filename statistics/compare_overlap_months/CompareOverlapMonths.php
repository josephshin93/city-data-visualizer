<?php
/*
	There are two systems that contain data for the city of boston. Among the city of boston
	police report data, three months exist in both systems (June 2015, July 2015, and August 2015). This script queries APIs of both systems and compares the data of those same months
	to see there is any data overlap or data that exists in only one of the systems.
*/



	function compareMonth($month_number, $month_name){
		$oldMonthjson = file_get_contents('https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2015&month='.$month_number.'&$limit=8500');
		$oldMonth = json_decode($oldMonthjson, true);

		$newMonthjson = file_get_contents('https://data.cityofboston.gov/resource/29yf-ye7n.json?year=2015&month='.$month_number.'&$limit=8500');
		$newMonth = json_decode($newMonthjson, true);

		$setMatch = array();
		$setNoMatch = array();
		$unsetMatch = array();
		$unsetNoMatch = array();

		foreach($oldMonth as $oldEntry){
			$setM = 0; $unsetM = 0;
			foreach($newMonth as $newEntry){
				if(!isset($oldEntry['compnos'])){
					$unsetM++;
					if($oldEntry['location']['coordinates'][0] == $newEntry['location']['coordinates'][0] && $oldEntry['location']['coordinates'][1] == $newEntry['location']['coordinates'][1] && $oldEntry['fromdate'] == $newEntry['occurred_on_date']){
						array_push($unsetMatch, array("old" => $oldEntry, "new" => $newEntry));
						break;
					}elseif($unsetM == count($newMonth)){
						array_push($unsetNoMatch, array("old" => $oldEntry));
					}
				}else{
					$setM++;
					if($oldEntry['compnos'] == $newEntry['incident_number'] || $oldEntry['compnos'] == substr($newEntry['incident_number'], 1) || substr($oldEntry['compnos'], 1) == $newEntry['incident_number']){
						array_push($setMatch, array("old" => $oldEntry, "new" => $newEntry));
						break;
					}elseif($setM == count($newMonth)){
						array_push($setNoMatch, array("old" => $oldEntry));
					}
				}
			}
		}

		$monthComparison = array("old_month_total" => count($oldMonth), "new_month_total" => count($newMonth), "set_match" => $setMatch, "set_no_match" => $setNoMatch, "unset_match" => $unsetMatch, "unset_no_match" => $unsetNoMatch);
		$monthComparisonjson = json_encode($monthComparison);
		$fp = fopen($month_name.'_comparison.json', 'w');
	    fwrite($fp, $monthComparisonjson);
	    fclose($fp);
	}

//	compareMonth("6", "june");
//	compareMonth("7", "july");
//	compareMonth("8", "august");


    /*
		STRUCTURE OF COMPARISON ANAYLSIS JSON

		"old_month_total" => total count of old month entries,
		"new_month_total" => total count of new month entries,
		"set_match" => array(
			array(
				"old" => old entry,
				"new" => new entry
			)
		),
		"set_no_match" => array(
			array(
				"old" => old entry
			) 
		),
		"unset_match" => array(
			array(
				"old" => old entry,
				"new" => new entry
			)
		),
		"unset_no_match" => array(
			array(
				"old" => old entry
			) 
		)
	*/

	function analyzeMonthComparisons($month_name){
		$monthjson = file_get_contents($month_name."_comparison.json");
		$month = json_decode($monthjson, true);
		$equals = array();
		$notequals = array();

		echo $month_name." 2015 => total old: ".$month['old_month_total']." total new: ".$month['new_month_total']."\n";
		echo "----------------------------------+\n";
		echo "Compnos Set:      ".(count($month['set_match'])+count($month['set_no_match']))."            |\n";
		echo ">>>> Matches:     ".count($month['set_match'])."             |\n>>>> Non Matches: ".count($month['set_no_match'])."            |\n";
		echo "Compnos Not set:  ".(count($month['unset_no_match'])+count($month['unset_match']))."            |\n";
		echo ">>>> Matches:     ".count($month['unset_match'])."             |\n>>>> Non Matches: ".count($month['unset_no_match'])."            |\n";
		echo "----------------------------------+\n";

		echo "Matches with a set compnos:\n";
		foreach($month['set_match'] as $entry){
			if($entry['old']['fromdate'] == $entry['new']['occurred_on_date'] && strtolower($entry['old']['incident_type_description']) == strtolower($entry['new']['offense_code_group'])){
				continue;
			}elseif($entry['old']['fromdate'] == $entry['new']['occurred_on_date']){
				echo " > incident: ".$entry['old']['incident_type_description']." ?= ".$entry['new']['offense_code_group']."\n";
			}else{
				echo " > incident: ".$entry['old']['incident_type_description']." ?= ".$entry['new']['offense_code_group']."\n   datetime: ".$entry['old']['fromdate']." ?= ".$entry['new']['occurred_on_date']."\n";
			}
		}
		echo "Matches with an unset compnos:\n";
		foreach($month['unset_match'] as $entry){
			if($entry['old']['fromdate'] == $entry['new']['occurred_on_date'] && strtolower($entry['old']['incident_type_description']) == strtolower($entry['new']['offense_code_group'])){
				continue;
			}elseif($entry['old']['fromdate'] == $entry['new']['occurred_on_date']){
				echo " > incident: ".$entry['old']['incident_type_description']." ?= ".$entry['new']['offense_code_group']."\n";
			}else{
				echo " > incident: ".$entry['old']['incident_type_description']." ?= ".$entry['new']['offense_code_group']."\n   datetime: ".$entry['old']['fromdate']." ?= ".$entry['new']['occurred_on_date']."\n";
			}
		}
	}

//	 analyzeMonthComparisons("june");
//	 analyzeMonthComparisons("july");
//	 analyzeMonthComparisons("august");
    
    //2015June is the only month that needs to be combined with the new dataset, the others have an empty 'compnos' field so they cannot be identified
    $juneOverlap = array();
    $juneComparejson = file_get_contents("june_comparison.json");
    $juneCompare = json_decode($juneComparejson, true);
    $newJunejson = file_get_contents('https://data.cityofboston.gov/resource/29yf-ye7n.json?year=2015&month=6&$limit=8500');
    $newJune = json_decode($newJunejson, true);
    foreach($juneCompare['set_no_match'] as $entry){
        array_push($juneOverlap, $entry);
    }
    foreach($newJune as $entry){
        array_push($juneOverlap, $entry);
    }
    echo "JUNE OVERLAP COUNT = ".count($juneOverlap)."\n";
    $juneOverlapjson = json_encode($juneOverlap);
    $fp = fopen('june_overlap.json', 'w');
    fwrite($fp, $juneOverlapjson);
    fclose($fp);
    
?>