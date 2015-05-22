					<?php
					try {
						$stmt = $locationsdb->prepare("SELECT locationid,locationname,time FROM `$user` GROUP BY locationname ORDER BY time DESC");
						$stmt->execute();
						while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							echo '<input type="checkbox" name="predictLocations[]" value="'.$row['locationname'].'##'.date('Y-m',$row['time']).'" id="'.$row['locationid'].'"> <label for="'.$row['locationid'].'" style="cursor: pointer;">'.$row['locationname'].' (You were last here in '.date('F Y',$row['time']).')</label><br />';
						}	
					} catch(PDOException $e) {
						echo "Errror: $e";
					}
					?>