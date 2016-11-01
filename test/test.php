<!doctype html>
<html>
    <head>
        <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
    </head>
    <body>
        <? php
            header('Access-Control-Allow-Origin: *');
        ?>
        <h2>TEST PAGE</h2>
<!--
        <div id="data" style="font-size: 8px; column-count: 3"></div>
         <script>
             var cdRequest = new XMLHttpRequest();
             cdRequest.open("GET", "https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday", false);
             cdRequest.send();
             var bosTGCrimesJSON = JSON.parse(cdRequest.responseText),
                 bosTGCrimesText = "",
                 i;
             for (i = 0; i < bosTGCrimesJSON.length; i = i + 1) {
                bosTGCrimesText = bosTGCrimesText + bosTGCrimesJSON[i].incident_type_description + "@ location: " + bosTGCrimesJSON[i].location.coordinates + "<br>";
             }
             document.getElementById("data").innerHTML = bosTGCrimesText;
         </script>
-->
        
        <h4>THANKSGIVING CRIMES QUERY</h4>
        <form id="tgcQuery" action="queryDatabase.php" method="post">
            Note: leave unknown fields blank<br>
            Compnos: <input type="text" name="comp"><br>
            Incident Description: <input type="text" name="inc"><br>
            Location: <input type="text" name="lon" value="Longitude"> , <input type="text" name="lat" value="Latitude"><br>
            Time: <input type="text" name="hour" value="Hour"> : <input type="text" name="min" value="Minute"> : <input type="text" name="sec" value="Second"><br><br>
            <input type="submit" value="Query">
        </form>
        <br><br><br>
        <div id="results" style="font-size: 10px; color: green">right here...</div>
        
        <script>
//            var comp,
//                inc,
//                lon,
//                lat,
//                hour,
//                min,
//                sec;
//            $.ajax({
//                type: "POST",
//                url: "http://localhost:8100",
////                contentType: "application/x-www-form-urlencoded; charset=UTF-8",
//                data: {
//                    'compnos' : comp,
//                    'incident' : inc,
//                    'longitude' : lon,
//                    'latitude' : lat,
//                    'hour' : hour,
//                    'minute' : min,
//                    'second' : sec
//                },
//                crossDomain: true,
//                success: function(data) {
//                    console.log(data);
//                    
//                },
//                error: function(xhr, desc, err) {
//                    console.log(xhr);
//                    console.log("Details: " + desc + "\nError:" + err);
//                }
//            });
            
            //write code to handle form ajax
        </script>
        
        <h2>END OF TEST PAGE</h2>
        
        
        
    </body>
</html>