<?php
error_reporting(E_ALL & ~E_NOTICE );

if(!$user) { 
	header('Location: /');
} else {
	$subpage = $_GET['subpage'];
	if ($subpage == 'forceretrieve') {
		
	} else if (checkResultsExist($user)==true) {
		echo '<meta http-equiv="refresh" content="0;URL=/results/">';
		exit();
	}
	if ($user) {
		$name =				$user_info['name'];
		$first_name =		$user_info['first_name'];
		$last_name =		$user_info['last_name'];
		$username =			$user_info['username'];
		$current_location =	$user_info['location']['name'];
		$hometown =			$user_info['hometown']['name'];
		$gender =			$user_info['gender'];
		$religion =			$user_info['religion'];
		$birthday =			$user_info['birthday'];
		$lastlogin =		$time;
	}
	?>
	<div class="row">
		<div class="col-md-10 jumbotron">
			<div class="row">
				<div class="col-md-10">
					<h1>Hello <?php echo $first_name; ?></h1>
					<h2>Please wait while we retrieve your information...</h2>
					<p>This may take a few minutes, you've got a lot of data!</p>
				</div>
				<div class="col-md-2">
					<img src="/img/processing.gif" />
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	//Call the grab.php file via AJAX - This enhances user experience due to potential long page loads grabbing facebook location data
	//If the response is OK - redirect the user to locations.php
	//Else output the error
	$.ajax({
		<?php if ($subpage == 'forceretrieve') { ?>
			url: "/inc/grab.php?forcedel=yes",
		<?php } else { ?>
			url: "/inc/grab.php",
		<?php } ?>
        success:function(response){ 
			console.log('response:'+response);
			if (response == 'OK') {
				window.location.href = "/processing/";
			} else {
				$('#status').html('Error ('+response+')');
				<?php
				$sitelog->LogError('retrieve.php - 60'); 
				?>
			}
        }
	});
	</script>
<?php
}
?>