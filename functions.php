<?php

function hc_get_points(){
	global $wpdb;
	$user_id = get_current_user_id();
	$date = hc_format_date($_POST["data"]);
	$results = $wpdb->get_row( 'SELECT * FROM hc_points WHERE date = "' . $date . '" AND user_id = ' . $user_id );
	echo(json_encode($results));
	wp_die();
}

function hc_format_date($date){
	$aDate = explode("/", $date);
	$date = $aDate[2] . "-" . $aDate[1] . "-" . $aDate[0];
	return $date;
}

add_action('wp_ajax_hc_date_changed_get_points', 'hc_get_points');

function hc_ninja_form_register_points(){
  add_action( 'ninja_forms_post_process' , 'hc_process_points_form' );
}

add_action( 'init', 'hc_ninja_form_register_points' );

function hc_process_points_form(){
	global $ninja_forms_processing;
	global $wpdb;
	define("WORKOUT_MINUTES", 20);
	define("POINTS_FIRST_WORKOUT", 10);
	define("POINTS_SUBSEQUENT_WORKOUT", 5);
	define("MAX_CARDIO", 30);
	define("CARDIO_MIN", 1);
	define("WEIGHT_MIN", 10);
	define("WEIGHT_POINTS", 2);
	define("WATER_MIN", 8);
	define("WATER_POINTS", 5);
	define("MEAL_POINTS", 5);
	define("MAX_VEG", 5);
	define("MAX_GREENS", 4);
	define("GREENS_POINTS", 1);
	define("VEG_POINTS", 2);
	define("SWEET_CALORIES", 100);
	define("JUNK_CALORIES", 100);
	define("SWEET_PENALTY", -2);
	define("JUNK_PENALTY", -2);
	define("ALCOHOL_PENALTY", -2);
	
	$form_id = $ninja_forms_processing->get_form_ID();
	if ( $form_id == 6){
		$data = array(
			"date"			=>	hc_format_date($ninja_forms_processing->get_field_value( 11 )),
			"user_id"		=>	get_current_user_id(),
			"workout"		=>	$ninja_forms_processing->get_field_value( 13 ),
			"cardio"		=>	$ninja_forms_processing->get_field_value( 14 ),
			"weight_training"	=>	$ninja_forms_processing->get_field_value( 18 ),
			"water"			=>	$ninja_forms_processing->get_field_value( 19 ),
			"breakfast"		=>	$ninja_forms_processing->get_field_value( 20 ),
			"lunch"			=>	$ninja_forms_processing->get_field_value( 21 ),
			"dinner"		=>	$ninja_forms_processing->get_field_value( 22 ),
			"vegetable"		=>	$ninja_forms_processing->get_field_value( 23 ),
			"greens"		=>	$ninja_forms_processing->get_field_value( 24 ),
			"sweets"		=>	$ninja_forms_processing->get_field_value( 25 ),
			"junk"			=>	$ninja_forms_processing->get_field_value( 26 ),
			"alcohol"		=>	$ninja_forms_processing->get_field_value( 27 )
		);
		$workouts = $data["workout"]/WORKOUT_MINUTES;
		$workouts = floor($workouts);
		
		if($workouts >= 1){
			$data["p_workout"] = POINTS_FIRST_WORKOUT + POINTS_SUBSEQUENT_WORKOUT * ($workouts - 1);
		} else{
			$data["p_workout"] = 0;
		}
		
		if( $data["cardio"] > MAX_CARDIO ){
			$data["p_cardio"] = MAX_CARDIO / CARDIO_MIN;
		} else{
			$data["p_cardio"] = $data["cardio"] / CARDIO_MIN;
		}
		
		if($data["weight_training"] >= WEIGHT_MIN){
			$data["p_weight_training"] = WEIGHT_POINTS;
		} else{
			$data["p_weight_training"] = 0;
		}
		
		if($data["water"] >= WATER_MIN){
			$data["p_water"] = WATER_POINTS;
		} else {
			$data["p_water"] = 0;
		}
		
		if($data["breakfast"] == "checked"){
			$data["breakfast"] = 1;
			$data["p_breakfast"] = MEAL_POINTS;
		} else{
			$data["breakfast"] = 0;
			$data["p_breakfast"] = 0;
		}
		
		if($data["lunch"] == "checked"){
			$data["lunch"] = 1;
			$data["p_lunch"] = MEAL_POINTS;
		} else{
			$data["lunch"] = 0;
			$data["p_lunch"] = 0;
		}
		
		if($data["dinner"] == "checked"){
			$data["p_dinner"] = MEAL_POINTS;
			$data["dinner"] = 1;
		} else {
			$data["p_dinner"] = 0;
			$data["p_dinner"] = 0;
		}
		
		if($data["vegetable"] > MAX_VEG){
			$data["p_vegetable"] = MAX_VEG * VEG_POINTS;
		} else{
			$data["p_vegetable"] = $data["vegetable"] * VEG_POINTS;
		}
		
		if( $data["greens"] > MAX_GREENS ){
			$data["p_greens"] = MAX_GREENS * GREENS_POINTS;
		} else{
			$data["p_greens"] = $data["greens"] * GREENS_POINTS;
		}
		
		$data["p_sweets"] = ceil($data["sweets"]/SWEET_CALORIES) * SWEET_PENALTY;
		$data["p_junk"] = ceil($data["junk"]/JUNK_CALORIES) * JUNK_PENALTY;
		$data["p_alcohol"] = $data["alcohol"] * ALCOHOL_PENALTY; 
				
		$wpdb->replace('hc_points', $data);
		
  	}  	
}

