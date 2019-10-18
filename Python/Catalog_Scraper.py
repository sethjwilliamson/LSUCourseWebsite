def cat_scrape():
	from urllib.request import urlopen as uReq
	import urllib
	from bs4 import BeautifulSoup as soup

	import pymysql  
	db = pymysql.connect(user='root', password='', host='localhost', database='test2', use_unicode = True, charset='utf8')

	cursor = db.cursor()
	cursor.execute("""SELECT `course_id` FROM `courses` ORDER BY `courses`.`courseCatalogDesc` ASC""")
	courseList = cursor.fetchall()
	cursor.close()

	for i in range(0, len(courseList)):
		deptNum = courseList[i][0][courseList[i][0].find('-') + 1:]
		deptNum = deptNum.split('-')

		myurl = 'http://catalog.lsu.edu/search_advanced.php?cur_cat_oid=17&search_database=Search&search_db=Search&cpage=1&ecpage=1&ppage=1&spage=1&tpage=1&location=33&filter%5Bkeyword%5D=' + deptNum[0] + '+' + deptNum[1]

		uClient = uReq(myurl)
		page_html = uClient.read()
		uClient.close()

		page_soup = soup(page_html, "html.parser")

		try:
			myurl = 'http://catalog.lsu.edu/' + page_soup.findAll("td", {"class":"td_dark"})[1].a['href']

			uClient = uReq(myurl)
			page_html = uClient.read()
			uClient.close()

			page_soup = soup(page_html, "html.parser")

			catalogText = page_soup.find("td", {"class":"block_content"}).getText()[101:-74]
			fullName = catalogText[catalogText.find(deptNum[1]) + 5 : catalogText.find('(') - 1]
			catalogText = catalogText[catalogText.find(')') + 1:].strip()

			cursor = db.cursor()
			cursor.execute("""UPDATE `courses` SET `courseName`= %s,`courseCatalogDesc`= %s WHERE `course_id` LIKE %s""", (fullName, catalogText, str('%' + deptNum[0] + '-' + deptNum[1] + '%')))
			cursor.close()

			if i % 10 == 0:
				db.commit()
			print(deptNum)
		
		except:
			print('Error ' + str(deptNum))
			continue
cat_scrape()