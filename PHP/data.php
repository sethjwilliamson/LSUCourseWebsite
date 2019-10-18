<?php
	if (isset($_POST['getData'])) {
		$conn = new mysqli('localhost', 'root', '', 'data');

		$start = $conn->real_escape_string($_POST['start']);
		$limit = $conn->real_escape_string($_POST['limit']);

		$sql = $conn->query("SELECT * FROM `courses` LIMIT $start, $limit");
		if ($sql->num_rows > 0) {
			$response = "";

			while($data = $sql->fetch_array()) {
				$response .= '<div>
						<h2>2</h2>
						<p>2</p>
					</div>';
			}

			exit($response);
		} else
			exit('reachedMax');
	}
?>