<?php
include 'functions.php';
include 'functions-crime.php';
$latitude = "51.48226360";
$longitude = "-3.17767544";

$date = date('Y-m',strtotime("-2 month", time()));
$crimes = callApi($date,$latitude,$longitude,false);
$jsonCrimes = json_decode($crimes,true);
$num_crimes = countCrimes($crimes);

echo "Count: $num_crimes<br>";
$crimeStats = crimeStats($jsonCrimes);


echo "<pre>";
print_r($crimeStats);
echo "</pre>";
?>