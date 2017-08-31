<?php
$servername = "10.24.12.21";
$username = "soda";
$password = "MSUJHU2016";
$dbname = "soda";

// Create connection
$conn = new mysqli($servername, $username, $password,$dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
//echo "Connected successfully";

if($conn === FALSE){
	user_error("unable to connect to database.", $error_type);
	return FALSE;
}


$request = $_POST['hashtag'];
$maxDate = $_POST['maxDate'];
$minDate = $_POST['minDate'];


$hashtag = $conn->query("SELECT COUNT(*) as assoc_count, tweets_key
						 FROM association_table
						 WHERE association = '".$request."'
						 AND association_type = 'hashtag'
						 GROUP by tweets_key
						 ORDER by tweets_key
						 ");

$tweets = $conn->query("SELECT tweets_key, DATE(date_time) AS date_time
						 FROM tweets_table 
						 GROUP by tweets_key
						 ORDER by tweets_key
						 ");

if($hashtag === FALSE){
	echo "Query failed: hashtag";
	return FALSE;
}
if($tweets === FALSE){
	echo "Query failed: tweets".$tweets;
	return FALSE;
}
if($hashtag->num_rows == 0){
	return null;
}

$hashtag_arr = [];
$tweet_dates = [];

while ($tweet = mysqli_fetch_assoc($tweets)) {
	$tweet_dates[] = $tweet['date_time'];
}

while ($row = mysqli_fetch_assoc($hashtag)) {
	if($minDate < $tweet_dates[$row['tweets_key'] - 1 ] and $tweet_dates[$row['tweets_key'] - 1 ] < $maxDate){
		$hashtag_arr[] = ['datetime' => $tweet_dates[$row['tweets_key'] - 1 ], 'assoc_count' => $row['assoc_count']];
	}
}
$hashtag_arr = json_encode($hashtag_arr);
echo $hashtag_arr;
$conn->close();




?>