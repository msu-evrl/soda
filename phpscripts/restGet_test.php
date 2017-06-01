<?php

/* ---------------------EVRL LAB------------------APL LABS------------------>-----------SODA Database---------------------------<----------------------------------------------------*/
/*---------------------------------------------This program makes REST/GET requests from Twitter API and stores the information in EVRL SODA DB-------------------------------------*/
/* -------------------------------------------------------------------------Author - Emmanuel Shedu--------------------------------------------------------------------------------*/




//error_reporting(0);
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');



function SeedPopulate($arg)
{
	$servername = "127.0.0.1:3306"; //evrlnas IP address
	$username = "root";
	$password = "toor";
	$dbname = "soda";
	
	//inicount is used to originally pull a large amount of data from timeline, this should be occassionally and done for one user at a time, due to high amount of data recieved
	//count is the number of tweets to try to pull from the user's timeline
	$inicount = 1500;
	$count = 200;
	$i = 0;
	// new change - 06/09/2016
	$track_count= 0;
	$t = 0;
	
	
	//evrlmorgan@gmail.com
	$settings = array(
			'oauth_access_token' => "775745056154288133-hAQlfGuCFLdO9c1XIiNFcyzN0GNOArq",
			'oauth_access_token_secret' => "wmsUnQ4dPDEObgLVJjvjrHick7ssrY4tGgEM7Y29JOWv0",
			'consumer_key' => "dH0Q8q419r721XyBpbs6of7c6",
			'consumer_secret' => "bFnFb67WGkAMv3Mz8VGYVDwITe8qvqLETd2JHL44FnW3NmGXs0");

	//url to getuser timeline, SEE https://dev.twitter.com/rest/public to learn more about REST API requests
	$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
	$getfield = '?screen_name=' . $arg. '&count=' .$count;
	$requestMethod = 'GET';
	$twitter = new TwitterAPIExchange($settings);
	//restGet contains the array of tweets and its information from the user mention in the $getfield [screen_name]
	$restGet = $twitter->setGetfield($getfield)
			->buildOauth($url, $requestMethod)
			->performRequest();


	$arrrestGet = (array)$restGet;
	foreach((array)$restGet as $arr)
		{
			$stuff = json_decode($arr, true);
			foreach($stuff as $str)
			{
				print_r($str["user"]["name"]); // this can be commented out, It is used during deveopment to know what user, information is being mined from
				echo "<br>";
				echo "<br>";
				
				//assign the elements of the array to specific variables to improve readability
				$name[$i] = $str["user"]["name"];
				$screen_name[$i] = $str["user"]["screen_name"];
				$twitter_id8[$i] = $str["user"]["id_str"];
				$reply_to_id8[$i] = $str["retweeted_status"]["user"]["id_str"];
				$reply_to_author8[$i] = $str["retweeted_status"]["user"]["name"];
				(int)$quotweet_count8[$i] = $str["retweeted_status"]["retweet_count"];
				$text8[$i] = $str["text"];
				$date8[$i] = $str["created_at"];
				(int)$favorite_count8[$i] = $str["favorite_count"];
				(int)$retweet_count8[$i] = $str["retweet_count"];
				$tweet_id8[$i] = $str["id_str"];
				$quoted_id8[$i] = $str["retweeted_status"]["id_str"];
				
				
				date_default_timezone_set('America/New_York');
				$s = $date8[$i];
				$formatted_date = date('Y-m-d H:i:s', strtotime($s));
				$curr_date = date("Y-m-d H:i:s");
				
				// TRY to make connection to database and begin populating
				try 
				{								
					$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
					// set the PDO error mode to exception
					//check whether profile exists
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$check = $conn->prepare("SELECT * FROM profile_table WHERE twitter_id = ?");
					$check->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
					$check->execute();
					$profile_check = $check->fetch(PDO::FETCH_ASSOC);
					
					
					// check whether tweet exists
					$check = $conn->prepare("SELECT * FROM tweets_table WHERE tweet_id = ?");
					$check->bindValue(1, $tweet_id8[$i], PDO::PARAM_STR);
					$check->execute();
					$tweet_check = $check->fetch(PDO::FETCH_ASSOC);


	/* -------------if profile check is FALSE and tweet check is also FALSE, i.e if profile and tweet are not found------------------------------------------------*/

					if($profile_check == FALSE && $tweet_check == FALSE)
					{
						echo "the first!"; // this allows me to know what section of the conditional statement is working
						echo "<br>";
						echo "<br>";
						
						
						$stmt = $conn->prepare("INSERT INTO profile_table (name, screen_name, twitter_id) VALUES (?, ?, ?)");
						// insert a row
						$stmt->execute(array($name[$i], $screen_name[$i], $twitter_id8[$i]));
						
						// prepare sql and bind parameters for tweets
						$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_profile_ky->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
						$row_profile_ky->execute();
						// set the resulting array to associative
						$resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
						//print_r("Resultp: ".$resultp['profile_key']);
							
						$stmt1->execute(array($resultp['profile_key'],$formatted_date, $favorite_count8[$i], $reply_to_id8[$i], $reply_to_author8[$i], $quoted_id8[$i], (int)$quotweet_count8[$i] , $retweet_count8[$i], $text8[$i], $tweet_id8[$i]));
						// prepare sql and bind parameters for tweets
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
								
						//historical_count table fill
						if(($screen_name[$i]) == "BaltimorePolice" || ($screen_name[$i]) == "BaltimoreSAO" ||($screen_name[$i]) == "MarilynMosbyEsq" || ($screen_name[$i]) == "CommishDavis" || ($screen_name[$i]) == "NicoleSMonroe" ||($screen_name[$i]) == "TJSmithMedia" ||($screen_name[$i]) == "FOP3" || ($screen_name[$i]) == "MDSP" || ($screen_name[$i]) == "MayorSRB")
						{
							$stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
							// insert a row
							$stmth->execute(array($curr_date, $resultt['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));
						}
						
						$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
						
						$hashtag = "hashtag";
						$url = "url";
						$user_mentions = "user_mention";
						$no_associations = "no_association";
									
						if ($str["entities"]["hashtags"][0]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][0]["text"]));
						}
						if ($str["entities"]["hashtags"][1]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][1]["text"]));
						}
						if ($str["entities"]["hashtags"][2]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][2]["text"]));
						}
						if ($str["entities"]["hashtags"][3]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][3]["text"]));
						}
						if ($str["entities"]["hashtags"][4]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][4]["text"]));
						}
						if ($str["entities"]["hashtags"][5]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][5]["text"]));
						}
							
						if ($str["entities"]["urls"][0]["url"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $url, $str["entities"]["urls"][0]["url"]));
						}
							
						if ($str["entities"]["user_mentions"][0]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][0]["name"]));
						}
						if ($str["entities"]["user_mentions"][1]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][1]["name"]));
						}
						else if ($str["entities"]["urls"][0]["url"] == NULL && $str["entities"]["user_mentions"][0]["name"] == NULL && $str["entities"]["hashtags"][0]["text"] == NULL)
						{								
							$stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
						}
						
					}
					//--------------------------------------------------------------------------------------------------------------------------------------------------------------//
					
	/*--------------if profile check is NOT FALSE and tweet check is false, i.e if profile is found BUT tweet is not found------------------------------------------*/
					else if ($profile_check != FALSE && $tweet_check == FALSE)
					{				
						echo "the second!";
						echo "<br>";
						echo "<br>";
						
						// prepare sql and bind parameters for tweets
						$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_profile_ky->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
						$row_profile_ky->execute();
						$resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
						
						//print_r("Resultp: ".$resultp['profile_key']);
						$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$stmt1->execute(array($resultp['profile_key'],$formatted_date, $favorite_count8[$i], $reply_to_id8[$i], $reply_to_author8[$i], $quoted_id8[$i], (int)$quotweet_count8[$i] , $retweet_count8[$i], $text8[$i], $tweet_id8[$i]));
						// prepare sql and bind parameters for tweets
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
								
						//historical_count table fill
						if(($screen_name[$i]) == "BaltimorePolice" || ($screen_name[$i]) == "BaltimoreSAO" ||($screen_name[$i]) == "MarilynMosbyEsq" || ($screen_name[$i]) == "CommishDavis" || ($screen_name[$i]) == "NicoleSMonroe" ||($screen_name[$i]) == "TJSmithMedia" ||($screen_name[$i]) == "FOP3" || ($screen_name[$i]) == "MDSP" || ($screen_name[$i]) == "MayorSRB")
						{
							$stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
							// insert a row
							$stmth->execute(array($curr_date, $resultt['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));
						}
						
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
						
						$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
						
						$hashtag = "hashtag";
						$url = "url";
						$user_mentions = "user_mention";
						$no_associations = "no_association";
									
						if ($str["entities"]["hashtags"][0]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][0]["text"]));
						}
						if ($str["entities"]["hashtags"][1]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][1]["text"]));
						}
						if ($str["entities"]["hashtags"][2]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][2]["text"]));
						}
						if ($str["entities"]["hashtags"][3]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][3]["text"]));
						}
						if ($str["entities"]["hashtags"][4]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][4]["text"]));
						}
						if ($str["entities"]["hashtags"][5]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][5]["text"]));
						}
							
						if ($str["entities"]["urls"][0]["url"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $url, $str["entities"]["urls"][0]["url"]));
						}
							
						if ($str["entities"]["user_mentions"][0]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][0]["name"]));
						}
						if ($str["entities"]["user_mentions"][1]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][1]["name"]));
						}
						else if ($str["entities"]["urls"][0]["url"] == NULL && $str["entities"]["user_mentions"][0]["name"] == NULL && $str["entities"]["hashtags"][0]["text"] == NULL)
						{								
							$stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
						}
					}
					//---------------------------------------------------------------------------------------------------------------------------------------------------//
					
	/*--------------if profile check is NOT FALSE and tweet check is also NOT false, i.e if profile and tweet are both found-------------------------------*/
					else if ($profile_check != FALSE && $tweet_check != FALSE)
					{
						echo "the third!";
						echo "<br>";
						echo "<br>";
						
						// since the tweet is already in the database, update the favorite count and the retweet count of rows that has the specific tweet with "tweet_id"
						$row_tweets_ky = $conn->prepare("UPDATE `tweets_table` SET `favourite_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
						$row_tweets_ky->bindValue(1, $favorite_count8[$i], PDO::PARAM_INT);
						$row_tweets_ky->bindValue(2, $tweet_id8[$i], PDO::PARAM_STR);
						$row_tweets_ky->execute();
						
						
						$row_tweets_ky1 = $conn->prepare("UPDATE `tweets_table` SET `retweet_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
						$row_tweets_ky1->bindValue(1, $retweet_count8[$i], PDO::PARAM_INT);
						$row_tweets_ky1->bindValue(2, $tweet_id8[$i], PDO::PARAM_STR);
						$row_tweets_ky1->execute();				
						
						
						$row_tweets_ky2 = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE tweet_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_tweets_ky2->bindValue(1, $tweet_id8[$i], PDO::PARAM_INT);
						$row_tweets_ky2->execute();
						$result2 = $row_tweets_ky2->fetch(PDO::FETCH_ASSOC);

						
						$row_tweets_ky3 = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
						$row_tweets_ky3->execute(array($curr_date, $result2['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));					
					}
					//------------------------------------------------------------------------------------------------------------------------------------------//		
					
					
				}
				catch(PDOException $e)
				{
					echo "Error: " . $e->getMessage();
				}
				$i++;
				
			}
			/* Update to code - 06/09/2016 */
			$t++;
		}
	
	$last_id = $stuff[$t]["id_str"];
	$prev_id = 0;
	$loop_num = 0;
	while ($last_id != $prev_id && $loop_num < 4)
	{
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield = '?screen_name=' . $arg. '&count=' .$count. 'max_id='. $last_id;
		$requestMethod = 'GET';
		$twitter = new TwitterAPIExchange($settings);
		//restGet contains the array of tweets and its information from the user mention in the $getfield [screen_name]
		$restGet = $twitter->setGetfield($getfield)
				->buildOauth($url, $requestMethod)
				->performRequest();
				
		foreach((array)$restGet as $arr)
		{
			$stuff = json_decode($arr, true);
			foreach($stuff as $str)
			{
				print_r($str["user"]["name"]); // this can be commented out, It is used during deveopment to know what user, information is being mined from
				echo "<br>";
				echo "<br>";
				
				//assign the elements of the array to specific variables to improve readability
				$name[$i] = $str["user"]["name"];
				$screen_name[$i] = $str["user"]["screen_name"];
				$twitter_id8[$i] = $str["user"]["id_str"];
				$reply_to_id8[$i] = $str["retweeted_status"]["user"]["id_str"];
				$reply_to_author8[$i] = $str["retweeted_status"]["user"]["name"];
				(int)$quotweet_count8[$i] = $str["retweeted_status"]["retweet_count"];
				$text8[$i] = $str["text"];
				$date8[$i] = $str["created_at"];
				(int)$favorite_count8[$i] = $str["favorite_count"];
				(int)$retweet_count8[$i] = $str["retweet_count"];
				$tweet_id8[$i] = $str["id_str"];
				$quoted_id8[$i] = $str["retweeted_status"]["id_str"];
				
				
				date_default_timezone_set('America/New_York');
				$s = $date8[$i];
				$formatted_date = date('Y-m-d H:i:s', strtotime($s));
				$curr_date = date("Y-m-d H:i:s");
				
				// TRY to make connection to database and begin populating
				try 
				{								
					$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
					// set the PDO error mode to exception
					//check whether profile exists
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$check = $conn->prepare("SELECT * FROM profile_table WHERE twitter_id = ?");
					$check->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
					$check->execute();
					$profile_check = $check->fetch(PDO::FETCH_ASSOC);
					
					
					// check whether tweet exists
					$check = $conn->prepare("SELECT * FROM tweets_table WHERE tweet_id = ?");
					$check->bindValue(1, $tweet_id8[$i], PDO::PARAM_STR);
					$check->execute();
					$tweet_check = $check->fetch(PDO::FETCH_ASSOC);


	/* -------------if profile check is FALSE and tweet check is also FALSE, i.e if profile and tweet are not found------------------------------------------------*/

					if($profile_check == FALSE && $tweet_check == FALSE)
					{
						echo "the first!"; // this allows me to know what section of the conditional statement is working
						echo "<br>";
						echo "<br>";
						
						
						$stmt = $conn->prepare("INSERT INTO profile_table (name, screen_name, twitter_id) VALUES (?, ?, ?)");
						// insert a row
						$stmt->execute(array($name[$i], $screen_name[$i], $twitter_id8[$i]));
						
						// prepare sql and bind parameters for tweets
						$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_profile_ky->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
						$row_profile_ky->execute();
						// set the resulting array to associative
						$resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
						//print_r("Resultp: ".$resultp['profile_key']);
							
						$stmt1->execute(array($resultp['profile_key'],$formatted_date, $favorite_count8[$i], $reply_to_id8[$i], $reply_to_author8[$i], $quoted_id8[$i], (int)$quotweet_count8[$i] , $retweet_count8[$i], $text8[$i], $tweet_id8[$i]));
						// prepare sql and bind parameters for tweets
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
								
						//historical_count table fill
						if(($screen_name[$i]) == "BaltimorePolice" || ($screen_name[$i]) == "BaltimoreSAO" ||($screen_name[$i]) == "MarilynMosbyEsq" || ($screen_name[$i]) == "CommishDavis" || ($screen_name[$i]) == "NicoleSMonroe" ||($screen_name[$i]) == "TJSmithMedia" ||($screen_name[$i]) == "FOP3" || ($screen_name[$i]) == "MDSP" || ($screen_name[$i]) == "MayorSRB")
						{
							$stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
							// insert a row
							$stmth->execute(array($curr_date, $resultt['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));
						}
						
						$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
						
						$hashtag = "hashtag";
						$url = "url";
						$user_mentions = "user_mention";
						$no_associations = "no_association";
				
						if ($str["entities"]["hashtags"][0]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][0]["text"]));
						}
						if ($str["entities"]["hashtags"][1]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][1]["text"]));
						}
						if ($str["entities"]["hashtags"][2]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][2]["text"]));
						}
						if ($str["entities"]["hashtags"][3]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][3]["text"]));
						}
						if ($str["entities"]["hashtags"][4]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][4]["text"]));
						}
						if ($str["entities"]["hashtags"][5]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][5]["text"]));
						}
						
						if ($str["entities"]["urls"][0]["url"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $url, $str["entities"]["urls"][0]["url"]));
						}
						
						if ($str["entities"]["user_mentions"][0]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][0]["name"]));
						}
						if ($str["entities"]["user_mentions"][1]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][1]["name"]));
						}
						else if ($str["entities"]["urls"][0]["url"] == NULL && $str["entities"]["user_mentions"][0]["name"] == NULL && $str["entities"]["hashtags"][0]["text"] == NULL)
						{								
							$stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
						}
						
					}
					//--------------------------------------------------------------------------------------------------------------------------------------------------------------//
					
	/*--------------if profile check is NOT FALSE and tweet check is false, i.e if profile is found BUT tweet is not found------------------------------------------*/
					else if ($profile_check != FALSE && $tweet_check == FALSE)
					{				
						echo "the second!";
						echo "<br>";
						echo "<br>";
						
						// prepare sql and bind parameters for tweets
						$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_profile_ky->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
						$row_profile_ky->execute();
						$resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
						
						//print_r("Resultp: ".$resultp['profile_key']);
						$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$stmt1->execute(array($resultp['profile_key'],$formatted_date, $favorite_count8[$i], $reply_to_id8[$i], $reply_to_author8[$i], $quoted_id8[$i], (int)$quotweet_count8[$i] , $retweet_count8[$i], $text8[$i], $tweet_id8[$i]));
						// prepare sql and bind parameters for tweets
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
								
						//historical_count table fill
						if(($screen_name[$i]) == "BaltimorePolice" || ($screen_name[$i]) == "BaltimoreSAO" ||($screen_name[$i]) == "MarilynMosbyEsq" || ($screen_name[$i]) == "CommishDavis" || ($screen_name[$i]) == "NicoleSMonroe" ||($screen_name[$i]) == "TJSmithMedia" ||($screen_name[$i]) == "FOP3" || ($screen_name[$i]) == "MDSP" || ($screen_name[$i]) == "MayorSRB")
						{
							$stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
							// insert a row
							$stmth->execute(array($curr_date, $resultt['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));
						}
						
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
						
						$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
						
						$hashtag = "hashtag";
						$url = "url";
						$user_mentions = "user_mention";
						$no_associations = "no_association";
									
						if ($str["entities"]["hashtags"][0]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][0]["text"]));
						}
						if ($str["entities"]["hashtags"][1]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][1]["text"]));
						}
						if ($str["entities"]["hashtags"][2]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][2]["text"]));
						}
						if ($str["entities"]["hashtags"][3]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][3]["text"]));
						}
						if ($str["entities"]["hashtags"][4]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][4]["text"]));
						}
						if ($str["entities"]["hashtags"][5]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][5]["text"]));
						}
							
						if ($str["entities"]["urls"][0]["url"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $url, $str["entities"]["urls"][0]["url"]));
						}
							
						if ($str["entities"]["user_mentions"][0]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][0]["name"]));
						}
						if ($str["entities"]["user_mentions"][1]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][1]["name"]));
						}
						else if ($str["entities"]["urls"][0]["url"] == NULL && $str["entities"]["user_mentions"][0]["name"] == NULL && $str["entities"]["hashtags"][0]["text"] == NULL)
						{								
							$stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
						}
					}
					//---------------------------------------------------------------------------------------------------------------------------------------------------//
					
	/*--------------if profile check is NOT FALSE and tweet check is also NOT false, i.e if profile and tweet are both found-------------------------------*/
					else if ($profile_check != FALSE && $tweet_check != FALSE)
					{
						echo "the third!";
						echo "<br>";
						echo "<br>";
						
						// since the tweet is already in the database, update the favorite count and the retweet count of rows that has the specific tweet with "tweet_id"
						$row_tweets_ky = $conn->prepare("UPDATE `tweets_table` SET `favourite_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
						$row_tweets_ky->bindValue(1, $favorite_count8[$i], PDO::PARAM_INT);
						$row_tweets_ky->bindValue(2, $tweet_id8[$i], PDO::PARAM_STR);
						$row_tweets_ky->execute();
						
						
						$row_tweets_ky1 = $conn->prepare("UPDATE `tweets_table` SET `retweet_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
						$row_tweets_ky1->bindValue(1, $retweet_count8[$i], PDO::PARAM_INT);
						$row_tweets_ky1->bindValue(2, $tweet_id8[$i], PDO::PARAM_STR);
						$row_tweets_ky1->execute();				
						
						
						$row_tweets_ky2 = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE tweet_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_tweets_ky2->bindValue(1, $tweet_id8[$i], PDO::PARAM_INT);
						$row_tweets_ky2->execute();
						$result2 = $row_tweets_ky2->fetch(PDO::FETCH_ASSOC);

						
						$row_tweets_ky3 = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
						$row_tweets_ky3->execute(array($curr_date, $result2['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));					
					}
					//------------------------------------------------------------------------------------------------------------------------------------------//		
					
					
				}
				catch(PDOException $e)
				{
					echo "Error: " . $e->getMessage();
				}
				$i++;
				
			}
			/* Update to code - 06/09/2016 */
			$t++;
		}
		$prev_id = $last_id;
		$last_id = $stuff[$t]["id_str"];		
		$loop_num++;
	}
	
}
function pulltweet($restGet)
	{
		foreach((array)$restGet as $arr)
		{
			$stuff = json_decode($arr, true);
			foreach($stuff as $str)
			{
				print_r($str["user"]["name"]); // this can be commented out, It is used during deveopment to know what user, information is being mined from
				echo "<br>";
				echo "<br>";
				
				//assign the elements of the array to specific variables to improve readability
				$name[$i] = $str["user"]["name"];
				$screen_name[$i] = $str["user"]["screen_name"];
				$twitter_id8[$i] = $str["user"]["id_str"];
				$reply_to_id8[$i] = $str["retweeted_status"]["user"]["id_str"];
				$reply_to_author8[$i] = $str["retweeted_status"]["user"]["name"];
				(int)$quotweet_count8[$i] = $str["retweeted_status"]["retweet_count"];
				$text8[$i] = $str["text"];
				$date8[$i] = $str["created_at"];
				(int)$favorite_count8[$i] = $str["favorite_count"];
				(int)$retweet_count8[$i] = $str["retweet_count"];
				$tweet_id8[$i] = $str["id_str"];
				$quoted_id8[$i] = $str["retweeted_status"]["id_str"];
				
				
				date_default_timezone_set('America/New_York');
				$s = $date8[$i];
				$formatted_date = date('Y-m-d H:i:s', strtotime($s));
				$curr_date = date("Y-m-d H:i:s");
				
				// TRY to make connection to database and begin populating
				try 
				{								
					$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
					// set the PDO error mode to exception
					//check whether profile exists
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$check = $conn->prepare("SELECT * FROM profile_table WHERE twitter_id = ?");
					$check->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
					$check->execute();
					$profile_check = $check->fetch(PDO::FETCH_ASSOC);
					
					
					// check whether tweet exists
					$check = $conn->prepare("SELECT * FROM tweets_table WHERE tweet_id = ?");
					$check->bindValue(1, $tweet_id8[$i], PDO::PARAM_STR);
					$check->execute();
					$tweet_check = $check->fetch(PDO::FETCH_ASSOC);


	/* -------------if profile check is FALSE and tweet check is also FALSE, i.e if profile and tweet are not found------------------------------------------------*/

					if($profile_check == FALSE && $tweet_check == FALSE)
					{
						echo "the first!"; // this allows me to know what section of the conditional statement is working
						echo "<br>";
						echo "<br>";
						
						
						$stmt = $conn->prepare("INSERT INTO profile_table (name, screen_name, twitter_id) VALUES (?, ?, ?)");
						// insert a row
						$stmt->execute(array($name[$i], $screen_name[$i], $twitter_id8[$i]));
						
						// prepare sql and bind parameters for tweets
						$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_profile_ky->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
						$row_profile_ky->execute();
						// set the resulting array to associative
						$resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
						//print_r("Resultp: ".$resultp['profile_key']);
							
						$stmt1->execute(array($resultp['profile_key'],$formatted_date, $favorite_count8[$i], $reply_to_id8[$i], $reply_to_author8[$i], $quoted_id8[$i], (int)$quotweet_count8[$i] , $retweet_count8[$i], $text8[$i], $tweet_id8[$i]));
						// prepare sql and bind parameters for tweets
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
								
						//historical_count table fill
						if(($screen_name[$i]) == "BaltimorePolice" || ($screen_name[$i]) == "BaltimoreSAO" ||($screen_name[$i]) == "MarilynMosbyEsq" || ($screen_name[$i]) == "CommishDavis" || ($screen_name[$i]) == "NicoleSMonroe" ||($screen_name[$i]) == "TJSmithMedia" ||($screen_name[$i]) == "FOP3" || ($screen_name[$i]) == "MDSP" || ($screen_name[$i]) == "MayorSRB")
						{
							$stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
							// insert a row
							$stmth->execute(array($curr_date, $resultt['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));
						}
						
						$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
						
						$hashtag = "hashtag";
						$url = "url";
						$user_mentions = "user_mention";
						$no_associations = "no_association";
									
						if ($str["entities"]["hashtags"][0]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][0]["text"]));
						}
						if ($str["entities"]["hashtags"][1]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][1]["text"]));
						}
						if ($str["entities"]["hashtags"][2]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][2]["text"]));
						}
						if ($str["entities"]["hashtags"][3]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][3]["text"]));
						}
						if ($str["entities"]["hashtags"][4]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][4]["text"]));
						}
						if ($str["entities"]["hashtags"][5]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][5]["text"]));
						}
							
						if ($str["entities"]["urls"][0]["url"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $url, $str["entities"]["urls"][0]["url"]));
						}
							
						if ($str["entities"]["user_mentions"][0]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][0]["name"]));
						}
						if ($str["entities"]["user_mentions"][1]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][1]["name"]));
						}
						else if ($str["entities"]["urls"][0]["url"] == NULL && $str["entities"]["user_mentions"][0]["name"] == NULL && $str["entities"]["hashtags"][0]["text"] == NULL)
						{								
							$stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
						}
						
					}
					//--------------------------------------------------------------------------------------------------------------------------------------------------------------//
					
	/*--------------if profile check is NOT FALSE and tweet check is false, i.e if profile is found BUT tweet is not found------------------------------------------*/
					else if ($profile_check != FALSE && $tweet_check == FALSE)
					{				
						echo "the second!";
						echo "<br>";
						echo "<br>";
						
						// prepare sql and bind parameters for tweets
						$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_profile_ky->bindValue(1, $twitter_id8[$i], PDO::PARAM_STR);
						$row_profile_ky->execute();
						$resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
						
						//print_r("Resultp: ".$resultp['profile_key']);
						$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$stmt1->execute(array($resultp['profile_key'],$formatted_date, $favorite_count8[$i], $reply_to_id8[$i], $reply_to_author8[$i], $quoted_id8[$i], (int)$quotweet_count8[$i] , $retweet_count8[$i], $text8[$i], $tweet_id8[$i]));
						// prepare sql and bind parameters for tweets
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
								
						//historical_count table fill
						if(($screen_name[$i]) == "BaltimorePolice" || ($screen_name[$i]) == "BaltimoreSAO" ||($screen_name[$i]) == "MarilynMosbyEsq" || ($screen_name[$i]) == "CommishDavis" || ($screen_name[$i]) == "NicoleSMonroe" ||($screen_name[$i]) == "TJSmithMedia" ||($screen_name[$i]) == "FOP3" || ($screen_name[$i]) == "MDSP" || ($screen_name[$i]) == "MayorSRB")
						{
							$stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
							// insert a row
							$stmth->execute(array($curr_date, $resultt['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));
						}
						
						
						// pull tweets key form tweets table using profile key as a query means
						$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
						$row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_INT);
						$row_tweets_ky->execute();
						$resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
						
						$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
						
						$hashtag = "hashtag";
						$url = "url";
						$user_mentions = "user_mention";
						$no_associations = "no_association";
									
						if ($str["entities"]["hashtags"][0]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][0]["text"]));
						}
						if ($str["entities"]["hashtags"][1]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][1]["text"]));
						}
						if ($str["entities"]["hashtags"][2]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][2]["text"]));
						}
						if ($str["entities"]["hashtags"][3]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][3]["text"]));
						}
						if ($str["entities"]["hashtags"][4]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][4]["text"]));
						}
						if ($str["entities"]["hashtags"][5]["text"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $hashtag, $str["entities"]["hashtags"][5]["text"]));
						}
							
						if ($str["entities"]["urls"][0]["url"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $url, $str["entities"]["urls"][0]["url"]));
						}
							
						if ($str["entities"]["user_mentions"][0]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][0]["name"]));
						}
						if ($str["entities"]["user_mentions"][1]["name"] != NULL)
						{
							$stmt2->execute(array($resultt['tweets_key'], $user_mentions, $str["entities"]["user_mentions"][1]["name"]));
						}
						else if ($str["entities"]["urls"][0]["url"] == NULL && $str["entities"]["user_mentions"][0]["name"] == NULL && $str["entities"]["hashtags"][0]["text"] == NULL)
						{								
							$stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
						}
					}
					//---------------------------------------------------------------------------------------------------------------------------------------------------//
					
	/*--------------if profile check is NOT FALSE and tweet check is also NOT false, i.e if profile and tweet are both found-------------------------------*/
					else if ($profile_check != FALSE && $tweet_check != FALSE)
					{
						echo "the third!";
						echo "<br>";
						echo "<br>";
						
						// since the tweet is already in the database, update the favorite count and the retweet count of rows that has the specific tweet with "tweet_id"
						$row_tweets_ky = $conn->prepare("UPDATE `tweets_table` SET `favourite_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
						$row_tweets_ky->bindValue(1, $favorite_count8[$i], PDO::PARAM_INT);
						$row_tweets_ky->bindValue(2, $tweet_id8[$i], PDO::PARAM_STR);
						$row_tweets_ky->execute();
						
						
						$row_tweets_ky1 = $conn->prepare("UPDATE `tweets_table` SET `retweet_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
						$row_tweets_ky1->bindValue(1, $retweet_count8[$i], PDO::PARAM_INT);
						$row_tweets_ky1->bindValue(2, $tweet_id8[$i], PDO::PARAM_STR);
						$row_tweets_ky1->execute();				
						
						
						$row_tweets_ky2 = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE tweet_id = ? ORDER BY profile_key DESC LIMIT 1");
						$row_tweets_ky2->bindValue(1, $tweet_id8[$i], PDO::PARAM_INT);
						$row_tweets_ky2->execute();
						$result2 = $row_tweets_ky2->fetch(PDO::FETCH_ASSOC);

						
						$row_tweets_ky3 = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
						$row_tweets_ky3->execute(array($curr_date, $result2['tweets_key'], $favorite_count8[$i], $retweet_count8[$i]));					
					}
					//------------------------------------------------------------------------------------------------------------------------------------------//		
					
					
				}
				catch(PDOException $e)
				{
					echo "Error: " . $e->getMessage();
				}
				$i++;
				
			}
			/* Update to code - 06/09/2016 */
			$t++;
		}
	}


// calling functions that will perform get requests for specific seeds
// seperate the fuction calls with a delay to allow http request to "relax" -this prevents HTTP request timeout
// sleep(time in seconds);
while (1)
{
	SeedPopulate("BaltimorePolice");
	sleep(30);
	SeedPopulate("NicoleSMonroe");
	sleep(30);
	SeedPopulate("TJSmithMedia");
	sleep(30);
	SeedPopulate("FOP3");
	sleep(30);
	SeedPopulate("MajorWard300");
	sleep(30);
	SeedPopulate("CrimeLabBoss");
	sleep(1*60*60); // lets program run every 1 hour  good @CrimeLabBoss @MajorWard300 X @MayorSRB, @BaltimoreSAO, @MDSP, @MarylnMosbyEsq 
}