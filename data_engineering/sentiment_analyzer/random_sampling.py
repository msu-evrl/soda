import MySQLdb as mysql

conn = mysql.connect(host='127.0.0.1', user='root', password='', database='tweets_table')
c = conn.cursor()

c.execute('SELECT tweets_table.tweets_key, tweet_sentiments, content FROM sentiments_table LEFT JOIN(tweets_table) ON (sentiments_table.tweets_key = tweets_table.tweets_key) ORDER BY RAND() LIMIT 10')
argument = c.fetchall()
print(argument)
conn.commit()
conn.close()
