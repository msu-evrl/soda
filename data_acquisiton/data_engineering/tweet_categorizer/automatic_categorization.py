
# coding: utf-8

# # Automatic Categorization Workflow

# In[1]:


import numpy as np
import pandas as pd

import MySQLdb

import matplotlib.pyplot as plt

get_ipython().magic(u'matplotlib inline')


# In[2]:


import sys
sys.path.insert(0, r'/Users/kelechi/Dropbox/Research_Central/EVRL/social_data_analytics/nlp')

# K-Fold Analysis
import nlp
import database_access as dba


# In[3]:


pd.set_option('display.max_colwidth', -1)


# In[4]:


def get_query(sql_statement, get_count=False, db='test_soda'):
    """
    get_query(sql_statement, get_count=False)
    
    This function is a database accessor function that will 
    probe the database for particular queries.
    
    It returns either a count of the rows when get_count = True,
    or the data for a pandas DataFrame when get_count = False
    """
    conn = MySQLdb.connect(host="10.24.12.21",
                           user="soda",
                           passwd="MSUJHU2016",
                           db=db)
    cur = conn.cursor()
    cur.execute(sql_statement)
    conn.close
    
    #data = cur.fetchall();
    
    # Direct from database
    data = []

    count = 0
    for row in cur.fetchall():
        data.append(row)
        count += 1
    # Return either the data for a DataFrame or Just the count
    return data if get_count == False else count


# In[5]:


def build_corpus(cat_df, tweet_df=None, create_corpus=False):
    """
    build_corpus(cat_df, tweets_df, create_corpus=False)
    
    This function either creates a repository of a corpus per category (1-8)
    or uses this information to categorize new tweet information.
    
    When create_corpus = False it is in categorize new tweet mode
    When create_corpus = True it is in build corpus mode
    
    In either case, it returns a dictionary that can be passed into a 
    pd.DataFrame() function in order to return a DataFrame datatype.
    """
    
    data_dict = {}
    corpus_dict = {}
    
    for i in cat_df.category[cat_df.category.notnull()].unique():

        # corpus per category
        corpus_per_cat = cat_df[cat_df.category == i].content.sum()

        # Note the conversion of the category to INT
        i = str(i)
        
        # Checking to make sure extra computation is necessary
        if create_corpus == True and i != cat_df.Category[cat_df.Category.notnull()].unique()[-1]:
            file_name = 'corpus_' + i + '.pkl'
            
            #corpus_per_cat.to_pickle(file_name)
            print "\n\nsaved in ", file_name
            print "\n"
            #print corpus_per_cat 
            corpus_dict[i] = corpus_per_cat
            
            continue
            
        elif create_corpus == True:
            #last computation in create_corpus bool
            file_name = 'corpus_' + i + '.pkl'
            
            #corpus_per_cat.to_pickle(file_name)
            print "\n\nsaved in ", file_name
            print "\n"
            #print corpus_per_cat 
            corpus_dict[i] = corpus_per_cat
            
            return corpus_dict
        
        # comparing every tweet against each corpus to see how it stacks up
        #for tweet_key in cat_df[cat_df.Category.notnull()].tweets_key:
        for tweet_key in tweet_df.tweets_key:
            #data_dict.setdefault("category_assigned",
            #                      {})[tweet_key] = tweet_df[cat_df.tweets_key == tweet_key].Category.iloc[0]

            data_dict.setdefault(i,
                                  {})[tweet_key] = nlp.cosine_sim(tweet_df.content[tweet_df.tweets_key == tweet_key].iloc[0], 
                                                                   corpus_per_cat)
    return data_dict


# ## Automatic Categorization Options

# #### Select database

# In[6]:


database = "test_soda"
database = "soda"


# #### Number of tweets to categorize before corpus update

# In[7]:


no_of_tweets = 200


# #### `categorization_type`

# In[8]:


categorization_type = 1


# #### `user_id`

# In[9]:


user_id = 629


# ## Preparing Corpus for Categorization

# Selecting **all entries** in `categorization_table`
# 
# ```SQL
# SELECT category_key, tweets_key, category_1,
# (SELECT content
#  FROM tweets_table
#  WHERE tweets_key = q1.tweets_key)
# AS `content`
# FROM `category_table`
# AS q1
# ORDER BY tweets_key
# ```
# 
# Selecting **all manual entries** in `categorization_table`
# ```SQL
# SELECT category_key, tweets_key, category_1,
# (SELECT content
#  FROM tweets_table
#  WHERE tweets_key = q1.tweets_key
#  )
# AS `content`
# FROM `category_table`
# AS q1
# WHERE categorization_type = 0
# ORDER BY tweets_key
# ```

# Selecting **all entries** in `categorization_table` as **corpus**

# In[10]:


corpus_query = "SELECT category_key, tweets_key, category_1, (SELECT content FROM tweets_table WHERE tweets_key = q1.tweets_key) AS `content` FROM `category_table` AS q1 ORDER BY tweets_key DESC"


# Selecting **all manual entries** in `categorization_table` as **corpus**

# In[11]:


corpus_query = "SELECT category_key, tweets_key, category_1, (SELECT content FROM tweets_table WHERE tweets_key = q1.tweets_key) AS `content` FROM `category_table` AS q1 WHERE categorization_type = 0 ORDER BY tweets_key DESC"


# In[12]:


corpus_col = ["category_key", "tweets_key", "category", "content"]


