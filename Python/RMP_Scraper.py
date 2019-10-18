from urllib.request import urlopen as uReq
from bs4 import BeautifulSoup as soup
import urllib.request
import pymysql  
cnx = pymysql.connect(user='root', password='', host='localhost', database='test2')

myurl = 'http://www.ratemyprofessors.com/search.jsp?queryBy=teacherName&schoolName=louisiana+state+university'

class AppURLopener(urllib.request.FancyURLopener):
	version = "Mozilla/5.0"

opener = AppURLopener()
uClient = opener.open(myurl)
page_html = uClient.read()
uClient.close()

page_soup = soup(page_html, "html.parser")

containers = page_soup.findAll("div", {"class":"result-count"})

for i in range(0, int(containers[1].getText()[16:21].strip()), 20):
	uClient = opener.open('http://www.ratemyprofessors.com/search.jsp?queryBy=teacherName&schoolName=louisiana+state+university&offset=' + str(i))
	page_html = uClient.read()
	uClient.close()

	page_soup = soup(page_html, "html.parser")

	containers = page_soup.findAll("li", {"class":"listing PROFESSOR"})

	#try:
	for j in range(0, 20):
		myurl = containers[j].a['href']
		instructor = containers[j].a.select('span')[2].select('span')[0].text[:containers[j].a.select('span')[2].select('span')[0].text.find(',') + 3].upper().replace(',', '')

		uClient = opener.open('http://www.ratemyprofessors.com' + myurl)
		page_html = uClient.read()
		uClient.close()

		page_soup = soup(page_html, "html.parser")

		containers2 = page_soup.findAll("div", {"class":"grade"})

		if len(containers2) < 3:
			continue

		overall = containers2[0].text
		takeAgain = containers2[1].text.strip()
		if takeAgain == 'N/A':
			takeAgain = None
		difficulty = containers2[2].text.strip()

		#try:
		cursor = cnx.cursor()
		cursor.execute("""INSERT INTO `instructors` (`instructor_id`, `instructorName`, `rmpOverall`, `rmpTakeAgain`, `rmpDifficulty`, `rmpLink`) VALUES (NULL, %s, %s, %s, %s, %s);""", (instructor, overall, takeAgain, difficulty, myurl))
		#cursor.execute("""SELECT * FROM `instructors`""")
		print("succes?")
	cursor.close()
	cnx.commit()
cnx.close()
		#print (str([overall, takeAgain, difficulty, instructor, myurl]))
		#cursor.execute("INSERT INTO `instructors` (`instructor_id`, `instructorName`, `rmpOverall`, `rmpTakeAgain`, `rmpDifficulty`, `rmpLink`) VALUES (NULL, 'qweasd1', '12', '212', '21', 'kj1n');")#, (instructor, overall, takeAgain, difficulty, myurl))
		#cursor.close()
	#except:
	#	print('done')
