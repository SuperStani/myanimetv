from pyrogram import Client, filters
from pyrogram.handlers import MessageHandler
import database as conn
from numpy import random

api_id = 707541
api_hash = "8f9f3b9eff7524256a57bdbcc75da866"
app = Client("my_profile", api_id, api_hash)
#python3 /var/www/html/bots/myanimetv/chat/bot.py

@app.on_message(filters.new_chat_members)
async def savenewmember(client, msg):
    user_id = msg.from_user.id
    conn.wquery("INSERT INTO users SET user_id = %s, name = %s", user_id, msg.from_user.first_name)
    

@app.on_message(filters.command("livelli", ["!", "/", "."]))
async def showlevels(client, msg):
    q = conn.rquery("SELECT * FROM levels ORDER by exp DESC", one=False)
    text = "<b>⭐️ LIVELLI ED EXP </b>\n\n"
    txt = []
    exp = q[0][2]
    for row in q:
        if exp == row[2]:
            points = f"{exp}+"
        else:
            points = f"{row[2]} ~ {exp - 1}"
        txt.append(f" - {row[1]} ({points})\n")
        exp = row[2]
    text = text + ''.join(reversed(txt))
    await msg.reply(text)


@app.on_message(filters.command(["stat", "stat@matvChatRankerbot"], ["!", "/", "."]) & filters.reply)
async def getinfo(client, msg):
    query = """
            SELECT 
                users.user_id, 
                users.reputation,
                users.exp, 
                levels.name,
                (SELECT exp FROM levels WHERE exp > users.exp LIMIT 1) AS next_exp
            FROM users 
            INNER JOIN levels
            ON levels.exp <= users.exp 
            WHERE users.user_id = %s 
            ORDER by levels.exp DESC 
            LIMIT 1
            """
    media_messaggi = conn.rquery("SELECT AVG(message_len) as tot FROM messages WHERE user_id = %s AND message_type = 1", msg.reply_to_message.from_user.id, one=True)[0]
    info = conn.rquery(query, msg.reply_to_message.from_user.id)
    await msg.reply(f"👤 | <a href='tg://user?id={info[0]}'>{msg.reply_to_message.from_user.first_name}</a>\n🏮 | Grado: <b>{info[3]}</b> ~ ({info[2]}/{info[4]}exp)\n⚜️ | Reputazione: <b>{info[1]}</b>\n🔡 | Lunghezza messaggi: <b>{int(media_messaggi)}</b> caratteri x messaggio\n")

@app.on_message(filters.command(["toplvl", "toplvl@matvChatRankerbot"], ["!", "/", "."]))
async def showtop(client, msg):
    limit = msg.text.split()
    if len(limit) > 1:
        limit = int(limit[1])
    else:
        limit = 10
    query = """
            SELECT 
	            users.user_id,
                users.name,
                users.exp,
                (SELECT name FROM levels WHERE exp <= users.exp ORDER by exp DESC LIMIT 1) AS level,
                (SELECT exp FROM levels WHERE exp > users.exp LIMIT 1) AS next_exp
            FROM users 
            ORDER by exp DESC 
            LIMIT %s
            """
    q = conn.rquery(query, limit, one=False)
    text = ""
    for row in q:
        text = text + f"➥ <a href='tg://user?id={row[0]}'>{row[1]}</a> ~ <b>{row[3]}</b> ({row[2]}/{row[4]})\n"
    await msg.reply(text)

@app.on_message(filters.command("add", ["!", "/", "."]) & filters.user(406343901))
async def add_points(client, msg):
    points = int(msg.text.split(" ")[1])
    print(msg)
    user_id = msg.reply_to_message.from_user.id
    conn.wquery("UPDATE users SET exp = exp + %s WHERE user_id = %s", points, user_id)
    await msg.reply(f"<a href='tg://user?id={user_id}'>🌟</a> <b>{msg.reply_to_message.from_user.first_name}</b> ha ricevuto <b>{points}</b> punti!")

