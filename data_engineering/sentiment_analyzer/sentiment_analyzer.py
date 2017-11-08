#!/usr/bin/env python3

import MySQLdb
import Algorithmia
import time

conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
c = conn.cursor()

c.execute('SELECT COUNT(*) FROM sentiments_table')
C_before = c.fetchone()
conn.commit()
conn.close()

f = open('log.txt','a')
f.write('\n')
f.write(time.strftime("%m/%d/%Y"))
f.write(' ')
f.write(time.strftime("%H:%M:%S"))
f.write(str(C_before))
f.close()

def sent_an(argument):

  #Authenticate with your API key
  apiKey = "simPMDsuHNaqSwTF60883+0CSYB1"
  client = Algorithmia.client('simPMDsuHNaqSwTF60883+0CSYB1')
  algo = client.algo('nlp/SocialSentimentAnalysis/0.1.4')
  result = algo.pipe(argument[0]).result
#  print(result[0])
  return(result)
 
def sent_an2(argument_rt):
    #Authenticate with your API key
  apiKey = "simPMDsuHNaqSwTF60883+0CSYB1"
  client = Algorithmia.client('simPMDsuHNaqSwTF60883+0CSYB1')
  algo = client.algo('nlp/SocialSentimentAnalysis/0.1.4')
  result_rt = algo.pipe(argument_rt[0]).result
#  print(result_rt[0])
  return(result_rt)
def read_tbl():
  
  table_name1 = 'tweets_table'
  new_field = 'content'
  new_field2 = 'tweets_key'
  column_name = 'tweet_sentiment'
  table_name2 = 'sentiments_table'
  
  conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
  c = conn.cursor()

  c.execute('SELECT {coi}, {coi2} FROM {tn} WHERE {coi2} NOT IN (SELECT {coi2} FROM {tn2}) AND NOT INSTR ({coi},"RT @") ORDER BY {coi2}'.\
  format(coi=new_field, coi2=new_field2, tn=table_name1, tn2=table_name2))
  argument = c.fetchone()
 
  conn.commit()
  conn.close()
#  print(argument[0])
#  print(argument[1])
  
  return (argument)
  
def read_tbl2():
    table_name1 = 'tweets_table'
    new_field = 'content'
    new_field2 = 'tweets_key'
    new_field3 = 'quoted_id'
    column_name = 'tweet_sentiment'
    table_name2 = 'sentiments_table'

    conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
    c = conn.cursor()

    c.execute('SELECT {coi}, {coi2}, {coi3} FROM {tn} WHERE {coi2} NOT IN (SELECT {coi2} FROM {tn2}) AND (SELECT DISTINCT {coi3}) GROUP BY {coi3}, {coi}'.\
        format(coi=new_field, coi2=new_field2, tn=table_name1, tn2=table_name2, coi3=new_field3))
    argument_rt = c.fetchone()
    conn.commit()
    conn.close()
#    print(argument_rt[0])
#    print(argument_rt[1])
#    print(argument_rt[2])
    return(argument_rt)


def write_tbl(result, argument):
  
  new_field2 = 'tweets_key'
  column_name = 'tweet_sentiments'
  table_name2 = 'sentiments_table'

 
  conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
  c = conn.cursor()
  c.execute("INSERT INTO {tn2} ({nf2}, {cn}) VALUES ({twk},{result})".\
    format(tn2=table_name2, nf2=new_field2, cn=column_name, twk=str(argument) , result=str(result[0]['compound'])))

  conn.commit()
  conn.close()

def write_tbl2(result_rt, argument_rt):
   
  new_field2 = 'tweets_key'
  column_name = 'tweet_sentiments'
  table_name2 = 'sentiments_table'


  conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
  c = conn.cursor()
  c.execute("INSERT INTO {tn2} ({nf2}, {cn}) VALUES ({twk},{result})".\
    format(tn2=table_name2, nf2=new_field2, cn=column_name, twk=str(argument_rt) , result=str(result_rt[0]['compound'])))

  conn.commit()
  conn.close()


#def read_tbl_disabled():
#  sqlite_file = 'my_databse1.db' #location of database
#  table_name1 = 'TweetData'
#  new_field = 'TweetText'
#  field_type = 'TINYTEXT'
#  column_name = 'Data'
#  id_column = 'TweetText'
#  new_column1 = 'Data'
#  column_type = 'TINYTEXT'

#  conn = sqlite3.connect(sqlite_file)
#  c = conn.cursor()

#  c.execute('SELECT ({coi}) FROM {tn} WHERE {cn}="null"'.\
#    format(coi=new_field, tn=table_name1, cn=column_name))
#  all_rows = c.fetchone()

#  conn.commit()
#  conn.close()
#  argument = all_rows
#  print(argument)
#  return (argument)

def non_retweet():
    #connect to  tweet database
    argument=read_tbl()
    #print (argument[0], argument[1])
    #apply sentiment analysis
    result=sent_an(argument)

    #write result to database
    write_tbl(result, argument[1])
   
   
def first_retweet():
    #connect to  tweet database
    argument_rt = read_tbl2()

    #apply sentiment analysis
    result_rt = sent_an2(argument_rt)

    #write result to database
    write_tbl2(result_rt, argument_rt[1])
    


i=0
while i<10 :
  try:
    non_retweet()
    i=i+1
  except: 
    #print ("No more unique data") 
    if i<10:
        try:
          first_retweet()
          i=i+1
        except:
            #print ("No more data")
            break

conn=MySQLdb.connect(host='127.0.0.1', user = 'root', password ='', database = 'tweets_table')
c = conn.cursor()

c.execute('SELECT COUNT(*) FROM sentiments_table')
C_after = c.fetchone()
conn.commit()
conn.close()

f = open('log.txt','a')
f.write(time.strftime("%H:%M:%S"))
f.write(str(C_after))
f.close()