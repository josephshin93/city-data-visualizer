/*global L, d3*/
/*jslint vars: true, plusplus: true, devel: true, nomen: true, indent: 4, maxerr: 50 */
/*global define, $, jQuery, alert */

var centerOfBostonCoordinates = [42.312626, -71.071870], //center was calculated from the bounds of hand designed grid
    mymap = L.map('map').setView(centerOfBostonCoordinates, 12),
    i, //incrementor
    j; //incrementor

//optional function of marking peter's location
function markPjfanLocation() {
    "use strict";
    var pjfanLocationCoordinates = [42.3629, -71.0890],
        pjfanMarkerIcon = L.icon({
            iconUrl: 'icons/pjfan_marker_icon.png',
            iconSize: [80, 107],
            iconAnchor: [40, 106]
        }),
        pjfanLocationMarker = L.marker(pjfanLocationCoordinates, {icon: pjfanMarkerIcon}).addTo(mymap);
    pjfanLocationMarker.bindPopup("<b>Peter \"BBOY\" Fan's Location</b>");
}
//must retrieve Mapbox API key from node server
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

//create choropleth grid
var grid = [],
    minLatitude = 42.227953,
    minLongitude = -71.190647,
    boundWidth = 0.237555,
    boundHeight = 0.169347,
    increment = 0.004234;
//    increment = 0.008467;
for (i = 0; i < (boundHeight - increment); i += increment) {
    for (j = 0; j < (boundWidth - increment); j += increment) {
        var gridboxCoordinates = [],
            gridbox;
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

//create text arrays for display and query
var years = ["2012", "2013", "2014", "2015", "2016"],
    months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    displayMonths = [],
    postMonths = [],
    queryResults,
    queryTotal;
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

//sticky query bar
var queryBarElement = document.getElementById("wrapper-query");
window.addEventListener('scroll', function () {
    if (window.pageYOffset >= 44) {
        queryBarElement.classList.add("fixed");
    } else {
        queryBarElement.classList.remove("fixed");
    }
});


function createHorizontalChart(id, data) {
    "use strict";
    var chart = d3.select(id),
        chartWidth = 750,
        barHeight = 15,
        values = Object.values(data);
    var x = d3.scaleLinear()
        .domain([0, d3.max(values)])
        .range([0, chartWidth - (barHeight * 2)]);
    var xAxis = d3.axisBottom(x)
        .tickSize(barHeight * values.length + 5);

    chart.classed("hidden", false);
    chart.attr("width", chartWidth)
        .attr("height", barHeight * (values.length + 2) + 5);
    chart.append("g")
        .attr("transform", "translate(" + barHeight + ", " + barHeight + ")")
        .call(xAxis);
    var bar = chart.selectAll("svg")
        .data(values)
        .enter().append("g")
            .attr("transform", function(d, i) {return "translate(" + barHeight + ", " +  barHeight * (i + 1) + ")";});
    bar.append("rect")
        .attr("width", function(d) { return x(d); })
        .attr("height", barHeight - 1)
        .attr("fill", "#d85d5d");
    bar.append("text")
        .attr("transform", "translate(2, " + (barHeight - (barHeight / 4)) + ")")
        // .attr("fill", "lightGrey")
        .attr("font-family", "Verdana")
        .attr("font-size", 10)
        .text(function(d, i) {return Object.keys(data)[i];});
}
function createWordCloud(id, data){
    //max street count for one month is 455 (August 2012)
    var wordcloud = d3.select(id);
    wordcloud.classed("hidden", false);
    wordcloud.selectAll("g")
        .data(Object.keys(data))
        .enter().append("p")
            .text(function(d) {return d;})
            .attr("style", function(d) {return "font-size:" + (data[d] / 10 + 6) + "px"});
}

//retrieve data for the month being queried
function query(post) {
    "use strict";
    console.log(post);
    document.getElementById("histogram-category").innerHTML = ""; //clear previous histogram
    document.getElementById("wordcloud-streets").innerHTML = ""; //clear previous wordcloud
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
                    gridIndex = (((row - 1) * Math.round(boundWidth / increment)) + column - 1);
                if (gridIndices[gridIndex] === undefined) {
                    gridIndices[gridIndex] = 1;
                } else {
                    gridIndices[gridIndex]++;
                }
            }
            var tot = 0;
            for (i = 0; i < gridIndices.length; i++) {
                if (gridIndices[i] !== undefined && grid[i] !== undefined) {
//                    console.log("index:" + i + " sum:" + gridIndices[i] + " opacity:" + (gridIndices[i] / 250));
                    grid[i].setStyle({fillOpacity: (gridIndices[i] / 100)});
                }
            }
            createHorizontalChart("#histogram-category", queryResults.category);
            createWordCloud("#wordcloud-streets", queryResults.streets);
            console.log("All entries processed");
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(xhr.status);
            console.log(thrownError);
        }
    });
    console.log(queryResults);
}
function queryDatabase(newValue) {
    "use strict";
    var postData = {
        month: postMonths[newValue]
    };
    query(postData);
}
function showValue(newValue) {
    "use strict";
    document.getElementById("slider-value").innerHTML = displayMonths[newValue];
}



function getColors() {
    "use strict";
    var incidents = {};
    $.ajax({
        type: "POST",
        url: "http://localhost:8000",
        data: {month: "IncidentFrequency"},
        crossDomain: true,
        success: function (data) {
            data = JSON.parse(data);
            for (i = 0; i < data.length; i++) {
                var inc = document.createElement("div");
                inc.style.cssText = "float:left;width:300px;height:30px;text-align:center;background-color:" + data[i].color + ";";
                inc.innerHTML = data[i].name + " - " + data[i].total;
                document.getElementById("colors").appendChild(inc);
            }
        }
    });
}
//getColors();