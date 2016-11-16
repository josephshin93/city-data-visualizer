//get api token

var http = require('http'),
	fs = require('fs'),
	url = require('url'),
	mapboxAPI = process.env.MAPBOX_API_TOKEN;

http.createServer(function(request, response) {
	// Website you wish to allow to connect
    response.setHeader('Access-Control-Allow-Origin', '*');

    // Request methods you wish to allow
    response.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH, DELETE');

    // Request headers you wish to allow
    response.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');

    // Set to true if you need the website to include cookies in the requests sent
    // to the API (e.g. in case you use sessions)
    response.setHeader('Access-Control-Allow-Credentials', true);

    console.log("Request received");
    console.log(url.parse(request.url).pathname);
	response.writeHead(200, {'Content-Type': 'text/plain'});
	// console.log("sending api key: "+mapboxAPI);
	response.end(mapboxAPI);
	console.log("Mapbox API key sent");
}).listen(8080);
