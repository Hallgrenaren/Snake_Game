<?php 
  session_start(); 
  //för chachen också
  header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
  header("Cache-Control: post-check=0, pre-check=0", false);
  header("Pragma: no-cache");

  //är inte username satt är man inte inloggad
  if (!isset($_SESSION['username'])) {
  	$_SESSION['msg'] = "You must log in first";
  	header('location: login.php');
  }
  //den här är satt när användaren loggar ut, ta bort användare ur session
  if (isset($_GET['logout'])) {
  	session_destroy();
    unset($_SESSION['username']);
    unset($_SESSION['userid']);
  	header("location: login.php");
  }
  //cache disable för snake.js
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
  <!--plockar in Jquery-->
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
  <table>
    <tr>
      <td>
        <div class= 'left_side,game'>
          <div id = 'home'>
            <canvas id='mycanvas' width='500' height='500'><!--planen-->
            </canvas>
          </div>
          <p>Press start and eat all the lemons!</p>
          <button id='btn'>START</button><!--för att kunna hänvisa till denna via javascript så att spelet börjar i samband med knapptryckningen-->
        </div>
        <script src="<?php echo auto_version('Snake.js'); ?>"></script><!--anropar javascripten, med spelet-->
      </td>
      <td>
        <table class="right_side">
          <tr>
            <td>
              <table class="highscores">
                <caption><b>High Scores</b></caption>
                <thead>
                  <tr>
                    <th class="hs_cell">Who</th>
                    <th class="hs_cell">When</th>
                    <th class="hs_cell">Score</th>
                  </tr>
                </thead>
                <tbody>
                <?php 
                  //Om användaren är inloggad, hämta top 5 av allas highscores.
                  if(isset($_SESSION['userid'])) {
                    $mysqli = new mysqli('localhost', 'root', '', 'snake_game');
                    if($mysqli->connect_error) {
                      exit('Error connecting to database'); //Should be a message a typical user could understand in production
                    }
                    $query = "SELECT * FROM users u, highscores h WHERE u.id = h.userid ORDER BY h.score DESC, h.score_time DESC LIMIT 5";
                    $result = $mysqli->query($query);
                    if($result) {
                      while($r = $result->fetch_assoc()) {
                        printf("<tr><td class='hs_cell'>%s</td><td class='hs_cell'>%s</td><td class='hs_cell' align=right>%s</td></tr>\n",
                          $r['username'], date('y-m-d H:i', strtotime($r['score_time'])), $r['score']);
                      }
                    }
                  }
                ?>
                </tbody>
              </table>
              <?php 
                  //individuell top highscore
                  if(isset($_SESSION['userid'])) {
                    $stmt = $mysqli->prepare("SELECT * FROM highscores WHERE userid = ? ORDER BY score DESC LIMIT 1");
                    $stmt->bind_param("i", $_SESSION['userid']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows === 1) {
                      $r = $result->fetch_assoc();
                      printf("<div>Your personal record is <b>%s</b>. Achieved %s</div>\n",
                      $r['score'], date('y-m-d H:i', strtotime($r['score_time'])));
                    }
                    $stmt->close();
                  }
                ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
		
</body>
</html>