<?php


/** Set access tokens here - see: https://dev.twitter.com/apps/ **/


/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
/*$url = 'https://api.twitter.com/1.1/blocks/create.json';
$requestMethod = 'POST';*/

/** POST fields required by the URL above. See relevant docs as above **/
/*$postfields = array(
    'screen_name' => 'BaltimorePolice', 
    'skip_status' => '1'
);*/

/** Perform a POST request and echo the response **/
/*$twitter = new TwitterAPIExchange($settings);
echo $twitter->buildOauth($url, $requestMethod)
             ->setPostfields($postfields)
             ->performRequest();*/

/** Perform a GET request and echo the response **/
/** Note: Set the GET field BEFORE calling buildOauth(); **/

/*$getfield = '?screen_name=BaltimorePolice';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$restGet = $twitter->setGetfield($getfield)
        ->buildOauth($url, $requestMethod)
        ->performRequest();*/
//$arr = json_decode($restGet, true);
//declaration of variables to store elements from array

ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');



function  Database_backtrack()
{
	$servername = "10.24.12.21:3306";
	$username = "soda";
	$password = "MSUJHU2016";
	$dbname = "soda";
	$i = 0;
	
	
	// connect to database and begin populating
	try 
	{								
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
		// set the PDO error mode to exception
		//check whether profile exists
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
		// check whether tweet exists
		$check = $conn->prepare("SELECT `reply_to_id`,`quotweet_count`,`quoted_id` FROM `tweets_table` WHERE quotweet_count != 0 ORDER BY `tweets_table`.`tweets_key` ASC");
		//$check->bindValue(1, $tweet_id8[$i], PDO::PARAM_STR);
		$check->execute();
		$tweet_check = $check->fetchAll();
		
		foreach($tweet_check as $array)
		{
			$check = $conn->prepare("SELECT `quotweet_count` FROM `tweets_table` WHERE `reply_to_id` = ? ORDER BY `tweets_table`.`quotweet_count` DESC LIMIT 1");
			$check->bindValue(1, $tweet_check[$i]['reply_to_id'], PDO::PARAM_STR);
			$check->execute();
			$curr_quotweet = $check->fetchAll();
			
			$check = $conn->prepare("UPDATE `tweets_table` SET `retweet_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
			//$check->bindValue(1, $tweet_id8[$i], PDO::PARAM_STR);
			$check->execute();
			$i++;
		}
		
		print_r($tweet_check);
		// if row is not found
	}
	catch(PDOException $e)
	{
		echo "Error: " . $e->getMessage();
	}
	$i++;

}



// calling functions that will perform get requests for seeds
Database_backtrack();

