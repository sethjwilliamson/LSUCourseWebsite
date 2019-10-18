<?php
	if (isset($_POST['getData'])) {
		$conn = new mysqli('localhost', 'coursesatlsu_root', 'j-YR(1pB~AV6', 'coursesatlsu_data');

		$semester = $conn->real_escape_string($_POST['semester']);
		$department = $conn->real_escape_string($_POST['department']);

		$sqlSemesters = $conn->query("SELECT `course_id` FROM `courses` WHERE `course_id` LIKE '%" . $semester . '-' . $department . "%'");

		$response = '
          <input type="text" placeholder="Search.." id="myInputCourse' . rand() . '" onkeyup="filterFunctionCourse(this.parentNode)">';

		while($data = $sqlSemesters->fetch_array()) {
			$course = substr($data['course_id'], strpos($data['course_id'], '-') + 1);
			$course = substr($course, strpos($course, '-') + 1);

			$response .= '
				<li role="presentation">
				    <a role="menuitem" href="#" onclick="getSections(\''. $semester .'\', \''. $department .'\', \''. $course .'\', this.parentNode.parentNode.parentNode.parentNode)">'. $course .'</a>
			    </li>
			';
		}
		exit($response);
	}
?>