<?php
$page = htmlspecialchars(strtolower($_GET['page']));
#Check if search results - if search results, then redirect
if ($_POST['search']) {
	
	$search = urlencode(stripslashes(strip_tags($_POST['search'])));
	$search = str_replace(' ','+',$search);
	$search = str_replace('%20','+',$search);
	$distance = stripslashes(strip_tags($_POST['distanceArea']));
	if($_POST['date']) {
		$date = stripslashes($_POST['date']);
	} else {
		$date = date('Y-m',strtotime("-2 month", time()));
	}
	
	if (!is_numeric($distance)) {
		$distance = 1;
	} else if ($distance > 5) {
		$distance = 5;
	}
	$distance = round($distance, 3);
	
	$searchq = "$search/$distance/$date/";
	
	#echo $searchq;
	
	if ($search != '') {
		header( 'Location: http://crime.timcoysh.co.uk/search/0/'.$searchq );
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Social Media Walking Prediction Model</title>
	<!-- Bootstrap core CSS -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="/css/custom.css" rel="stylesheet">
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script>
	<script src="/js/highcharts.js" type="text/javascript"></script>
	<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
	
	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->
</head>

<body>
	<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	
		<!--<div class="container">
			<div class="col-md-12 alert-danger text-center">
				Site upgrade currently in progress - 19:15pm - 13/05/2014
			</div>
		</div>!-->
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/">Social Media Walking Prediction Model</a>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
					<li<?php if ((!$page == 'search')&&(!$page == 'prediction-test')&&(!$page == 'about')&&(!$page == 'prediction')) { echo ' class="active"'; } ?>><a href="/">Home</a></li>
					<?php if ($user) { ?><li<?php if ($page == 'results') { echo ' class="active"'; } ?>><a href="/results/">Results</a></li><?php } ?>
					<li<?php if ($page == 'search') { echo ' class="active"'; } ?>><a href="/search/">Search</a></li>
					<li<?php if ($page == 'about') { echo ' class="active"'; } ?>><a href="/about/">About</a></li>
					<li<?php if (($page == 'prediction-test')||($page == 'prediction')) { echo ' class="active"'; } ?>><a href="/prediction-test/">Predict</a></li>
					<?php if ($user) { ?><li><a href="/logout/">Logout</a></li><?php } ?>
				</ul>
				 <div class="col-sm-3 col-md-3" style="padding: 0; float: right;">
					<form class="navbar-form" role="search" action="/search/" method="post" style="padding: 0;">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="Search for a location" name="search">
						<div class="input-group-btn">
							<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
						</div>
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<div class="container">