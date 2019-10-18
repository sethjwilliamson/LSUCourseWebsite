def _department(db, school, semester, department):
	import time as time2
	start = time2.time()

	from urllib.request import urlopen as uReq
	import urllib
	from bs4 import BeautifulSoup as soup
	import re

	myurl = 'http://appl101.lsu.edu/booklet2.nsf/739a404981df154f862582650053221e?CreateDocument'

	data = urllib.parse.urlencode({'SemesterDesc':semester, 'Department':department}).encode("utf-8")

	req = urllib.request.Request(myurl, data=data, method='POST')

	uClient = uReq(req)
	page_html = uClient.read()
	uClient.close()

	page_soup = soup(page_html, "html.parser")

	try:
		courses = str(page_soup.find('pre').getText())
	except:
		print(str(time2.time() - start) + ': ' + school + ' - ' + semester + ' - ' + department)
		return

	courseList = courses.splitlines()

	deptAbbr = courseList[4][10:16].strip()

	cursor = db.cursor()
	cursor.execute("""INSERT INTO `departments` (`department_id`, `departmentName`, `courses`) VALUES (%s, %s, %s) 
		ON DUPLICATE KEY UPDATE `department_id` = %s, `departmentName` = %s, `courses` = %s;""", 
		(semester + '-' + deptAbbr, department, '?', semester + '-' + deptAbbr, department, '?'))
	cursor.close()

	cursor = db.cursor()
	cursor.execute("""SELECT `departments` FROM `semesters` WHERE `semester` = %s""", (semester))
	semesterFetch = cursor.fetchone()

	if semesterFetch == None or semesterFetch[0] == '?':
		semesterFetch = ['']

	cursor.execute("""UPDATE `semesters` SET `departments` = %s WHERE `semester` = %s""", (semesterFetch[0] + '|' + deptAbbr + '~' + department, semester))
	cursor.close()
	db.commit()

	commentStatic = ''
	coursesDone = []

	for i in range(4, len(courseList) - 35):
		line = courseList[i]

		if line.strip().startswith('SESSION') or line.strip() == '':
			continue

		if line.strip().startswith('**'):
			if line.strip().startswith('***'): # Add comment to next line
				commentStatic  += ' ' + line[26:].strip()
			else: # Add comment to previous line
				prevLine = courseList[i - 1]

				lineDifference = 1
				while prevLine[:21].strip() == '' or prevLine.strip().startswith('**'):
					prevLine = courseList[i - lineDifference]
					lineDifference += 1	

				#####
			continue
	
		comment = commentStatic
		commentStatic = ''

		available = None #int
		enrolled = None #int
		courseNum = None #int
		courseType = None #str
		sectionNumber = None #int
		courseName = None #str
		hours = None #int
		time = None #str
		days = None #str
		room = None #str
		building = None #str
		specialEnrollment = None #str
		instructor = None #str
		onHold = False

		try:
			if line.startswith('(F)'):
				available = 0
			elif line.startswith('(H)'):
				available = 0
				onHold = True
			else:
				available = line[:4].strip()
				if available != '': available = int(available)

			enrolled = line[4:10].strip()
			if enrolled != '': enrolled = int(enrolled)
			else: enrolled = 0
			courseNum = line[16:21].strip()
			courseType = line[21:26].strip()
			sectionNumber = line[26:31].strip()
			if sectionNumber != '': sectionNumber = int(sectionNumber)
			courseName = line[31:54].strip()
			hours = line[54:59].strip()
			time = line[59:70].strip()
			days = line[72:78] + '    ' # Hard coded Space since there will be string out of bounds for labs due to line ending
			room = line[79:84].strip()
			building = line[84:99].strip()
			specialEnrollment = line[100:116].strip()
			instructor = line[117:].strip()
		except Exception as e:
			print(str(e) + ': '+ department)
			continue

		if '-' in time:
			times = time.split('-')
			isNight = 'N' in time
			times[1] = times[1].replace('N', '')

			for j in range(0, len(times)):
				if isNight or int(times[j]) < 600:
					times[j] = int(times[j]) + 1200
				times[j] = str(times[j])

			startTime = times[0][:-2] + ':' + times[0][-2:]
			endTime = times[1][:-2] + ':' + times[1][-2:]
		elif 'TBA' in time:
			startTime = '0:00'
			endTime = '0:00'

		monday = 0
		tuesday = 0
		wednesday = 0
		thursday = 0
		friday = 0

		if 'M' in days: monday = 1
		if days[1] == 'T': tuesday = 1
		if 'W' in days: wednesday = 1
		if days[3] == 'T' or 'H' in days: thursday = 1
		if 'F' in days: friday = 1

		if line[:21].strip() == '': # Add items to previous line
			prevLine = courseList[i - 1]

			lineDifference = 1
			while prevLine[:21].strip() == '': # Some classes require additional times / instructors
				prevLine = courseList[i - lineDifference]
				lineDifference += 1	

			courseNum = prevLine[16:21].strip()
			sectionNumber = prevLine[26:31].strip()

		if line[116:].strip() != '':
			#Append instructor to section
			try:
				cursor = db.cursor()
				cursor.execute("""SELECT * FROM `instructors` WHERE `instructorName` LIKE %s ORDER BY `rmpOverall` DESC""", (instructor))
				fetch = cursor.fetchall()
				if len(fetch) == 1:
					cursor.execute("""INSERT INTO `section_instructors` (`section_id`, `instructor_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
				elif len(fetch) == 0:
					cursor.execute("""INSERT INTO `instructors` (`instructor_id`, `instructorName`, `rmpOverall`, `rmpTakeAgain`, `rmpDifficulty`, `rmpLink`) VALUES (NULL, %s, NULL, NULL, NULL, NULL)""", 
						(instructor))
					#fetch after insert?
					cursor.execute("""SELECT * FROM `instructors` WHERE `instructorName` LIKE %s ORDER BY `rmpOverall` DESC""", (instructor))
					fetch = cursor.fetchall()
					cursor.execute("""INSERT INTO `section_instructors` (`section_id`, `instructor_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
				else:
					# 2+ results for one initial + last name
					cursor.execute("""INSERT INTO `section_instructors` (`section_id`, `instructor_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
			finally:
				cursor.close()

		if line[59:78].strip() != '':
			#Append time to section
			try:
				cursor = db.cursor()
				cursor.execute("""SELECT * FROM `time` WHERE `startTime` = %s AND `endTime` = %s AND `monday` = %s AND `tuesday` = %s AND `wednesday` = %s AND `thursday` = %s AND `friday` = %s""", 
					(startTime, endTime, monday, tuesday, wednesday, thursday, friday))
				fetch = cursor.fetchall()
				if len(fetch) == 1:
					cursor.execute("""INSERT INTO `section_times` (`section_id`, `time_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
				else:
					cursor.execute("""INSERT INTO `time` (`time_id`, `startTime`, `endTime`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s)""", 
						(startTime, endTime, monday, tuesday, wednesday, thursday, friday))
					#fetch after insert?
					cursor.execute("""SELECT * FROM `time` WHERE `startTime` = %s AND `endTime` = %s AND `monday` = %s AND `tuesday` = %s AND `wednesday` = %s AND `thursday` = %s AND `friday` = %s""", 
						(startTime, endTime, monday, tuesday, wednesday, thursday, friday))
					fetch = cursor.fetchall()
					cursor.execute("""INSERT INTO `section_times` (`section_id`, `time_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
			finally:
				cursor.close()

		if line[100:116].strip() != '':
			#Append Special Enrollment
			try:
				cursor = db.cursor()
				cursor.execute("""SELECT * FROM `specialenrollments` WHERE `specialEnrollment` LIKE %s""", 
					(specialEnrollment))
				fetch = cursor.fetchall()
				if len(fetch) == 1:
					cursor.execute("""INSERT INTO `section_specialenrollments` (`section_id`, `specialEnrollment_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
				else:
					cursor.execute("""INSERT INTO `specialenrollments` (`specialEnrollment_id`, `specialEnrollment`) VALUES (NULL, %s)""", 
						(specialEnrollment))
					#fetch after insert?
					cursor.execute("""SELECT * FROM `specialenrollments` WHERE `specialEnrollment` LIKE %s""", 
						(specialEnrollment))
					fetch = cursor.fetchall()
					cursor.execute("""INSERT INTO `section_specialenrollments` (`section_id`, `specialEnrollment_id`) VALUES (%s, %s) ON DUPLICATE KEY UPDATE section_id=section_id""", 
						(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), fetch[0][0]))
			finally:
				cursor.close()

		if line[:21].strip() == '':
			continue

		courseExists = False
		courseIndex = None

		for j in range(0, len(coursesDone)):
			if coursesDone[j] == courseNum:
				courseIndex = j
				courseExists = True
				break
			courseExists = False

		if courseExists:
			cursor = db.cursor()
			cursor.execute("""INSERT INTO `sections` (`section_id`, `available`, `enrolled`, `onHold`, `room`, `building`, `sectionComment`) 
				VALUES (%s, %s, %s, %s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE `available` = %s, `enrolled` = %s, `onHold` = %s, `room` = %s, `building` = %s, `sectionComment` = %s;""", 
				(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), available, enrolled, onHold, str(room), building, comment, 
				available, enrolled, onHold, room, building, comment))
			cursor.close()
		else:
			cursor = db.cursor()
			cursor.execute("""SELECT `courseName`, `courseCatalogDesc` FROM `courses` WHERE `course_id` LIKE %s""", ('%' + deptAbbr + '-' + courseNum + '%'))
			
			try:
				#print(deptAbbr + courseNum)
				fetchCatalog = cursor.fetchall()
				#print(fetchCatalog)
				courseCatalogDesc = fetchCatalog[0][1]
				courseName = fetchCatalog[0][0]
			except:
				courseCatalogDesc = None
			finally:
				cursor.execute("""INSERT INTO `courses` (`course_id`, `courseCatalogDesc`, `courseComment`, `courseCreditHours`, `courseName`, `sections`) 
					VALUES (%s, %s, %s, %s, %s, %s) ON DUPLICATE KEY UPDATE `courseComment` = %s, `courseCreditHours` = %s, `courseName` = %s, `sections` = %s;""", 
					(semester + '-' + deptAbbr + '-' + courseNum, courseCatalogDesc, comment, hours, courseName, '?', comment, hours, courseName, '?'))
			cursor.execute("""INSERT INTO `sections` (`section_id`, `available`, `enrolled`, `onHold`, `room`, `building`, `sectionComment`) 
				VALUES (%s, %s, %s, %s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE `available` = %s, `enrolled` = %s, `onHold` = %s, `room` = %s, `building` = %s, `sectionComment` = %s;""", 
				(semester + '-' + deptAbbr + '-' + courseNum + '-' + str(sectionNumber), available, enrolled, onHold, str(room), building, comment, 
				available, enrolled, onHold, room, building, comment))
			cursor.close()
			coursesDone.append(courseNum)
	coursesStr = ''
	for i in coursesDone:
		coursesStr += '|' + i
	cursor = db.cursor()
	cursor.execute("""UPDATE `departments` SET `courses` = %s WHERE `department_id` = %s""", (coursesStr, semester + '-' + deptAbbr))
	cursor.close()

	db.commit()
	print(str(time2.time() - start) + ': ' + school + ' - ' + semester + ' - ' + department)

#import pymysql 
#_department(pymysql.connect(user='root', password='', host='localhost', database='test2'), 'lsu', 'Spring 2018', 'COMPUTER SCIENCE')