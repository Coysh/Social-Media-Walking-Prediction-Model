<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!$user) { 
	echo "<a href=\"$loginUrl\">Login</a>";
	//header('Location: /');
} else {
	if ($user) {
		$name =				$user_info['name'];
		$first_name =		$user_info['first_name'];
		$last_name =		$user_info['last_name'];
		$username =			$user_info['username'];
		$current_location =	$user_info['location']['name'];
		$hometown =			$user_info['hometown']['name'];
		$gender =			$user_info['gender'];
		$religion =			$user_info['religion'];
		$birthday =			'bday';#$user_info['birthday'];
		$lastlogin =		$time;
	}
	if (userExist($user)==false) {
		#User does not exist in db
		$stmt = $backenddb->prepare("INSERT INTO users (id,name,fname,lname,username,location,hometown,gender,religion,birthday,lastlogin,accesstoken) VALUES (:id,:name,:fname,:lname,:username,:location,:hometown,:gender,:religion,:birthday,:time,:accesstoken)");
		$stmt->execute(array(':id'=>$user,':name'=>$name,':fname'=>$first_name,':lname'=>$last_name,':username'=>$username,':location'=>$current_location,':hometown'=>$hometown,':gender'=>$gender,':religion'=>$religion,':birthday'=>$birthday,':time'=>$time,':accesstoken'=>$access_token));
	} else {
		#Update lastlogin
		$stmt = $backenddb->prepare("UPDATE users SET lastlogin=?,accesstoken=? WHERE id=?");
		$stmt->execute(array(time(),$access_token,$user));
	}

	?>
	<div class="row">
		<div class="col-md-10">
			<div class="row">
				<div class="col-md-8">
					<h1>Your data has been retrieved</h1>
					<p>You can now check below for your facebook locations</p>
				</div>
				<div class="col-md-2">
					<img class="img-responsive" src="/img/tick.png" />
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-xs-12">
			<ul class="nav nav-pills nav-justified thumbnail">
				<li class="active"><a href="#">
					<h4 class="list-group-item-heading">View On Map</h4>
					<p class="list-group-item-text">See your locations (and crime?) plotted on a map</p>
				</a></li>
				<li><a href="#">
					<h4 class="list-group-item-heading">View In Table</h4>
					<p class="list-group-item-text">See your locations in a table</p>
				</a></li>
			</ul>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div id="map-canvas" style="width: 100%; height: 700px;"></div>
		</div>
	</div>
	<script type="text/javascript">
	var map, pointarray, heatmap;
	//var heatmapLocations = [
		<?php
		$locations = array();
		#Get user details from mySQL database
		$stmt = $locationsdb->prepare('SELECT locationname,latitude,longitude FROM `'.$user.'`');
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {		
			#echo "new google.maps.LatLng(".$row['latitude'].", ".$row['longitude']."),\n";
			$locations[] = array($row['locationname'], $row['latitude'], $row['longitude']);
		}
		#echo '];';
		echo 'var locations = ' . json_encode($locations) . ';';
		?>

	
	function initialize() {
		var mapOptions = {
			zoom: 4,
			center: new google.maps.LatLng(48.6908333333, 9.14055555556),
			mapTypeId: google.maps.MapTypeId.SATELLITE
		};

		map = new google.maps.Map(document.getElementById('map-canvas'),mapOptions);

		/*var pointArray = new google.maps.MVCArray(heatmapLocations);

		heatmap = new google.maps.visualization.HeatmapLayer({
			data: pointArray,
			radius: 75
		});

		heatmap.setMap(map);*/
		var infowindow = new google.maps.InfoWindow();
		var marker, i;
		for (i = 0; i < locations.length; i++) {
			marker = new google.maps.Marker({
				position: new google.maps.LatLng(locations[i][1], locations[i][2]),
				map: map
			});

			google.maps.event.addListener(marker, 'click', (function(marker, i) {
				return function() {
					infowindow.setContent(locations[i][0]);
					infowindow.open(map, marker);
				}
			})(marker, i));
		}
	}
	google.maps.event.addDomListener(window, 'load', initialize);
	</script>
<?php
}
?>