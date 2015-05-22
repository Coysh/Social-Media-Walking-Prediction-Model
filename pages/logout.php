<?php
//Destroy the sessions ang log the user out
//Redirect the user back to home page
session_start();
session_destroy();
header('Location: /');
?>