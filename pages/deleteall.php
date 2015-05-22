<?php
$subpage = $_GET['subpage'];
if ($subpage == 'confirm') { ?>
	<div class="row">
		<div class="col-md-12">
			<h1>Deleting Your Data...</h1>
			<?php
			try {
				echo "<h3>Deleting Locations...";
				$stmt = $locationsdb->prepare("DROP TABLE IF EXISTS `$user`");
				$stmt->execute();
				echo "Deleted</h3>";
			} catch(PDOException $e) {
				echo $e->getMessage();
			}

			try {
				echo "<h3>Deleting Crime Results...";
				$stmt = $resultsdb->prepare("DROP TABLE IF EXISTS `$user`");
				$stmt->execute();
				echo "Deleted</h3>";
			} catch(PDOException $e) {
				echo $e->getMessage();
			}
			?>
		</div>
	</div>
<?php	
} else { ?>
	<div class="row">
		<div class="col-md-12">
			<h1>Delete All Your Data</h1>
			<h3>To comply with EU regulations, you can remove all of your data from the system</h3>
			<br />
			<div class="row">
				<div class="col-md-8">
					<h2>Are you sure you want to delete all your data?</h2>
				</div>
				<div class="col-md-2">
					<a href="/deleteall/confirm/">
						<img class="img-responsive" src="/img/tick.png" /><br />
						Yes
					</a>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>