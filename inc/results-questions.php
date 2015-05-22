	<div class="modal fade" id="questionsModal">
		<div class="modal-dialog" style="width: 85%">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title text-center">Questionnaire</h3>
					<h4>Your locations have been retrieved succesfully. Please answer the questions below to access the website.</h4>
				</div>
				<div class="modal-body">	
					<form class="form-horizontal">
						<div class="form-group row">
							<?php
							$i=1;
							$stmt = $resultsdb->prepare("SELECT * FROM `$user` ORDER BY RAND() LIMIT 1 ");
							$stmt->execute();
							$q1id = 0;
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$q1id = $row['id'];
								echo '<label class="modal-label " for="q1"><p>';
									echo "$i) Were you at <b>".$row['location']."</b> during the month of <b>".date('F, Y',strtotime($row['month']))."</b>?";
								echo '</p></label>';		
								$i++;
								?>
								<div class="col-md-10 offset1">
									<input type="radio" name="q1" id="q1-1" value="yes" checked="checked"> <label class="labelcursor" for="q1-1"> Yes</label><br />
									<input type="radio" name="q1" id="q1-2" value="no">
									<label class="labelcursor" for="q1-2"> No</label><br />
									<input type="radio" name="q1" id="q1-3" value="maybe">
									<label class="labelcursor" for="q1-2"> Maybe</label>
								</div>
								<?php
							}
							?>
						</div>
						
						<div class="form-group row">
							<?php
							$stmt = $resultsdb->prepare("SELECT * FROM `$user` WHERE id != '$q1id' ORDER BY RAND() LIMIT 1 ");
							$stmt->execute();
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								echo '<label class="modal-label " for="q2"><p>';
									echo "$i) Were you at <b>".$row['location']."</b> during the month of <b>".date('F, Y',strtotime($row['month']))."</b>?";
								echo '</p></label>';	
								$i++;						
								?>
								<div class="col-md-10 offset1">
									<input type="radio" name="q2" id="q2-1" value="yes" checked="checked"> <label class="labelcursor" for="q2-1"> Yes</label><br />
									<input type="radio" name="q2" id="q2-2" value="no">
									<label class="labelcursor" for="q2-2"> No</label><br />
									<input type="radio" name="q2" id="q2-3" value="maybe">
									<label class="labelcursor" for="q2-3"> Maybe</label>
								</div>
								<?php
							}
							?>
						</div>	
						
						<div class="form-group row">
							<?php
							$stmt = $resultsdb->prepare("SELECT * FROM `$user` ORDER BY RAND() LIMIT 1 ");
							$stmt->execute();
							$q3id = 0;
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								$q3id = $row['id'];
								echo '<label class="modal-label " for="q3"><p>';
									echo "$i) Do you think <b>".$row['num_crimes']."</b> crimes would happen within 1km of <b>".$row['location']."</b> in a single month?";
								echo '</p></label>';			
								$i++;
								?>
								<div class="col-md-10 offset1">
									<input type="radio" name="q3" id="q3-1" value="yes" checked="checked"> <label class="labelcursor" for="q3-1"> Yes</label><br />
									<input type="radio" name="q3" id="q3-2" value="no">
									<label class="labelcursor" for="q3-2"> No</label><br />
									<input type="radio" name="q3" id="q3-3" value="maybe">
									<label class="labelcursor" for="q3-3"> Maybe</label>
								</div>
								<?php
							}
							?>
						</div>	
						
						<div class="form-group row">
							<?php
							$stmt = $resultsdb->prepare("SELECT * FROM `$user` WHERE id != '$q3id' ORDER BY RAND() LIMIT 1 ");
							$stmt->execute();
							while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
								echo '<label class="modal-label " for="q4"><p>';
									echo "$i) Do you think <b>".$row['num_crimes']."</b> crimes would happen within 1km of <b>".$row['location']."</b> in a single month?";
								echo '</p></label>';	
								$i++;
								?>
								<div class="col-md-10 offset1">
									<input type="radio" name="q4" id="q4-1" value="yes" checked="checked"> <label class="labelcursor" for="q4-1"> Yes</label><br />
									<input type="radio" name="q4" id="q4-2" value="no">
									<label class="labelcursor" for="q4-2"> No</label><br />
									<input type="radio" name="q4" id="q4-3" value="maybe">
									<label class="labelcursor" for="q4-3"> Maybe</label>
								</div>
								<?php
							}
							?>
						</div>	
						
						<div class="form-group row">
							<div class="col-md-7" style="padding-left: 0;">
								<label class="modal-label" for="q4"><p>
									<?php echo $i ?>) Please look at the list to the right.<br />
									On a scale of 1 to 5 how accurate is this list in representing the places you have been tagged in on Facebook?
								</p></label>
								<div class="col-md-10 offset1 q5">
									<input type="radio" name="q5" id="q5-1" value="5" checked="checked"> <label class="labelcursor" for="q5-1"> 5 (very accurate)</label><br />
									<input type="radio" name="q5" id="q5-2" value="4"> <label class="labelcursor" for="q5-2"> 4</label><br />
									<input type="radio" name="q5" id="q5-3" value="3"> <label class="labelcursor" for="q5-3"> 3</label><br />
									<input type="radio" name="q5" id="q5-4" value="2"> <label class="labelcursor" for="q5-4"> 2</label><br />
									<input type="radio" name="q5" id="q5-5" value="1"> <label class="labelcursor" for="q5-5"> 1 (not accurate)</label><br />
									
								</div>
							</div>
							<div class="col-md-4 offset1">
								<p>
									<?php
									$stmt = $resultsdb->prepare("SELECT * FROM `$user` ORDER BY month DESC LIMIT 10");
									$stmt->execute();
									while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
										echo "<b>".$row['location']."</b> - ".date('F, Y',strtotime($row['month']))."<br />";
									}
									?>
								</p>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="submit" id="submitquestions" class="btn btn-primary">Submit Answers</button>
				</div>
				</form>
			</div>
		</div>
	</div>
	
	<script type="text/javascript">
	$("#submitquestions").click(function() {	
		var q1 = $("input:radio[name ='q1']:checked").val();
		var q2 = $("input:radio[name ='q2']:checked").val();
		var q3 = $("input:radio[name ='q3']:checked").val();
		var q4 = $("input:radio[name ='q4']:checked").val();
		var q5 = $("input:radio[name ='q5']:checked").val();
	
		var dataString = 'page=results&q1='+q1+'&q2='+q2+'&q3='+q3+'&q4='+q4+'&q5='+q5;
		
		$.ajax({
			type: "POST",
			url: "/inc/questions-submit.php",
			data: dataString,
			success: function(data) {
				if (data == 'true') {
					$('#questionsModal').modal('hide');
				} else {
					$('#questionsModal').modal('hide');
					console.log("Error: "+data);
				}
			}
		});
		return false;
	});
	</script>