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

$tweet_query = $conn->query("SELECT * FROM tweets_table");
$hashtag = $conn->query("SELECT association, COUNT(*) as assoc_count
						 FROM association_table
						 WHERE association_type = 'hashtag'
						 GROUP BY association
						 ORDER BY assoc_count DESC
						 LIMIT 40");
$profile_query = $conn->query("SELECT * FROM profile_table");
$association_query = $conn->query("SELECT * FROM association_table");
$hashtag_arr = array();

while ($row = mysqli_fetch_assoc($hashtag)) {
	$hashtag_arr[] = array('association' => $row['association'], 'assoc_count' => $row['assoc_count']);
}
$hashtag_arr = json_encode($hashtag_arr);
echo $hashtag_arr;
$conn->close();
?>