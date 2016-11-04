/*global L*/
/*jslint vars: true, plusplus: true, devel: true, nomen: true, indent: 4, maxerr: 50 */
/*global define, $, jQuery, alert */

var centerOfBostonCoordinates = [42.3601, -71.0589],
    pjfanLocationCoordinates = [42.3629, -71.0890],
    mymap = L.map('mapid').setView(centerOfBostonCoordinates, 11),
    pjfanMarkerIcon = L.icon({
        iconUrl: 'icons/pjfan_marker_icon.png',
        shadowURL: 'icons/pjfan_marker_icon_shadow.png',
        iconSize: [80, 107],
        shadowSize: [53, 53],
        iconAnchor: [40, 106],
        shadowAnchor: [1, 1]
    });

function getMapboxApiKey() {
    "use strict";
    $.ajax({
        type: "GET",
        url: "http://localhost:8080",
        dataType: "text",
        crossDomain: true,
        success: function (data) {
            L.mapbox.accessToken = data;
            L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/dark-v9/tiles/256/{z}/{x}/{y}?access_token=' + data, {
                attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
                accessToken: data
            }).addTo(mymap);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
        }
    });
}
getMapboxApiKey();

var pjfanLocationMarker = L.marker(pjfanLocationCoordinates, {icon: pjfanMarkerIcon}).addTo(mymap);
pjfanLocationMarker.bindPopup("<b>Peter \"BBOY\" Fan's Location</b>");

//var request = new XMLHttpRequest();
//request.open("GET", "https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday", false);
//request.send();
//var bosThanksgivingCrime = JSON.parse(request.responseText),
//    bosThanksgivingCrimeIncidentALocation = "",
//    i;
//for (i = 0; i < bosThanksgivingCrime.length; i = i + 1) {
//    bosThanksgivingCrimeIncidentALocation = bosThanksgivingCrimeIncidentALocation + bosThanksgivingCrime[i].incident_type_description + "@ location: " + bosThanksgivingCrime[i].location.coordinates + "<br>";
//    
//    var tempMarker = L.marker([bosThanksgivingCrime[i].location.coordinates[1], bosThanksgivingCrime[i].location.coordinates[0]]).addTo(mymap);
//    tempMarker.bindPopup(bosThanksgivingCrime[i].incident_type_description);
//}
//document.getElementById("rspTxt").innerHTML = bosThanksgivingCrimeIncidentALocation;



/*
    $_POST = {
        "year": "year",
        "month": "month",
        "comp": "compnos",
        "inc": "incident type description",
        "coor": ["longitude", "latitude"]
        "day": "day of month",
        "time": ["hour", "minute", "second"]
    }
*/

function queryDatabase() {
    "use strict";
    var form = document.getElementById("bostonCrimeQuery"),
        i,
        queryData = {};
    if ($("input[name=year]").val() !== "") {
        queryData.year = $("input[name=year]").val();
    }
    if ($("input[name=month]").val() !== "") {
        queryData.month = $("input[name=month]").val();
    }
    if ($("input[name=comp]").val() !== "") {
        queryData.comp = $("input[name=comp]").val();
    }
    if ($("input[name=inc]").val() !== "") {
        queryData.inc = $("input[name=inc]").val();
    }
    if ($("input[name=coor-lon]").val() !== "" || $("input[name=coor-lat]").val() !== "") { queryData.coor = []; }
    if ($("input[name=coor-lon]").val() !== "") {
        queryData.coor.push($("input[name=coor-lon]").val());
    }
    if ($("input[name=coor-lat]").val() !== "") {
        queryData.coor.push($("input[name=coor-lat]").val());
    }
    if ($("input[name=day]").val() !== "") {
        queryData.day = $("input[name=day]").val();
    }
    if ($("input[name=time-hour]").val() !== "" || $("input[name=time-min]").val() !== "" || $("input[name=time-sec]").val() !== "") { queryData.time = []; }
    if ($("input[name=time-hour]").val() !== "") {
        queryData.time.push($("input[name=time-hour]").val());
    }
    if ($("input[name=time-min]").val() !== "") {
        queryData.time.push($("input[name=time-min]").val());
    }
    if ($("input[name=time-sec]").val() !== "") {
        queryData.time.push($("input[name=time-sec]").val());
    }
    
    console.log(queryData);
    
    $.ajax({
        type: "POST",
        url: "http://localhost:8100",
        data: queryData,
        crossDomain: true,
        success: function (data) {
            console.log(data);
            document.getElementById("rspTxt").innerHTML = data;
            console.log("all data received");
            //now do work with the data
            var j = JSON.parse(data),
                i,
                comp = 0,
                inc = 1,
                lon = 2,
                lat = 3,
                day = 4,
                hour = 5,
                min = 6,
                sec = 7;
            for (i = 0; i < j.length; i++) {
//                console.log(j[i][lat] + ", " + j[i][lon]);
                //do stuff with each entry of the query that was made
                var m = L.marker([j[i][lat], j[i][lon]]).addTo(mymap);
                m.bindPopup("At time " + j[i][hour] + ":" + j[i][min] + ":" + j[i][sec]);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
        }
    });
}













