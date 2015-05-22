<?php
/**
 *
 * Calculates the bearing between two latitude,longitude positions
 *
 * @param    int $lat1 The latitude of the first position
 * @param    int $lon1 The longitude of the first position
 * @param    int $lat2 The latitude of the second position
 * @param    int $lon2 The latitude of the second position
 * @return   int The bearing degrees
 *
 */
function bearingCalc($lat1, $lon1, $lat2, $lon2) { 
	$bearingDeg = (rad2deg(atan2(sin(deg2rad($lon2) - deg2rad($lon1)) * 
	   cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - 
	   sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)))) + 360) % 360;
	return $bearingDeg;
}

/**
 *
 * Calculates the distance between two latitude,longitude positions
 *
 * @param    int $lat1 The latitude of the first position
 * @param    int $lon1 The longitude of the first position
 * @param    int $lat2 The latitude of the second position
 * @param    int $lon2 The latitude of the second position
 * @return   int The distance in miles between point 1 and point 2
 *
 */
function distance($lat1, $lon1, $lat2, $lon2) {
	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = ($dist * 60 * 1.1515)*1.609344;
	return $miles;
}

/**
 *
 * Gets the average between each latitude,longitude within an array
 *
 * @param    array $locations The array contain locations in an array
 * @return   array An array containing the average lat,lons
 *
 */
function averageLatLon($locations) {
	$average = array();
	//Get average between each lat,lon
	for ($i = 0; $i<sizeof($locations);$i++) {
		if (is_array($locations[$i+1])) {
			$latAvg = ($locations[$i][0]+$locations[$i+1][0])/2;
			$lonAvg = ($locations[$i][1]+$locations[$i+1][1])/2;
			$average[] = array($latAvg,$lonAvg);
		}
	}
	return $average;
}

/**
 *
 * Calculates the average distance between each location in an array
 *
 * @param    array $locations The array contain locations in an array
 * @return   int The average distance
 *
 */
function averageDistance($average) {
	$distance = array();
	//Get distance between average lat,lons
	for ($i = 0; $i<sizeof($average);$i++) {
		if (is_array($average[$i+1])) {
			$lat = $average[$i][0];
			$long = $average[$i][1];
			$lat1 = $average[$i+1][0];
			$long1 = $average[$i+1][1];
			
			$distance[] = distance($lat,$long,$lat1,$long1);
		}
	}	
	$sum = array_sum($distance);
	$num = sizeof($distance);
	$avgDistance = $sum/$num;
	return $avgDistance;
}

/**
 *
 * Gets the exact middle location of all of the average latitude and longitude
 *
 * @param    array $average The array that contains the average locations
 * @return   array The latitude and longitude of the average point
 *
 */
function getMiddleLocation($average) {
	//Get average of ALL locations
	$latavg = 0;
	$lonavg = 0;
	foreach ($average as $location) {
		$lat = $location[0];
		$lon = $location[1];
		$latavg += $lat;
		$lonavg += $lon;
	}
	$latavg = $latavg/sizeof($average);
	$lonavg = $lonavg/sizeof($average);
	return array($latavg,$lonavg);
}

/**
 *
 * Predict the next location based on latitude,longitude, bearing and distance
 *
 * @param    int $lat The latitude of the last location
 * @param    int $lon The longitude of the last location
 * @param    int $bearing The bearing calculated
 * @param    int $distance The distance to add
 * @return   array The latitude and longitude of the predicted point
 *
 */
function predictLocation($lat,$lon,$bearing,$distance) {
	$earthRadius = 6371; //Earth's radius in km
	$lat1 = deg2rad($lat);
	$lon1 = deg2rad($lon);
	$bearing = deg2rad($bearing);

	$lat2 = asin(sin($lat1) * cos($distance / $earthRadius) + cos($lat1) * sin($distance / $earthRadius) * cos($bearing));
	$lon2 = $lon1 + atan2(sin($bearing) * sin($distance / $earthRadius) * cos($lat1), cos($distance / $earthRadius) - sin($lat1) * sin($lat2));

	$predictLat = rad2deg($lat2);
	$predictLon = rad2deg($lon2);
	
	return array($predictLat,$predictLon);
}

/**
 *
 * Predicts the user's next location based on array of locations
 * Implements the Facade pattern.
 *
 * @param    array $locations Array containg latitude,longitudes of locations
 * @return   array Array contain the array of locations+predicted array AND the predicted place name
 *
 */
function predictMain($locations) {
	
	$average = averageLatLon($locations);
	$distance = averageDistance($average);

	$latlonavg = getMiddleLocation($average);
	$latavg = $latlonavg[0];
	$lonavg = $latlonavg[1];

	//Work out the bearing
	$lastLocation = end($locations);
	$bearing = bearingCalc($latavg,$lonavg,$lastLocation[0],$lastLocation[1]);
	#echo "Avg Lat: $latavg,$lonavg<br>Last Loc:".$lastLocation[0].",".$lastLocation[1]."<br>Bearing: $bearing<br>";

	#echo "Average Distance: $distance<br /><br />";

	//Predict next location
	$predictLatLon = predictLocation($lastLocation[0],$lastLocation[1],$bearing,$distance);
	$predictLat = $predictLatLon[0];
	$predictLon = $predictLatLon[1];

	#echo "Last location: ".$lastLocation[0].",".$lastLocation[1]."<br>";
	#echo "Distance to add: $distance<br>";
	#echo "Predicted location: $predictLat,$predictLon <br><br>";
	$locations[] = array($predictLat,$predictLon);

	// format this string with the appropriate latitude longitude
	$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$predictLat,$predictLon&sensor=false";
	// make the HTTP request
	$data = file_get_contents($url);
	// parse the json response
	$jsondata = json_decode($data,true);
	// if we get a placemark array and the status was good, get the addres
	$place = $jsondata ['results'][0]['address_components'][1]['long_name'];
	
	return array($locations,$place);
}

