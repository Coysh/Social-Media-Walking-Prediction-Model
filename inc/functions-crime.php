<?php
function readableSlug($slug) {
	$slug = str_replace('-',' ',$slug);
	$slug = ucwords($slug);
	return $slug;
}

//Functions that mainly analyse a crime location
function processStats($crimeStats) {
	//Assign array results to variables and sort them in high to low
	$categories = $crimeStats[0];
	arsort($categories);
	$outcomes = $crimeStats[1];
	arsort($outcomes);
	$forces = $crimeStats[2];
	arsort($forces);

	//Category Statistics
	//Get highest category and remove from categories array
	foreach($categories as $key => $value) {
		$highest_cat_name	= $key;
		$highest_cat_count	= $value;
		//array_shift($categories);
		break;
	}

	//Outcome Statistics
	//Get highest category and remove from outcomes array
	foreach($outcomes as $key => $value) {
		$highest_outcome_name	= $key;
		$highest_outcome_count	= $value;
		//array_shift($outcomes);
		break;
	}

	//Force Statistics
	//Get highest category and remove from forces array
	foreach($forces as $key => $value) {
		$highest_forces_name	= $key;
		$highest_forces_count	= $value;
		array_shift($forces);
		break;
	}
	
	return array(
		$categories,$highest_cat_name,$highest_cat_count,
		$outcomes,$highest_outcome_name,$highest_outcome_count,
		$forces,$highest_forces_name,$highest_forces_count
	);
}

function crimeStats($crimes) {
	$category = array();
	$outcome_status = array();
	$location_type = array();
	$i=0;
	foreach ($crimes as $item) {
		foreach ($item as $key => $value) {
			if(($key == 'persistent_id')||($key == 'id')||($key == 'location')||($key == 'context')||($key == 'month')) {
				//Do nothing
			} else if ($key == 'category') {
				$value = readableSlug($value);
				$category["$value"]++;
			} else if ($key == 'outcome_status') {
				if (is_array($value)) {
					$outcome_cat = $value['category'];
					$outcome_status["$outcome_cat"]++;
				} else {
					$outcome_status["No outcome yet"]++;
				}
			} else if ($key == 'location_type') {
				if ($value == "Force") {
					$value = "The Police";
				}
				$location_type["$value"]++;
			}
		}
	}
	$crimeStats = array($category,$outcome_status,$location_type);
	return processStats($crimeStats);
}