/*global L*/
/*jslint vars: true, plusplus: true, devel: true, nomen: true, indent: 4, maxerr: 50 */
/*global define, $, jQuery, alert */

var centerOfBostonCoordinates = [42.312626, -71.071870], //center was calculated from the bounds of hand designed grid
    pjfanLocationCoordinates = [42.3629, -71.0890],
    mymap = L.map('mapid').setView(centerOfBostonCoordinates, 12),
    markers,
    pjfanMarkerIcon = L.icon({
        iconUrl: 'icons/pjfan_marker_icon.png',
        iconSize: [80, 107],
        iconAnchor: [40, 106]
    }),
    opMarkerIcon = L.icon({
        iconUrl: 'icons/op-marker.png',
        iconSize: [12, 12],
        iconAnchor: [6, 6]
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
            markers = new L.FeatureGroup();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
        }
    });
}
getMapboxApiKey();


//var pjfanLocationMarker = L.marker(pjfanLocationCoordinates, {icon: pjfanMarkerIcon}).addTo(mymap);
//pjfanLocationMarker.bindPopup("<b>Peter \"BBOY\" Fan's Location</b>");

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

var grid = [],
    minLatitude = 42.227953,
    minLongitude = -71.190647,
    width = 0.237555,
    height = 0.169347,
    increment = 0.008467;
for (i = 0; i < (height - increment); i += increment) {
    for (j = 0; j < (width - increment); j += increment) {
        var gridboxCoordinates = [];
        gridboxCoordinates.push([minLatitude + i, minLongitude + j]);
        gridboxCoordinates.push([minLatitude + increment + i, minLongitude + j]);
        gridboxCoordinates.push([minLatitude + increment + i, minLongitude + increment + j]);
        gridboxCoordinates.push([minLatitude + i, minLongitude + increment + j]);
        gridbox = L.polygon(
            gridboxCoordinates,
            {
                fillColor: "#ff0000", //red
                stroke: false,
                fillOpacity: 0
            }
        ).addTo(mymap);
        grid.push(gridbox);
    }
}

var years = ["2012", "2013", "2014", "2015", "2016"],
    months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    displayMonths = [],
    postMonths = [],
    queryResults,
    queryTotal,
    i,
    j;
    
for (i in years) {
    for (j in months) {
        if (!(years[i] === "2012" && (months[j] === "January" || months[j] === "February" || months[j] === "March" || months[j] === "April" || months[j] === "May" || months[j] === "June"))) {
            displayMonths.push(months[j] + " " + years[i]);
            if (years[i] === "2012") {
                postMonths.push("Twelve" + months[j]);
            } else if (years[i] === "2013") {
                postMonths.push("Thirteen" + months[j]);
            } else if (years[i] === "2014") {
                postMonths.push("Fourteen" + months[j]);
            } else if (years[i] === "2015") {
                postMonths.push("Fifteen" + months[j]);
            } else if (years[i] === "2016") {
                postMonths.push("Sixteen" + months[j]);
            }
        }
    }
}
function query(post) {
    "use strict";
    console.log(post);
    var queryResults = {
            'category': {},
            'districts': {},
            'shooting': {
                'No': 0,
                'Yes': 0
            },
            'hours': {},
            'dayWeek': {
                'Sunday': 0,
                'Monday': 0,
                'Tuesday': 0,
                'Wednesday': 0,
                'Thursday': 0,
                'Friday': 0,
                'Saturday': 0
            },
            'ucr': {
                'Part One': 0,
                'Part Two': 0,
                'Part Three': 0,
                'Other': 0,
                'NA': 0
            },
            'streets': {}
        },
        queryTotal = 0;
    $.ajax({
        type: "POST",
        url: "http://localhost:8000",
        data: post,
        crossDomain: true,
        success: function (data) {
            data = JSON.parse(data);
            queryTotal = data.length;
            console.log(queryTotal + " entries received");
            var gridIndices = [];
            for (i = 0; i < data.length; i++) {
                if (queryResults.category[data[i].incident] === undefined) {
                    queryResults.category[data[i].incident] = 1;
                } else {
                    queryResults.category[data[i].incident]++;
                }
                if (queryResults.districts[data[i].district] === undefined) {
                    queryResults.districts[data[i].district] = 1;
                } else {
                    queryResults.districts[data[i].district]++;
                }
                if (queryResults.streets[data[i].street] === undefined) {
                    queryResults.streets[data[i].street] = 1;
                } else {
                    queryResults.streets[data[i].street]++;
                }
                if (queryResults.hours[data[i].hour] === undefined) {
                    queryResults.hours[data[i].hour] = 1;
                } else {
                    queryResults.hours[data[i].hour]++;
                }
                queryResults.shooting[data[i].shooting]++;
                queryResults.dayWeek[data[i].day_of_week]++;
                queryResults.ucr[data[i].ucr]++;
                
                var row = Math.ceil((data[i].latitude - minLatitude) / increment),
                    column = Math.ceil((data[i].longitude - minLongitude) / increment),
                    gridIndex = (((row - 1) * 28) + column - 1);
                if (gridIndices[gridIndex] === undefined) {
                    gridIndices[gridIndex] = 1;
                } else {
                    gridIndices[gridIndex]++;
                }
            }
            console.log(gridIndices);
            var tot = 0;
            for (i = 0; i < gridIndices.length; i++) {
                if (gridIndices[i] !== undefined) {
//                    console.log("index:" + i + " sum:" + gridIndices[i] + " opacity:" + (gridIndices[i] / 250));
                    grid[i].setStyle({fillOpacity: (gridIndices[i] / 250)});
                }
            }
            console.log("All entries processed");
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
        }
    });
    console.log(queryResults);
}
function showValue(newValue) {
    "use strict";
    document.getElementById("slider_value").innerHTML = displayMonths[newValue];
    var postData = {
        month: postMonths[newValue]
    };
    query(postData);
}

