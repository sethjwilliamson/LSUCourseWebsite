def _departments(db, school, semester, departments):
	import department as departmentClass
	for i in range(0, len(departments)):
		departmentClass._department(db, school, semester, departments[i])