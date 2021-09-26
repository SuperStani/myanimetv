from pyrogram import Client, filters
from pyrogram.handlers import MessageHandler
from animescraper import AnimeWorld
from downloader import Video
import urllib.parse
import requests
import json
import re
import math
import time
import os
import asyncio

#python3 /var/www/html/bots/myanimetv/downloader/bot.py
api_id = 707541
api_hash = "8f9f3b9eff7524256a57bdbcc75da866"

app = Client("my_profile", api_id, api_hash)
user = Client("ubot", api_id, api_hash)
user.start()
CURR_DIR = os.path.dirname(os.path.realpath(__file__))
uploading = False

def human_size(bytes, units=[' bytes','KB','MB','GB','TB', 'PB']):
    return str(bytes) + units[0] if bytes < 1024 else human_size(bytes>>10, units[1:])

async def progress_bar_f(current, total, time1, direction, message_id):
    if round((current * total / 100)) % 8 == 0 or direction == "<b>Download":
        time2 = time.time()
        diff = time2 - time1
        #progress bar Generator... string
        if direction == "<b>Download":
            emoji = ["🔴", "⚪️"]
        else:
            emoji = ["🔵", "🔴"]
        pro_bar_str = "".join([emoji[0] for i in range(1,math.floor(current/total*10)+1)])
        pro_bar_str = pro_bar_str + "".join([emoji[1] for i in range(1, 11 -math.floor(current/total*10))])
        # k1 is current downloaded bytes
        k1 = human_size(current)
        # where k2 is total byte length of file
        k2 = human_size(total)
        #percntage is k3
        tmp = "{:.2f}".format(current/total*100)
        k3 = "[ "+tmp+"% ]  "
        #transfer speed is k4
        k4 = human_size(math.floor(current/diff))
        #this case when file is downloaded completly... 
        if total == current: 
            txt_to_send=direction + "ed!</b> \nComplete 100% [" + k2 + "]\n" + pro_bar_str
        #this else case is when file is currently in downloading state
        else:
            txt_to_send = direction + "ing...</b>\n\nTransfer Speed: " + k4 + "/s\n" + k1 + "/" + k2 + " " + k3 + "\n" + pro_bar_str
        try:
            await app.edit_message_text("SuperVideoConverterBot", message_id, txt_to_send)
        except:
            pass

async def download_link(link, file_name, message_id):
  time1 = time.time()
  direction = "<b>Download"
  num_list=[str(i)+".000" for i in range(0,101,5)]
  headers = {'User-Agent': "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Safari/537.36"}
  response = requests.get(link, stream=True, headers=headers)
  with open(file_name, "wb") as f:
          total_length = response.headers.get('content-length')
          if total_length is None: # no content length header
              f.write(response.content)
          else:
              dl = 0
              total_length = int(total_length)
              for data in response.iter_content(chunk_size=4096):
                  dl += len(data)
                  f.write(data)
                  n="{:.3f}".format((dl/total_length)*100)
                  if n in num_list:
                      await progress_bar_f(dl, total_length, time1, direction, message_id)

#Convert document to video
@app.on_message(filters.document & ~ filters.me & filters.chat([406343901, 198253421,156371150,808699539, 856835224]))
async def convert_file_to_video(client, message):
    name = message.document.file_name
    erase = ["'", "(", ")", " ", "-"]
    name = re.sub("@\w+", "", name)
    for row in erase:
        name = name.replace(row, "_", )
    result = await message.reply_text("<b>Start download...</b>", quote=True)
    direction = "<b>Download"
    time1 = time.time()
    message_id = result.message_id
    await message.download(file_name=name, progress=progress_bar_f, progress_args=(time1, direction, message_id))
    filepath = CURR_DIR + "/downloads/{}".format(name)
    video = Video(filepath=filepath)
    streams = video.getStreams()
    duration = streams["duration"]
    width = streams["width"]
    height = streams["height"]
    thumbnail = video.getThumb()
    time1 = time.time()
    direction = "<b>Upload"
    await app.send_video(message.chat.id, filepath, caption=message.document.file_name, progress=progress_bar_f, progress_args=(time1, direction, message_id), duration=duration, thumb=thumbnail, width=width, height=height, reply_to_message_id=message.message_id)
    await app.delete_messages("me", message_id)
    video.delete()

