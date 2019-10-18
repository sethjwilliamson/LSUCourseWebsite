<?php
	if (isset($_POST['getData'])) {
		$conn = new mysqli('localhost', 'coursesatlsu_root', 'j-YR(1pB~AV6', 'coursesatlsu_data');

		$sqlSemesters = $conn->query("SELECT `semester` FROM `semesters`");

		$response = '
          <input type="text" placeholder="Search.." id="myInputSemester" onkeyup="filterFunctionSemester()">';

		while($data = $sqlSemesters->fetch_array()) {
			$response .= '
				<li role="presentation">
				    <a role="menuitem" href="#" onclick="getDepartments(\''. $data['semester'] .'\')">'. $data['semester'] .'</a>
			    </li>
			';
		}
		exit($response);
	}
?>