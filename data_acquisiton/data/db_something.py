import sqlite3
from sqlite3 import Error

DB_LOC = 'soda.db'

def create_connection(db_file):
    """ create a database connection to a SQLite database """
    conn = None
    try:
        conn = sqlite3.connect(db_file)
        print(sqlite3.version)
    except Error as e:
        print(e)
    finally:
        if conn:
            conn.close()
 
def create_table(db_file=DB_LOC):
    try:
        conn = sqlite3.connect(db_file)
        cur = conn.cursor()

        # Create Table
        cur.execute('''CREATE TABLE IF NOT EXISTS
                        soda(id INTEGER PRIMARY KEY AUTOINCREMENT,
                                pulled_at TEXT, 
                                screen_name TEXT, 
                                social_media TEXT, 
                                response_content BLOB
                            )
                    ''')
    except Error as e:
        print(e)
    finally:
        if conn:
            conn.close()

def save_tweets(tweet_list, db_file=DB_LOC):
    conn = None
    try:
        conn = sqlite3.connect(db_file)
        cur = conn.cursor()
        for tweet in tweet_list:
            cur.execute("""INSERT INTO soda ("pulled_at", "screen_name", "social_media", "response_content") VALUES(?,?,?,?)""", 
            # (tweet.created_at, tweet.user.screen_name, tweet.json_))
            (tweet[0], tweet[1], tweet[2], tweet[3]))
            conn.commit()
    except Error as e:
        print(e)
    finally:
        if conn:
            conn.close()
 
def test_lagosvibs(list_of_names):
    for name in list_of_names:
        print(name)


if __name__ == '__main__':
    # create_connection(r"C:\sqlite\db\pythonsqlite.db")
    # create_connection(r"soda.db")
    create_table()
    tweet_list = [["pulled_at1", "screen_name1", "social_media1", "tweet_json1"],
                 ["pulled_at2", "screen_name2", "social_media2", "tweet_json2"]]
    save_tweets(tweet_list)