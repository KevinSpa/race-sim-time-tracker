<?php require_once("inc/header.php");

if ((int)$_SESSION["user_id"] == 1) {
?>

<form method="post" action="">
    <div class="row justify-content-between">
        <div class="col-12 col-md-5">
            <?php pickCar(1,$pdo); ?>
        </div>
        <div class="col-12 col-md-5">
            <?php pickTrack($pdo); ?>
        </div>
    </div>

    <br>

    <label for="time">Time:</label>
    <div class="row">
        <input type="number" name="minutes" id="timeInput" placeholder="Minutes">
        <input type="number" name="seconds" id="timeInput" placeholder="Seconds">
        <input type="number" name="milliseconds" id="timeInput" placeholder="Milliseconds">
    </div>
    <br>

<input type="submit" value="Submit">
<div class="col-12">
<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = array();
    $car_id = strtok($_POST['car_id1'], '_');
    $track_id = strtok($_POST['track_id'], '_');
    $minutes = $_POST['minutes'];
    $seconds = $_POST['seconds'];
    $milliseconds = $_POST['milliseconds'];

    // Check if all fields are filled in
    if (empty($car_id) || empty($track_id) || empty($seconds) || empty($milliseconds)) {
        $errors[] = "Please fill in all fields.";
    }

    if(empty($minutes)) {
        $minutes = 0;
    }
    // Check if minute, seconds and milliseconds are round numbers
    if (!is_numeric($minutes) || !is_numeric($seconds) || !is_numeric($milliseconds)) {
        $errors[] = "Please enter a round number for seconds and milliseconds.";
    }

    if (!checkCar($pdo, $car_id)) {
        $errors[] = "Chosen car is invalid!";
    }
    
    if (empty($errors)) {
        // Convert minutes to seconds and add to seconds field
        $seconds += $minutes * 60;
        $time = $seconds + $milliseconds / 1000;
        $time = floatval($time);

        // Insert time into database
        $stmt = $pdo->prepare("INSERT INTO times (CarID, TrackID, LapTime) VALUES (?, ?, ?)");
        $stmt->execute([$car_id, $track_id, $time]);

        //Update the times submitted in tracks
        $update_track = $pdo->prepare("UPDATE tracks SET times_submitted = times_submitted + 1 WHERE ID = :targetID");
        $update_track->bindParam(':targetID', $track_id, PDO::PARAM_INT);
        $update_track->execute();

        //Update the times submitted in cars
        $update_car = $pdo->prepare("UPDATE cars SET times_submitted = times_submitted + 1 WHERE ID = :targetID");
        $update_car->bindParam(':targetID', $car_id, PDO::PARAM_INT);
        $update_car->execute();
        
        // Get the position in the times list
        $pos = $pdo->prepare("SELECT `LapTime` FROM `times` WHERE `TrackID` = :trackid AND `LapTime` < :newtime");
        $pos->bindParam(":trackid", $track_id);
        $pos->bindParam(":newtime", $time);
        $pos->execute();
        $position = $pos->rowCount(); 
        if($position == 0) {
            $position = 1;
        } 

        // Display the success message with the position in the times list
        echo "<hr>Time uploaded successfully.";
        echo "<br>Current fastest: <span class='stat'>". getFastestTime($pdo, $track_id)."</span>";
        echo "<br>Your time: <span class='stat''>".formatTime($time)."</span>";
        echo "<br>Position in the times list: <span class='stat'>#".$position."</span>";

    } else {
        echo "<hr>";
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
    }
}?>
</div>
</form>

<?php
    require_once("inc/footer.php");
} else {
    header('Location: ./');
}



?>