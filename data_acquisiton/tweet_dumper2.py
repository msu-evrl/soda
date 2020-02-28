import tweepy  # https://github.com/tweepy/tweepy
import csv
import json
import time
# import db_something as db


with open('twitter-credentials.json') as fp:
    cred = json.load(fp)


# Twitter API credentials
consumer_key = cred['consumer_key']
consumer_secret = cred['consumer_secret']
access_key = cred['access_token']
access_secret = cred['access_token_secret']


def get_all_tweets(screen_name):
    # Twitter only allows access to a users most recent 3240 tweets with this method

    # authorize twitter, initialize tweepy
    auth = tweepy.OAuthHandler(consumer_key, consumer_secret)
    auth.set_access_token(access_key, access_secret)
    api = tweepy.API(auth)

    # initialize a list to hold all the tweepy Tweets
    alltweets = []

    # make initial request for most recent tweets (200 is the maximum allowed count)
    new_tweets = api.user_timeline(screen_name=screen_name, count=200)

    # save most recent tweets
    alltweets.extend(new_tweets)

    # save the id of the oldest tweet less one
    oldest = alltweets[-1].id - 1

    # keep grabbing tweets until there are no tweets left to grab
    while len(new_tweets) > 0:

        print("getting tweets before {}".format(oldest))

        # all subsiquent requests use the max_id param to prevent duplicates
        new_tweets = api.user_timeline(
            screen_name=screen_name, count=200, max_id=oldest)

        # save most recent tweets
        alltweets.extend(new_tweets)

        # update the id of the oldest tweet less one
        oldest = alltweets[-1].id - 1

        #rate limiter 
        # try:
        #     yield cursor.next()
        # except tweepy.RateLimitError:
        #     time.sleep(15 * 60)

        print("...{} tweets downloaded so far".format(len(alltweets)))

    # transform the tweepy tweets into a 2D array that will populate the csv
    outtweets = [[tweet.created_at, tweet.user ] for tweet in alltweets]

    # write the csv
    with open('{}_tweets.csv'.format(screen_name), 'w') as f:
        writer = csv.writer(f)
        writer.writerow(["created_at", "screen_name", "tweet_json"])
        writer.writerows(outtweets)

    # db.save_tweets(outtweets)

if __name__ == '__main__':
    # pass in the username of the account you want to download
    get_all_tweets("Mike_Pence")