# In[13]:


corpus_df = pd.DataFrame(get_query(corpus_query, db=database), columns=corpus_col)
type(corpus_df)


# In[14]:


corpus_df.info()


# Check to make sure that all tweets from categorization_table has `category_1` present!

# In[15]:


corpus_df.category.sort_values().unique()


# ## Preparing Tweet for Categorization

# Select all tweets that have **not** been categorized
# 
# ```SQL
# SELECT t1.tweets_key, t1.profile_key, t1.content
# FROM tweets_table t1
# LEFT JOIN category_table t2 ON t1.tweets_key = t2.tweets_key
# WHERE t2.tweets_key IS NULL
# ORDER BY t1.tweets_key
# ```
# 
# Selecting all tweets that have **not** been categorized *OR* that have been categorized by `user_id = 447`
# 
# ```SQL
# SELECT t1.tweets_key, t1.profile_key, t1.content
# FROM tweets_table t1
# LEFT JOIN category_table t2 ON t1.tweets_key = t2.tweets_key
# WHERE (t2.tweets_key IS NULL OR (t2.tweets_key IS NOT NULL AND t2.user_id = 447))
# AND 
# (t1.profile_key = 3317 OR t1.profile_key = 12731 OR t1.profile_key = 13046 OR 
#  t1.profile_key = 13091 OR t1.profile_key = 13104 OR t1.profile_key = 13148 OR 
#  t1.profile_key = 13684 OR t1.profile_key = 13818 OR t1.profile_key = 14453)
# ORDER BY t1.tweets_key
# ```

# Select all **NOT CATEGORIZED** tweets.

# In[16]:


tweet_query = "select t1.tweets_key, t1.profile_key, t1.content FROM tweets_table t1 LEFT JOIN category_table t2 on t2.tweets_key = t1.tweets_key WHERE t2.tweets_key IS NULL ORDER BY t1.tweets_key DESC LIMIT " + str(no_of_tweets)


# Select all **NOT CATEGORIZED** tweets from **SEED** account, or **CATEGORIZED** by `user_id = 447`

# In[17]:


tweet_query = "SELECT t1.tweets_key, t1.profile_key, t1.content FROM tweets_table t1 LEFT JOIN category_table t2 ON t1.tweets_key = t2.tweets_key WHERE (t2.tweets_key IS NULL OR (t2.tweets_key IS NOT NULL AND t2.user_id = 447)) AND (t1.profile_key = 3317 OR t1.profile_key = 12731 OR t1.profile_key = 13046 OR  t1.profile_key = 13091 OR t1.profile_key = 13104 OR t1.profile_key = 13148 OR  t1.profile_key = 13684 OR t1.profile_key = 13818 OR t1.profile_key = 14453)  ORDER BY t1.tweets_key"


# Select all **NOT CATEGORIZED** tweets from **SEED** account

# In[18]:


tweet_query = "SELECT t1.tweets_key, t1.profile_key, t1.content FROM tweets_table t1 LEFT JOIN category_table t2 ON t1.tweets_key = t2.tweets_key WHERE t2.tweets_key IS NULL AND (t1.profile_key = 3317 OR t1.profile_key = 12731 OR t1.profile_key = 13046 OR  t1.profile_key = 13091 OR t1.profile_key = 13104 OR t1.profile_key = 13148 OR  t1.profile_key = 13684 OR t1.profile_key = 13818 OR t1.profile_key = 14453) ORDER BY t1.tweets_key"


# In[19]:


tweet_col = ["tweets_key", "profile_key", "content"]


# In[20]:


tweet_df = pd.DataFrame(get_query(tweet_query, db=database), columns=tweet_col)
type(tweet_df)


# In[21]:


tweet_df.info()


# In[22]:


tweet_df.head(24)


# In[23]:


#tweet_df.sort_values(by="tweets_key")


# ## Automatic Categorization

# In[24]:


auto_df = pd.DataFrame(build_corpus(corpus_df, tweet_df, create_corpus=False))
type(auto_df)


# In[25]:


auto_df.info()


# In[26]:


auto_df["category_computed"] = auto_df.ix[:, '1':'8'].idxmax(axis=1)


# In[27]:


#auto_df


# ## Inserting Into Database

# In[28]:


def insert_query(sql_statement, values, db='test_soda'):
    """
    insert_query(sql_statement)
    
    This function is a database accessor function that will 
    insert a query into the database.
    
    It has no return value.    
    """
    conn = MySQLdb.connect(host="10.24.12.21",
                           user="soda",
                           passwd="MSUJHU2016",
                           db=db)
    cur = conn.cursor()
    
    try:
        cur.execute(sql_statement, values)
        conn.commit()
    except:
        conn.rollback()
    
    conn.close()


# In[29]:


update_query = """INSERT INTO category_table (tweets_key, category_1, categorization_type, user_id) VALUES (%s,%s,%s,%s)"""


# Test insert
# 
# ```Python
# insert_query(update_query, (str(28608), str(3), str(1), str(447)), db=database)
# ```

# In[30]:


for i in auto_df.index.sort_values():
    print "updating tweet_key", i,           "with category_1 =", str(auto_df.loc[i]['category_computed']),           "for user_id =", str(user_id)

    insert_query(update_query, (str(i), str(str(auto_df.loc[i]['category_computed'])),
                                str(categorization_type), str(user_id)), db=database)

