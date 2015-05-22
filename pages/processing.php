<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!$user) { 
	header('Location: /');
} else {
	//Check if user's results are already processed, if so - redirect.
	if(checkProcess($user)=="false") {
		header('Location: /results/');
	}
	?>
	<div class="row">
		<div class="col-md-10">
			<div class="row">
				<div class="col-md-12">
					<?php
					$stmt = $locationsdb->prepare("SELECT * FROM `$user`");
					$stmt->execute();
					$num_rows = $stmt->rowCount(); 
					?>
					<h1>We have found <b><?php echo $num_rows;?></b> locations!</h1>
				</div>
			</div>
			<div class="row">
				<div class="col-md-10">
					<h3>Please wait as we process your data for crimes. This page will refresh automatically.</h3>
					<h3>Why The Wait?</h3>
					<p>We gather <b>all</b> crimes that have been reported at the month you were at each Facebook location.<br />
					This can take a long time as there can be thousands of crimes.</p>
					<br />
				</div>
				<div class="col-md-2">
					<img class="img-responsive loading" src="/img/loading.gif" /><br />
					<!--<p class="loading"> Checking...</p>!-->
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	//Call the checkProcess.php file via AJAX
	//If the response is false - redirect the user to the results page
	function checkProcess() {
		$('.loading').show();
		$.ajax({
			type: "POST",
			url: "/inc/checkProcess.php",
			data: { user: "<?php echo $user; ?>" },
			success:function(response){ 
				console.log('response:'+response);
				if (response == 'false') {
					window.location.href = "/results/";
				}
				$('.loading').delay(3000).fadeOut();
				setTimeout(function() {
					checkProcess();
				}, 2500);
			}
		});
	}
	jQuery(document).ready(function () {
		checkProcess();		
	});
	</script>
<?php
}
?>