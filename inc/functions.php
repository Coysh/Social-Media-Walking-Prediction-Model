<?php
require_once('pointInArea.php');
$pointInArea = new pointInArea();

/**
 *
 * Checks if date string is in YYYY-MM format
 *
 * @param    String	$date The date to check
 * @return   boolean
 *
 */
function checkDateFormat($date) {
	if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $date)) {
		return true;
	} else {
		return false;
	}
}

/**
 *
 * Checks if the user exists in the users database
 *
 * @param    String	$user The user to check
 * @return   boolean
 *
 */
function userExist($user) {
	global $backenddb;
	$stmt = $backenddb->prepare('SELECT `id` FROM `users` WHERE id=:user LIMIT 1');
	$stmt->execute(array(':user' => $user));
	$count = $stmt->rowCount();
	if ($count == 0) return false; else return true;
}

/**
 *
 * Checks if the user's results have been processed by checking to see if they are still in the pending table
 *
 * @param    int	$user The user's id to check
 * @return   String true if user is still being processed - or error / false if already processed (String for javascript)
 *
 */
function checkProcess($user) {
	global $backenddb;
	global $processlog;
	try {
		$stmt = $backenddb->prepare('SELECT * FROM `pending` WHERE user_id=:user LIMIT 1');
		$stmt->execute(array(':user'=>$user));
		$count = $stmt->rowCount();
		if ($count == 0) return "false"; else return "true";
	} catch(PDOException $e) {
		$processlog->logFatal($e->getMessage());
		return "true";
	}
}

/**
 *
 * Gets all of the previous months since the date
 *
 * @param    String	$date The date in YYYY-MM format
 * @return   array $months the array of 12 months
 *
 */
function getMonths($date) {
	/*$date = $date."-01";
	$date_yearago = date("Y-m",strtotime($date." -1 year"))."-01";	
	$start    = new DateTime("$date_yearago");
	$start->modify('first day of this month');
	$end      = new DateTime("$date");	
	$end->modify('first day of next month');
	
	$interval = DateInterval::createFromDateString('1 month');
	$period   = new DatePeriod($start, $interval, $end);

	$months = array();
	foreach ($period as $dt) {
		$months[] = $dt->format("Y-m");
	}
	return $months;*/
	//Temporary, just get the month the user was there.
	return array($date);
}

/**
 *
 * Checks if the crime date table exists in the crime database
 *
 * @param    String $date The date to check
 * @return   boolean
 *
 */
function crimeExist($date) {
	global $crimedb;
	try {
		$stmt = $crimedb->prepare("SELECT 1 FROM `$date` LIMIT 1");
		$stmt->execute();
		$count = $stmt->rowCount();
		if ($count == 0) return false; else return true;
	} catch(PDOException $e) {
		return false;
	}
}

/**
 *
 * Checks if the crime date table exists in the crime database
 *
 * @param    String $date The date to check
 * @return   String
 *
 */
function createCrimeDate($date) {
	global $processlog;
	global $crimedb;	
	
	echo "Creating crime table for $date...";
	
	try {
		$crimedb->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); //Error Handling
		$sql ="CREATE TABLE IF NOT EXISTS `$date` (
			`db_id` int(11) NOT NULL AUTO_INCREMENT,
			`category` varchar(50) NOT NULL,
			`persistent_id` varchar(225) NOT NULL,
			`location_subtype` varchar(225) NOT NULL,
			`id` int(11) NOT NULL,
			`latitude` decimal(10,8) NOT NULL,
			`longitude` decimal(10,8) NOT NULL,
			`street_id` int(11) NOT NULL,
			`street_name` varchar(225) NOT NULL,
			`context` varchar(225) NOT NULL,
			`location_type` varchar(20) NOT NULL,
			`outcome_category` varchar(225) NOT NULL,
			`outcome_date` varchar(7) NOT NULL,
			`insert_time` int(11) NOT NULL,
			PRIMARY KEY (`db_id`)
		);";
		$crimedb->exec($sql);
		echo "Created!\n";
		return true;
	} catch(PDOException $e) {
		$processlog->logFatal($e->getMessage());
		echo "Failed!\n";
		return false;
	}
}

/**
 *
 * Grab the data from the police API
 *
 * @param    String $date The date to check
 * @param    String $lat The latitude
 * @param    String $lon The longitude
 * @param    boolean $display - Whether to display to debug output or not
 * @return   String The JSON returned from the police.uk API
 *
 */