@app.on_message(filters.chat([406343901, 198253421,156371150,808699539, 856835224]))
async def manage(client, msg):
    try:
        text = msg.text.replace("/start dl_", "https://www.animeworld.tv/play/").replace("999", ".")
    except:
        text = ""
    if text.find("http") == 0:
        url = text
        if url.find("https://www.animeworld.tv/play/") == 0:
            if len(url.split("/")) == 5:
                    global uploading
                    if uploading is not True:
                        uploading = True
                        await dl_anime(msg, url, 1)
                        uploading = False
                        await msg.reply(f"<a href='https://t.me/matvuploader/{anime_m.message_id}'>Anime scaricato con successo!</a>")
            else:
                direct = AnimeWorld.getDirectLink(url)
                try:
                    name = os.path.basename(direct)
                    filepath = CURR_DIR + "/downloads/{}".format(name)
                    result = await msg.reply_text("<b>Start download...</b>", quote=True)
                    direction = "<b>Download"
                    time1 = time.time()
                    message_id = result.message_id
                    await download_link(direct, filepath, message_id)
                    video = Video(filepath=filepath)
                    time.sleep(1)
                    streams = video.getStreams()
                    duration = streams["duration"]
                    width = streams["width"]
                    height = streams["height"]
                    thumbnail = video.getThumb()
                    time1 = time.time()
                    direction = "<b>Upload"
                    mess = await app.send_video(-1001183090675, filepath, caption=name, progress=progress_bar_f, progress_args=(time1, direction, message_id), duration=duration, thumb=thumbnail, width=width, height=height)
                    video.delete()
                    await app.edit_message_text(msg.chat.id, message_id, f"<a href='https://t.me/matvuploader/{mess.message_id}'>Episodio scaricato!</a>", disable_web_page_preview=True)
                except Exception as e :
                    await msg.reply("Formato link non valido!\n<code>{}</code>".format(e))
        else:
            if text.find("streamtape.com") > 0:
                try:
                    videoid = text.split("/")[4]
                    url = get_url(videoid)
                    video = Video(url)
                    filename = video.download()
                    time.sleep(1)
                    streams = video.getStreams()
                    duration = streams["duration"]
                    width = streams["width"]
                    height = streams["height"]
                    thumbnail = video.getThumb()
                    await app.send_video(-1001183090675, filename, caption=video.filename, thumb=thumbnail, duration=duration, width=width, height=height)
                    video.delete()
                    await msg.reply("Episodio scaricato!")
                except Exception as e:
                    await msg.reply("Errore: <code>{}</code>".format(e))
            else:
                try:
                    name = os.path.basename(url)
                    filepath = CURR_DIR + "/downloads/{}".format(name)
                    result = await msg.reply_text("<b>Start download...</b>", quote=True)
                    direction = "<b>Download"
                    time1 = time.time()
                    message_id = result.message_id
                    await download_link(url, filepath, message_id)

                    video = Video(filepath=filepath)
                    time.sleep(1)
                    streams = video.getStreams()
                    duration = streams["duration"]
                    width = streams["width"]
                    height = streams["height"]
                    thumbnail = video.getThumb()
                    time1 = time.time()
                    direction = "<b>Upload"
                    mess = await app.send_video(-1001183090675, filepath, caption=name, progress=progress_bar_f, progress_args=(time1, direction, message_id), duration=duration, thumb=thumbnail, width=width, height=height)
                    video.delete()
                    await app.edit_message_text(msg.chat.id, message_id, f"<a href='https://t.me/matvuploader/{mess.message_id}'>Episodio scaricato!</a>", disable_web_page_preview=True)
                except Exception as e :
                    await msg.reply("Formato link non valido!\n<code>{}</code>".format(e))
    else:
        if msg.forward_from_chat is not None and msg.forward_from_chat.id == -1001336263112:
            url = msg.reply_markup.inline_keyboard[0][0].url
            direct = AnimeWorld.getDirectLink(url)
            try:
                name = os.path.basename(direct)
                filepath = CURR_DIR + "/downloads/{}".format(name)
                result = await msg.reply_text("<b>Start download...</b>", quote=True)
                direction = "<b>Download"
                time1 = time.time()
                message_id = result.message_id
                await download_link(direct, filepath, message_id)
                video = Video(filepath=filepath)
                time.sleep(1)
                streams = video.getStreams()
                duration = streams["duration"]
                width = streams["width"]
                height = streams["height"]
                thumbnail = video.getThumb()
                time1 = time.time()
                direction = "<b>Upload"
                mess = await app.send_video(-1001183090675, filepath, caption=name, progress=progress_bar_f, progress_args=(time1, direction, message_id), duration=duration, thumb=thumbnail, width=width, height=height)
                video.delete()
                await app.edit_message_text(msg.chat.id, message_id, f"<a href='https://t.me/matvuploader/{mess.message_id}'>Episodio scaricato!</a>", disable_web_page_preview=True)
            except Exception as e :
                await msg.reply("Formato link non valido!\n<code>{}</code>".format(e))
        else:
            results = AnimeWorld.search(text) 
            text = "Ho trovato <b>{}</b> anime:\n".format(len(results))
            if len(results) > 0:
                for row in results:
                    text = text + "\n<a href='t.me/SuperVideoConverterBot?start=dl_" + row["url"].replace("https://www.animeworld.tv/play/", "").replace(".", "999") + "'>⬇️</a> | <b>" + row["name"] + "</b>"
            else:
                text = "Non ho trovato anime con questo nome!"
            await msg.reply(text, disable_web_page_preview=True)

