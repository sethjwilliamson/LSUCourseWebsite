def deptScraper():
	from urllib.request import urlopen as uReq
	from bs4 import BeautifulSoup as soup
	import re

	myurl = 'http://appl101.lsu.edu/booklet2.nsf/Selector2?OpenForm'

	uClient = uReq(myurl)
	page_html = uClient.read()
	uClient.close()

	page_soup = soup(page_html, "html.parser")

	semesters = page_soup.findAll("select")[0]
	departments = page_soup.findAll("select")[1] 

	for i in semesters.find_all('option'):
	    arrSemesters = (str(i.text).split('\n')[:-1])
	    break

	for i in departments.find_all('option'):
	    arrDepartments = (str(i.text).split('\n')[:-1])
	    break

	return [arrSemesters, arrDepartments]