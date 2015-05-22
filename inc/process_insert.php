<pre>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
#ob_start();

require_once('db.connect');
require_once('functions.php');
#require_once('fbaccess.php');

$processlog = new KLogger('/var/www/html/log/', KLogger::INFO);
$time = time();

$delete_ids = array(); //Array holding ids to delete from pending

echo "STARTED - only processing locations that users have been too in the last 6 months\n";

$stmt = $backenddb->prepare('SELECT * FROM `pending`');
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$id = $row['id'];
	$user_id = $row['user_id'];
	$date = $row['date'];
	$latitude = $row['latitude'];
	$longitude = $row['longitude'];
	$months = getMonths($date);
	echo "Started processing id number $id\n";
	foreach ($months as $month) {
		//Get locations from database
		if (!checkDateFormat($month)) {
			$error = "Bad Date Format - $date\n";
			$processlog->logFatal($error);
			echo $error;
			break;
		}

		if (!crimeExist($month)) {
			$create_crime_date = createCrimeDate($month);
			if ($create_crime_date) {
				//If it does not exist now, something has gone wrong
				if (crimeExist($month)) {
					$error = "crimeExist failed second check\n";
					$processlog->logFatal($error);
					echo $error;
					break;
				}
			} else {
				$error = "Creating crime table failed, check log\n";
				$processlog->logFatal($error);
				echo $error;
				break;
			}
		}
		
		// Call the API
		$crimes = callApi($month,$latitude,$longitude);

		//Insert into Crimes Database
		$insertcrime = insertCrime($crimes,$time,$month);
		if ($insertcrime==-1) {
			$stmt = $backenddb->prepare("UPDATE `pending` SET error=? WHERE id=?");
			$stmt->execute(array('Check Log',$user));
			$error = "Oh no, check log - $insertcrime\n";
			$processlog->logFatal($errror);
			echo $error;
		} else {
			echo "Processed $month for $latitude,$longitude - inserted $insertcrime crimes\n";
			$delete_ids[] = $id;
		}
	}
	echo "\nCompleted processing ".date("Y-m-d",strtotime($date." -1 year"))." to $date for $latitude,$longitude (pending id: $id)\n\n";
}

echo "Processed all pending rows\n";
echo "Deleting processed rows\n";

foreach ($delete_ids as $id) {
	try {
		$stmt = $backenddb->prepare("DELETE FROM `pending` WHERE id=:id");
		$stmt->bindParam(':id', $id);
		$stmt->execute();
		
		echo "Deleted id $id\n";
	} catch(PDOException $e) {
		$error = "Error deleting from `pending`: ".$e->getMessage()."\n";
		$processlog->logFatal($errror);
		echo $error;
	}
}

//Logging for processing - should hold all the succesful messages
#$filename = "/var/www/html/log/processing/$user_id-".date("Y-m-d H:i:s").".txt";
#file_put_contents($filename, ob_get_contents(),FILE_APPEND);

#ob_end_flush();
?>