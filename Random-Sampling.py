import MySQLdb

conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
c = conn.cursor()

#c.execute('SELECT tweets_key, content FROM tweets_table WHERE tweets_key = ANY (SELECT tweets_key FROM sentiments_table) LIMIT 10')
#content = c.fetchall()
#print(content)
#c.execute('SELECT tweets_key, tweet_sentiments FROM sentiments_table WHERE tweets_key = ANY (SELECT tweets_key FROM tweets_table)')
#sentiment = c.fetchall()
#print(sentiment)

c.execute('SELECT tweets_table.tweets_key, tweet_sentiments, content FROM sentiments_table LEFT JOIN(tweets_table) ON (sentiments_table.tweets_key = tweets_table.tweets_key) ORDER BY RAND() LIMIT 10')
argument = c.fetchall()
print(argument)
conn.commit()
conn.close()