@app.on_message(filters.command("togli", ["!", "/", "."]) & filters.user(406343901))
async def add_points(client, msg):
    e = msg.text.split(" ", 2)
    points = int(e[1])
    add_text = ""
    if len(e) > 2 :
        add_text = f"\n🔖 | Motivazione: <i>{e[2]}</i>"
    user_id = msg.reply_to_message.from_user.id
    conn.wquery("UPDATE users SET exp = exp - %s WHERE user_id = %s", points, user_id)
    await msg.reply(f"<a href='tg://user?id={user_id}'>💔</a> <b>{msg.reply_to_message.from_user.first_name}</b> ha perso <b>{points}</b> punti!{add_text}")

@app.on_message(filters.chat("myanimetvchat") & ~filters.command(["toplvl", "livelli", "stat"], ["!", "/"]))
async def manager(client, msg):
    user_id = msg.from_user.id
    keywords = [["+", "quoto", "👍"], ["-", "👎"]]
    try:
        message = msg.text.lower() 
    except:
        message = ""
    if (message in keywords[0] or message in keywords[1]):
        if filters.reply:
            if message in keywords[0]:
                conn.wquery("UPDATE users SET reputation = reputation + 1 WHERE user_id = %s", msg.reply_to_message.from_user.id)
            else:
                conn.wquery("UPDATE users SET reputation = reputation - 1 WHERE user_id = %s", msg.reply_to_message.from_user.id)
    else:
        ismedia = msg.media
        if ismedia is not None:
            if msg.photo:
                msgtype = 2
            elif msg.sticker:
                msgtype = 3
            elif msg.animation:
                msgtype = 4
            else:
                msgtype = 5
        else:
            msgtype = 1
        reputation = conn.rquery("SELECT reputation FROM users WHERE user_id = %s", user_id, one=True)[0]
        add_lucky = - (reputation * 100)
        lucky = random.randint(1, 2501 + add_lucky)
        exp = random.randint(1, 5)
        if lucky == 1:
            points = exp * 800
            conn.wquery("UPDATE users SET exp = exp + %s WHERE user_id = %s", points, user_id)
            await msg.reply(f"<b>⭐️ {msg.from_user.first_name}</b> è stato/a assistito dalla fortuna, ricevendo un bonus di <b>{points}exp</b>!")
        else:
            unlucky = random.randint(1, 1001)
            if unlucky == 1000 and user_id != 406343901:
                points = exp * 150
                conn.wquery("UPDATE users SET exp = exp - %s WHERE user_id = %s", points, user_id)
                await msg.reply(f"<b>😈 {msg.from_user.first_name}</b> è stato/a colpito/a dalla sfortuna, perdendo cosi <b>{points}exp</b>!")
        query = """
                SELECT 
                    users.exp,
                    levels.id,
                    levels.name,
                    levels.exp
                FROM users 
                INNER JOIN levels
                ON levels.exp > users.exp 
                WHERE users.user_id = %s 
                LIMIT 1
                """
        info = conn.rquery(query, user_id)
        #Save message and add exp points
        if msgtype == 1:
            msglen = len(msg.text)
        else:
            msglen = 1
        conn.wquery("INSERT INTO messages SET user_id = %s, message_id = %s, message_type = %s, message_len = %s", user_id, msg.message_id, msgtype, msglen)
        conn.query("UPDATE users SET exp = exp + %s, name = %s WHERE user_id = %s", exp, msg.from_user.first_name, user_id)
        if info[0] + exp >= info[3]:
            banner = "https://superstani.ml/stani/myanimetv/img/levelup{}.png".format(random.randint(1, 4))
            await msg.reply(f"<a href='{banner}'>&#8203;</a>🌟 <b>{msg.from_user.first_name}</b> 『{info[2]}』 è salito al livello {info[1]}!")
app.run()