def main():
	# Just LSU for now
	school = 'lsu'

	print(school)

	import pymysql  
	db = pymysql.connect(user='root', password='', host='localhost', database='test2')

	import Department_Scraper
	semDeptList = Department_Scraper.deptScraper()

	import semesters as semestersClass
	semestersClass._semesters(db, school, semDeptList[0], semDeptList[1])
	#semestersClass._semesters(db, school, ['Fall 2018'], semDeptList[1])

main()