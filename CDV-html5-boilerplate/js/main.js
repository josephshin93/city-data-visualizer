/*global L*/
/*jslint vars: true, plusplus: true, devel: true, nomen: true, indent: 4, maxerr: 50 */
/*global define */

var centerOfBostonCoordinates = [42.3601, -71.0589],
    pjfanLocationCoordinates = [42.3629, -71.0890],
    mapboxAPIToken = prompt("Please enter jbshin's mapbox API token", "API Token");

var pjfanMarkerIcon = L.icon({
    iconUrl: '../pjfan_marker_icon.png',
    shadowURL: '../pjfan_marker_icon_shadow.png',
    iconSize: [60, 80],
    shadowSize: [40, 40],
    iconAnchor: [30, 79],
    shadowAnchor: [1, 1]
});

var mymap = L.map('mapid').setView(centerOfBostonCoordinates, 11);

L.mapbox.accessToken = mapboxAPIToken;
L.tileLayer('https://api.mapbox.com/styles/v1/mapbox/dark-v9/tiles/256/{z}/{x}/{y}?access_token=' + mapboxAPIToken, {
    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://mapbox.com">Mapbox</a>',
    accessToken: mapboxAPIToken
}).addTo(mymap);
var pjfanLocationMarker = L.marker(pjfanLocationCoordinates, {icon: pjfanMarkerIcon}).addTo(mymap);
pjfanLocationMarker.bindPopup("<b>Peter \"BBOY\" Fan's Location</b>");


var request = new XMLHttpRequest();
request.open("GET", "https://data.cityofboston.gov/resource/ufcx-3fdn.json?year=2012&month=11&day_week=Thursday", false);
request.send();
var bosThanksgivingCrime = JSON.parse(request.responseText),
    bosThanksgivingCrimeIncidentALocation = "",
    i;
for (i = 0; i < bosThanksgivingCrime.length; i = i + 1) {
    bosThanksgivingCrimeIncidentALocation = bosThanksgivingCrimeIncidentALocation + bosThanksgivingCrime[i].incident_type_description + "@ location: " + bosThanksgivingCrime[i].location.coordinates + "<br>";
    
    var tempMarker = L.marker([bosThanksgivingCrime[i].location.coordinates[1], bosThanksgivingCrime[i].location.coordinates[0]]).addTo(mymap);
    tempMarker.bindPopup(bosThanksgivingCrime[i].incident_type_description);
}
document.getElementById("rspTxt").innerHTML = bosThanksgivingCrimeIncidentALocation;
