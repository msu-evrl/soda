<?php
define ('DB_USER', 'root');
define ('DB_PASSWORD', 'toor');
define ('DB_HOST', 'localhost:3306');
define ('DB_NAME', 'soda'); 
$dbc=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) 
    OR die ('Could not connect to MySQL: '.mysql_error()); @mysql_select_db (DB_NAME) 
    OR die ('Could not select the database" '.mysql_error());;
	/*try 
				{
					//print_r($_data->created_at.", ".$_data->text.", ".$_data->user->name.", ".$_data->user->screen_name.", ".$_data->user->location);
					print_r($_data->user->name, $_data->id, $_data->user->name);
					echo "<br>";
					echo "<br>";
					print_r($_data->created_at, $_data->coordinates, $_data->user->location, $_data->user->favourites_count, $_data->retweeted_status->in_reply_to_user_id, $_data->retweeted_status->user->name, $_data->retweet_count, $_data->text);
					echo "<br>";
					echo "<br>";
					print_r($_data->entities->hashtags[0]->text, $_data->user->url);
					echo "<br>";
					echo "<br>";
					print_r($_data->entities->user_mentions[0]->name, $_data->user->url);
					echo "<br>";
					echo "<br>";
					print_r($_data->user->url, $_data->user->url);
					echo "<br>";
					echo "<br>";
					
					//print_r($_data->text);
				//if (empty($_data)) die($_data);
				
				/*$f = fopen('php://output', 'w');
				$fl = fopen ('Myles.txt','a');
				$firstLineKeys = false;*/
				
				/*if (is_array($_data))
				{
					if ($checker <= 0)
					{
						fputcsv($fl, array_keys($_data));
						fputcsv($f, array_keys($_data));						
						$checker++;
					}
					
					foreach (array($_data->text) as $line)
					{
						
						fputcsv($f, $line);						
						fputcsv($fl, $line);
						//$firstLineKeys = array_flip($firstLineKeys);
					// Using array_merge is important to maintain the order of keys acording to the first element
						//fputcsv($f, array_merge($firstLineKeys, $line));
					}
				}
				
					$conn = new PDO("mysql:host=$servername;dbname=SOCIA_DATA", $username, $password);
						
					// set the PDO error mode to exception
					$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						
					// sql to create table
					$proflietable = "CREATE TABLE Profile (
					PId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
					firstname VARCHAR(30) NOT NULL,
					Twitter_ID BIGINT(30) NOT NULL,
					email VARCHAR(50),
					reg_date TIMESTAMP
					)";
								
					$tweetstable = "CREATE TABLE Tweets (
					TId INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,	
					Time_Created VARCHAR(30) NOT NULL,
					Coordinates INT(50),
					Location VARCHAR(30),
					Favorite_Count INT(30) NOT NULL,
					Reply_to_ID BIGINT(50),
					Reply_to_User BIGINT(50),
					Retweet_Count INT,
					Text LONGTEXT(1000000000),
					PId INT;
					reg_date TIMESTAMP
					FOREIGN KEY (PId) REFERENCES Persons(PId)
					)";
								
					$associationtable = "CREATE TABLE Assiociation (
					Aid INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
					Hashtag VARCHAR(30),
					URL VARCHAR(30) ,
					user_mentioned VARCHAR(50),
					TId INT
					reg_date TIMESTAMP
					FOREIGN KEY (TId) REFERENCES Persons(TId)
					)";			
							
					// use exec() because no results are returned
					$conn->exec($proflietable);
					$conn->exec($tweetstable);
					$conn->exec($associationtable);
					echo "Tables created successfully using PDO<br>";
				}
				catch(PDOException $e)
				{
					echo $proflietable . "<br>" . $e->getMessage();
					echo $tweetstable . "<br>" . $e->getMessage();
					echo $associationtable . "<br>" . $e->getMessage();
				}*/
				private function process_tweet($_data)
			{
				$servername = "locahost:3306";
				$username = "root";
				$password = "toor";
				$dbname = "soda";
				
				
				if (($_data->created_at) && ($_data->text) && ($_data->user->name) && ($_data->user->screen_name) && ($_data->id) != NULL)
				{
					try 
					{						
						$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
						// set the PDO error mode to exception
						$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$check = $conn->prepare("SELECT name FROM profile_table");
						$check->execute();
						$result = $check->fetch(PDO::FETCH_ASSOC);

						if($result["name"] != $data->user->name) 
						{
							// row not found, do stuff...
							// prepare sql and bind parameters for profile
							$stmt = $conn->prepare("INSERT INTO profile_table (name, screen_name, twitter_id) VALUES (?, ?, ?)");
							// insert a row
							$stmt->execute(array($_data->user->name, $_data->user->screen_name, $_data->id));
						
							// prepare sql and bind parameters for tweets
							$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, orig_coordinates, associated_place, favorite_Count, reply_to_id, reply_to_author, retweet_count, content) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
							$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table ORDER BY profile_key DESC LIMIT 1");
							$row_profile_ky->execute();
							// set the resulting array to associative
							//$result = $row_profile_ky->setFetchMode(PDO::FETCH_ASSOC);
								
							$stmt1->execute(array($row_profile_ky['profile_key'],$_data->created_at, $_data->coordinates, $_data->user->location, $_data->user->favourites_count, $_data->retweeted_status->in_reply_to_user_id, $_data->retweeted_status->user->name, $_data->retweet_count, $_data->text));

							// prepare sql and bind parameters for tweets
							$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
							// insert a row
							$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table ORDER BY tweets_key DESC LIMIT 1");
							$row_tweets_ky->execute();
								
							if ($_data->entities->hashtags[0]->text != NULL)
							{
								$stmt2->execute(array($row_tweets_ky['tweets_key'], $_data->entities->hashtags[0]->text, $_data->user->url));
							}
								
							if ($_data->entities->user_mentions[0]->name !=NULL)
							{
								$stmt2->execute(array($row_tweets_ky['tweets_key'], $_data->entities->user_mentions[0]->name, $_data->user->url));
							}
								
							if ($_data->user->url != NULL)
							{
									$stmt2->execute(array($row_tweets_ky['tweets_key'], $_data->user->url, $_data->user->url));
							}
								
							
							echo "New records created successfully";

						} 
						else 
						{
						// do other stuff...
							$stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, orig_coordinates, associated_place, favorite_Count, reply_to_id, reply_to_author, retweet_count, content) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
							// insert a row
							$row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE name == $_data->user->name");
							$row_profile_ky->execute();
							// set the resulting array to associative
							//$result = $row_profile_ky->setFetchMode(PDO::FETCH_ASSOC);
								
							$stmt1->execute(array($row_profile_ky['profile_key'],$_data->created_at, $_data->coordinates, $_data->user->location, $_data->user->favourites_count, $_data->retweeted_status->in_reply_to_user_id, $_data->retweeted_status->user->name, $_data->retweet_count, $_data->text));

							// prepare sql and bind parameters for tweets
							$stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
							// insert a row
							$row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table ORDER BY tweets_key DESC LIMIT 1");
							$row_tweets_ky->execute();
								
							if ($_data->entities->hashtags[0]->text != NULL)
							{
								$stmt2->execute(array($row_tweets_ky['tweets_key'], $_data->entities->hashtags[0]->text, $_data->user->url));
							}
								
							if ($_data->entities->user_mentions[0]->name !=NULL)
							{
								$stmt2->execute(array($row_tweets_ky['tweets_key'], $_data->entities->user_mentions[0]->name, $_data->user->url));
							}
								
							if ($_data->user->url != NULL)
							{
								$stmt2->execute(array($row_tweets_ky['tweets_key'], $_data->user->url, $_data->user->url));
							}
								
							
							echo "New records created successfully";
						}
					
						
						// use exec() because no results are returned
						
					}
					catch(PDOException $e)
					{
						echo "Error: " . $e->getMessage();
					}
					$conn = null;
				
					fclose($f);
					return true;
				
				}

			}
?>