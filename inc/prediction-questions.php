	<div class="modal fade" id="questionsModal">
		<div class="modal-dialog" style="width: 85%">
			<div class="modal-content">
				<form class="form-horizontal" method="post" action="/inc/questions-submit.php" onSubmit="return false;">
					<div class="modal-header">
						<h3 class="modal-title text-center">Questionnaire</h3>
						<h4>Your locations have been predicted successfully. Please answer the questions below to see the predictions.</h4>
					</div>
					<div class="modal-body">	
							<div class="form-group row">
								<div class="col-md-12">
									<label class="modal-label " for="q1"><p>1) How easy was it for you to use the prediction feature on this website?</p></label>
									<br />
									<input type="radio" name="q1" id="q1-1" value="5" checked="checked"> <label class="labelcursor" for="q1-1"> 5 (very easy)</label><br />
									<input type="radio" name="q1" id="q1-2" value="4"> <label class="labelcursor" for="q1-2"> 4</label><br />
									<input type="radio" name="q1" id="q1-3" value="3"> <label class="labelcursor" for="q1-3"> 3</label><br />
									<input type="radio" name="q1" id="q1-4" value="2"> <label class="labelcursor" for="q1-4"> 2</label><br />
									<input type="radio" name="q1" id="q1-5" value="1"> <label class="labelcursor" for="q1-5"> 1 (extremely difficult)</label><br />
								</div>
							</div>
							
							<div class="form-group row">
								<div class="col-md-6">
									<label class="modal-label" for="q2">
										<p>2) The image to the right contains your chosen locations, with the blue marker indicating your predicted location.<br />Does this prediction seem realistic?</p>
									</label>				
									<input type="radio" name="q2" id="q2-1" value="yes" checked="checked"> <label class="labelcursor" for="q2-1"> Yes</label><br />
									<input type="radio" name="q2" id="q2-2" value="no">
									<label class="labelcursor" for="q2-2"> No</label><br />
									<input type="radio" name="q2" id="q2-3" value="maybe">
									<label class="labelcursor" for="q2-3"> Maybe</label>		
									<br /><br />
									<label class="modal-label" for="q3">
										<p>3) Is the map easy for you to understand?</p>
									</label>				
									<br />
									<input type="radio" name="q3" id="q3-1" value="yes" checked="checked"> <label class="labelcursor" for="q3-1"> Yes</label><br />
									<input type="radio" name="q3" id="q3-2" value="no">
									<label class="labelcursor" for="q3-2"> No</label>	
								</div>
								<div class="col-md-5 col-md-offset-1">
									<?php
									$url_location_parameters = "";
									$locations_count = count($locations);
									$i = 0;
									foreach ($locations as $location) {
										if ($i != $locations_count-1) {
											$url_location_parameters .= "&markers=size:mid%7Ccolor:red%7Clabel:$i%7C".$location[0].",".$location[1];
										}
										$i++;
									}
									$url_location_parameters .= "&markers=color:blue%7Clabel:Predicted%7C".$predictedLoc[0].",".$predictedLoc[1];
									$url = "									http://maps.googleapis.com/maps/api/staticmap?size=500x275&maptype=roadmap\
									$url_location_parameters&sensor=false";
									echo "<img class=\"img-responsive\" src=\"$url\" />";								
									?>
								</div>						
							</div>	

							<div class="form-group row">
								<div class="col-md-4">
									<label class="modal-label" for="q4"><p>4) How easy have you been able to navigate the website?</p></label>
									<br />
									<input type="radio" name="q4" id="q4-1" value="5" checked="checked"> <label class="labelcursor" for="q4-1"> 5 (very easy)</label><br />
									<input type="radio" name="q4" id="q4-2" value="4"> <label class="labelcursor" for="q4-2"> 4</label><br />
									<input type="radio" name="q4" id="q4-3" value="3"> <label class="labelcursor" for="q4-3"> 3</label><br />
									<input type="radio" name="q4" id="q4-4" value="2"> <label class="labelcursor" for="q4-4"> 2</label><br />
									<input type="radio" name="q4" id="q4-5" value="1"> <label class="labelcursor" for="q4-5"> 1 (extremely difficult)</label><br />
								</div>
								<div class="col-md-4">
									<label class="modal-label" for="q5"><p>5) Would you be interested in more websites allowing you to predict your location?</p></label>
									<br />
									<input type="radio" name="q5" id="q5-1" value="yes" checked="checked"> <label class="labelcursor" for="q5-1"> Yes</label><br />
									<input type="radio" name="q5" id="q5-2" value="no">
									<label class="labelcursor" for="q5-2"> No</label><br />
								</div>
								<div class="col-md-4">
									<label class="modal-label" for="q6"><p>6) Briefly explain your reason for your answer in question 5</p></label>
									<br />
									<textarea name="q6" id="q6-1" cols="40" rows="6">Answer here...</textarea><br />
								</div>
							</div>							
					</div>
					<div class="modal-footer">
						<input type="hidden" name="page" id="page" value="prediction"> 
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
		var q6 = $("textarea#q6-1").val();
		if(q1.length == 0) {
			alert('Please answer all questions');
		} else if(q2.length == 0) {
			alert('Please answer all questions');
		} else if(q3.length == 0) {
			alert('Please answer all questions');
		} else if(q4.length == 0) {
			alert('Please answer all questions');
		} else if(q5.length == 0) {
			alert('Please answer all questions');
		} else if(q6.length == 0) {
			alert('Please answer all questions');
		} else {	
			var dataString = 'page=prediction&q1='+q1+'&q2='+q2+'&q3='+q3+'&q4='+q4+'&q5='+q5+'&q6='+q6;
			
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
		}
		return false;
	});
	</script>