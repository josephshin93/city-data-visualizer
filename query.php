<?php
    header('Access-Control-Allow-Origin: *'); //prevent server from blocking requests

    //open up database
    class MyDB extends SQLite3 {
        function __construct() {
            $this->open('Boston_Crime_Database.db');
        }
    }
    $db = new MyDB();
    if (!$db) { echo $db->lastErrorMsg(); }
  
    $return = array();
    if(isset($_POST["month"])){
         $ret = $db->query("SELECT * FROM ".$_POST["month"]);
         while($retArray = $ret->fetchArray(SQLITE3_ASSOC)){
             array_push($return, $retArray);
        }
    }
    echo json_encode($return);
?>