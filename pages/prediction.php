<?php
//Work out predicted location
$postLocations = $_POST['predictLocations'];
if (is_array($postLocations)) {
	$count = count($postLocations);
	if ($count < 4) {
		echo "<h1>Please select four or more locations</h1>";
		die();
	}
	$names = array_reverse($postLocations);
	$locations = array();
	foreach ($names as $name) {
		//Strip name and month from $name
		$pieces = explode("##", $name);
		$name = $pieces[0];
		$date = $pieces[1];
		#echo "<b>$name</b><br>";
		//Loo through each name and find lat,lon
		try {
			$sql = "SELECT locationname,latitude,longitude,DATE_FORMAT(FROM_UNIXTIME(time), '%Y-%m') AS date FROM `$user` WHERE locationname='".addslashes($name)."' GROUP BY locationname LIMIT 1";
			$stmt = $locationsdb->prepare($sql);
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$latitude = $row['latitude'];
				$longitude = $row['longitude'];			
				$locations[] = getRoadLocation($latitude,$longitude);
				##echo '<input type="checkbox" name="predictLocations[]" value="'.$row['locationname'].'" id="'.$row['locationid'].'"> <label for="'.$row['locationid'].'">'.$row['locationname'].' ('.date('F Y',$row['time']).')</label><br />';
			}	
		} catch(PDOException $e) {
			#echo "Errror: $e";
		}
	}

	$prediction	= predictMain($locations);
	$locations	= $prediction[0];
	$place		= $prediction[1];
	
	$predictedLoc = end($locations);
	$predictedLat = $predictedLoc[0];
	$predictedLon = $predictedLoc[1];
	
	/*echo "Kate copy this:<br><pre>";
	print_r($locations);
	echo "</pre>";*/
	
	$distance = stripslashes(strip_tags($_POST['distanceArea']));
	if (!is_numeric($distance)) {
		$distance = 0.5;
	}
	
	//Get crimes for location
	// Call the API
	$date = date('Y-m',strtotime("-2 month", time()));
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

	$stmt = $backenddb->prepare("SELECT * FROM `questions-prediction` WHERE user_id='$user'");
	$stmt->execute();
	$num_rows = $stmt->rowCount(); 

	if ($num_rows < 6) { 
		$displayQuestions = true;
		include 'inc/prediction-questions.php';
	}
	?>	
	<div class="row">
		<div class="col-md-7">
			<h4><a href="/results/">< Go Back</a></h4>
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
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<h2><b>Previous Crimes</b></h2>
			<h3>There were <b><?php echo $num_crimes; ?></b> crimes within <?php echo $distance; ?>km of this location last month</h3>
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


	<script type="text/javascript">
		<?php
		if ($displayQuestions === true) { 
			?>
			$(window).load(function(){
				$('#questionsModal').modal({
					backdrop: 'static',
					keyboard: false
				});
			});
			<?php
		}
		
		echo 'var locations = ' . json_encode($locations) . ';';
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
		function makeMarker(num,colour) {
			marker = new google.maps.Marker({
				position: position,
				map: map,
				title: "Location number "+num+"",
				icon: 'https://chart.googleapis.com/chart?chst=d_map_pin_letter&chld='+num+'|'+colour+'|000000'
			});
		}
		function updateBounds() {
			map.fitBounds (bounds);
			console.log('updateBounds()');
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
			makeMarker(num,colour);

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
	echo "<h1>You did not select any locations</h1>";
}