function hc_getTotalPoints(){
	global $wpdb;
	$totalPoints = $wpdb->get_results('SELECT SUM(  `p_workout` +  `p_cardio` +  `p_weight_training` +  `p_water` +  `p_breakfast` +  `p_lunch` +  `p_dinner` +  `p_vegetable` +  `p_greens` + `p_sweets` +  `p_junk` +  `p_alcohol` ) AS total_points, display_name FROM  `hc_points` 
INNER JOIN wpmg_users ON hc_points.user_id = wpmg_users.ID
WHERE `date` < DATE_SUB( NOW( ) , INTERVAL 1 DAY )
GROUP BY user_id
ORDER BY total_points DESC' );
	return $totalPoints;
}

function hc_getDailyTotal($userID){
	global $wpdb;
	$dailyTotal = $wpdb->get_results('SELECT (
 `p_workout` +  `p_cardio` +  `p_weight_training` +  `p_water` +  `p_breakfast` +  `p_lunch` +  `p_dinner` +  `p_vegetable` +  `p_greens` + `p_sweets` +  `p_junk` +  `p_alcohol`
) AS daily_total
FROM  `hc_points` 
WHERE  `date` = CURDATE( ) 
AND user_id =' . $userID);
	return $dailyTotal[0]->daily_total;
}

function hc_addPersonalTotal($content){
if(is_page('progress')){
	$userID = get_current_user_id();
	$dailyTotal = hc_getDailyTotal($userID);
	$dailyTotalTable = 	'<table class="hc_results">
					<tr>
						<th style="text-align: left; padding: 6px">Your current daily total:</th>
						<th>' . $dailyTotal . '</th>
					<tr>
				</table>';
			
	return $dailyTotalTable . $content;
} else {
	return $content;
}
}

function hc_addResultsTable($content){
	if(is_page('progress')){
		$resultTable = '<table class="hc_results">
					<tr>
						<th>Name</th>
						<th>Total Points</th>
					</tr>
					
				';

		$table_contents = hc_getTotalPoints();
		foreach($table_contents as $user_result){
			$resultTable .= "<tr>
						<td> {$user_result->display_name} </td>
						<td> {$user_result->total_points} </td>
					</tr>";	
		}
		$resultTable .= "</table>
				<p>*As of yesterday</p>";
		return $resultTable . $content;
	} else {
		return $content;
	}
}

add_filter( 'the_content', 'hc_addResultsTable' , 8);
add_filter( 'the_content', 'hc_addPersonalTotal', 9);
add_filter( 'visualizer-get-chart-series', 'hc_daily_total_filter_chart_series', 10, 3);

function hc_daily_total_filter_chart_series( $series, $chart_id, $type ){

	if($chart_id == 352){
		global $wpdb;
		$names = $wpdb->get_results("SELECT display_name FROM `wpmg_users` where display_name != 'admin' ORDER BY id");
		$series = array(
			array(
				"label" => "x",
				"type" => "string"
			)
		);
		foreach($names as $name){
			$series[] = array(
				"label" => $name->display_name,
				"type" => "number"
			);
		} 

	}
	return $series;
}

add_filter( 'visualizer-get-chart-data', 'hc_daily_points_filter_charts_data', 10, 3);

function hc_daily_points_filter_charts_data( $data, $chart_id, $type ){
	if($chart_id == 352){
		global $wpdb;
		$user_ids_result = $wpdb->get_results("SELECT ID from wpmg_users where display_name != 'admin' ORDER BY ID");
		$user_ids = array();
		foreach($user_ids_result as $user_id){
			$user_ids[] = $user_id->ID;
		}
		
		$daily_points = $wpdb->get_results("SELECT DATE_FORMAT(date, '%b %e') AS date_f, (p_workout + p_cardio + p_weight_training + p_water + p_breakfast + p_lunch + p_dinner + p_vegetable + p_greens + p_sweets + p_junk + p_alcohol) AS daily_total, user_id FROM `hc_points` ORDER BY date, user_id");
		
		$data = array();
		$i = 0;
		$len = count($daily_points);
		while($i < $len){
			$cur_date = $daily_points[$i]->date_f;
			$cur_a = array($cur_date);
			
			foreach($user_ids as $user_id){
				if($cur_date != $daily_points[$i]->date_f){
					$cur_a[] = 0;
				}
				else{
					while($i < $len && !($user_id < $daily_points[$i]->user_id || $user_id == $daily_points[$i]->user_id)){
							$i++;
					}
					if($daily_points[$i]->user_id == $user_id){
						$cur_a[] = intval($daily_points[$i]->daily_total);
						$i++;
					}else{
						$cur_a[] = 0;
					}
				}
			}
			
			$data[] = $cur_a;
		}
	}

	return $data;
}

?>