	<?php
	if ($_GET['search']) {
		require_once('/home/crimetimcoyshco/public_html/inc/pointInArea.php');
		$pointInArea = new pointInArea();

		$search = stripslashes(strip_tags($_GET['search']));
		$distance = stripslashes(strip_tags($_GET['distance']));
		$date = stripslashes($_GET['date']);
		
		if (!is_numeric($distance)) {
			$distance = 1;
		}
		$distance = round($distance, 3);
		if (!$user) { 
			$user = 0;
		}
		
		$time = time();
		$ip = $_SERVER['REMOTE_ADDR'];
		try {
			$stmt = $backenddb->prepare("SELECT * FROM `searches` WHERE search='$search'");
			$stmt->execute();
			$num_rows = $stmt->rowCount(); 
			if(!$num_rows>1) {
				$stmt = $backenddb->prepare("INSERT INTO searches (search,distance,time,user_id,ip) VALUES (:search,:distance,:time,:user_id,:ip)");
				$stmt->execute(array(':search'=>$search,':distance'=>$distance,':time'=>$time,':user_id'=>$user,':ip'=>$ip));
			}
		} catch(PDOException $e) {
			echo "Failed! - ".$e->getMessage()."\n";
			return false;
		}
	}

	?>	
	<div class="row">
		<div class="col-md-9">
			<h1>Search Location</h1>
			<form role="search" method="post" action="/search/">
				<div class="row">
					<div class="col-md-9">
						<div class="form-group">							
							<input type="text" class="form-control" name="search" placeholder="Search" value="<?php echo $search; ?>" style="padding: 25px; font-size: 24px;">
						</div>
						<div class="form-group">
							<p>Select area (in km) of predicted location you want to analyse for crimes</p>
							<input name="distanceArea" type="text" value="<?php echo $distance; ?>" data-slider="true" data-slider-range="0.1,5" data-slider-step="0.1">
						</div>
					</div>
					<div class="col-md-3">
						<button type="submit" class="btn btn-default">Submit</button>
					</div>
				</div>
			</form>
		</div>
		<div class="col-md-3 text-right">
			<?php
			if ($_GET['search']) {
				echo "<h3>Select Month</h3>";
				$lastMonthM = date('m',strtotime("-2 month", time()));
				$lastMonthY = date('Y',strtotime("-2 month", time()));
				$lastYearM = date('m',strtotime("-8 month", time()));
				$lastYearY = date('Y',strtotime("-8 month", time()));
				$startDate = strtotime("$lastYearY/$lastYearM/01");
				$endDate   = strtotime("$lastMonthY/$lastMonthM/01");
				$currentDate = $endDate;
				while ($currentDate >= $startDate) {
					$YM = date('Y-m',$currentDate);
					$searchurl = urlencode($search);
					echo "<a href=\"/search/0/$searchurl/$distance/$YM/\">".date('F, Y',$currentDate)."</a><br />";
					$currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
				}
			} else {
				echo "<h3>Random Searches</h3><p>Searches other people have made:</p>";
				try {
					$stmt = $backenddb->prepare("SELECT * FROM `searches` ORDER BY RAND() DESC LIMIT 5");
					$stmt->execute();
					while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$recent_search	= $row['search'];
						echo "<p><b>$recent_search</b></p>";
					}
				} catch(PDOException $e) {
					echo "Failed! - ".$e->getMessage()."\n";
				}
			}
			?>
		</div>
	</div>
	<?php
	if ($_GET['search']) {
		//Get lat long for location
		// format this string with the appropriate latitude longitude
		$url = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($search)."&sensor=false";
		#echo $url;
		// make the HTTP request
		$data = file_get_contents($url);
		// parse the json response
		$jsondata = json_decode($data,true);
		// if we get a placemark array and the status was good, get the addres
		$latitude = $jsondata['results'][0]['geometry']['location']['lat'];
		$longitude = $jsondata['results'][0]['geometry']['location']['lng'];
		$point = "$latitude $longitude";
		if ($jsondata['status'] == "ZERO_RESULTS") {
			echo "<h2><b>Could not find location</b><br />(Error: invalid location)</h2>";
		} else if($pointInArea->pointInPolygon($point)==false) {
			echo "<h2><b>Location is not within United Kingdom</b><br />(Error: invalid location)</h2>";
		} else {			
			//Get crimes for location
			// Call the API
			if ($date == '') {
				$date = date('Y-m',strtotime("-2 month", time()));
			}
			$monthYearDate = date('F, Y',strtotime($date));
			$crimes = callApiArea($date,$latitude,$longitude,$distance,false);
			$jsonCrimes = json_decode($crimes,true);
			$num_crimes = count($jsonCrimes);
			
			//Insert Average to Average Table
			$risk = checkLocationRisk($num_crimes);
			$risk = checkLocationRisk($num_crimes);
			if ($risk == "High Risk") {
				$riskOutput = "<h2 class=\"text-right high-risk\">High Risk</h2>";
			} else if ($risk == "Low Risk") {
				$riskOutput = "<h2 class=\"text-right low-risk\">Low Risk</h2>";
			} else {
				$riskOutput = "<h2 class=\"text-right medium-risk\">Medium Risk</h2>";
			}


			//Get stats
			$crimeStats 				= crimeStats($jsonCrimes);
			$crime_cats 				= $crimeStats[0];
			$crime_cats_high_name 		= $crimeStats[1];
			$crime_cats_high_count 		= $crimeStats[2];
			$crime_outcomes 			= $crimeStats[3];
			$crime_outcomes_high_name 	= $crimeStats[4];
			$crime_outcomes_high_count 	= $crimeStats[5];
			$crime_forces 				= $crimeStats[6];
			$crime_forces_high_name 	= $crimeStats[7];
			$crime_forces_high_count 	= $crimeStats[8];
			?>
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-8">
							<h2><b>Crimes at <?php echo $search; ?></b></h2>
						</div>
						<div class="col-md-4">
							<?php echo $riskOutput; ?>
							<?php
							#<p class="text-right">Compared to the national user average</p>
							?>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<div class="dropdown">
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
									<li>1</li>
									<li>2</li>
								</ul>
							</div>
							<h3>There were <b><?php echo $num_crimes; ?></b> crimes within a <b><?php echo $distance; ?></b>km radius of <?php echo $search; ?> during <div class="month-dropdown">
								<a data-toggle="dropdown" href="#"><?php echo $monthYearDate; ?></a>
								<ul class="dropdown-menu month-dropdown-list" role="menu" aria-labelledby="dLabel">
									<?php			
									$lastMonthM = date('m',strtotime("-2 month", time()));
									$lastMonthY = date('Y',strtotime("-2 month", time()));
									$lastYearM = date('m',strtotime("-1 year", time()));
									$lastYearY = date('Y',strtotime("-1 year", time()));
									$startDate = strtotime("$lastYearY/$lastYearM/01");
									$endDate   = strtotime("$lastMonthY/$lastMonthM/01");
									$currentDate = $endDate;
									while ($currentDate >= $startDate) {
										$YM = date('Y-m',$currentDate);
										$searchurl = urlencode($search);
										echo "<li><a href=\"/search/0/$searchurl/$distance/$YM/\">".date('F, Y',$currentDate)."</a></li>";
										$currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
									}
									?>
								</ul>
							</div></h3>

						</div>
					</div>
				</div>
				<span style="float:right;" id="hide-map"><a href="#">Hide Map</a></span>
				<span style="float:right; display:none;" id="show-map"><a href="#">Show Map</a></span>
				<div id="map-canvas" style="width: 100%; height: 500px; overflow: display;"></div>
				<div class="col-md-4">
					<h3>Types of Crime</h3>
					<h4>You are at risk from<br /><b><?php echo $crime_cats_high_name; ?></b></h4>
					<p>There were <b><?php echo $crime_cats_high_count; ?></b> <?php echo $crime_cats_high_name; ?> crimes at this location last month.</p>
					<p>There were also other crimes:</p>
					<table class="table table-list-search">
						<thead>
							<tr>
								<th>Type of Crime</th>
								<th>Number of Crimes</th>
							</tr>
							<?php
							foreach ($crime_cats as $key => $value) {
								echo "<tr>";
								echo "	<td>$key</td>";
								echo "	<td>$value</td>";
								echo "</tr>";
							}
							?>
						</thead>
					</table>
				</div>
				<div class="col-md-4">
					<h3>Outcomes of Crime</h3>
					<h4>The most frequent outcome is:<br /><b><?php echo $crime_outcomes_high_name; ?></b></h4>
					<p><b><?php echo $crime_outcomes_high_count; ?></b> crimes are classified with <b><?php echo $crime_outcomes_high_name; ?></b></p>
					<p>There were also other outcomes:</p>
					<table class="table table-list-search">
						<thead>
							<tr>
								<th>Outcome</th>
								<th>Number of Instances</th>
							</tr>
							<?php
							foreach ($crime_outcomes as $key => $value) {
								echo "<tr>";
								echo "	<td>$key</td>";
								echo "	<td>$value</td>";
								echo "</tr>";
							}
							?>
						</thead>
					</table>
				</div>
				<div class="col-md-4">
					<h3>Forces Involved</h3>
					<h4>A majority of crimes were dealt with by<br /><b><?php echo $crime_forces_high_name; ?></b></h4>
					<p><b><?php echo $crime_forces_high_count; ?></b> crimes were dealt with by <b><?php echo $crime_forces_high_name; ?></b></p>
					<?php
					foreach ($crime_forces as $key => $value) {
						if ($key == 'BTP') {
							$key = "British Transport Police";
						}
						echo "<p>The <b>$key</b> dealt with <b>$value</b> crimes near this location</p>";
					}
					?>
				</div>
			</div>
			<?php
		}
	}
	?>
		<script>
		<?php
		echo 'var locations = ' . json_encode($locations) . ';';
		?>
		google.maps.visualRefresh = true;
		
		var map = new google.maps.Map(document.getElementById('map-canvas'), {
			zoom: 14,
			center: new google.maps.LatLng(<?php
			echo $latitude.",".$longitude;
		?>),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		function makeMarker(num,colour) {
			marker = new google.maps.Marker({
				position: position,
				map: map,
				title: "Location number "+num+"",
				icon: 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+num+'|'+colour+'|000000'
			});
		}
		
		var marker, i;
		var bounds = new google.maps.LatLngBounds();
		var colour = 'FF776B';
		var position = new google.maps.LatLng(<?php
			echo $latitude.",".$longitude;
		?>);
		makeMarker(1,colour);
		bounds.extend(position);
		
		var circle = new google.maps.Circle({
			map: map,
			radius: <?php echo $distance*1000; ?>,
			fillColor: '#AA0000'
		});
		circle.bindTo('center', marker, 'position');
		
		$('#hide-map').click(function(event) {
			event.preventDefault();
			$('#map-canvas').hide();
			$('#hide-map').hide();
			$('#show-map').show();
		});	
		$('#show-map').click(function(event) {
			event.preventDefault();
			$('#map-canvas').show();
			$('#hide-map').show();
			$('#show-map').hide();
		});
	</script>
	<script src="/js/simple-slider.min.js"></script>
	<script>
	$("[data-slider]").each(function () {
		var input = $(this);
		$("<span>").addClass("output").insertAfter($(this));
	}).bind("slider:ready slider:changed", function (event, data) {
		$(this).nextAll(".output:first").html(data.value.toFixed(2)+" km");
	});
	</script>

