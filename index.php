<?php 
  session_start(); 
  //för chachen också
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  if (!isset($_SESSION['username'])) {
  	$_SESSION['msg'] = "You must log in first";
  	header('location: login.php');
  }
  if (isset($_GET['logout'])) {
  	session_destroy();
  	unset($_SESSION['username']);
  	header("location: login.php");
  }
  function auto_version($file='')
  {
      if(!file_exists($file))
          return $file;
   
      $mtime = filemtime($file);
      return $file.'?'.$mtime;
  }
?>
<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!-- för att slippa att den cachar under tiden man vill göra ändringar -->
	<link rel="stylesheet" type="text/css" href="style.css?<?php echo date('Y-m-d_H:i:s'); ?>">
</head>
<body>

<div class="header">
	<h2>Competitive Snake Game</h2>
</div>
<div class="content">

  <!--inloggad user information -->
  <?php  if (isset($_SESSION['username'])) : ?>
    <p>Logged in as: <strong><?php echo $_SESSION['username']; ?></strong></p>
    <p> <a href="index.php?logout='1'" style="color: red;">logout</a> </p>
	<?php endif ?>
	<!--test kommentar för github-->
  <table>
    <tr>
      <td>
        <div class= 'game'>
          <div id = 'home'>
            <canvas id='mycanvas' width='500' height='500'><!--planen-->
            </canvas>
          </div>
          <p>Press start and eat all the lemons!</p>
          <button id='btn'>START</button><!--för att kunna hänvisa till denna via javascript så att spelet börjar i samband med knapptryckningen-->
        </div>
        <script src="<?php echo auto_version('Snake.js'); ?>"></script><!--anropar javascripten, där allt händer-->
      </td>
      <td>
        <table>
          <tr>
            <td>
              <p>High Scores</p>
              <table>
                <thead>
                  <tr>
                    <th>When</th>
                    <th>Score</th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                  if(isset($_SESSION['userid'])) {
                    $userid = $_SESSION['userid'];
                    $query = "SELECT * FROM highscores WHERE userid='$userid' ORDER BY score DESC LIMIT 5";
                    $db = mysqli_connect('localhost', 'root', '', 'snake_game');
                    $results = mysqli_query($db, $query);
                    if($results) {
                      while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
                        printf("<tr><td>%s</td><td align=right>%s</td></tr>\n", date('y-m-d H:i', strtotime($r['score_time'])), $r['score']);
                      }
                    }
                  }
                ?>
                </tbody>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              fubar
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
		
</body>
</html>