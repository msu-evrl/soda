'''
This is the Twitter API to do all of the data acquisition.
'''

from __future__ import print_function

import json
import csv
import sys
import tweepy


#class TwitterAPI(tweepy.streaming.StreamListener):
class TwitterAPI(tweepy.StreamListener):
    '''
    This is the DEPA TwitterAPI version 2. Written in Python 3.7.2
    '''

    def __init__(self, user_credentials=None):
        self.credentials = user_credentials

    
    def get_user_info_csv(self, filename: str) -> dict:
        '''
        This gets the user information that will be used for the API 
        '''
        user_info = {}
        try:
            with open(filename, 'r') as fp:
                reader = csv.reader(fp)
                for row in reader:
                    user_info['consumer_key'] = row[0]
                    user_info['consumer_secret'] = row[1]
                    user_info['access_token'] = row[2]
                    user_info['access_token_secret'] = row[3]
                return user_info
        except IOError as e:
            print(e)
            filename = raw_input("Please enter a new file name: ")
            self.get_user_info_csv(filename)

    def get_user_info_json(self, filename: str) -> None:
        '''
        '''
        try:
            with open(filename, 'r') as fp:
                self.credentials = json.load(fp)
        except IOError as e:
            print(e)
            filename = raw_input("Please enter a new user file name: ")
            self.get_user_info_json(filename)

    def get_search_criteria(self, filename: str) -> list:
        '''
        '''
        #search_criteria = []
        try:
            with open(filename, 'r') as fp:
                reader = csv.reader(fp)
                for row in reader:
                    #search_criteria.append(row)
                    search_criteria = row
                return search_criteria
        except:
            pass

class StdOutListener(tweepy.StreamListener):
    '''
    This is the basic listener that just prints received tweets to stdout.
    '''
    def on_data(self, data):
        print(data)
        return True

    def on_error(self, status):
        print(status)
        return True # Don't kill the stream
        #print("Stream restarted")


def limit_handled(cursor):
    while True:
        try:
            yield cursor.next()
        except tweepy.RateLimitError:
            time.sleep(15 * 60)


    
    


def main():

    api = TwitterAPI()
    listener = StdOutListener()

   
    api.get_user_info_json('twitter-credentials.json')
 
    # search_criteria = ['python', 'javascript', 'ruby']

    auth = tweepy.OAuthHandler(api.credentials['consumer_key'],
                               api.credentials['consumer_secret'])
    auth.set_access_token(api.credentials['access_token'],
                          api.credentials['access_token_secret'])
    stream = tweepy.Stream(auth, listener)
    api = tweepy.API(auth)
    
    stream.filter(track = "apples") 
   

if __name__ == '__main__':
    main()
