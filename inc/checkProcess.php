<?php
ini_set("display_errors", "0");
error_reporting(0);

require_once('db.connect');
require_once('functions.php');
$user = $_POST['user'];
if (isset($user)) {
	echo checkProcess($user);
} else {
	echo "true";
}
?>