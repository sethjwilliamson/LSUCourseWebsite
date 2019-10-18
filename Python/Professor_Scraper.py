def prof_scrape(myurl):
	from urllib.request import urlopen as uReq
	from bs4 import BeautifulSoup as soup
	import urllib.request

	class AppURLopener(urllib.request.FancyURLopener):
		version = "Mozilla/5.0"

	opener = AppURLopener()
	uClient = opener.open(myurl)
	page_html = uClient.read()
	uClient.close()

	page_soup = soup(page_html, "html.parser")

	containers = page_soup.findAll("div", {"class":"grade"})

	#print(containers)

	if len(containers) < 3:
		return ['N/A','N/A','N/A','N/A']
	try:
		overall = containers[0].text
		takeAgain = containers[1].text.strip()
		difficulty = containers[2].text.strip()
	except Exception as e:
		print(myurl)
		print(e)

	return [overall, takeAgain, difficulty, myurl]