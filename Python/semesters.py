def _semesters(db, school, semesters, departments):
	import departments as departmentsClass

	for i in range(0, 15): #len(semesters)):

		print(school + ' - ' + semesters[i])

		cursor = db.cursor()
		cursor.execute("""INSERT INTO `semesters` (`semester`, `departments`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE `semester` = %s, `departments` = %s;""", (semesters[i], '?', semesters[i], '?'))
		cursor.close()
		db.commit()

		departmentsClass._departments(db, school, semesters[i], departments)
	db.close()