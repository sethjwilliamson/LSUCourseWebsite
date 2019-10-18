<?php
	if (isset($_POST['getData'])) {
		$conn = new mysqli('localhost', 'coursesatlsu_root', 'j-YR(1pB~AV6', 'coursesatlsu_data');
		$semester = $conn->real_escape_string($_POST['semester']);

		$sqlSemesters = $conn->query("SELECT `department_id` FROM `departments` WHERE `department_id` LIKE '%" . $semester . "%'");

		$response = '
          <input type="text" placeholder="Search.." id="myInputDepartment" onkeyup="filterFunctionDepartment()">';

		while($data = $sqlSemesters->fetch_array()) {
			$department = substr($data['department_id'], strpos($data['department_id'], '-') + 1);
			$response .= '
				<li role="presentation">
				    <a role="menuitem" href="#" onclick="getCourses(\''. $semester .'\', \''. $department .'\')">'. $department .'</a>
				</li>
			';
		}
		exit($response);
	}
?>