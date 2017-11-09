from __future__ import print_function

import csv
import tweepy

# class TwitterAPI:

#     def __

# Getting user information
def get_user_info(filename):
    '''
    '''

    # List = []
    # Dict = {}
    # Tuple = ()

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
        file_name = raw_input("Please enter a new file name: ")
        get_user_info(file_name)


def twitter_api(auth_info):
    auth = tweepy.OAuthHandler(auth_info['consumer_key'],
                               auth_info['consumer_secret'])
    auth.set_access_token(auth_info['access_token'],
                          auth_info['access_token_secret'])
    

def main():

    user_info = get_user_info('user_info.csv')

    print(user_info['consumer_key'])

if __name__ == '__main__':
    main()
