<?php
    $responseArray = [];
    
	function courseSchedulerS() {
		$conn = new mysqli('localhost', 'coursesatlsu_root', 'j-YR(1pB~AV6', 'coursesatlsu_data');
		$semester = $conn->real_escape_string($_POST['semester']);
		$courses = $_POST['courses'];
		$times = stripslashes($conn->real_escape_string($_POST['times']));
		$sortBy = $conn->real_escape_string($_POST['sortBy']);
		$limit = $conn->real_escape_string($_POST['limit']);
		
		$course = $courses[0];
		$courses = array_splice($courses, 1);

		$sqlSection = $conn->query("SELECT sections.section_id, sections.available, sections.enrolled, sections.onHold, sections.building, sections.room, sections.sectionComment, time.time_id, time.startTime, time.endTime, time.monday, time.tuesday, time.wednesday, time.thursday, time.friday, instructors.instructorName, instructors.rmpOverall, instructors.rmpTakeAgain, instructors.instructor_id, instructors.rmpDifficulty, instructors.rmpLink, specialenrollments.specialEnrollment_id, specialenrollments.specialEnrollment FROM `sections` 
			LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
			LEFT JOIN `section_instructors` ON sections.section_id = section_instructors.section_id LEFT JOIN `instructors` ON section_instructors.instructor_id = instructors.instructor_id
			LEFT JOIN `section_specialenrollments` ON sections.section_id = section_specialenrollments.section_id LEFT JOIN `specialenrollments` ON section_specialenrollments.specialEnrollment_id = specialenrollments.specialEnrollment_id
			WHERE sections.section_id LIKE '$semester%' AND sections.section_id LIKE '%" . $course . "%'" . $times . $sortBy . ", sections.section_id ASC LIMIT " . $limit);
			
		if ($sqlSection->num_rows == 0) {
		    exit("No results found matching your search criteria.");
		}
			
		$prevSection = '';
		while($data = $sqlSection->fetch_array()) {
		    if ($data['section_id'] == $prevSection)
		        continue;
	        
			courseSchedulerF($conn, $semester, $courses, $times, $data, [], $sortBy, $limit);
			$prevSection = $data['section_id'];
		}
		
		responseFunction($conn, $GLOBALS['responseArray']);
	}
	
	function courseSchedulerF ($conn, $semester, $courses, $times, $data, $line, $sortBy, $limit) {

		$sqlSection = $conn->query("SELECT sections.building, sections.room, time.time_id, time.startTime, time.endTime, time.monday, time.tuesday, time.wednesday, time.thursday, time.friday FROM `sections`
			LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
			WHERE sections.section_id LIKE '". $data['section_id']."'");
        
        if ($sqlSection->num_rows == 0) {
		    echo("Course Time doesn't line up " . $data['section_id']);
			return;
		}
		
		$times .= " AND NOT sections.section_id LIKE '" . $data['section_id'] . "'";

		$sqlSectionList = $conn->query("SELECT time.time_id, time.startTime, time.endTime, time.monday, time.tuesday, time.wednesday, time.thursday, time.friday FROM `sections` 
			LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
			WHERE sections.section_id LIKE '". $data['section_id']."'");

		$sectionPartDone = [$data['time_id']];

		while($data3 = $sqlSectionList->fetch_array()) { 
			if(!in_array($data3['time_id'], $sectionPartDone)) {
				if ($conn->query("SELECT time.time_id FROM `sections` 
						LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
						WHERE time.time_id = '" . $data3['time_id'] . "' " . $times)->num_rows == 0) 
					return;
					
				if ($data3['time_id'] != '5') {
					if ($data3['monday'] == 1) {
						$times .= " AND NOT (time.monday = 1 AND (time.startTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "' OR time.endTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "'))";
					}
					if ($data3['tuesday'] == 1) {
						$times .= " AND NOT (time.tuesday = 1 AND (time.startTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "' OR time.endTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "'))";
					}
					if ($data3['wednesday'] == 1) {
						$times .= " AND NOT (time.wednesday = 1 AND (time.startTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "' OR time.endTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "'))";
					}
					if ($data3['thursday'] == 1) {
						$times .= " AND NOT (time.thursday = 1 AND (time.startTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "' OR time.endTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "'))";
					}
					if ($data3['friday'] == 1) {
						$times .= " AND NOT (time.friday = 1 AND (time.startTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "' OR time.endTime BETWEEN '" . $data3['startTime'] . "' AND '" . $data3['endTime'] . "'))";
					}
				}
			}
		}

		if ($data['time_id'] != '5') {
			if ($data['monday'] == 1) {
				$times .= " AND NOT (time.monday = 1 AND (time.startTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "' OR time.endTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "'))";
			}
			if ($data['tuesday'] == 1) {
				$times .= " AND NOT (time.tuesday = 1 AND (time.startTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "' OR time.endTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "'))";
			}
			if ($data['wednesday'] == 1) {
				$times .= " AND NOT (time.wednesday = 1 AND (time.startTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "' OR time.endTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "'))";
			}
			if ($data['thursday'] == 1) {
				$times .= " AND NOT (time.thursday = 1 AND (time.startTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "' OR time.endTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "'))";
			}
			if ($data['friday'] == 1) {
				$times .= " AND NOT (time.friday = 1 AND (time.startTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "' OR time.endTime BETWEEN '" . $data['startTime'] . "' AND '" . $data['endTime'] . "'))";
			}
		}
		
        if(count($courses) > 0) {
            array_push($line, $data['section_id']);
            
    		$course = $courses[0];
    		$courses = array_splice($courses, 1);
    
    	    $sqlSection2 = $conn->query("SELECT sections.section_id, sections.available, sections.enrolled, sections.onHold, sections.building, sections.room, sections.sectionComment, time.time_id, time.startTime, time.endTime, time.monday, time.tuesday, time.wednesday, time.thursday, time.friday, instructors.instructorName, instructors.rmpOverall, instructors.rmpTakeAgain, instructors.instructor_id, instructors.rmpDifficulty, instructors.rmpLink, specialenrollments.specialEnrollment_id, specialenrollments.specialEnrollment FROM `sections` 
    		LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
    		LEFT JOIN `section_instructors` ON sections.section_id = section_instructors.section_id LEFT JOIN `instructors` ON section_instructors.instructor_id = instructors.instructor_id
    		LEFT JOIN `section_specialenrollments` ON sections.section_id = section_specialenrollments.section_id LEFT JOIN `specialenrollments` ON section_specialenrollments.specialEnrollment_id = specialenrollments.specialEnrollment_id
    		WHERE sections.section_id LIKE '$semester%' AND sections.section_id LIKE '%" . $course . "%'" . $times . $sortBy . ", sections.section_id ASC LIMIT " . $limit);
		
        	$prevSection = '';
        	while($data2 = $sqlSection2->fetch_array()) {
        	    if ($data2['section_id'] == $prevSection) {
        	        continue;}
        		courseSchedulerF($conn, $semester, $courses, $times, $data2, $line, $sortBy, $limit);
        		$prevSection = $data2['section_id'];
        	}
		}
		else {
		    array_push($line, $data['section_id']);
			array_push($GLOBALS['responseArray'], $line);
		}
	}
	
	function responseFunction($conn, $responseArr) {
	    $scheduleNum = 1;
	    $response = '';
	    foreach ($responseArr as $line) {
	        $response .= '
	          <div class="panel panel-default" style="background-color:#f9f9f9; ">
						<div class="panel-heading" style="margin-bottom:20px"><h2 style="margin-top:10px">Schedule ' . $scheduleNum++ . '</h2></div>';
						
	        foreach ($line as $section) {
	            $sqlSection = $conn->query("SELECT sections.section_id, sections.available, sections.enrolled, sections.onHold, sections.building, sections.room, sections.sectionComment, time.time_id, time.startTime, time.endTime, time.monday, time.tuesday, time.wednesday, time.thursday, time.friday, instructors.instructorName, instructors.rmpOverall, instructors.rmpTakeAgain, instructors.instructor_id, instructors.rmpDifficulty, instructors.rmpLink, specialenrollments.specialEnrollment_id, specialenrollments.specialEnrollment FROM `sections` 
					LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
					LEFT JOIN `section_instructors` ON sections.section_id = section_instructors.section_id LEFT JOIN `instructors` ON section_instructors.instructor_id = instructors.instructor_id
					LEFT JOIN `section_specialenrollments` ON sections.section_id = section_specialenrollments.section_id LEFT JOIN `specialenrollments` ON section_specialenrollments.specialEnrollment_id = specialenrollments.specialEnrollment_id
					WHERE sections.section_id LIKE '%" . $section . "'");

				$prevSection = "";

				while($data2 = $sqlSection->fetch_array()) {
                    if ($prevSection == $data2['section_id'])
    					continue;
    					
					$sectionNum = substr($data2['section_id'], -2);
					$sectionNum = str_replace('-', '', $sectionNum);
					
$courseNum = substr($data2['section_id'], strpos($data2['section_id'], '-') + 1);
$courseNum = substr($courseNum, 0, strrpos( $courseNum, '-'));
				$courseNum = str_replace('-', ' ', $courseNum);
				
					$mondayBack = 'white'; $tuesdayBack = 'white'; $wednesdayBack = 'white'; $thursdayBack = 'white'; $fridayBack = 'white';
					$mondayFont = 'black'; $tuesdayFont = 'black'; $wednesdayFont = 'black'; $thursdayFont = 'black'; $fridayFont = 'black';
					if ($data2['monday'] == 1) {$mondayBack = '#337ab7'; $mondayFont = 'white';}
					if ($data2['tuesday'] == 1) {$tuesdayBack = '#337ab7'; $tuesdayFont = 'white';}
					if ($data2['wednesday'] == 1) {$wednesdayBack = '#337ab7'; $wednesdayFont = 'white';}
					if ($data2['thursday'] == 1) {$thursdayBack = '#337ab7'; $thursdayFont = 'white';}
					if ($data2['friday'] == 1) {$fridayBack = '#337ab7'; $fridayFont = 'white';}

					$response.= '
					<div class="row">
						<div class="panel panel-default" style="margin-left:auto; margin-right:auto; width:90%">
							<div class="row" style="margin-right:0; margin-left:0">
								<div class="col-sm-3" style="text-align:center"><h3><strong>' . $courseNum . '</strong></h3>
									<p style="margin-top:10px">Section</p>
									<h2><strong>'.$sectionNum.'</strong></h2>';
									
					if ($data2['enrolled'] + $data2['available'] != 0)
					    $response .= '
									<div class="progress" style="text-align: right; height: 20px; margin-bottom:10px">
										<div class="progress-bar" style="padding-left: 10px; width: '. ($data2['enrolled'] / ($data2['enrolled'] + $data2['available']) * 100) .'%; height: 20px;" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"><span style="color: white; float:left;">'.(int)($data2['enrolled'] / ($data2['enrolled'] + $data2['available']) * 100).'%</span></div>
									</div>
									<p>'.$data2['enrolled'].'/'.($data2['enrolled'] + $data2['available']).'</p>';
					else
					    $response .= '
									<div class="progress" style="text-align: right; height: 20px; margin-bottom:10px">
										<div class="progress-bar" style="padding-left: 10px; width: 100%; height: 20px;" role="progressbar" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"><span style="color: white; float:left;"></span></div>
									</div>
					                <p>On Hold</p>';

					if ($data2['sectionComment'] != NULL) {
						$response .= '
									<br>
									<button type="button" style="margin-bottom:10px" data-placement="bottom" class="btn btn-md" data-toggle="popover" title="" data-content="'. $data2['sectionComment'] . '" data-original-title="Section Comment">Section Comment</button>
									<script>
										$(document).ready(function(){
										    $(\'[data-toggle="popover"]\').popover();   
										});
									</script>';
					}
					if ($data2['specialEnrollment'] != NULL) {
						$response .= '
									<br>
									<button type="button" style="margin-bottom:10px" data-placement="bottom" class="btn btn-md" data-toggle="popover" title="" data-content="'. $data2['specialEnrollment'];

						$sqlSectionList = $conn->query("SELECT specialenrollments.specialEnrollment_id, specialenrollments.specialEnrollment FROM `sections` 
							LEFT JOIN `section_specialenrollments` ON sections.section_id = section_specialenrollments.section_id LEFT JOIN `specialenrollments` ON section_specialenrollments.specialEnrollment_id = specialenrollments.specialEnrollment_id
							WHERE sections.section_id LIKE '". $data2['section_id']."'");

						$sectionPartDone = [$data2['specialEnrollment_id']];

						while($data3 = $sqlSectionList->fetch_array()) { 
							if(!in_array($data3['specialEnrollment_id'], $sectionPartDone)) {
								$response .= ', '.
										$data3['specialEnrollment'];
							}
						}
						$response .= '
									" data-original-title="Special Enrollment">Special Enrollment</button>
									<script>
										$(document).ready(function(){
										    $(\'[data-toggle="popover"]\').popover();   
										});
									</script>';
					}
					

					$response .= '
								</div>';

					if ($data2['time_id'] == '5') {
						$response.=	'
									<div class="col-sm-6" style="text-align:center; margin-bottom:auto">
										<div>
											<h3>No Time Indicated</h3>
										</div>
										<div>
											<h3><strong>'.$data2['building'].'</strong></h3>
										</div>
										<div>
											<h4 style="margin-top:0"><strong>'.$data2['room'].'</strong></h4>
										</div>';
					}	
					else {
						$response.=	'
									<div class="col-sm-6" style="text-align:center; margin-bottom:auto">
										<div class="panel panel-default" style="margin-left:8.333%; margin-right:8.333%; border-top:0; margin-bottom:0">
											<div class="row">
												<div class="container-fluid" style="width:100%; margin-bottom: 15px">
													<div class="col-xs-2" style="width:20%">
														<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$mondayBack.'">
															<h3 style="color: '.$mondayFont.'; margin-top: 10px"><strong>M</strong></h3>
														</div>
													</div>
													<div class="col-xs-2" style="width:20%">
														<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$tuesdayBack.'">
															<h3 style="color: '.$tuesdayFont.'; margin-top: 10px"><strong>T</strong></h3>
														</div>
													</div>
													<div class="col-xs-2" style="width:20%">
														<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$wednesdayBack.'">
															<h3 style="color: '.$wednesdayFont.'; margin-top: 10px"><strong>W</strong></h3>
														</div>
													</div>
													<div class="col-xs-2" style="width:20%">
														<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$thursdayBack.'">
															<h3 style="color: '.$thursdayFont.'; margin-top: 10px"><strong>T</strong></h3>
														</div>
													</div>
													<div class="col-xs-2" style="width:20%">
														<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$fridayBack.'">
															<h3 style="color: '.$fridayFont.'; margin-top: 10px"><strong>F</strong></h3>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div>
											<h3><strong>'.date("g:i", strtotime($data2['startTime'])).' - '.date("g:i", strtotime($data2['endTime'])).'</strong></h3>
										</div>
										<div>
											<h3><strong>'.$data2['building'].'</strong></h3>
										</div>
										<div>
											<h4 style="margin-top:0"><strong>'.$data2['room'].'</strong></h4>
										</div>';
					}

					$sqlSectionList = $conn->query("SELECT sections.building, sections.room, time.time_id, time.startTime, time.endTime, time.monday, time.tuesday, time.wednesday, time.thursday, time.friday FROM `sections` 
						LEFT JOIN `section_times` ON sections.section_id = section_times.section_id LEFT JOIN `time` ON section_times.time_id = time.time_id
						WHERE sections.section_id LIKE '". $data2['section_id']."'");

					$sectionPartDone = [$data2['time_id']];
					while($data3 = $sqlSectionList->fetch_array()) { 
						if(!in_array($data3['time_id'], $sectionPartDone)) {
							$mondayBack = 'white'; $tuesdayBack = 'white'; $wednesdayBack = 'white'; $thursdayBack = 'white'; $fridayBack = 'white';
							$mondayFont = 'black'; $tuesdayFont = 'black'; $wednesdayFont = 'black'; $thursdayFont = 'black'; $fridayFont = 'black';
							if ($data3['monday'] == 1) {$mondayBack = '#337ab7'; $mondayFont = 'white';}
							if ($data3['tuesday'] == 1) {$tuesdayBack = '#337ab7'; $tuesdayFont = 'white';}
							if ($data3['wednesday'] == 1) {$wednesdayBack = '#337ab7'; $wednesdayFont = 'white';}
							if ($data3['thursday'] == 1) {$thursdayBack = '#337ab7'; $thursdayFont = 'white';}
							if ($data3['friday'] == 1) {$fridayBack = '#337ab7'; $fridayFont = 'white';}

							if($data3['time_id'] == '5') {
								$response .= '
									<hr style ="margin-bottom: 0; margin-top: 0; height: 1px; margin-left: -15px; margin-right: -15px; width:auto">
									<div>
										<h3><strong>'.$data3['building'].'</strong></h3>
									</div>
									<div>
										<h4 style="margin-top:0"><strong>'.$data3['room'].'</strong></h4>
									</div>';
							}
							else {
								$response.=	'
								<hr style ="margin-bottom: 0; margin-top: 0; height: 1px; margin-left: -15px; margin-right: -15px; width:auto">
									<div class="panel panel-default" style="margin-left:8.333%; margin-right:8.333%; border-top:0; margin-bottom:0">
										<div class="row">
											<div class="container-fluid" style="width:100%; margin-bottom: 15px">
												<div class="col-xs-2" style="width:20%">
													<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$mondayBack.'">
														<h3 style="color: '.$mondayFont.'; margin-top: 10px"><strong>M</strong></h3>
													</div>
												</div>
												<div class="col-xs-2" style="width:20%">
													<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$tuesdayBack.'">
														<h3 style="color: '.$tuesdayFont.'; margin-top: 10px"><strong>T</strong></h3>
													</div>
												</div>
												<div class="col-xs-2" style="width:20%">
													<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$wednesdayBack.'">
														<h3 style="color: '.$wednesdayFont.'; margin-top: 10px"><strong>W</strong></h3>
													</div>
												</div>
												<div class="col-xs-2" style="width:20%">
													<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$thursdayBack.'">
														<h3 style="color: '.$thursdayFont.'; margin-top: 10px"><strong>T</strong></h3>
													</div>
												</div>
												<div class="col-xs-2" style="width:20%">
													<div class="panel panel-default" style="border-top:0; margin-bottom:0; background-color: '.$fridayBack.'">
														<h3 style="color: '.$fridayFont.'; margin-top: 10px"><strong>F</strong></h3>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div>
										<h3><strong>'.date("g:i", strtotime($data3['startTime'])).' - '.date("g:i", strtotime($data3['endTime'])).'</strong></h3>
									</div>
									<div>
										<h3><strong>'.$data3['building'].'</strong></h3>
									</div>
									<div>
										<h4 style="margin-top:0"><strong>'.$data3['room'].'</strong></h4>
									</div>';
							}

							array_push($sectionPartDone, $data3['time_id']);
						}
					}

					$response .= '</div>';
					

					if($data2['rmpLink'] == NULL) {
						if($data2['instructorName'] != NULL) {
							$response .='
								<div class="col-sm-3">
									<h4 style="text-align: center;"><strong><a href= "http://www.ratemyprofessors.com" target="_blank" style="color:black">'.$data2['instructorName'].'</a></strong></h4>
									<hr>
									<p style="text-align:center">We could not find a Rate My Professor page for this instructor.</p>
								';
						}
						else {
							$response .= '
								<div class="col-sm-3">';
						}
					}
					else {
						$response .='
								<div class="col-sm-3">
									<h4 style="text-align: center;"><strong><a href= "http://www.ratemyprofessors.com'. $data2['rmpLink'] .'" target="_blank">'.$data2['instructorName'].'</a></strong></h4>
									<p style="text-align: center; clear: both; margin-bottom:0">Overall</p>
									<div class="progress" style="text-align: center; height: 20px;">
										<div class="progress-bar" style="padding-left: 10px; text-align: center; width: '. ($data2['rmpOverall'] / 5 * 100).'%; height: 20px;" role="progressbar" aria-valuenow="'.$data2['rmpOverall'].'" aria-valuemin="1" aria-valuemax="5"><span style="color: white; float:left;"> <p>'.($data2['rmpOverall'] / 5 * 100).'%</p></span>
										</div>
									</div>
									<p style="text-align: center; clear: both; margin-bottom:0">Difficulty</p>
									<div class="progress" style="height: 20px; width: 80%; margin-left: 10%;">
										<div class="progress-bar" style="padding-left: 10px; text-align: center; width: '. ($data2['rmpDifficulty'] / 5 * 100).'%; height: 20px;" role="progressbar" aria-valuenow="'.$data2['rmpDifficulty'].'" aria-valuemin="1" aria-valuemax="5"><span style="color: white; float:left;"> <p>'.($data2['rmpDifficulty'] / 5 * 100).'%</p></span>
										</div>
									</div>';
					}
					
					$sectionPartDone = [$data2['instructor_id']];

					$sqlSectionList = $conn->query("SELECT instructors.instructor_id, instructors.instructorName, instructors.rmpOverall, instructors.rmpTakeAgain, instructors.rmpDifficulty, instructors.rmpLink FROM `sections` 
						LEFT JOIN `section_instructors` ON sections.section_id = section_instructors.section_id LEFT JOIN `instructors` ON section_instructors.instructor_id = instructors.instructor_id
						WHERE sections.section_id LIKE '". $data2['section_id']."'");

					while($data3 = $sqlSectionList->fetch_array()) { 
						if(!in_array($data3['instructor_id'], $sectionPartDone)) {
							if($data3['rmpLink'] == NULL) {
								if($data3['instructorName'] != NULL) {
									$response .='
									<hr style="height:1px; margin-left:-15px; margin-right:-15px; width:auto">
									<h4 style="text-align: center;"><strong><a href= "http://www.ratemyprofessors.com" target="_blank" style="color:black">'.$data3['instructorName'].'</a></strong></h4>
									<hr>
									<p style="text-align:center">We could not find a Rate My Professor page for this instructor.</p>';
								}
							}
							else {
								$response .='
									<hr style="height: 1px; margin-left:-15px; margin-right:-15px; width:auto">
									<h4 style="text-align: center;"><strong><a href= "http://www.ratemyprofessors.com'. $data3['rmpLink'] .'" target="_blank">'.$data3['instructorName'].'</a></strong></h4>
									<p style="text-align: center; clear: both; margin-bottom:0">Overall</p>
									<div class="progress" style="text-align: center; height: 20px;">
										<div class="progress-bar" style="padding-left: 10px; text-align: center; width: '. ($data3['rmpOverall'] / 5 * 100).'%; height: 20px;" role="progressbar" aria-valuenow="'.$data3['rmpOverall'].'" aria-valuemin="1" aria-valuemax="5">
											<span style="color: white; float:left;"> <p>'.($data3['rmpOverall'] / 5 * 100).'%</p></span>
										</div>
									</div>
									<p style="text-align: center; clear: both; margin-bottom:0">Difficulty</p>
									<div class="progress" style="height: 20px; width: 80%; margin-left: 10%;">
										<div class="progress-bar" style="padding-left: 10px; text-align: center; width: '. ($data3['rmpDifficulty'] / 5 * 100).'%; height: 20px;" role="progressbar" aria-valuenow="'.$data3['rmpDifficulty'].'" aria-valuemin="1" aria-valuemax="5">
											<span style="color: white; float:left;"> <p>'.($data3['rmpDifficulty'] / 5 * 100).'%</p></span>
										</div>
									</div>';
							}
						}
					}
					$response .= '
								</div>
							</div>
						</div>
					</div>';
					$prevSection = $data2['section_id'];
				}
	        }
	        
	        $response .= '
	          </div>';
	    }
	    if ($response != '')
	        exit($response);
        else
           exit("No results found matching your search criteria.");
	}
	courseSchedulerS();
?>