function callApi($date,$lat,$lon,$display) {
	$start = microtime(true);
	
	//File caching
	$filePath = "/home/crimetimcoyshco/public_html/cache/".preg_replace("/[^A-Za-z0-9 ]/", '', "$date-$lat-$lon").".txt"; //remove symbols from file name
	
	if (file_exists($filePath)) {
		$cachetime = (60 * 60 * 24 * 7 * 5 * 12); //one year
		$filetimemod = filemtime($filePath) + $cachetime;
	} else {
		$filetimemod = 0; //Set file time to the beginning of time - so the file is updated
	}
	//if the renewal date is smaller than now, return true; else false (no need for update)
	if ($filetimemod < time()) {
		if ($display==true) { echo "Calling police API for crimes committed near $lat,$lon on $date..."; }
		$curl = curl_init();
		$url  = "http://data.police.uk/api/crimes-street/all-crime?date=$date&lat=$lat&lng=$lon";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 

		$result = curl_exec($curl);
		#$info = curl_getinfo($curl);
		//output the data to get more information.
		#print_r($info);
		
		$time_taken = microtime(true) - $start;
		
		file_put_contents($filePath, $result);
		
		if ($display==true) { echo "Retrieved! - Cached to file - $time_taken seconds\n"; }
	} else {
		if ($display==true) { echo "Getting from cache crimes committed near $lat,$lon on $date..."; }
		$result = file_get_contents($filePath);
		$time_taken = microtime(true) - $start;
		if ($display==true) { echo "Retrieved from cache! - $time_taken seconds\n"; }
	}
	
	return $result;
}

/**
 *
 * Grab the data from the police API, differs from Call APi as it returns crimes around the area
 *
 * @param    String $date The date to check
 * @param    String $lat The latitude
 * @param    String $lon The longitude
 * @param    int $distance The distance to check
 * @param    boolean $display - Whether to display to debug output or not
 * @return   String The JSON returned from the police.uk API
 *
 */
function callApiArea($date,$lat,$lon,$distance,$display) {
	$start = microtime(true);
	
	//Get Area
	$area = locationArea($lat,$lon,$distance);
	
	//File caching
	$filePath = "/home/crimetimcoyshco/public_html/cache/".preg_replace("/[^A-Za-z0-9 ]/", '', "$date-$lat-$lon-$distance").".txt"; //remove symbols from file name
	
	if (file_exists($filePath)) {
		$cachetime = (60 * 60 * 24 * 7 * 5 * 12); //one year
		$filetimemod = filemtime($filePath) + $cachetime;
	} else {
		$filetimemod = 0; //Set file time to the beginning of time - so the file is updated
	}
	//if the renewal date is smaller than now, return true; else false (no need for update)
	if ($filetimemod < time()) {
		if ($display==true) { echo "Calling police API for crimes committed in a $distance km area of $lat,$lon on $date..."; }
		$curl = curl_init();
		$url  = "http://data.police.uk/api/crimes-street/all-crime?date=$date&poly=$area";

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 

		$result = curl_exec($curl);
		#$info = curl_getinfo($curl);
		//output the data to get more information.
		#print_r($info);
		
		$time_taken = microtime(true) - $start;
		
		file_put_contents($filePath, $result);
		
		if ($display==true) { echo "Retrieved! - Cached to file - $time_taken seconds\n"; }
	} else {
		if ($display==true) { echo "Getting from cache crimes committed near $lat,$lon on $date..."; }
		$result = file_get_contents($filePath);
		$time_taken = microtime(true) - $start;
		if ($display==true) { echo "Retrieved from cache! - $time_taken seconds\n"; }
	}
	
	return $result;
}

/**
 *
 * Counts the total number of crimes in the JSON query
 *
 * @param	String $crimes The JSON crimes object
 * @return	int The number of crimes in the crimes JSON object
 *
 */
function countCrimes($crimes) {
	global $resultsdb;
	echo "Processing crimes...";
	
	$crimes = json_decode($crimes, TRUE);
	$crime_tot = count($crimes);
	echo "Finished - Crimes: $crime_tot\n";	
	return $crime_tot;
}

/**
 *
 * Checks to see if the month and location are already in the user's table in the results database
 *
 * @param	String $location The name of the location
 * @param	String $month The month of the location in YYYY-MM format
 * @param	int $user The id of the user from Facebook
 * @return	boolean false if there are no results for parameters, true if there are results for parameters - or error
 *
 */
