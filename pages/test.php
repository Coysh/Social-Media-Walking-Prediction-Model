<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Heatmaps</title>
    <style>
      html, body, #map-canvas {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=visualization"></script>
    <script>
var map, pointarray, heatmap;
var heatmapLocations = [
		<?php
		include '../inc/db.connect';
		$locations = array();
		#Get user details from mySQL database
		$stmt = $locationsdb->prepare('SELECT locationname,latitude,longitude FROM `1260944853`');
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {		
			echo "new google.maps.LatLng(".$row['latitude'].", ".$row['longitude']."),\n";
			$locations[] = array($row['locationname'], $row['latitude'], $row['longitude']);
		}
		echo '];
		
		';
		echo 'var locations = ' . json_encode($locations) . ';';
		?>
		
/*
  new google.maps.LatLng(37.782551, -122.445368),
  new google.maps.LatLng(37.765139, -122.405139),
  new google.maps.LatLng(37.764457, -122.405094),
  new google.maps.LatLng(37.763716, -122.405142),
  new google.maps.LatLng(37.762932, -122.405398),
  new google.maps.LatLng(37.762126, -122.405813),
  new google.maps.LatLng(37.761344, -122.406215),
  new google.maps.LatLng(37.760556, -122.406495),
  new google.maps.LatLng(37.759732, -122.406484),
  new google.maps.LatLng(37.758910, -122.406228),
  new google.maps.LatLng(37.758182, -122.405695),
  new google.maps.LatLng(37.757676, -122.405118),
  new google.maps.LatLng(37.757039, -122.404346),
  new google.maps.LatLng(37.756335, -122.403719),
  new google.maps.LatLng(37.755503, -122.403406),
  new google.maps.LatLng(37.754665, -122.403242),
  new google.maps.LatLng(37.753837, -122.403172),
  new google.maps.LatLng(37.752986, -122.403112),
  new google.maps.LatLng(37.751266, -122.403355)*/

function initialize() {
  var mapOptions = {
    zoom: 4,
    center: new google.maps.LatLng(48.6908333333, 9.14055555556),
    mapTypeId: google.maps.MapTypeId.SATELLITE
  };

  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);

  var pointArray = new google.maps.MVCArray(heatmapLocations);

  heatmap = new google.maps.visualization.HeatmapLayer({
    data: pointArray,
	radius: 75
  });

  heatmap.setMap(map);
	var infowindow = new google.maps.InfoWindow();
	var marker, i;
	for (i = 0; i < locations.length; i++) { 
		marker = new google.maps.Marker({
			position: new google.maps.LatLng(locations[i][1], locations[i][2]),
			map: map
		});

		google.maps.event.addListener(marker, 'click', (function(marker, i) {
			return function() {
				infowindow.setContent(locations[i][0]);
				infowindow.open(map, marker);
			}
		})(marker, i));
	}
}


google.maps.event.addDomListener(window, 'load', initialize);

    </script>
  </head>

  <body>
    <div id="map-canvas"></div>
  </body>
</html>