@user.on_message()
async def user_response(c, message):
    if message.via_bot is not None:
        if message.via_bot.id == 941973391 and message.chat.username == "myanimetvbot":
            global uploading
            if uploading is not True:
                uploading = True
                await dl_anime(message, message.web_page.url)
                uploading = False
            
async def dl_anime(message, url, edit=None):
    anime = AnimeWorld(url)
    if len(anime.info["episodes"]) == 0:
        await message.reply_text("😔 Gli episodi di questo anime non hanno un link diretto!")
    else:
        i = 0
        if edit is not None:
            msg = "<b>{}</b>\n\n🔗 | Url: <a href='{}'>Clicca qui</a>\n➕ | Episodi scaricati: <code>{}/{}</code>".format(anime.info["name"], url, i, len(anime.info["episodes"]))
            result = await message.reply(msg)
        anime_m = await app.send_message(-1001183090675, "<b>{}</b> ({})".format(anime.info["name"], anime.info["alternative-title"]))
        eps = []
        for row in anime.info["episodes"]:
            direct_url = AnimeWorld.getDirectLink(row)
            video = Video(direct_url)
            filename = video.download()
            await asyncio.sleep(2)
            streams = video.getStreams()
            duration = streams["duration"]
            width = streams["width"]
            height = streams["height"]
            thumbnail = video.getThumb()
            try:
                video_send = await app.send_video(-1001183090675, filename, caption=video.filename, thumb=thumbnail, duration=duration, width=width, height=height)
                video.delete()
                i += 1
                if edit is None:
                    await user.copy_message("myanimetvbot", -1001183090675, video_send.message_id)
                    
            except Exception as e:
                await message.reply_text(e)
                break

            if edit is not None:
                msg= "<b>{}</b>\n\n🔗 | Url: <a href='{}'>Clicca qui</a>\n➕ | Episodi scaricati: <code>{}/{}</code>".format(anime.info["name"], url, i, len(anime.info["episodes"]))
                await app.edit_message_text("me", result.message_id, msg)
            await asyncio.sleep(2)

app.run()

def get_ticket(file, login, key):
    url = "https://api.streamtape.com/file/dlticket"
    post = {
            "file": file, 
            "login": login, 
            "key": key
            }
    r = requests.post(url, data=post)
    j = json.loads(r.text)
    return j["result"]["ticket"]

def get_url(file):
    ticket = get_ticket(file, '517a30fc8883999442cd', 'jkBM9lj8rmfDXZ')
    time.sleep(4)
    url = "https://api.streamtape.com/file/dl"
    post = {
            "file": file, 
            "ticket": ticket
            }
    r = requests.post(url, data=post)
    j = json.loads(r.text)
    return j["result"]["url"]