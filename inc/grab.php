<?php
/*error_reporting(E_ALL);
ini_set('display_errors', 1);*/
require_once('fbaccess.php');
require_once('db.connect');
require_once('pointInArea.php');
$pointInArea = new pointInArea();
if ($user) {
	$forcedel = $_GET['forcedel'];
	if ($forcedel == 'yes') {
		try {
			$stmt = $locationsdb->prepare("DROP TABLE IF EXISTS `$user`");
			$stmt->execute();
			$stmt = $resultsdb->prepare("DROP TABLE IF EXISTS `$user`");
			$stmt->execute();
		} catch(PDOException $e) {
			$sitelog->LogError('grab.php - 17 - '.$e->getMessage()); 
			echo "Error";
		}
	}

	//Check if user already has locations in dv
	$stmt = $locationsdb->prepare("SELECT 1 FROM `$user` LIMIT 1");
	$stmt->execute();
	$count = $stmt->rowCount();
	if($count==0) {
		//Proceed!
		//Get user details from mySQL database
		$stmt = $crimedb->prepare('SELECT * FROM `users` WHERE id=:user LIMIT 1');
		$stmt->execute(array(':user' => $user));
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$access_token = $row['accesstoken'];
		}
		$params = array('access_token' => $access_token);
		//Create user's location table in database
		try {
			 $locationsdb->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling
			 $sql ="CREATE TABLE IF NOT EXISTS `$user` (
			`id` varchar(50) NOT NULL,
			`locationid` varchar(50) NOT NULL,
			`locationname` varchar(100) NOT NULL,
			`latitude` decimal(10,8) NOT NULL,
			`longitude` decimal(10,8) NOT NULL,
			`time` int(11) NOT NULL,
			PRIMARY KEY (`id`)
			);";
			$locationsdb->exec($sql);
		} catch(PDOException $e) {
			$sitelog->LogError('grab.php - 49 - '.$e->getMessage()); 
		}
		
		//Fetch first page of locations from facebook and place into user_locations array
		$user_locations = array();
		$base_url = "https://graph.facebook.com/v2.0/me?fields=tagged_places&access_token=".$access_token;
		$data = json_decode(file_get_contents($base_url),true);
		#echo "$base_url<br><hr><pre>";
		#print_r($data);
		$user_locations = array_merge($user_locations, $data["tagged_places"]["data"]);
		//Loop through all user locations from facebook and place into user_locations array
		$i=0;
		if ($data["tagged_places"]["paging"]["next"]) {
			while($data["tagged_places"]) {
				$url  = $data["tagged_places"]["paging"]["next"];
				#echo $i.": ".$url."\n";
				$data = json_decode(file_get_contents($url), TRUE);
				//print_r($data);
				$user_locations = array_merge($user_locations, $data["data"]);
				$i++;
				#if ($i==10) { break; }
			}
		}
		#print_r($user_locations);
		
		//Loop through user_locations array and insert into db
		#print_r($user_locations);
		foreach($user_locations as $location){
			$id = $location['id'];
			
			//if location data there
			if($location['place']) {
				$locationid = 	$location['place']['id'];
				$latitude =		$location['place']['location']['latitude'];
				$longitude =	$location['place']['location']['longitude'];
				$locationname =	$location['place']['name'];
			}
			$time = strtotime($location['created_time']);

			//Check to see if location is within United Kingdom, if not - skip (continue)
			$point = "$latitude $longitude";
			if($pointInArea->pointInPolygon($point)==false) {
				continue;
			}
			#echo $locationname." at ".$time."\n";
			
			$stmt = $locationsdb->prepare("INSERT INTO `$user` (id,locationid,locationname,latitude,longitude,time) VALUES (:id,:locationid,:locationname,:latitude,:longitude,:time)");
			$stmt->execute(array(':id'=>$id,':locationid'=>$locationid,':locationname'=>$locationname,':latitude'=>$latitude,':longitude'=>$longitude,':time'=>$time,));
			
			//Check to see if the lat/long and date(YYYY-MM) are already pending to be processed - if they are not add to pending
			/*$date = date("Y-m",$time);
			$stmt = $backenddb->prepare('SELECT latitude,longitude,date WHETE latitude=:latitude AND longitude=:longitude AND date=:date FROM `pending` LIMIT 1');
			$stmt->execute(array(':latitude' => $latitude,':longitude' => $longitude,':date' => $date,));
			$count = $stmt->rowCount();
			if ($count == 0) {
				while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {	
					$stmt = $backenddb->prepare("INSERT INTO `pending` (user_id,latitude,longitude,date) VALUES (:user_id,:latitude,:longitude,:date)");
					$stmt->execute(array(':user_id'=>$user,':latitude'=>$latitude,':longitude'=>$longitude,':date'=>$date,));			
				}
			}*/
		}
	}
	
	//Insert user's id to pending
	$stmt = $backenddb->prepare("SELECT * FROM `pending` WHERE user_id=':user_id'");
	$stmt->execute(array(':user_id'=>$user));
	$count = $stmt->rowCount();
	if ($count == 0) {
		$stmt = $backenddb->prepare("INSERT INTO `pending` (user_id) VALUES (:user_id)");
		$stmt->execute(array(':user_id'=>$user));	
	}
	echo "OK";
}
?>