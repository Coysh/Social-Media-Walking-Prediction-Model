<?php
include_once 'Highchart.php';

$chart = new Highchart();

$chart->includeExtraScripts();
$chart->chart = array(
    'renderTo' => 'graph',
    'type' => 'scatter',
    'marginRight' => 250,
    'marginBottom' => 50,
	'zoomType' => 'xy',
	
);

$chart->title = array(
    'text' => 'Crime Over Time',
    'x' => - 20
);

//Get mysql
$stmt = $resultsdb->prepare("SELECT location, num_crimes FROM `$user` ORDER BY num_crimes DESC");
$stmt->execute();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$locations[] = $row['location'];
}

$locations = array_unique($locations);
foreach($locations as $location) {
	$month_ok		= false;
	$crime_results = array();
	$stmt = $resultsdb->prepare("SELECT * FROM `$user` WHERE location='$location' GROUP BY month ORDER BY month ");
	$stmt->execute();
	$i=0;
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$num_crimes 	= (int)$row['num_crimes'];
		$month			= $row['month'];
		
		echo "$location - $month - $num_crimes crimes \n";
		
		//For first result
		if ($i==0) {
			$months_since_start = monthsSinceStart($month);
			if (monthsSinceStart($month)>0) {
				//The first month is not 2010-12
				for ($months_since_start = monthsSinceStart($month); $months_since_start!==0; $months_since_start--) {
					#echo "NULL for $location on $month ($num_crimes crimes)\n";
					#$crime_results[] = array(null,null);
				}
			}
		}
				
		if (!$num_crimes == 0) {
			$date = DateTime::createFromFormat('Y-m', $month);
			$month = $date->format('Y, m');
			$crime_results[]	= array(new HighchartJsExpr("Date.UTC($month)"),$num_crimes);
		}
		$i++;
	}

	$chart->series[] = array(
		'name' => $location,
		'data' => $crime_results,
		'marker' => array(
            'lineWidth' => 0,
            'lineColor' => '#00B7FF',
			'symbol' => 'circle'
			),
	);
	
}

//Get crime dates available, make unique and sort in low to high order.
//Messy code
/*$curl = curl_init();
$url  = "http://data.police.uk/api/crimes-street-dates";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
$result = json_decode(curl_exec($curl), TRUE);
foreach ($result as $date) {
	$months[] = $date['date'];
}
$months = array_unique($months);
sort($months);
foreach ($months as $date) {
	$retrieved = $date;
	$date = DateTime::createFromFormat('Y-m', $retrieved);
	$month_results[] = $date->format('m/y');	
}*/
$stmt = $resultsdb->prepare("SELECT month FROM `$user` GROUP BY month ORDER BY month ");
$stmt->execute();
$month_results = array();
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$month = $row['month'];
	$date = DateTime::createFromFormat('Y-m', $month);
	$month_results[] = $date->format('m/y');	
}

$chart->xAxis = array(	
	'type' => 'datetime',
	'startOnTick' => true,
	'tickInterval' => 30* 24 * 3600 * 1000,
	'labels' => array(
        'enabled' => true,
        'rotation' => - 90,
        'align' => 'right',
	)
);

$chart->yAxis = array(
    'title' => array(
        'text' => 'Number of crimes (per month)'
    )
);

$chart->legend = array(
    'layout' => 'vertical',
    'align' => 'right',
    'verticalAlign' => 'top',
    'x' => 10,
    'borderWidth' => 0,
	'title' => array(
		'text' => 'Your Locations (click to hide/show)'
	)
);	
$chart->credits = array(
	'enabled' => false,
);

$chart->tooltip->formatter = new HighchartJsExpr(
    "function() {
	
		var date = new Date(this.x);
		var year = date.getFullYear();
		var month = date.getMonth()+1;
		if (month <10) { month = '0'+month; }
		return '<b>'+ this.series.name +'</b><br/>'+ year+'-'+month+': '+ this.y +' crimes';
	}");
?>
<script type="text/javascript"><?php echo $chart->render("chart1"); ?></script>