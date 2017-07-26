# city-data-visualizer
I noticed recently that a lot of cities have started their own open data initiatives. I'm interested in developing some tools to visualize some of it / to see how much of it gives useful insights.


#### Overview of data pipeline
* The city of boston police report data is retrieved from two separate Socrata Open Data APIs (legacy system and new system) and relevant information is inserted into a sqlite database through the PopulateCrimeDatabase.php script
	* Comparison of police reports of June 2015, July 2015, and August 2015 from both APIs was performed with the statistics/compare_overlap_months/CompareOverlapMonths.php script, and the resulting data is used when populating the database.
	* statistics/incident_standard.json file is also used to standardize the name field when inserting entries into the database as well.
* Additional analysis was performed on the resulting database through the statistics/CrimeDatabaseAnalysis.php script, and results were saved in the statistics/BCDNotes.txt file

#### Data visualization
* Visualizations are rendered for one month at a time, more data caused a significant decrease in performance.
* As the database should already be populated, the query.php script also runs on localhost:8000 so that the specified month can be queried from the database and then the rest of the visualization logic is handled by the js/main.js script (which uses Mapbox, Leaflet, and D3 services). 
	* One visualization is a simple chloropleth map of incidents in Boston, MA. Mapbox services are used to create this visualization and the API key to access these services are on my machine only and they are retrieved through running the MapboxAPIKey.js script on localhost:8080

  
