<?php
function processNationalAverage($average,$user_id) {
	global $backenddb;
	try {
		$stmt = $backenddb->prepare("SELECT user_id FROM average");
		$stmt->execute();
		$count = $stmt->rowCount();
		if ($count == 0) {
			$stmt = $backenddb->prepare("INSERT INTO average (average_crimes_per_month,user_id) VALUES (:average,:user_id)");
			$stmt->execute(array(':average'=>$average,':user_id'=>$user_id));
		} else {
			$stmt = $backenddb->prepare("UPDATE `average` SET average_crimes_per_month=:average WHERE user_id=:id");
			$stmt->execute(array(':average'=>$average,':id'=>$user_id));
		}
	} catch(PDOException $e) {
		echo "Failed! - ".$e->getMessage()."\n";
		return false;
	}
}

function getUserAverageCrimes() {
	global $backenddb;
	try {
		$stmt = $backenddb->prepare("SELECT AVG(`average_crimes_per_month`) as average FROM average");
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$average	= $row['average'];
		}
		return $average;
	} catch(PDOException $e) {
		echo "Failed! - ".$e->getMessage()."\n";
		return false;
	}
}

function checkLocationRisk($num_crimes) {
	$average = getUserAverageCrimes();
	if ($average < $num_crimes) {
		return "High Risk";
	} else if ($average > $num_crimes) {
		return "Low Risk";
	} else if ($average == $num_crimes) {
		return "Medium Risk";
	} else {
		return "Unknown";
	}
}
?>