<?php require_once("inc/header.php");
if ((int)$_SESSION["user_id"] == 1) {
    if ($_GET["add"] == "new") {
        ?>

        <form method="post" action="topspeed?add=new">
            <div class="row justify-content-between">
                <div class="col-12 col-md-5">
                    <?php pickCar(1, $pdo); ?>
                </div>
                <div class="col-12 col-md-5">
                    <label> Top Speed:</label>
                    <input type="text" name="topspeed" class="w-100">
                </div>
            </div>
            <input type="submit" value="Submit">
            <div class="col-12">
                <?php
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $errors = array();
                    $car_id = strtok($_POST['car_id1'], '_');
                    $topspeed = $_POST['topspeed'];

                    // Check if all fields are filled in
                    if (empty($car_id) || empty($topspeed)) {
                        $errors[] = "Please fill in all fields.";
                    }

                    if (!checkCar($pdo, $car_id)) {
                        $errors[] = "Chosen car is invalid!";
                    }

                    if (!isNumericString($topspeed)) {
                        $errors[] = "Top speed can only contain numbers and a . or a ,";
                    }

                    if ($topspeed <= 0) {
                        $errors[] = "Top speed can't be 0 or lower";
                    }


                    if (empty($errors)) {
                        $topspeed = str_replace(',', '.', $topspeed);
                        $currentTopSpeed = getHighestTopSpeed($pdo);

                        // Insert topspeed into database
                        $stmt = $pdo->prepare("INSERT INTO topspeed (speed, CarID) VALUES (?, ?)");
                        $stmt->execute([$topspeed, $car_id]);

                        // Display the success message with the position in the times list
                        echo "<hr>Top speed uploaded successfully.";
                        echo "<br>Current highest top speed: <span class='stat'>" . $currentTopSpeed . "</span>";
                        echo "<br>Submitted speed: <span class='stat''>" . $topspeed . "</span>";
                        echo "<br>There are: <span class='stat'>" . getAmountOfFasterCars($pdo, $topspeed) . "</span> cars faster!<br>";
                        if ($topspeed > $currentTopSpeed) {
                            echo "You are <span class='stat'>+" . $currentTopSpeed - $topspeed . "</span> km/h faster than previous top speed!";
                        } elseif ($topspeed == $currentTopSpeed) {
                            echo "Your top speed is the same as the current top speed!";
                        } else {
                            echo "You are <span class='stat'>" . $topspeed - $currentTopSpeed . "</span> km/h slower than current top speed!";
                        }
                        echo "<hr><br><br>";
                    } else {
                        echo "<hr>";
                        foreach ($errors as $error) {
                            echo "<div class='alert alert-danger'>$error</div>";
                        }
                    }
                } ?>
                <a href="topspeed" class="mt-4 btn btn-danger">Close form</a>
            </div>
        </form>

        <?php
    }
}
?>
<a href="?add=new" class="btn btn-green">Upload new speed!</a>
<div class="row justify-content-between mt-2 border-bottom">
    <div class="col-3 col-lg-1">
        <h2>Image</h2>
    </div>
    <div class="col-8 col-lg-8">
        <h2>Car Name</h2>
    </div>
    <div class="col-lg-2 text-center border-left border-right">
        <h2>Top Speed</h2>
    </div>
</div>

<?php
$allSpeeds = $pdo->prepare("SELECT carID, MAX(speed) AS highest_speed
                                    FROM topspeed
                                    GROUP BY carID
                                    ORDER BY highest_speed DESC ; ");
$allSpeeds->execute();
$row = $allSpeeds->fetchAll();

$i = 0;
foreach ($row as $speed) :
    $i++;
    $carInfo = getCarInfo($pdo, $speed['carID']);
    ?>
    <a href='cars?car=<?= $speed['CarID']?>' class='carLink'>
        <div class="row justify-content-between trackCarList mt-2">
            <div class="col-3 col-lg-1">
                <img src="uploads/cars/<?php echo $carInfo[0]['Image']; ?>" alt="<?php echo $carInfo[0]['Name']; ?>" class="w-100">
            </div>
            <div class="col-8 col-lg-8">
                <h2 class="preventLongText"><?php echo getBrandName($pdo, $carInfo[0]['Brand']) . " - ".$carInfo[0]['Name']; ?></h2>
                <div class="">
                    <div class="col-10 col-lg-5 d-flex align-items-center">
                    </div>
                </div>
            </div>
            <div class="col-lg-2 text-center border-left border-right">
                <span class="top px-2"> <?= $speed["highest_speed"]?> km/h</span>
            </div>
        </div>
    </a>
<?php
endforeach;

require_once("inc/footer.php");

?>