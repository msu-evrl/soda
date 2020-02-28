import numpy as np
import pandas as pd
import MySQLdb as mysql
import sys
# import nlp
# import database_access as dba

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

def post_query(sql_statement, values, db='test_soda'):
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