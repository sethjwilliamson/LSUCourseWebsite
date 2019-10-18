<?php
	if (isset($_POST['getData'])) {
		$conn = new mysqli('localhost', 'coursesatlsu_root', 'j-YR(1pB~AV6', 'coursesatlsu_data');

		$semester = $conn->real_escape_string($_POST['semester']);
		$department = $conn->real_escape_string($_POST['department']);
		$course = $conn->real_escape_string($_POST['course']);

		$sqlSemesters = $conn->query("SELECT `section_id` FROM `sections` WHERE `section_id` LIKE '%" . $semester . '-' . $department . '-' . $course . "%'");
		
		$response = '
          <input type="text" placeholder="Search.." id="myInputSection' . rand() . '" onkeyup="filterFunctionSection(this.parentNode)">';

		while($data = $sqlSemesters->fetch_array()) {
			$section = substr($data['section_id'], strpos($data['section_id'], '-') + 1);
			$section = substr($section, strpos($section, '-') + 1);
			$section = substr($section, strpos($section, '-') + 1);

			$response .= '
				<li role="presentation">
				    <a role="menuitem" href="#" onclick="sectionSelected(\''. $semester .'\', \''. $department .'\', \''. $course .'\', \''. $section .'\', this.parentNode.parentNode.parentNode.parentNode)">'. $section .'</a>
			    </li>
			';
		}
		exit($response);
	}
?>