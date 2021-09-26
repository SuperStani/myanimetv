import requests
from bs4 import BeautifulSoup

my_headers = {}
my_headers['User-Agent'] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36"

class AnimeWorld():

    def __init__(self, base_url):
        scrape = BeautifulSoup(requests.get(base_url, headers=my_headers).text, 'html.parser')
        self.info = {}
        titles = scrape.find('h2', {"class": "title"})
        self.info["name"] = titles.text
        self.info["alternative-title"] = titles.get('data-jtitle')
        server = scrape.find("div", {"class": "server", "data-name": 9})
        self.info["episodes"] = []
        self.info["error"] = None
        if server is not None:
            episodes_li = server.findAll("li", {"class": "episode"})
            for row in episodes_li:
                episode_dict = "https://www.animeworld.tv" + row.find("a").get("href")
                self.info["episodes"].append(episode_dict)
                
    def getDirectLink(episode_url):
        scrape = BeautifulSoup(requests.get(episode_url, headers=my_headers).text, 'html.parser')
        div = scrape.find('div', {"id": "download"}) 
        url = div.find('a').get("href")
        direct_url = url.replace("download-file.php?id=", "")
        return direct_url

    def search(keyword):
        result = []
        scrape = BeautifulSoup(requests.get("https://www.animeworld.tv/search?keyword={}".format(keyword.replace(" ", "+")), headers=my_headers).text, 'html.parser')
        items = scrape.find("div", {"class": "film-list"}).findAll("div", {"class": "item"})
        for row in items:
            results = {}
            info = row.find("a", {"class": "name"})
            results["name"] = info.text
            results["url"] = "https://www.animeworld.tv" + info.get("href")
            results["img"] = row.find("img").get("src")
            result.append(results)
        return result
