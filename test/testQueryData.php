<?php
 	header('Access-Control-Allow-Origin: *');

	if (isset($_POST)) {
		echo "post is set\n";
		if (isset($_POST["date"])) {
			echo "-date is set\n";
			foreach ($_POST["date"] as $entry) {
				echo "--=".$entry."\n";
			}
		}
		if (isset($_POST["comp"])) {
			echo "-comp is set = ".$_POST["comp"]."\n";
		}
		if (isset($_POST["inc"])) {
			echo "-inc is set = ".$_POST["inc"]."\n";
		}
		if (isset($_POST["coor"])) {
			echo "-coor is set\n";
			foreach ($_POST["coor"] as $entry) {
				echo "--=".$entry."\n";
			}
		}
		if (isset($_POST["day"])) {
			echo "-day is set = ".$_POST["day"]."\n";
		}
		if (isset($_POST["time"])) {
			echo "-time is set\n";
			foreach ($_POST["time"] as $entry) {
				echo "--=".$entry."\n";
			}
		}
	}
	foreach($_POST as $row) {
		if(is_array($row)) {
			foreach($row as $r) {
				echo $r;
			}
		} else {
			echo $row;
		}
	}

?>	