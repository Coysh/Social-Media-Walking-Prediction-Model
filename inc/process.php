#!/usr/bin/php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_start();

require_once('db.connect');
require_once('functions.php');

$start = microtime(true);

$processlog = new KLogger('/home/crimetimcoyshco/public_html/log/', KLogger::INFO);
$time = time();
$yearago = strtotime("-1 year");
$latlons = array();
$total_crimes = 0;

$users_to_delete = array();

echo "<pre>STARTED - only processing locations that users have been to within the last year in the UK\n";

$stmt = $backenddb->prepare('SELECT * FROM `pending` LIMIT 10');
$stmt->execute();
$count = $stmt->rowCount();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$user = $row['user_id'];
	echo "Processing $user\n";
	$stmt1 = $locationsdb->query('SELECT * FROM `'.$user.'` WHERE time>1293843661'); //2011/01/01
	$count1 = $stmt1->rowCount();
	$stmt1->execute();
	if ($count1 != 0) {
		while($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
			$location = $row['locationname'];
			$date = date("Y-m",$row['time']);
			$latitude = $row['latitude'];
			$longitude = $row['longitude'];
			$latlondate = $latitude.",".$longitude.",".$date;
			if (in_array($latlondate,$latlons)) {
				continue;
			} else {
				$latlons[] = $latlondate;
			}
			echo "Started processing location: $location\n";
			$months = getMonths($date);
			foreach ($months as $month) {
				//Get locations from database
				if (!checkDateFormat($month)) {
					$error = "Bad Date Format - $month\n";
					$processlog->logFatal($error);
					echo $error;
					break;
				}
				
				// Call the API
				$crimes = callApi($month,$latitude,$longitude,true);

				$num_crimes = countCrimes($crimes);		
							
				if (checkMonthLocation($location,$month,$user)==false) {
					echo "There are no results for $location in $month, inserting new results...\n";
					if (insertResults($user,$num_crimes,$month,$latitude,$longitude,$location)==true) {		
						echo "\nCompleted processing $month for $latitude,$longitude ($location)\n\n";
						$success = true;
					} else {
						echo "\nThere were errors processing $month for $latitude,$longitude ($location) - Check log\n\n";
						$success = false;
					}
				} else {
					echo "There is a result for $location in $month, updating current result...\n";
					if (updateResults($user,$num_crimes,$month,$location)==true) {		
						echo "\nCompleted processing $month for $latitude,$longitude ($location)\n\n";
						$success = true;
					} else {
						echo "\nThere were errors processing $month for $latitude,$longitude ($location) - Check log\n\n";
						$success = false;
					}			
				}
			}
		}
	} else {
		$num_crimes = 0;
		$success = true;
	}
	
	$total_crimes += $num_crimes;
	
	if ($success == true) {
		$users_to_delete[] = $user;
		//The process completed without any errors, so lets delete the pending and update total_crimes

	}
	echo "\nCompleted processing $user\n\n"; 
}
echo "Deleting users from pending\n";
foreach ($users_to_delete as $userid) {
	$stmt = $backenddb->prepare("DELETE FROM `pending` WHERE user_id=:id");
	$stmt->bindParam(':id', $userid);
	$stmt->execute();
	echo "Deleted user $user from pending\n";		
}
$time_taken = microtime(true) - $start;

echo "\nFinished - $total_crimes crimes processed in $time_taken seconds\n";

if ($total_crimes != 0) {
	//Logging for processing - should hold all the succesful messages
	$filename = "/home/crimetimcoyshco/public_html/log/processing/process-".date("d-m-Y H:i:s").".txt";
	file_put_contents($filename, ob_get_contents(),FILE_APPEND);
}

ob_end_flush();
?>