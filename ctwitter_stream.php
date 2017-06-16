<?

//
// A simple class to access the Twitter streaming API, with OAuth authentication
//
//	Mike (mike@mikepultz.com)
//
// Simple Example:
//
//	require 'ctwitter_stream.php';
//
//	$t = new ctwitter_stream();
//
//	$t->login('consumer_key', 'consumer secret', 'access token', 'access secret');
//
//	$t->start(array('facebook', 'fbook', 'fb'))
//


class ctwitter_stream
{
    private $m_oauth_consumer_key;
    private $m_oauth_consumer_secret;
    private $m_oauth_token;
    private $m_oauth_token_secret;

    private $m_oauth_nonce;
    private $m_oauth_signature;
    private $m_oauth_signature_method = 'HMAC-SHA1';
    private $m_oauth_timestamp;
    private $m_oauth_version = '1.0';

    public function __construct($_consumer_key, $_consumer_secret, $_token, $_token_secret)
    {
        // set the login details
        $this->m_oauth_consumer_key     = $_consumer_key;
        $this->m_oauth_consumer_secret  = $_consumer_secret;
        $this->m_oauth_token            = $_token;
        $this->m_oauth_token_secret     = $_token_secret;

        // generate a nonce; we're just using a random md5() hash here.
        $this->m_oauth_nonce = md5(mt_rand());
        // set a time limit to unlimited
        set_time_limit(0);
    }

    // the main stream manager
    
    public function start(array $_keywords)
    {
        while(1)
        {
            $hostname = "ssl://stream.twitter.com";
            $port = 443;
            $time_out =  30;

            $socket = fsockopen($hostname, $port, $errno, $errstr, $time_out );
            if (!$socket)
            {
                echo "ERROR: Twitter Stream Error: failed to open socket";
            } else
            {
                // build the data and store it so we can get a length

                $data = 'track='.rawurlencode(implode($_keywords, ','));

                // store the current timestamp
                
                $this->m_oauth_timestamp = time();

                
                // generate the base string based on all the data
                
                $base_string =  'POST&'.
                                    rawurlencode('https://stream.twitter.com/1.1/statuses/filter.json').
                                    '&'.
                                    rawurlencode(   'oauth_consumer_key=' . $this->m_oauth_consumer_key.'&'.
                                                    'oauth_nonce=' . $this->m_oauth_nonce.'&'.
                                                    'oauth_signature_method=' . $this->m_oauth_signature_method.'&'.
                                                    'oauth_timestamp=' . $this->m_oauth_timestamp.'&'.
                                                    'oauth_token=' . $this->m_oauth_token.'&'.
                                                    'oauth_version=' . $this->m_oauth_version.'&'.
                                                    $data);

                //
                // generate the secret key to use to hash
                //
                $secret =   rawurlencode($this->m_oauth_consumer_secret).'&'.
                            rawurlencode($this->m_oauth_token_secret);

                //
                // generate the signature using HMAC-SHA1
                //
                // hash_hmac() requires PHP >= 5.1.2 or PECL hash >= 1.1
                //
                $raw_hash = hash_hmac('sha1', $base_string, $secret, true);

                //
                // base64 then urlencode the raw hash
                //
                $this->m_oauth_signature = rawurlencode(base64_encode($raw_hash));

                //
                // build the OAuth Authorization header
                

                $oauth ='OAuth oauth_consumer_key="'. $this->m_oauth_consumer_key.'", '.
                        'oauth_nonce="'. $this->m_oauth_nonce.'", '.
                        'oauth_signature="'. $this->m_oauth_signature.'", '.
                        'oauth_signature_method="'. $this->m_oauth_signature_method.'", '.
                        'oauth_timestamp="' . $this->m_oauth_timestamp . '", ' .
                        'oauth_token="'. $this->m_oauth_token . '", ' .
                        'oauth_version="'. $this->m_oauth_version.'"';

                //
                // build the request
                //
                $request  = "POST /1.1/statuses/filter.json HTTP/1.1\r\n".
                            "Host: stream.twitter.com\r\n".
                            "Authorization: " . $oauth . "\r\n".
                            "Content-Length: " . strlen($data) . "\r\n".
                            "Content-Type: application/x-www-form-urlencoded\r\n\r\n".
                            $data;

                //
                // write the request
                //
                fwrite($socket, $request);

                //
                // set it to non-blocking
                //
                stream_set_blocking($socket, 0);

                while(!feof($socket))
                {
                    $read   = array($socket);
                    $write  = null;
                    $except = null;

                    //
                    // select, waiting up to 10 minutes for a tweet; if we don't get one, then
                    // then reconnect, because it's possible something went wrong.
                    //
                    $res = stream_select($read, $write, $except, 600, 0);
                    if ( ($res == false) || ($res == 0) )
                    {
                        break;
                    }

                    //
                    // read the JSON object from the socket
                    //
                    $json = fgets($socket);

                    //
                    // look for a HTTP response code
                    //
                    if (strncmp($json, 'HTTP/1.1', 8) == 0)
                    {
                        $json = trim($json);
                        if ($json != 'HTTP/1.1 200 OK')
                        {
                            echo 'ERROR: ' . $json . "\n";
                            return false;
                        }
                    }

                    //
                    // if there is some data, then process it
                    //
                    if ( ($json !== false) && (strlen($json) > 0) )
                    {
                        //
                        // decode the socket to a PHP array
                        //
                        $data = json_decode($json, true);
                        if ($data)
                        {
                            //
                            // process it
                            //
                            $this->process_tweet($data);
                        }
                    }
                }
            }

            fclose($socket);
            sleep(10);
        }

        return;
    }

