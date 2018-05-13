<?php
    // Kod som hanterar insert av ny highscore
    session_start();

    header('Content-Type: application/json');

    $aResult = array();
    //kontrollera parametrar från ajax anropet
    if( !isset($_POST['functionname']) ) { $aResult['error'] = 'No function name!'; }

    if( !isset($_POST['arguments']) ) { $aResult['error'] = 'No function arguments!'; }
    //Hämta argumentarrayen och kolla vilken funktion som anropas
    $args = $_POST['arguments'];
    switch($_POST['functionname']) {
    case 'add_score':
        //Kolla att args är en array och att den innehåller ett element
        if( !(is_array($args) && count($args) == 1)) {
            $aResult['error'] = 'Error in arguments!';
            break;
        }

        $oldMax = 0;
        $aResult['result'] = false;
        //Plocka ut argumentet som är score
        $newScore = $args[0];
        if($newScore > 0) {
            //Kolla användarens maxscore just nu
            $userid = $_SESSION['userid'];
            $mysqli = new mysqli('localhost', 'root', '', 'snake_game');
            $stmt = $mysqli->prepare("SELECT MAX(score) FROM highscores WHERE userid = ?");
            $stmt->bind_param("i", $userid);
            $stmt->execute();

            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $row = $result->fetch_row();
                $oldMax = (int)$row[0];
            }
            // Lagra nya scoren i databasen, med den tid det skedde.
            //
            // Viktigt att använda prepared statement med parametrar här för att skydda mot SQL-injection attack
            // eftersom score kommer från en POST
            $stmt = $mysqli->prepare("INSERT INTO highscores (userid, score, score_time) VALUES(?, ?, ?)");
            $timestamp = date('Y-m-d G:i:s');
            $stmt->bind_param("iis", $userid, $newScore, $timestamp);
            $stmt->execute();
            $stmt->close();
            //Returnera sant eller falsk beroende på ifall det var nytt rekord
            if($oldMax < $newScore) {
                $aResult['result'] = true;
            }
        }
    break;

    default:
        $aResult['error'] = 'Not found function '.$_POST['functionname'].'!';
        break;
    }
    echo json_encode($aResult);
?>