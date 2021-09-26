from flask import Flask, request
from flask_restful import Api, Resource, abort
from animescrape import AnimeWorld
app = Flask(__name__)
api = Api(app)

class Anime(Resource):
    def get(self):
        url = request.args.get('url', type=str)
        return AnimeWorld(url).get(), 201

class searchAnime(Resource):
    def get(self):
        name = request.args.get('q', default="", type=str)
        return AnimeWorld.search(name)
        
api.add_resource(Anime, "/scrape/info")
api.add_resource(searchAnime, "/scrape/search")