function checkMonthLocation($location,$month,$user) {
	global $resultsdb;
	global $processlog;
	try {
		$stmt = $resultsdb->prepare("SELECT 1 FROM `$user` WHERE location=:location AND month=:month LIMIT 1");
		$stmt->execute(array(':location'=>$location,':month'=>$month));
		$count = $stmt->rowCount();
		if ($count == 0) return false; else return true;
	} catch(PDOException $e) {
		$processlog->logFatal("checkMonthLocation error: ".$e->getMessage());
		return true;
	}				
}

/**
 *
 * Checks if the table for user exists in the results database
 *
 * @param	int $user The Facebook id of the user
 * @return	boolean false if table does not exist, true if table already exists - or error

 *
 */
function checkResultsExist($user) {
	global $resultsdb;
	global $processlog;
	try {
		$stmt = $resultsdb->prepare("SELECT 1 FROM `$user` LIMIT 1");
		$stmt->execute();
		$count = $stmt->rowCount();
		if ($count == 0) return false; else return true;
	} catch(PDOException $e) {
		$processlog->logFatal("checkResultsExist error: ".$e->getMessage());
		return true;
	}
}

/**
 *
 * Inserts the results into the results database. Checks if the table for the user exists,
 * if not creates it and inserts. If it does exist, it updates the existing table
 *
 * @param	int $user The Facebook id of the user
 * @param	int $num_crimes The number of crimes for the month
 * @param	String $month The month in format YYYY-MM
 * @param	String $month The month in format YYYY-MM
 * @param	String $month The month in format YYYY-MM
 * @return	boolean true if insert succesfull, false if failed
 *
 */
function insertResults($user,$num_crimes,$month,$latitude,$longitude,$location) {
	global $resultsdb;
	global $processlog;
	echo "Inserting Results...\n";
	if (checkResultsExist($user)==false) {
		echo "Table does not exist...\n";
		echo "Creating crime table for $user... ";
		try {
			$resultsdb->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION ); //Error Handling
			$sql ="CREATE TABLE IF NOT EXISTS `$user` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`month` varchar(7) NOT NULL,
				`num_crimes` int(11) NOT NULL,
				`latitude` decimal(10,8) NOT NULL,
				`longitude` decimal(10,8) NOT NULL,
				`location` varchar(225) NOT NULL,
				PRIMARY KEY (`id`)
			);";
			$resultsdb->exec($sql);
			echo "Created!\n";
		} catch(PDOException $e) {
			$processlog->logFatal("insertResults error 1: ".$e->getMessage());
			echo "Failed! - ".$e->getMessage()."\n";
			return false;
		}
	}
	
	echo "Inserting results into created table... ";	
	
	try {
		$stmt = $resultsdb->prepare("INSERT INTO `$user` (num_crimes,month,latitude,longitude,location) VALUES (:num_crimes,:month,:latitude,:longitude,:location)");
		$stmt->execute(array(':num_crimes'=>$num_crimes,':month'=>$month,':latitude'=>$latitude,':longitude'=>$longitude,':location'=>$location));
		echo "Inserted!\n";
		return true;
	} catch(PDOException $e) {
		$processlog->logFatal("insertResults error 3: ".$e->getMessage());
		echo "Failed! - ".$e->getMessage()."\n";
		return false;
	}
}

/**
 *
 * Updates the results in the results database.
 * Checks current entry, to get unique id and num_crimes; then works out average num-crimes
 * and updates the current entry to new average.
 *
 * @param	int $user The Facebook id of the user
 * @param	int $num_crimes The number of crimes for the month
 * @param	String $month The month in format YYYY-MM
 * @param	String $location The name of the location
 * @return	boolean true if update successful, false if failed
 *
 */
