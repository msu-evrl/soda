<html>
<head>
    <meta charset="utf-8">

    <!-- Always force latest IE rendering engine or request Chrome Frame -->
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Use title if it's in the page YAML frontmatter -->
    <title>Welcome to XAMPP</title>

    <meta name="description" content="XAMPP is an easy to install Apache distribution containing MariaDB, PHP and Perl." />
    <meta name="keywords" content="xampp, apache, php, perl, mariadb, open source distribution" />

    <link href="/dashboard/stylesheets/normalize.css" rel="stylesheet" type="text/css" /><link href="/dashboard/stylesheets/all.css" rel="stylesheet" type="text/css" />
    <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/3.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />

    <script src="/dashboard/javascripts/modernizr.js" type="text/javascript"></script>


    <link href="/dashboard/images/favicon.png" rel="icon" type="image/png" />

<style>
table
{
border-style:solid;
border-width:2px;
border-color:pink;
}
</style>
  </head>
<body class="index">
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=277385395761685";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <div class="contain-to-grid">
      <nav class="top-bar" data-topbar>
        <ul class="title-area">
          <li class="name">
            <h1><a href="/dashboard/index.html">Home</a></h1>
          </li>
          <li class="toggle-topbar menu-icon">
            <a href="#">
              <span>Menu</span>
            </a>
          </li>
        </ul>

        <section class="top-bar-section">
          <!-- Right Nav Section -->
          <ul class="right">
              <li class=""><a href="/applications.html">Applications</a></li>
              <li class=""><a href="/dashboard/faq.html">FAQs</a></li>
              <li class=""><a href="/dashboard/howto.html">HOW-TO Guides</a></li>
              <li class=""><a target="_blank" href="/dashboard/phpinfo.php">PHPInfo</a></li>
              <li class=""><a href="/phpmyadmin/">phpMyAdmin</a></li>
              <li class=""><a href="/dashboard/phpscripts/Categorization.php">Manual Categorization</a></li>
          </ul>
        </section>
      </nav>
    </div>
    <div id="wrapper">
      <div class="hero">
  <div class="row">
    <div class="large-12 columns">
      <h1><img src="/dashboard/images/evrl_logo.jpg" /> MSU EVRL + John Hopkins APL <span> Data Mining</span></h1>
    </div>
  </div>
</div>
<div class="row">
  <div class="large-12 columns">
    <h3 align="center">                  Uncatergorized Tweets    </h3>
  </div>
</div>
<?php
$con = mysqli_connect("127.0.0.1:3306", "root", "toor", "soda");
if (!$con)
  {
  die('Could not connect: ' . mysql_error());
  }
 
$result = mysqli_query($con, "SELECT tweets_table.tweets_key,tweets_table.content, category_table.category_1 FROM tweets_table,category_table WHERE tweets_table.tweets_key=category_table.tweets_key LIMIT 100");
 
echo "<table border='1'>
<tr>
<th>ID</th>
<th>Content</th>
<th>Machine Category(Jk)</th>
<th>Category</th>
<td><input type='submit' value='Submit All' action=''></td>
</tr>
";
 $i=0;
 while ($i<100){
  while($row = mysqli_fetch_array($result))
    {
    echo "<tr>";
    echo "<td>" . $row['tweets_key']. "</td>";
    echo "<td>" . $row['content'] . "</td>";
    echo "<td>" . $row['category_1'] . "</td>";
    echo "<td><form method='POST' type='text' id='".$i."'action=''> 
    <select name='category' >
    <option disabled selected>Select One...</option>
    <option value=1>Violent Crimes</option>
    <option value=2>Arrests Made</option>
    <option value=3>Community Activities</option>
    <option value=4>Missing Persons</option>
    <option value=5>Victim of Crimes</option>
    <option value=6>Humanizing</option>
    <option value=7>DBP Campaigns
    <option value=8>Other</option>
    </select> </td>
    <td> <input type='submit'></form> </td>";
    echo "</tr>";
    $i++;
    }}
  echo "</table>";
  echo "<div>". mysqli_fetch_array($result)."</div>";
   
  mysqli_close($con);
  ?>
  <div class="row">
    <div class="large-12 columns"></div>
  </div>

      </div>

      <footer>
        <div class="row">
          <div class="large-12 columns">
            <div class="row">
              <div class="large-8 columns">
                

                <ul class="inline-list">
                 
                </ul>
              </div>
              <div class="large-4 columns">
                <p class="text-right">Copyright (c) 2015, Apache Friends</p>
              </div>
            </div>
          </div>
        </div>
      </footer>

      <!-- JS Libraries -->
      <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
      <script src="/dashboard/javascripts/all.js" type="text/javascript"></script>
  </body>
  </html>
  


 
