<?php
/*$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully";

// Create database
$sql = "CREATE DATABASE myDB";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();*/
?>
<?php
$servername = "192.168.1.102:3306";
$username = "test_user";
$password = "test_password";
$dbname = "test_foreign_key";

try 
{
	//$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
			
	// set the PDO error mode to exception
	//$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
	// sql to create table
	//$sql= "CREATE DATABASE SOCIA_DATA";
			
	// use exec() because no results are returned
	//$conn->exec($sql);
	$si = "Mon Mar 14 21:33:47 +0000 2016";
	$formatted_date = date('Y-M-d, H:i:s', strtotime($si));
	print_r ("Time: ".$formatted_date);
}
catch(PDOException $e)
{
	echo "Connection Failed:" . "<br>" . $e->getMessage();
	
}
		
/*try 
{
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

$conn = null;
?>s