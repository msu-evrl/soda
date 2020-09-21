# Social Data Analytics (SoDA)

This is a continuation/supporting piece to the Cyber Identity Reconciliation Engine.

Internet identity or internet persona is a social identity that an Internet user establishes in online communities and websites. While most online persona’s do not explicitly identify the individual, it is most likely that implicit identification can be accomplished through the combination of multiple data sets. Almost every interaction with technology creates digital traces, from the cell tower used to route mobile calls to the vendor recording a credit card transaction; from the photographs taken, to the “status updates” posted online. The idea that these traces can all be merged and connected opens many possibilities to positively identify individuals with multiple online personas. The ability to merge different datasets across domains can facilitate situational awareness for cybersecurity operations.


## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Prerequisites

What things you need to install the software and how to install them

```
pip
```
```
python 3.7.3 
```
```
tweepy v3.5.0
```
```
json 
```
```
csv
```
```
time
```
```
datetime
```
```
sqlite3
```
```
jupyter notebook
```


### Installing


## Running the tests

The expected output will be entered into a database that will be constanly updated. First we need to make sure that some things are running correctly before the data collection begins.

### Credential setup

First make sure that you copy your "twitter-credentials.json" into line 10

```
with open('/Users/user_info/../../../../twitter-credentials.json') as fp:
    cred = json.load(fp)
```


### Outtweets

The 'past_tweets.py' get the past 3,246 tweets from specified users. We write our 'outtweets' to a csv file. The fingerprints below is what we will be using to train model are: 

```
["pulled_at", "screen_name", "social_media",  "response_content"]
```

## Deployment

In order to deploy the data collection, you will have to pass in the username of the account that you want to download. We will use Barack Obama as an example below:

```
if __name__ == '__main__':
    get_all_tweets("barackobama")
```
This will collect the 3,346 tweets, and they will be outputted in this format: 

```
pulled_at,screen_name,social_media,response_content
2020-04-13T18:46:08.193377,BarackObama,Twitter,"{""created_at"": ""Mon Apr 13 18:01:58 +0000 2020"", ""id"": 1249759749102546946, ""id_str"": ""1249759749102546946"", ""text"": ""Dreamers have contributed so much to our country, and they are risking their lives fighting on the frontlines of th\u2026 https://t.co/TpcCIY3UzF"", ""truncated"": true, ""entities"": {""hashtags"": [], ""symbols"": [], ""user_mentions"": [], ""urls"": [{""url"": ""https://t.co/TpcCIY3UzF"", ""expanded_url"": ""https://twitter.com/i/web/status/1249759749102546946"", ""display_url"": ""twitter.com/i/web/status/1\u2026"", ""indices"": [117, 140]}]}, ""source"": ""<a href=\""http://twitter.com/download/iphone\"" rel=\""nofollow\"">Twitter for iPhone</a>"", ""in_reply_to_status_id"": null, ""in_reply_to_status_id_str"": null, ""in_reply_to_user_id"": null, ""in_reply_to_user_id_str"": null, ""in_reply_to_screen_name"": null, ""user"": {""id"": 813286, ""id_str"": ""813286"", ""name"": ""Barack Obama"", ""screen_name"": ""BarackObama"", ""location"": ""Washington, DC"", ""description"": ""Dad, husband, President, citizen."", ""url"": ""https://t.co/93Y27HEnnX"", ""entities"": {""url"": {""urls"": [{""url"": ""https://t.co/93Y27HEnnX"", ""expanded_url"": ""https://www.obama.org/"", ""display_url"": ""obama.org"", ""indices"": [0, 23]}]}, ""description"": {""urls"": []}}, ""protected"": false, ""followers_count"": 115704863, ""friends_count"": 607535, ""listed_count"": 230076, ""created_at"": ""Mon Mar 05 22:08:25 +0000 2007"", ""favourites_count"": 11, ""utc_offset"": null, ""time_zone"": null, ""geo_enabled"": false, ""verified"": true, ""statuses_count"": 15781, ""lang"": null, ""contributors_enabled"": false, ""is_translator"": false, ""is_translation_enabled"": true, ""profile_background_color"": ""77B0DC"", ""profile_background_image_url"": ""http://abs.twimg.com/images/themes/theme1/bg.png"", ""profile_background_image_url_https"": ""https://abs.twimg.com/images/themes/theme1/bg.png"", ""profile_background_tile"": false, ""profile_image_url"": ""http://pbs.twimg.com/profile_images/822547732376207360/5g0FC8XX_normal.jpg"", ""profile_image_url_https"": ""https://pbs.twimg.com/profile_images/822547732376207360/5g0FC8XX_normal.jpg"", ""profile_banner_url"": ""https://pbs.twimg.com/profile_banners/813286/1502508746"", ""profile_link_color"": ""2574AD"", ""profile_sidebar_border_color"": ""FFFFFF"", ""profile_sidebar_fill_color"": ""C2E0F6"", ""profile_text_color"": ""333333"", ""profile_use_background_image"": true, ""has_extended_profile"": true, ""default_profile"": false, ""default_profile_image"": false, ""following"": false, ""follow_request_sent"": false, ""notifications"": false, ""translator_type"": ""regular""}, ""geo"": null, ""coordinates"": null, ""place"": null, ""contributors"": null, ""is_quote_status"": false, ""retweet_count"": 12970, ""favorite_count"": 59615, ""favorited"": false, ""retweeted"": false, ""possibly_sensitive"": false, ""lang"": ""en""}"
```

## Built With

* [Tweepy](http://docs.tweepy.org/en/latest/getting_started.html) - The documention

## Contributing


## Versioning

We use [Tweepy](http://docs.tweepy.org/en/latest/index.html) for versioning. For the versions available, see the [tags on this repository](https://github.com/tweepy/tweepy). 

## Authors

* **Dr. Kofi Nyarko** 
* **Kelechi Nwachukwu**
* **Taofeek Obafemi-Babatunde** 

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

