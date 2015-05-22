<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!$user) { 
	#echo "<a href=\"$loginUrl\">Login</a>";
	header('Location: /');
} else {
	
	include 'inc/results-questions.php';
	?>
	
	
	<div class="row">
		<div class="col-md-8">
			<h1>Your data has been retrieved</h1>
			<h2>Basic Information</h2>
			<p>We have processed all the locations we have gathered from your Facebook profile. Locations are gathered from: photos, status updates, checkins and any posts with a 'place' attached to it.</p>
			<p>With each location, the system has scanned the crimes committed 'with 1km of that area for the month you were there.</p>
		</div>
		<div class="col-md-3 col-md-offset-1 text-right">
			<img class="img-responsive" src="/img/tick.png" />
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<h2>Predict Your Next Location</h2>
			<h4>Select four or more locations that you're likely to go to on your next journey.</h4>
			<form action="/prediction/" method="post">
				<p>Select area (in km) of predicted location you want to analyse for crimes</p>
				<input name="distanceArea" type="text" value="2.5" data-slider="true" data-slider-range="0.1,5" data-slider-step="0.1">
				<br />
				<div id="sel-locations">
					<?php
					include 'inc/results-select-locations.php';
					?>
				</div>
				<br />
				<input type="submit" value="Predict Location" class="btn btn-info btn-block">
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h2>Statistics</h2>
			<h3>Some statistics about your data</h3>
			<div class="row">
				<div class="col-xs-11">
					<p>
						<?php echo processStatistics($user); ?>
					</p>
				</div>
			</div>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-xs-12">
			<ul class="nav nav-pills nav-justified thumbnail">
				<li class="active"><a href="#">
					<h4 class="list-group-item-heading">View Graphs</h4>
					<p class="list-group-item-text">See some cool graphs that look cool</p>
				</a></li>
				<li><a href="#">
					<h4 class="list-group-item-heading">View Table</h4>
					<p class="list-group-item-text">See your locations and crime in a table</p>
				</a></li>
			</ul>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<h2>Crime Graphs</h2>
			<h3>Personalised graphs that show your locations and crime over time</h3>
		</div>
		<div class="col-md-12" id="graph">
			<?php
				require_once('inc/highchart/results.php');
			?>		
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<h2>Table</h2>
			<h3>Your crimes in a table</h3>
		</div>
		<div class="col-md-12" id="table">
			<?php
				require_once('inc/results-table.php');
			?>		
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<a href="http://crime.timcoysh.co.uk/retrieve/forceretrieve/">Force Refresh Locations</a>
			<span class="text-right"><a href="http://crime.timcoysh.co.uk/deleteall/">Delete ALL Data</a></span>
		</div>
	</div>
	<script src="/js/simple-slider.min.js"></script>
	<script>
	$("[data-slider]").each(function () {
		var input = $(this);
		$("<span>").addClass("output").insertAfter($(this));
	}).bind("slider:ready slider:changed", function (event, data) {
		$(this).nextAll(".output:first").html(data.value.toFixed(2)+" km");
	});
	
	<?php
	$stmt = $backenddb->prepare("SELECT * FROM `questions-results` WHERE user_id='$user'");
	$stmt->execute();
	$num_rows = $stmt->rowCount(); 

	if ($num_rows < 5) { 
	?>
		$(window).load(function(){
			$('#questionsModal').modal({
				backdrop: 'static',
				keyboard: false
			});
		});
	<?php
	}
	?>
	</script>
	
<?php
}
?>