function updateResults($user,$num_crimes,$month,$location) {
	global $resultsdb;
	global $processlog;
	echo "Updating results... ";	
	
	try {
		$stmt = $resultsdb->prepare("SELECT * FROM `$user` WHERE location=:location AND month=:month LIMIT 1");
		$stmt->execute(array(':location'=>$location,':month'=>$month));
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$id = $row['id'];
			$num_crimes_old = $row['num_crimes'];
		}
	} catch(PDOException $e) {
		$processlog->logFatal("updateResults error 1: ".$e->getMessage());
		echo "Failed! - ".$e->getMessage()."\n";
		return false;
	}
	
	//Generate new num_crimes, based on average
	$num_crimes = ($num_crimes_old+$num_crimes)/2;
	echo "Old Num Crimes: $num_crimes_old New Num Crimes: $num_crimes - ($num_crimes_old+$num_crimes)/2\n";
	
	try {
		$stmt = $resultsdb->prepare("UPDATE `$user` SET num_crimes=:num_crimes WHERE id=:id");
		$stmt->execute(array(':num_crimes'=>$num_crimes,':id'=>$id));
		echo "Updated!\n";
		return true;
	} catch(PDOException $e) {
		$processlog->logFatal("updateResults error 2: ".$e->getMessage());
		echo "Failed! - ".$e->getMessage()."\n";
		return false;
	}
}

/**
 *
 * Fixes Statistic Numbers
 *
 * @param    String	$date The date to check
 * @return   boolean
 *
 */
function formatNumber($num) {
	return number_format(round($num,2));
}

/**
 *
 * Gets the mean value of all the crimes in a user's results table
 * Gets the mean value of all the crimes in a specific locaiton, if location is passed
 *
 * @param	int $user The id of the user to check
 * @param	String $location The location to check
 * @return	int The mean value of all the crimes in the results table
 *
 */
function getAverageCrimes($user,$location) {
	global $resultsdb;
	try {
		if ($location!='ALL') {
			$stmt = $resultsdb->prepare("SELECT * FROM `$user` WHERE location=:location");
			$stmt->execute(array(':location'=>$location));
		} else {
			$stmt = $resultsdb->prepare("SELECT * FROM `$user`");
			$stmt->execute();
		}
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$num_crimes[] = $row['num_crimes'];
		}
	} catch(PDOException $e) {
		$processlog->logFatal("getAverage error 1: ".$e->getMessage());
		echo "Failed! - ".$e->getMessage()."\n";
		return 0;
	}	
	#echo $location."\n";
	#var_dump($num_crimes);
	return array_sum($num_crimes)/count($num_crimes);
}

/**
 *
 * Gets the mean value of all the crimes in a user's results table
 * Gets the mean value of all the crimes in a specific location, if location is passed
 *
 * @param	int $user The id of the user to check
 * @param	String $location The location to check
 * @return	int The mean value of all the crimes in the results table
 *
 */
function processStatistics($user) {
	global $resultsdb;
	$output = "";
	$averageCrimes = formatNumber(getAverageCrimes($user,'ALL'));
	$output .= "The average crimes committed in a month near your Facebook locations: <b>$averageCrimes</b><br />";
	
	//Insert Average to Average Table
	processNationalAverage($averageCrimes,$user);
	$risk = checkLocationRisk($averageCrimes);
	$output .= "You are at a <b>$risk</b> of crimes compared to the national user average<br />";
	
	try {
		$stmt = $resultsdb->prepare("SELECT * FROM `$user`");
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$locations[] = $row['location'];
		}
	} catch(PDOException $e) {
		$processlog->logFatal("processStatistics error 1: ".$e->getMessage());
		echo "Failed! - ".$e->getMessage()."\n";
		return 0;
	}
	$highest_avg_crimes = array('',0);
	foreach ($locations as $location) {
		$avg_crimes = getAverageCrimes($user,$location);
		if ($avg_crimes>$highest_avg_crimes[1]) {
			$highest_avg_crimes[0] = $location;
			$highest_avg_crimes[1] = $avg_crimes;
		}
	}
	$lowest_avg_crimes = array('',9999999); //High number so the first result is auto lowest
	foreach ($locations as $location) {
		$avg_crimes = getAverageCrimes($user,$location);
		if ($avg_crimes<$lowest_avg_crimes[1]) {
			$lowest_avg_crimes[0] = $location;
			$lowest_avg_crimes[1] = $avg_crimes;
		}
	}
	$output .= "The location with the most crimes committed is <b>".$highest_avg_crimes[0]."</b> with an average crime count of <b>".formatNumber($highest_avg_crimes[1])."</b> per month<br />";
	$output .= "The location with the least crimes committed is <b>".$lowest_avg_crimes[0]."</b> with an average crime count of <b>".formatNumber($lowest_avg_crimes[1])."</b> per month<br />";

	//Most crimes in a month
	try {
		$stmt = $resultsdb->prepare("SELECT * FROM (
			SELECT month,SUM(num_crimes) AS `num_crimes` FROM `$user` GROUP BY month ORDER BY num_crimes DESC
		) max LIMIT 1"); //Cool SQL
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$highest_month  = date("F Y",strtotime($row['month']));
			$highest_crimes = formatNumber($row['num_crimes']);
		}
	} catch(PDOException $e) {
		$processlog->logFatal("processStatistics error 2: ".$e->getMessage());
		echo "Failed! - ".$e->getMessage()."\n";
		return 0;
	}
	$output .= "The most crimes committed in a month was in <b>".$highest_month."</b> with a total of <b>".$highest_crimes."</b> crimes<br />";
	
	return $output;
}

