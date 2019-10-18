def prof_search(profName):
	from urllib.request import urlopen as uReq
	from bs4 import BeautifulSoup as soup
	import urllib.request

	if profName.find(' ') != -1:
		profLastName = profName[:-2]
		profInitial = profName[-1]
	else:
		profLastName = profName

	myurl = 'http://www.ratemyprofessors.com/search.jsp?queryoption=HEADER&queryBy=teacherName&schoolName=Louisiana+State+University&schoolID=3071&query=' + profLastName

	class AppURLopener(urllib.request.FancyURLopener):
		version = "Mozilla/5.0"

	opener = AppURLopener()
	uClient = opener.open(myurl)
	page_html = uClient.read()
	uClient.close()

	page_soup = soup(page_html, "html.parser")

	containers = page_soup.findAll("li", {"class":"listing PROFESSOR"})

	matchArr = []

	if profName.find(' ') != -1:
		for i in range(0, len(containers)): 
			if (profLastName + ", " + profInitial).lower() == containers[i].a.select('span')[2].select('span')[0].text[:containers[i].a.select('span')[2].select('span')[0].text.find(',') + 3].lower():
				matchArr.append(containers[i])
	else:
		for i in range(0, len(containers)): 
			if profLastName.lower() == containers[i].a.select('span')[2].select('span')[0].text[:containers[i].a.select('span')[2].select('span')[0].text.find(',')].lower():
				matchArr.append(containers[i])

	ratingURL = []
	if len(matchArr) > 0:
		for i in range(0, len(matchArr)):
			ratingURL.append("http://www.ratemyprofessors.com" + matchArr[i].a['href'])

	if len(ratingURL) == 0:
		return ['N/A', 'N/A', 'N/A', 'N/A']
	import Professor_Scraper
	return Professor_Scraper.prof_scrape(ratingURL[0])
#print(prof_search('ro2sby r'))