    private function GetCenterFromDegrees($data)
    {
                if (!is_array($data)) return FALSE;

                $num_coords = count($data);

                $X = 0.0;
                $Y = 0.0;
                $Z = 0.0;

                foreach ($data as $coord)
                {
                    $lat = $coord[0] * pi() / 180;
                    $lon = $coord[1] * pi() / 180;

                    $a = cos($lat) * cos($lon);
                    $b = cos($lat) * sin($lon);
                    $c = sin($lat);

                    $X += $a;
                    $Y += $b;
                    $Z += $c;
                }

                $X /= $num_coords;
                $Y /= $num_coords;
                $Z /= $num_coords;

                $lon = atan2($Y, $X);
                $hyp = sqrt($X * $X + $Y * $Y);
                $lat = atan2($Z, $hyp);

                return array($lat * 180 / pi(), $lon * 180 / pi());
    }
            
    private function process_tweet($_data)
    {
                $servername = "10.24.12.21:3306";
                $username = "soda";
                $password = "MSUJHU2016";
                $dbname = "soda";
                static $cill = 0;
                static $entries = 0;
                
                //    profile keys in SoDa Database || If changes are made in the database to alter these account, it must reflect here           
                $TJSmithMedia = 3317;
                $MayorSRB = 12156;
                $BaltimorePolice = 12731;
                $MarilyMosbyEsq = 13132;
                $FOP3 = 13148;
                $BaltimoreSAO = 13182;
                $MDSP = 13541;
                $NicoleSMonroe = 13684;
                
                
                if (($_data->created_at)!= NULL && ($_data->text)!= NULL && ($_data->user->name)!= NULL && ($_data->user->screen_name)!= NULL && ($_data->user->id) != NULL)
                {
                    if (($_data->retweeted_status->retweet_count)!= NULL )
                    {
                        $retweet_count = (int) $_data->retweeted_status->retweet_count;
                    }
                    else
                        $retweet_count = 0;
                    
                    try 
                    {                               
                        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                        // set the PDO error mode to exception
                        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $check = $conn->prepare("SELECT * FROM profile_table WHERE twitter_id = ?");
                        $check->bindValue(1, $_data->user->id, PDO::PARAM_STR);
                        $check->execute();
                        $result = $check->fetch(PDO::FETCH_ASSOC);
                        
                        date_default_timezone_set('America/New_York');
                        $s = $_data->created_at;
                        $formatted_date = date('Y-m-d H:i:s', strtotime($s));
                        $curr_date = date("Y-m-d H:i:s");


                        // Send

                        //$result = $check->fetchAll();

                        if($result == False) 
                        {
                            // row not found, do stuff...
                            // prepare sql and bind parameters for profile
                            echo "false";
                            if($_data->place->bounding_box->coordinates != NULL)
                            {
                                $coordinates[0][0] = $_data->place->bounding_box->coordinates[0][0][0];
                                $coordinates[0][1] = $_data->place->bounding_box->coordinates[0][0][1];
                                $coordinates[1][0] = $_data->place->bounding_box->coordinates[0][1][0];
                                $coordinates[1][1] = $_data->place->bounding_box->coordinates[0][1][1];
                                $coordinates[2][0] = $_data->place->bounding_box->coordinates[0][2][0];
                                $coordinates[2][1] = $_data->place->bounding_box->coordinates[0][2][1];
                                $coordinates[3][0] = $_data->place->bounding_box->coordinates[0][3][0];
                                $coordinates[3][1] = $_data->place->bounding_box->coordinates[0][3][1];             
                                $lat_long = $this->GetCenterFromDegrees($coordinates);
                            }
                            else
                            {
                                $lat_long = [0,0];
                            }
                            $stmt = $conn->prepare("INSERT INTO profile_table (name, screen_name, twitter_id) VALUES (?, ?, ?)");
                            // insert a row
                            $stmt->execute(array($_data->user->name, $_data->user->screen_name, $_data->user->id_str));
                        
                            // prepare sql and bind parameters for tweets
                            $stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, lat, lon, associated_place, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
                            $row_profile_ky->bindValue(1, $_data->user->id, PDO::PARAM_STR);
                            $row_profile_ky->execute();
                            // set the resulting array to associative
                            $resultp = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
                            //print_r("Resultp: ".$resultp['profile_key']);
                                
                            $stmt1->execute(array($resultp['profile_key'],$formatted_date, $lat_long[0], $lat_long[1], $_data->user->location, $_data->favorite_count, $_data->retweeted_status->user->id_str, $_data->retweeted_status->user->name, $_data->retweeted_status->id_str, $retweet_count , $_data->retweet_count, $_data->text, $_data->id_str));

                            // prepare sql and bind parameters for tweets
                            $stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
                            // pull tweets key form tweets table using profile key as a query means
                            $row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
                            $row_tweets_ky->bindValue(1, $resultp['profile_key'], PDO::PARAM_STR);
                            $row_tweets_ky->execute();
                            $resultt = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
                            
                            
                            // This update the retweet count of the seed (person retweeted) 
                            
                            if($_data->retweeted_status->id_str)
                            {
                                $row_tweets_ky = $conn->prepare("SELECT * FROM `tweets_table` WHERE `tweets_table`.`tweet_id` = ? ORDER BY tweets_key DESC LIMIT 1");
                                $row_tweets_ky->bindValue(1, $_data->retweeted_status->id_str, PDO::PARAM_STR);
                                $row_tweets_ky->execute();
                                $retweet_seed_id = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
                                
                                if($retweet_seed_id)
                                {
                                    $row_tweets_ky = $conn->prepare("UPDATE `tweets_table` SET `retweet_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
                                    $row_tweets_ky->bindValue(1, $retweet_count, PDO::PARAM_STR);
                                    $row_tweets_ky->bindValue(2, $_data->retweeted_status->id_str, PDO::PARAM_STR);
                                    $row_tweets_ky->execute();
                                    
                                    
                                    if(($retweet_seed_id['profile_key']) == $TJSmithMedia_PK || ($retweet_seed_id['profile_key']) == $MayorSRB_PK ||($retweet_seed_id['profile_key']) == $BaltimorePolice_PK || ($retweet_seed_id['profile_key']) == $MarilyMosbyEsq_PK || ($retweet_seed_id['profile_key']) == $FOP3_PK ||($retweet_seed_id['profile_key']) == $BaltimoreSAO_PK ||($retweet_seed_id['profile_key']) == $MDSP_PK || ($retweet_seed_id['profile_key']) == $NicoleSMonroe_PK)
                                    {
                                        $stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
                                    // inserts a row in historical_count table
                                        $stmth->execute(array($curr_date, $retweet_seed_id['tweets_key'], $_data->retweeted_status->favorite_count, $_data->retweeted_status->retweet_count));
                                    }
                                    
                                }
                            }
                            
                            
                            
                            //historical_count table fill
                            if(($_data->user->screen_name) == "BaltimorePolice" || ($_data->user->screen_name) == "BaltimoreSAO" ||($_data->user->screen_name) == "MarilynMosbyEsq" || ($_data->user->screen_name) == "CommishDavis" || ($_data->user->screen_name) == "NicoleSMonroe" ||($_data->user->screen_name) == "TJSmithMedia" ||($_data->user->screen_name) == "FOP3" || ($_data->user->screen_name) == "MDSP" || ($_data->user->screen_name) == "MayorSRB")
                            {
                                $stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
                                // insert a row
                                $stmth->execute(array($curr_date, $resultt['tweets_key'], $_data->favorite_count, $_data->retweet_count));
                            }
                            
                            $hashtag = "hashtag";
                            $url = "url";
                            $user_mentions = "user_mention";
                            $no_associations = "no_association";
                                
                            if ($_data->entities->hashtags[0]->text != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $hashtag, $_data->entities->hashtags[0]->text));
                            }
                            if ($_data->entities->hashtags[1]->text != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $hashtag, $_data->entities->hashtags[1]->text));
                            }
                            if ($_data->entities->hashtags[2]->text != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $hashtag, $_data->entities->hashtags[2]->text));
                            }
                            if ($_data->entities->hashtags[3]->text != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $hashtag, $_data->entities->hashtags[3]->text));
                            }
                            if ($_data->entities->hashtags[4]->text != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $hashtag, $_data->entities->hashtags[4]->text));
                            }
                            if ($_data->entities->hashtags[5]->text != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $hashtag, $_data->entities->hashtags[5]->text));
                            }
                                
                            if ($_data->entities->urls[0]->url != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $url, $_data->entities->urls[0]->url));
                            }
                                
                            if ($_data->entities->user_mentions[0]->name != NULL)
                            {
                                $stmt2->execute(array($resultt['tweets_key'], $user_mentions, $_data->entities->user_mentions[0]->name));
                            }
                            else if ($_data->entities->urls[0]->url == NULL && $_data->entities->user_mentions[0]->name == NULL && $_data->entities->hashtags[0]->text == NULL)
                            {                               
                                $stmt2->execute(array($resultt['tweets_key'], $no_associations, $no_associations));
                            }
                                
                            
                            echo "New records created successfully";
                            

                        } 
                        else 
                        {
                        // do other stuff...
                        echo "true";
                            if($_data->place->bounding_box->coordinates != NULL)
                            {
                                $coordinates[0][0] = $_data->place->bounding_box->coordinates[0][0][0];
                                $coordinates[0][1] = $_data->place->bounding_box->coordinates[0][0][1];
                                $coordinates[1][0] = $_data->place->bounding_box->coordinates[0][1][0];
                                $coordinates[1][1] = $_data->place->bounding_box->coordinates[0][1][1];
                                $coordinates[2][0] = $_data->place->bounding_box->coordinates[0][2][0];
                                $coordinates[2][1] = $_data->place->bounding_box->coordinates[0][2][1];
                                $coordinates[3][0] = $_data->place->bounding_box->coordinates[0][3][0];
                                $coordinates[3][1] = $_data->place->bounding_box->coordinates[0][3][1];             
                                $lat_long = $this->GetCenterFromDegrees($coordinates);
                            }
                            else
                            {
                                $lat_long = [0,0];
                            }
                            $stmt1 = $conn->prepare("INSERT INTO tweets_table (profile_key, date_time, lat, lon, associated_place, favourite_count, reply_to_id, reply_to_author, quoted_id, quotweet_count, retweet_count, content, tweet_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            // insert a row
                            $row_profile_ky = $conn->prepare("SELECT profile_key FROM profile_table WHERE twitter_id = ? ORDER BY profile_key DESC LIMIT 1");
                            $row_profile_ky->bindValue(1, $_data->user->id, PDO::PARAM_STR);
                            $row_profile_ky->execute();
                            $resultc = $row_profile_ky->fetch(PDO::FETCH_ASSOC);
                            // set the resulting array to associative
                            //$result = $row_profile_ky->setFetchMode(PDO::FETCH_ASSOC);
                                
                            $stmt1->execute(array($resultc['profile_key'],$formatted_date, $lat_long[0], $lat_long[1], $_data->user->location, $_data->favorite_count, $_data->retweeted_status->user->id_str, $_data->retweeted_status->user->name, $_data->retweeted_status->id_str, $retweet_count , $_data->retweet_count, $_data->text, $_data->id_str));

                            // prepare sql and bind parameters for tweets
                            $stmt2 = $conn->prepare("INSERT INTO association_table (tweets_key, association_type, association) VALUES (?, ?, ?)");
                            // insert a row
                            $row_tweets_ky = $conn->prepare("SELECT tweets_key FROM tweets_table WHERE profile_key = ? ORDER BY tweets_key DESC LIMIT 1");
                            $row_tweets_ky->bindValue(1, $resultc['profile_key'], PDO::PARAM_STR);
                            $row_tweets_ky->execute();
                            $resultp = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
                            
                            if($_data->retweeted_status->id_str)
                            {
                                $row_tweets_ky = $conn->prepare("SELECT * FROM `tweets_table` WHERE `tweets_table`.`tweet_id` = ? ORDER BY tweets_key DESC LIMIT 1");
                                $row_tweets_ky->bindValue(1, $_data->retweeted_status->id_str, PDO::PARAM_STR);
                                $row_tweets_ky->execute();
                                $retweet_seed_id = $row_tweets_ky->fetch(PDO::FETCH_ASSOC);
                                
                                if($retweet_seed_id)
                                {
                                    $row_tweets_ky = $conn->prepare("UPDATE `tweets_table` SET `retweet_count` = ? WHERE `tweets_table`.`tweet_id` = ?");
                                    $row_tweets_ky->bindValue(1, $retweet_count, PDO::PARAM_STR);
                                    $row_tweets_ky->bindValue(2, $_data->retweeted_status->id_str, PDO::PARAM_STR);
                                    $row_tweets_ky->execute();
                                    
                                    
                                    if(($retweet_seed_id['profile_key']) == $TJSmithMedia_PK || ($retweet_seed_id['profile_key']) == $MayorSRB_PK ||($retweet_seed_id['profile_key']) == $BaltimorePolice_PK || ($retweet_seed_id['profile_key']) == $MarilyMosbyEsq_PK || ($retweet_seed_id['profile_key']) == $FOP3_PK ||($retweet_seed_id['profile_key']) == $BaltimoreSAO_PK ||($retweet_seed_id['profile_key']) == $MDSP_PK || ($retweet_seed_id['profile_key']) == $NicoleSMonroe_PK)
                                    {
                                        $stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
                                    // inserts a row in historical_count table
                                        $stmth->execute(array($curr_date, $retweet_seed_id['tweets_key'], $_data->retweeted_status->favorite_count, $_data->retweeted_status->retweet_count));
                                    }
                                    
                                }
                            }
                            
                            //historical_count table fill
                            if(($_data->user->screen_name) == "BaltimorePolice" || ($_data->user->screen_name) == "BaltimoreSAO" ||($_data->user->screen_name) == "MarilynMosbyEsq" || ($_data->user->screen_name) == "CommishDavis" || ($_data->user->screen_name) == "NicoleSMonroe" ||($_data->user->screen_name) == "TJSmithMedia" ||($_data->user->screen_name) == "FOP3" || ($_data->user->screen_name) == "MDSP" || ($_data->user->screen_name) == "MayorSRB")
                            {                           
                                $stmth = $conn->prepare("INSERT INTO historical_count (date_time, tweet_key, favourite_count, retweet_count) VALUES (?, ?, ?, ?)");
                                // insert a row
                                $stmth->execute(array($formatted_date, $resultt['tweets_key'], $_data->favorite_count, $_data->retweet_count));
                            }
                            
                            $hashtag = "hashtag";
                            $url = "url";
                            $user_mentions = "user_mention";
                            $no_associations = "no_association";
                                
                            if ($_data->entities->hashtags[0]->text != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $hashtag, $_data->entities->hashtags[0]->text));
                            }
                            if ($_data->entities->hashtags[1]->text != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $hashtag, $_data->entities->hashtags[1]->text));
                            }
                            if ($_data->entities->hashtags[2]->text != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $hashtag, $_data->entities->hashtags[2]->text));
                            }
                            if ($_data->entities->hashtags[3]->text != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $hashtag, $_data->entities->hashtags[3]->text));
                            }
                            if ($_data->entities->hashtags[4]->text != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $hashtag, $_data->entities->hashtags[4]->text));
                            }
                            if ($_data->entities->hashtags[5]->text != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $hashtag, $_data->entities->hashtags[5]->text));
                            }
                                
                            if ($_data->entities->urls[0]->url != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $url, $_data->entities->urls[0]->url));
                            }
                                
                            if ($_data->entities->user_mentions[0]->name != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $user_mentions, $_data->entities->user_mentions[0]->name));
                            }
                            if ($_data->entities->user_mentions[1]->name != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $user_mentions, $_data->entities->user_mentions[1]->name));
                            }
                            if ($_data->entities->user_mentions[2]->name != NULL)
                            {
                                $stmt2->execute(array($resultp['tweets_key'], $user_mentions, $_data->entities->user_mentions[2]->name));
                            }
                            else if ($_data->entities->urls[0]->url == NULL && $_data->entities->user_mentions[0]->name == NULL && $_data->entities->hashtags[0]->text == NULL)
                            {                               
                                $stmt2->execute(array($resultp['tweets_key'], $no_associations, $no_associations));
                            }
                                
                            
                            echo "New records created successfully";
                            print_r(" and found user");
                        }
                    
                        
                    }
                    catch(PDOException $e)
                    {
                        echo "Error: " . $e->getMessage();
                    }
                    $conn = null;
                    
                    /*if($entries == 50)
                    {
                        $message = $entries. " additional number of entries has been recorded\r\n";

                        // In case any of our lines are larger than 70 characters, we should use wordwrap()
                        $message = wordwrap($message, 70, "\r\n");
                        mail('Kofi.Nyarko@morgan.edu', 'SoDA Database Update', $message);
                        mail('emshe1@morgan.edu', 'SoDA Database Update', $message);
                        $entries = 0;
                    }
                    $entries = $entries + 1;
                    print_r("entries: ".$entries);*/
                
                    fclose($f);
                    return true;
                
                }
    }

    //
    // process a tweet object from the stream
    //
    private function process_tweet(array $_data)
    {
        print_r($_data);

        return true;
    }
    
};