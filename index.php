<?php
require_once('inc/db.connect'); //DB Connection
require_once('inc/functions.php'); //Main functions file
require_once('inc/functions-predict-street.php'); //Prediction functions
require_once('inc/functions-crime.php'); //Crime API functions
require_once('inc/functions-average.php'); //Crime average functions
require_once('inc/fbaccess.php'); //Facebook API
require_once('header.php'); //The HTML header

$time = time();
if ($user) {
	#echo $user;
	$name =				$user_info['name'];
	$first_name =		$user_info['first_name'];
	$last_name =		$user_info['last_name'];
	$username =			$user_info['username'];
	$email =			$user_info['email'];
	$current_location =	$user_info['location']['name'];
	$hometown =			$user_info['hometown']['name'];
	$gender =			$user_info['gender'];
	$religion =			$user_info['religion'];
	$birthday =			$user_info['birthday'];
	$lastlogin =		$time;
	if (userExist($user)==false) {
		#User does not exist in db
		try {
			$stmt = $backenddb->prepare("INSERT INTO users (id,name,fname,lname,username,email,location,hometown,gender,religion,birthday,lastlogin,accesstoken) VALUES (:id,:name,:fname,:lname,:username,:location,:hometown,:gender,:religion,:birthday,:time,:accesstoken)");
			$stmt->execute(array(':id'=>$user,':name'=>$name,':fname'=>$first_name,':lname'=>$last_name,':username'=>$username,':location'=>$current_location,':hometown'=>$hometown,':gender'=>$gender,':religion'=>$religion,':birthday'=>$birthday,':time'=>$time,':accesstoken'=>$access_token));
			//echo "Logged in added";
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	} else {
		#Update lastlogin
		try {
			$stmt = $backenddb->prepare("UPDATE users SET email=?,lastlogin=?,accesstoken=? WHERE id=?");
			$stmt->execute(array($email,time(),$access_token,$user));
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}
}

if ($page) {
	//If user has chosen a page
	require_once('pages/'.$page.'.php');
} else {
	//User has not chosen a page
	if ($user) {
		//Check if user has the correct permissions
		$stmt = $crimedb->prepare('SELECT * FROM `users` WHERE id=:user LIMIT 1');
		$stmt->execute(array(':user' => $user));
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$access_token = $row['accesstoken'];
		}
		$base_url = "https://graph.facebook.com/v2.0/$user/permissions?access_token=".$access_token;
		$data = json_decode(file_get_contents($base_url),true);		
		foreach ($data['data'] as $permission) {
			if ($permission['permission']=='public_profile') {
				$permission1 = true;
			}
			if ($permission['permission']=='read_stream') {
				$permission2 = true;
			}
			if ($permission['permission']=='email') {
				$permission3 = true;
			}	
			if ($permission['permission']=='user_friends') {
				$permission4 = true;
			}
			if ($permission['permission']=='user_about_me') {
				$permission5 = true;
			}
			if ($permission['permission']=='user_tagged_places') {
				$permission6 = true;
			}
		}
		if (($permission1==true)&&($permission2==true)&&($permission3==true)&&($permission4==true)&&($permission5==true)&&($permission6==true)) {
			//User has all the necessary permissions
			echo '<meta http-equiv="refresh" content="0;URL=/retrieve/">';
			exit();
		} else {
			echo "Hi, we have updated the site with new features, please login again<br>";
			require_once('pages/home.php');
		}
		
		//If user is logged in (via FB)
		#require_once('pages/retrieve.php');
	} else {
		//User is not logged in, show home
		require_once('pages/home.php');
	}	
}

require_once('footer.php');

?>