/**
 *
 * Checks how many months there have been since December 2010 (When crime API first became available)
 *
 * @param    String $month The month to check in YYYY-MM format
 * @return   int number of months since 2010-12
 *
 */
function monthsSinceStart($month) {
	$d1 = strtotime("2010-12-01"); //2010-12 is the first date available
	$d2 = strtotime($month."-01");
	$min_date = min($d1, $d2);
	$max_date = max($d1, $d2);
	$i = 0;

	while (($min_date = strtotime("+1 MONTH", $min_date)) <= $max_date) {
		$i++;
	}
	return $i;
}
/**
 *
 * Gets the months between a year in a date
 *
 * @param    String $date The date to check
 * @return   int the date as months
 *
 */
function GetMonthsFromDate($date) {
	$year = (int) date('Y',$date);
	$months = (int) date('m', $date);
	$dateAsMonths = 12*$year + $months;
	return $dateAsMonths;
}
/**
 *
 * Returns a date from months
 *
 */
function GetDateFromMonths($months) {
	$years = (int) $months / 12;
	$month = (int) $months % 12;
	$myDate = strtotime("$years/$month/01");
	return $myDate;
}

/**
 *
 * Finds the area around a lat,lon
 *
 * @param    int $lat The latitude center
 * @param    int $lon The longitude center
 * @param    int $distance The distance around the point
 * @return   String string containing lats and lons
 *
 */
function locationArea($lat,$lon,$distance) {
	//predictLocation calculated the lat,long a distance away from the original point
	$point1 = predictLocation($lat,$lon,0,$distance);
	$point2 = predictLocation($lat,$lon,30,$distance);
	$point3 = predictLocation($lat,$lon,60,$distance);
	$point4 = predictLocation($lat,$lon,90,$distance);
	$point5 = predictLocation($lat,$lon,120,$distance);
	$point6 = predictLocation($lat,$lon,150,$distance);
	$point7 = predictLocation($lat,$lon,180,$distance);
	$point8 = predictLocation($lat,$lon,210,$distance);
	$point9 = predictLocation($lat,$lon,240,$distance);
	$point10 = predictLocation($lat,$lon,270,$distance);
	$point11 = predictLocation($lat,$lon,300,$distance);
	$point12 = predictLocation($lat,$lon,330,$distance);
	
	return $point1[0].",".$point1[1].":".$point2[0].",".$point2[1].":".$point3[0].",".$point3[1].":".$point4[0].",".$point4[1].":".$point5[0].",".$point5[1].":".$point6[0].",".$point6[1].":".$point7[0].",".$point7[1].":".$point8[0].",".$point8[1].":".$point9[0].",".$point9[1].":".$point10[0].",".$point10[1].":".$point11[0].",".$point11[1].":".$point12[0].",".$point12[1];
}

/**
 *
 * Gets a latitude and longitude from an address
 *
 * @param    int $location The location to search
 * @return   Array array containing lats and lons
 *
 */
function getLatlon($location) {
	//Check if $location is lat,lon already
	if(preg_match('/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/', $location)) {
		$coordinates = explode(',', $location);
		$latitude = $coordinates[0];
		$longitude = $coordinates[1];		
	} else {
		//Get lat long for location
		// format this string with the appropriate latitude longitude
		$url = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($location)."&sensor=false";
		// make the HTTP request
		$data = file_get_contents($url);
		// parse the json response
		$jsondata = json_decode($data,true);
		// if we get a placemark array and the status was good, get the addres
		$latitude = $jsondata['results'][0]['geometry']['location']['lat'];
		$longitude = $jsondata['results'][0]['geometry']['location']['lng'];
	}
	return array($latitude,$longitude);
}
?>