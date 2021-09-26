import urllib.request
import os
from subprocess import check_output
import random
import time
import json

class Video():

    def __init__(self, download_url = None, filepath = None):
        self.download_url = None
        self.filepath = None
        self.CURR_DIR = os.path.dirname(os.path.realpath(__file__))
        if download_url is not None:
            self.download_url = download_url
        else:
            self.filepath = filepath

    def download(self):
        if self.download_url is not None:
            name = os.path.basename(self.download_url)
            self.filename = name
            opener = urllib.request.build_opener()
            opener.addheaders = [('User-Agent', "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36")]
            urllib.request.install_opener(opener)
            urllib.request.urlretrieve(self.download_url, self.CURR_DIR + "/downloads/{}".format(name))
            self.filepath = '/var/www/html/stani/superconverter/downloads/{}'.format(name)
            return self.filepath

    
    def getThumb(self):
        if self.filepath is not None: 
            duration = self.duration
            thumbnail = self.CURR_DIR + "/downloads/{}.jpg".format(int(time.time()))
            os.system("ffmpeg -ss {} -i {} -vframes 1 -q:v 2 {}".format(int(duration / 2), self.filepath, thumbnail))
            self.thumbnail = thumbnail
            return thumbnail

    def getStreams(self):
        if self.filepath is not None:
            res = {}
            try:
                j = check_output(f"ffprobe -v error -show_entries stream=width,height -of csv=p=0:s=x {self.filepath}", shell=True)
                j = j.split(b"x")
                res["width"] = int(j[0].split(b"\n")[0])
                res["height"] = int(j[1].split(b"\n")[0])
            except:
                res["width"] = 1280
                res["height"] = 720
            j = check_output(f"ffprobe -v error -show_entries stream=duration -of csv=p=0:s=x {self.filepath}", shell=True)
            res["duration"] = int(j.split(b".")[0])
            self.duration =  res["duration"]
            return res

    def to_mp4(self):
        if self.filepath:
            name, ext = os.path.splitext(self.filepath)
            filepath = name + ".mp4"
            os.system("ffmpeg -i {} -c copy {}".format(self.filepath, filepath))
            self.delete()
            self.filepath = filepath
            return filepath

    def delete(self):
        os.remove(self.filepath)
        try:
            os.remove(self.thumbnail)
        except:
            pass
