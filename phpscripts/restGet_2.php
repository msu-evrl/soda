<?php

/* ---------------------EVRL LAB------------------APL LABS------------------>-----------SODA Database---------------------------<----------------------------------------------------*/
/*---------------------------------------------This program makes REST/GET requests from Twitter API and stores the information in EVRL SODA DB-------------------------------------*/
/* -------------------------------------------------------------------------Author - Emmanuel Shedu--------------------------------------------------------------------------------*/




//error_reporting(0);
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');
echo "Starting...<br>";
sleep(10);

// calling functions that will perform get requests for specific seeds
// seperate the fuction calls with a delay to allow http request to "relax" -this prevents HTTP request timeout
// sleep(time in seconds);

function start2()
{
	$servername = "127.0.0.1:3306"; //evrlnas IP address
	$username = "root";
	$password = "toor";
	$dbname = "soda";
	$settings = array(
			'oauth_access_token' => "775745056154288133-hAQlfGuCFLdO9c1XIiNFcyzN0GNOArq",
			'oauth_access_token_secret' => "wmsUnQ4dPDEObgLVJjvjrHick7ssrY4tGgEM7Y29JOWv0",
			'consumer_key' => "dH0Q8q419r721XyBpbs6of7c6",
			'consumer_secret' => "bFnFb67WGkAMv3Mz8VGYVDwITe8qvqLETd2JHL44FnW3NmGXs0");
	
	// TRY to make connection to database and begin populating
	try 
	{								
		$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
		// set the PDO error mode to exception
		//check whether profile exists
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//$check = $conn->prepare("SELECT `tweet_id` FROM `tweets_table` WHERE `lat` !=0 AND `lon` != 0");
		$query_key = $conn->prepare("SELECT `tweet_id` FROM `status_table` WHERE `status_table`.`status` = 'pending' ");
		$query_key->execute();
		$i = 0;
		$j = 0;
		$checkurl = FALSE;
		$checkco = FALSE;
		while ($tweet_id = $query_key->fetch(PDO::FETCH_ASSOC))
		{			
			$tweet_idi = $tweet_id["tweet_id"];
			
			
			$url = 'https://api.twitter.com/1.1/statuses/lookup.json';
			$getfield = '?id=' . $tweet_idi;
			$requestMethod = 'GET';
			$twitter = new TwitterAPIExchange($settings);
			//restGet contains the array of tweets and its information from the user mention in the $getfield [screen_name]
			$restGet = json_decode($twitter->setGetfield($getfield)
					->buildOauth($url, $requestMethod)
					->performRequest(), $assoc = TRUE);
			if(!$restGet)
				continue;
			
			echo "Count: ". $i. "<br>";
			echo $tweet_idi. "<br>";
			print_r($restGet);
			echo "<br>";
			
			

			
			if($restGet[0]["place"]["bounding_box"]["coordinates"] != NULL)
			{
				$coordinates[0][0] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][0][0];
				$coordinates[0][1] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][0][1];
				$coordinates[1][0] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][1][0];
				$coordinates[1][1] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][1][1];
				$coordinates[2][0] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][2][0];
				$coordinates[2][1] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][2][1];
				$coordinates[3][0] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][3][0];
				$coordinates[3][1] = $restGet[0]["place"]["bounding_box"]["coordinates"][0][3][1];				
				$lat_long = GetCenterFromDegrees($coordinates);
				echo "<br> latlong_raw: ";
				print_r($lat_long);
				echo "<br>";
				$latlon = $conn->prepare("UPDATE `tweets_table` SET `lat` = ? , `lon` = ? WHERE `tweets_table`.`tweet_id` = ?");
				$latlon->bindValue(1, $lat_long[1], PDO::PARAM_INT);
				$latlon->bindValue(2, $lat_long[0], PDO::PARAM_INT);
				$latlon->bindValue(3, $tweet_idi, PDO::PARAM_INT);
				if($latlon->execute())
					$checkco = "latlon_yes";
				else
					$checkco = "mysql_err";
			}
			else
			{
				$checkco = "latlon_yes";
			}
			
			
			
			
			$query_key0 = $conn->prepare("SELECT `tweets_key` FROM `tweets_table` WHERE `tweets_table`.`tweet_id` = '$tweet_idi' ");
			$query_key0->execute();
			$tweets_key = $query_key0->fetch(PDO::FETCH_ASSOC);
			

			
			if ($xURL = $restGet[0]["entities"]["urls"][0]["expanded_url"])
			{
				$stmt2 = $conn->prepare("UPDATE `association_table` SET `association` = ? WHERE `association_table`.`tweets_key` = ? AND `association_table`.`association_type` = 'url'");
				$stmt2->bindValue(1, $xURL, PDO::PARAM_STR);
				$stmt2->bindValue(2, $tweets_key["tweets_key"], PDO::PARAM_INT);
				if($stmt2->execute())
					$checkurl = "url_yes";
				else
					$checkurl = "mysql_err";
			}
			else
			{
				$checkurl = "url_yes";
			}
			
			if($checkco === "latlon_yes" && $checkurl === "url_yes")
			{
				$status_change = $conn->prepare("UPDATE `status_table` SET `status` = 'done' WHERE `status_table`.`tweet_id` = '$tweet_idi'");
				if($status_change->execute())
				{
					echo "status change<br>";
				}
			}
			
			
			//set checks back to false for re evaluation
			if($checkurl == "url_yes")
			{
				echo "url done <br>";
				$checkurl = "";
			}
			else
				echo "url mysql error <br>";
			
			if($checkco == "latlon_yes")
			{
				echo "latlon done<br>";
				$checkco = "";
			}
			else
				echo "latlon mysql error <br>";	
			
			if ($j == 14)
			{
				sleep(60*15);//time in minutes
				$j = 0;
			}
			$i++;
			$j++;
		}		
		
	}
	catch(PDOException $e)
	{
		echo "Error: " . $e->getMessage();
	}

}
?>