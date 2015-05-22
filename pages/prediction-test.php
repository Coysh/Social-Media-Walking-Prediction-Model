<?php
//Work out predicted location
if ($_POST['locations']) {
	require_once('/home/crimetimcoyshco/public_html/inc/pointInArea.php');
	$pointInArea = new pointInArea();
	
	$unique = 4; #The unique number of locations needed

	$postLocation = explode("\n", $_POST['locations']);
	$uniqueLocations = count((array_unique($postLocation)));

	if ($uniqueLocations < $unique) {
		echo "<h1>Please select four or more <b>unique</b> locations</h1>";
		die();
	}
	foreach ($postLocation as $name) {
		$latlon = getLatlon($name);
		$latitude = $latlon[0];
		$longitude = $latlon[1];		
		#echo $name." $latitude,$longitude<br>";
		if (strlen($name)>2) {
			if($pointInArea->pointInPolygon("$latitude $longitude")==true) {
				#echo "GOOD<br>";
				$postLocations[] = $name;				
				$postLocationsLatLon[] = array($latitude,$longitude);
			}
		}
	}
}
if (is_array($postLocations)) {
	$postLocations = array_filter($postLocations);
	#print_r($postLocations);
	
	$count = count($postLocations);
	if ($count < 4) {
		echo "<h1>Please select four or more locations</h1>";
		$die = true;
	}
	if (count($postLocation) < 4) {
		echo "<h1>All locations must be within the United Kingdom</h1>";
		$die = true;
	}
	if ($die == true) {
		die();
	}
	
	$locations = array();
	$i=0;
	foreach ($postLocations as $name) {
		#echo "-$name-";
		//Loop through each name and find lat,lon
		//Strip name and month from $name
		$pieces = explode("##", $name);
		$name = $pieces[0];
		$date = $pieces[1];
		$latlon = $postLocationsLatLon[$i];
		$latitude = $latlon[0];
		$longitude = $latlon[1];
		$locations[] = getRoadLocation($latitude,$longitude);
		$i++;
	}

	/*echo "<pre>";
	var_dump($locations);
	echo "</pre>";*/
	
	$prediction	= predictMain($locations);
	$locations	= $prediction[0];
	$place		= $prediction[1];
	
	$predictedLoc = end($locations);
	$predictedLat = $predictedLoc[0];
	$predictedLon = $predictedLoc[1];

	$distance = stripslashes(strip_tags($_POST['distanceArea']));
	if (!is_numeric($distance)) {
		$distance = 0.5;
	}
	
	$distance = round($distance,2);
	
	//Get crimes for location
	// Call the API
	if ($_POST['date']) {
		$date = stripslashes($_POST['date']);
	} else {
		$date = date('Y-m',strtotime("-2 month", time()));
	}
	$monthYearDate = date('F, Y',strtotime($date));
	$crimes = callApiArea($date,$predictedLat,$predictedLon,$distance,false);
	$jsonCrimes = json_decode($crimes,true);
	$num_crimes = count($jsonCrimes);

	//Get stats
	$crimeStats 				= crimeStats($jsonCrimes);
	#echo "<pre>";
	#print_r($crimeStats);
	$crime_cats 				= $crimeStats[0];
	$crime_cats_high_name 		= $crimeStats[1];
	$crime_cats_high_count 		= $crimeStats[2];
	$crime_outcomes 			= $crimeStats[3];
	$crime_outcomes_high_name 	= $crimeStats[4];
	$crime_outcomes_high_count 	= $crimeStats[5];
	$crime_forces 				= $crimeStats[6];
	$crime_forces_high_name 	= $crimeStats[7];
	$crime_forces_high_count 	= $crimeStats[8];
	
	//Risk Calculations
	$risk = checkLocationRisk($num_crimes);
	if ($risk == "High Risk") {
		$riskOutput = "<h1 class=\"high-risk\">High Risk</h1><h3>You are at a high risk of crime if you continue on your predicted walk</h3>";
	} else if ($risk == "Low Risk") {
		$riskOutput = "<h1 class=\"low-risk\">Low Risk</h1><h3>You are at a low risk of crime if you continue on your predicted walk</h3>";
	} else {
		$riskOutput = "<h1 class=\"medium-risk\">Medium Risk</h1><h3>You are at a medium risk of crime if you continue on your predicted walk</h3>";
	}

	?>
	<div class="row">
		<div class="col-md-7">
			<h4><a href="/prediction-test/">< Go Back</a></h4>
			<h1>Your Predicted Location</h1>
			<h3>We have predicted your next location is <b><?php echo $place; ?></b><br> This is based on the <?php echo count($locations)-1;?> locations you chose</h3>
			
			<?php echo $riskOutput; ?>
			
		</div>
		<div class="col-md-5" id="map-col">
			<div class="row">
				<div class="col-md-12 text-right">
					<br /><br /><a href="#" id="expand-map">Expand Map</a><a href="#" id="shrink-map">Shrink Map</a>
				</div>
			</div>
			<div id="map-canvas"></div>
			<div class="row">
				<div class="col-md-12 text-right" style="color: red;">
					If markers do not display, please refresh the page.
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<h2><b>Previous Crimes</b></h2>
			<h3>There were <b><?php echo $num_crimes; ?></b> crimes within <?php echo $distance; ?>km of this location in <?php echo $monthYearDate; ?></h3>
		</div>
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


	<script>
		<?php
		echo 'var locations = ' . json_encode($locations) . ';';
		echo 'var locationsName = ' . json_encode($postLocations) . ';';
		?>
		google.maps.visualRefresh = true;
		
		var map = new google.maps.Map(document.getElementById('map-canvas'), {
			zoom: 8,
			center: new google.maps.LatLng(51.742040,-2.224426),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		});
		var directionsDisplay;
		function renderDirections(result) {
			var directionsRenderer = new google.maps.DirectionsRenderer({suppressMarkers: true});
			directionsRenderer.setMap(map);
			directionsRenderer.setDirections(result);
		}
		function makeMarker(num,colour,locationName) {
			marker = new google.maps.Marker({
				position: position,
				map: map,
				title: num+" "+locationName,
				icon: 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+num+'|'+colour+'|000000'
			});
		}
		function updateBounds() {
			map.fitBounds (bounds);
		}
		
		var marker, i;
		var bounds = new google.maps.LatLngBounds();
		for (i = 0; i < locations.length; i++) {
			var num = i+1;
			if (num == locations.length) {
				var colour = '328ED9';
			} else {
				var colour = 'FF776B';
			}
			console.log(num+" - "+locations[i][0], locations[i][1]);
			var position = new google.maps.LatLng(locations[i][0], locations[i][1]);
			var locationName = locationsName[i];
			makeMarker(num,colour,locationName);

			bounds.extend(position);
			
			//Directions between this point and previous
			if (i!=0) {
				var directionsService = new google.maps.DirectionsService();
				directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});
				directionsDisplay.setMap(map);
				
				var start = new google.maps.LatLng(locations[i-1][0], locations[i-1][1]);				
				var request = {
					origin:start,
					destination:position,
					travelMode: google.maps.TravelMode.WALKING
				};
				directionsService.route(request, function(response, status) {
					if (status == google.maps.DirectionsStatus.OK) {
						renderDirections(response);
					}
				});	
			}
		}
		
		var circle = new google.maps.Circle({
			map: map,
			radius: <?php echo $distance*1000; ?>,
			fillColor: '#AA0000'
		});
		circle.bindTo('center', marker, 'position');
		
		$('#expand-map').click(function() {
			$('#expand-map').hide();
			$('#shrink-map').show();
			$('#map-col').css({width:'100%'});
			$('#map-canvas').css({height:'500px'});
			google.maps.event.trigger(map, 'resize');
			updateBounds();
		});
		$('#shrink-map').click(function() {
			$('#shrink-map').hide();
			$('#expand-map').show();
			$('#map-col').css({width:''});
			$('#map-canvas').css({height:'300px'});
			google.maps.event.trigger(map, 'resize');
			updateBounds();	
		});
		updateBounds();
		$(window).load(function() {
			updateBounds();
		});	
		setTimeout(function () { updateBounds(); }, 500);
		setTimeout(function () { updateBounds(); }, 1000);
		setTimeout(function () { updateBounds(); }, 1500);
		setTimeout(function () { updateBounds(); }, 2000);
		setTimeout(function () { updateBounds(); }, 3000);
		setTimeout(function () { updateBounds(); }, 4000);
	</script>
	<?php
} else {
	?>
	<div class="row">
		<div class="col-md-12">
			<h2>Test The Walk Prediction Model</h2>
			<h5>The Walking Prediction Model created for this website has a large range of potential uses, not only in the field of social media.<br />
			This tool helps you to test the model by allowing you to <b>input any number of locations in order</b> and submit them to the model.</h5>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<h4>Enter a different location on every line</h4>
			<form action="/prediction-test/" method="post">
				<textarea id="locations" name="locations" rows="5" cols="48"></textarea><br />
				Select Search Radius for Crimes<br />
				<input style="width: 75%" name="distanceArea" type="text" value="2.5" data-slider="true" data-slider-range="0.1,5" data-slider-step="0.1"><br />
				Select Date for Crimes<br />
				<select name="date">
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
						echo "<option value=\"$YM\">".date('F, Y',$currentDate)."</option>";
						$currentDate = strtotime( date('Y/m/01/',$currentDate).' -1 month');
					}
					?>
				</select><br /><br />
				<input type="submit" value="Predict Location" class="btn btn-info btn-block">
			</form>
		</div>
		<div class="col-md-6">
			<h4>Click these examples routes to test them</h4>
			<a href="#" id="cdf-student">Cardiff Student Walk</a><br />
			<a href="#" id="cdf-pub">Cardiff Pub Walk</a><br />
			<a href="#" id="misc-cotswolds">Gloucestershire Tourist Route</a><br />
			<a href="#" id="uk-tourist">United Kingdom Tourist Walk</a><br />
			<a href="#" id="misc-straight">Straight Line Walk</a><br />
		</div>
	</div>
	
	<script src="/js/simple-slider.min.js"></script>
	<script type="text/javascript">
	$("[data-slider]").each(function () {
		var input = $(this);
		$("<span>").addClass("output").insertAfter($(this));
	}).bind("slider:ready slider:changed", function (event, data) {
		$(this).nextAll(".output:first").html(data.value.toFixed(2)+" km");
	});
	
	var placeholder = 'Cardiff Metropolitan University\nCardiff Castle\nMillennium Stadium\nCardiff Central';
	$('#locations').val(placeholder);

	$('#locations').focus(function(){
		if($(this).val() === placeholder){
			$(this).val('');
		}
	});

	$('#locations').blur(function(){
		if($(this).val() ===''){
			$(this).val(placeholder);
		}    
	});
	
	$('#cdf-student').click(function() {
		$('#locations').val('Cardiff Metropolitan University\nCardiff University\nCardiff Castle\nMillennium Stadium');
	});
	$('#cdf-pub').click(function() {
		$('#locations').val('The Cryws Cardiff \nThe Mackintosh Cardiff\nThe Woodville Cardiff\nCardiff Students Union\nThe Prince of Wales Cardiff');
	});
	$('#misc-cotswolds').click(function() {
		$('#locations').val('Gloucester\nCheltenham\nPainswick\nStroud\nCirencester\nTetbury');
	});
	$('#uk-tourist').click(function() {
		$('#locations').val('Big Ben\nLondon Bridge\nBuckingham Palace\nHarrods London');
	});
	$('#misc-straight').click(function() {
		$('#locations').val('51.56838879,-3.18940401\n51.56838879,-3.13515901\n51.56838879,-3.10657739\n51.56838879,-3.03662538');
	});
	</script>
	<?php
	}
?>