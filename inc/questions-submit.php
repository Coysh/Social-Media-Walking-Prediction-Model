<?php
include 'db.connect';
include 'fbaccess.php';
if ($_POST['page']) {
	$page = stripslashes(htmlspecialchars($_POST['page']));
	$q1 = $_POST['q1'];
	$q2 = $_POST['q2'];
	$q3 = $_POST['q3'];
	$q4 = $_POST['q4'];
	$q5 = $_POST['q5'];
	$q6 = $_POST['q6'];
	if ($page == 'results') {
		try {
			$stmt = $backenddb->prepare("INSERT INTO `questions-results` (user_id,question_num,answer) VALUES (:user_id,:question,:answer)");
			
			$stmt->execute(array(':user_id'=>$user,':question'=>'1',':answer'=>$q1));		
			$stmt->execute(array(':user_id'=>$user,':question'=>'2',':answer'=>$q2));		
			$stmt->execute(array(':user_id'=>$user,':question'=>'3',':answer'=>$q3));
			$stmt->execute(array(':user_id'=>$user,':question'=>'4',':answer'=>$q4));
			$stmt->execute(array(':user_id'=>$user,':question'=>'5',':answer'=>$q5));
			
			echo "true";
		} catch(PDOException $e) {
			echo "Failed! - ".$e->getMessage()."\n";
		}
	} else if ($page == 'prediction') {
		try {
			$stmt = $backenddb->prepare("INSERT INTO `questions-prediction` (user_id,question_num,answer) VALUES (:user_id,:question,:answer)");
			
			$stmt->execute(array(':user_id'=>$user,':question'=>'1',':answer'=>$q1));		
			$stmt->execute(array(':user_id'=>$user,':question'=>'2',':answer'=>$q2));		
			$stmt->execute(array(':user_id'=>$user,':question'=>'3',':answer'=>$q3));
			$stmt->execute(array(':user_id'=>$user,':question'=>'4',':answer'=>$q4));
			$stmt->execute(array(':user_id'=>$user,':question'=>'5',':answer'=>$q5));
			$stmt->execute(array(':user_id'=>$user,':question'=>'6',':answer'=>$q6));
			
			echo "true";
		} catch(PDOException $e) {
			echo "Failed! - ".$e->getMessage()."\n";
		}
	}
}
?>