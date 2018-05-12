<?php
    session_start();

    header('Content-Type: application/json');

    $aResult = array();

    if( !isset($_POST['functionname']) ) { $aResult['error'] = 'No function name!'; }

    if( !isset($_POST['arguments']) ) { $aResult['error'] = 'No function arguments!'; }

    $args = $_POST['arguments'];
    switch($_POST['functionname']) {
    case 'add_score':
        if( !(is_array($args) && count($args) == 1)) {
            $aResult['error'] = 'Error in arguments!';
            break;
        }

        $oldMax = 0;
        $newScore = $args[0];
        $userid = $_SESSION['userid'];
        $query = "SELECT MAX(score) FROM highscores WHERE userid='$userid'";
        $db = mysqli_connect('localhost', 'root', '', 'snake_game');
        $results = mysqli_query($db, $query);
        if (mysqli_num_rows($results) == 1) {
            $row = mysqli_fetch_array($results);
            $oldMax = (int)$row[0];
        }
        if($oldMax < $newScore) {
            $timestamp = date('Y-m-d G:i:s');
            $query = "INSERT INTO highscores (userid, score, score_time) VALUES('$userid', '$newScore', '$timestamp')";
            mysqli_query($db, $query);
            $aResult['result'] = true;
        } else {
            $aResult['result'] = false;
        }
        break;

    default:
        $aResult['error'] = 'Not found function '.$_POST['functionname'].'!';
        break;
    }
    echo json_encode($aResult);
?>