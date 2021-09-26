import mysql.connector as db

def connect():
    mydb = db.connect(
        user      = "admin",
        passwd    = "@Naruto96",
        database  = "myanimetvchat",
        host      = "localhost"
    )
    return mydb

def query(query, *args, one=False, read=False):
    connection = connect()
    cursor = connection.cursor()
    cursor.execute(query, args)
    try:
        if read:
            if not one:
                return cursor.fetchall()
            else:
                return cursor.fetchone()
        else:
           connection.commit() 
           return cursor.lastrowid
    except:
        connection.rollback()
        raise
    finally:
        cursor.close()

#Get page session of user
def getPage(chat_id):
    return query("SELECT page FROM users WHERE chat_id = %s", chat_id, one=True, read=True)[0]

#Change page session of user
def page(page_text, chat_id):
    query("UPDATE users SET page = %s WHERE chat_id = %s", page_text, chat_id)
    
#Write query
def wquery(raw_query, *args):
    return query(raw_query, *args)

#Read query
def rquery(raw_query, *args, one=True):
    return query(raw_query, *args, one=one, read=True)