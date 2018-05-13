<?php
session_start();

// ge variablerna deras initiala värde
$username = "";
$email    = "";
$errors = array(); 

//Koppla till datorbas
$mysqli = new mysqli('localhost', 'root', '', 'snake_game');

// REGISTRERA USER
if (isset($_POST['reg_user'])) {
  // Få alla inskrivna värden från formuläret
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password_1 = $_POST['password_1'];
  $password_2 = $_POST['password_2'];

  // validera värden: se till att allt är correct
  // Om fel upptäcks, pusha till $errors array
  if (empty($username)) { array_push($errors, "Username is required"); }
  if (empty($email)) { array_push($errors, "Email is required"); }
  if (empty($password_1)) { array_push($errors, "Password is required"); }
  if ($password_1 != $password_2) {
	  array_push($errors, "The two passwords do not match");
  }

  // Kolla först databas för att vara säker 
  // på att ingen användare redan finns med samma username och/eller email
  $stmt = $mysqli->prepare("SELECT username, email FROM users WHERE username=? OR email=? LIMIT 1");
  $stmt->bind_param("ss", $username, $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 1) {
    $row = $result->fetch_assoc(); // Om användare finns
    if ($row['id'] == $username) {
      array_push($errors, "Username already exists");
    }

    if ($row['email'] == $email) {
      array_push($errors, "email already exists");
    }
  }
  $stmt->close();

  // Registrera ifall allt är korrekt
  if (count($errors) == 0) {
  	$password = md5($password_1);//kryptera lösenord innan det sparas i databas

    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password) VALUES(?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
  	$_SESSION['username'] = $username;
  	$_SESSION['userid'] = $mysqli->insert_id;
    $_SESSION['success'] = "You are now logged in";
    $stmt->close();
  	header('location: index.php');
  }
}

// LOGIN USER
if (isset($_POST['login_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }
  
    if (count($errors) == 0) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=? AND password=?");
        $password = md5($password);//kryptera lösenord innan det sparas i databas
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
          $row = $result->fetch_row();
          $_SESSION['userid'] = $row[0];
          $_SESSION['username'] = $username;
          $_SESSION['success'] = "You are now logged in";
          header('location: index.php');
        } else {
            array_push($errors, "Wrong username/password combination");
        }
        $stmt->close();
    }
  }
  
  ?>