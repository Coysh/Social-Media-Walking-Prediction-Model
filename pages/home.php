		<div class="row">
			<div class="col-md-12">
				<div class="jumbotron">
					<h1>Social Media Walking Prediction Model</h1>
					<p>Social Media - It's everywhere. You have it, your parents have it, maybe even your grandparents have it. The purpose of this website is to demonstrate the potential your social media locations have.<br />
					In this example, we will use Facebook as a social media data source and predict your next location based on selected locations you have chosen.</p>
					<div class="row">
						<div class="col-md-7">
							<p>To start, please login with Facebook</p>
						</div>
						<div class="col-md-5">
							<p><a href="<?php echo $loginUrl; ?>" class="pull-right"><img src="img/login-fb-small.png" /></a></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<h2>More Information</h2>
				<p>This website is split into two sections - the walking prediction and the crime analysis. The crime analysis aspect is to show an example of how the walking prediction model <b>could</b> be implemented into a new field</p>
				<p>As the crime analysis features were already available for this system, they can be used to find out details of crime at each location; however this is not the primary aspect of the website</p>
			</div>
		</div>
		<h2>You can find out a location's crime risk below</h2>
		<?php
			include 'pages/search.php';
		?>