import requests
from bs4 import BeautifulSoup

my_headers = {}
my_headers['User-Agent'] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36"

class AnimeWorld():
    def __init__(self, base_url):
        scrape = BeautifulSoup(requests.get(base_url, headers=my_headers).text, 'html.parser')
        self.scrape = scrape
        self.info = {}
        titles = scrape.find('h2', {"class": "title"})
        mal_id = scrape.find('a', {"class": "mal control tip tippy-desktop-only"}).get("href").split("/")[-1]
        try:
            trailer = "https://www.youtube.com/watch?v=" + scrape.find('div', {"class": "trailer control tip tippy-desktop-only"}).get("data-url").split("?")[0].split("/")[-1]
        except:
            trailer = None
        self.info["name"] = titles.text.strip()
        self.info["alternative-title"] = titles.get('data-jtitle')
        self.info["mal_id"] = mal_id
        self.info["trailer"] = trailer
        attr0 = scrape.findAll("dl", {"class": "meta col-sm-6"})
        attr = attr0[0].findAll("dd")
        attr1 = attr0[1].findAll("dd")
        self.info["uscita"] = attr[2].text.strip()
        self.info["studio"] = attr[4].text.replace("\n", "")
        self.info["generi"] = []
        self.info["durata_ep"] = attr1[1].text.strip()
        self.info["episodi"] = attr1[2].text.strip().replace("??", "0")
        for row in attr[5].text.replace("\n", "").split(","):
            self.info["generi"].append(row.strip())
        self.info["trama"] = scrape.find("div", {"class": "desc"}).text.replace("\n", "").replace("\r", "").strip()

    def get(self):
        return self.info

    def getEpisodes(self):
        server = self.scrape.find("div", {"class": "server", "data-name": 9})
        info = {}
        info["episodes"] = []
        if server is not None:
            episodes_li = server.findAll("li", {"class": "episode"})
            for row in episodes_li:
                episode_dict = "https://www.animeworld.tv" + row.find("a").get("href")
                info["episodes"].append(episode_dict)
            return info

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