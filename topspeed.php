<?php require_once("inc/header.php");
if ((int)$_SESSION["user_id"] == 1) {
    if (isset($_GET["add"]) && $_GET["add"] == "new") {
        ?>

        <div class="card text-light mb-4">
            <div class="card-body">
                <h3 class="mb-3 fw-bold">Upload New Top Speed</h3>
                <form method="post" action="topspeed?add=new">
                    <div class="row justify-content-between">
                        <div class="col-12 col-md-5">
                            <?php pickCar(1, $pdo); ?>
                        </div>
                        <div class="col-12 col-md-5">
                            <label>Top Speed (km/h):</label>
                            <input type="text" name="topspeed" class="form-control">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <input type="submit" value="Submit Top Speed" class="btn btn-red">
                            <a href="topspeed" class="btn btn-outline-light ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
                
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

                        echo "<div class='alert alert-success mt-3'>
                                <h5>Top speed uploaded successfully!</h5>
                                <p class='mb-1'><strong>Current highest top speed:</strong> <span class='stat'>" . $currentTopSpeed . " km/h</span></p>
                                <p class='mb-1'><strong>Your submitted speed:</strong> <span class='stat'>" . $topspeed . " km/h</span></p>
                                <p class='mb-1'><strong>Cars faster than yours:</strong> <span class='stat'>" . getAmountOfFasterCars($pdo, $topspeed) . "</span></p>";
                        
                        if ($topspeed > $currentTopSpeed) {
                            echo "<p class='mb-0'>🏆 You are <span class='stat'>+" . ($topspeed - $currentTopSpeed) . " km/h</span> faster than the previous top speed!</p>";
                        } elseif ($topspeed == $currentTopSpeed) {
                            echo "<p class='mb-0'>🤝 Your top speed matches the current record!</p>";
                        } else {
                            echo "<p class='mb-0'>You are <span class='stat'>" . ($currentTopSpeed - $topspeed) . " km/h</span> slower than the current top speed.</p>";
                        }
                        echo "</div>";
                    } else {
                        echo "<div class='mt-3'>";
                        foreach ($errors as $error) {
                            echo "<div class='alert alert-danger'>$error</div>";
                        }
                        echo "</div>";
                    }
                } ?>
            </div>
        </div>

        <?php
    }
}
?>

<div class="row justify-content-between align-items-center mb-4">
    <div class="col-md-6">
        <h2>Top Speed Rankings</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="?add=new" class="btn btn-red">🔝 Upload New Speed</a>
    </div>
</div>

<?php
$allSpeeds = $pdo->prepare("SELECT ts.carID, MAX(ts.speed) AS highest_speed
                                    FROM topspeed ts
                                    JOIN cars c ON ts.carID = c.ID
                                    WHERE c.DeletedDate IS NULL
                                    GROUP BY ts.carID
                                    ORDER BY highest_speed DESC");
$allSpeeds->execute();
$row = $allSpeeds->fetchAll();

if ($row): ?>
    <div class="card text-light">
        <div class="card-body">
            <h4 class="card-title mb-4">Fastest cars by top speed</h4>
            <?php 
            $i = 0;
            foreach ($row as $speed):
                $i++;
                $carInfo = getCarInfo($pdo, $speed['carID']);
                
                // Skip if car info is empty (car was deleted)
                if (empty($carInfo)) continue;
                
                $placeClass = '';
                if ($i == 1) $placeClass = 'first-place';
                elseif ($i == 2) $placeClass = 'second-place';
                elseif ($i == 3) $placeClass = 'third-place';
            ?>
                <a href='cars?car=<?= $speed['carID']?>' class='text-decoration-none text-light topspeed-link'>
                    <div class="leaderboard-entry d-flex align-items-center justify-content-between <?= $placeClass ?>">
                        <div class="d-flex align-items-center">
                            <img src="uploads/cars/<?php echo $carInfo[0]['Image']; ?>" alt="<?php echo $carInfo[0]['Name']; ?>" width="120" class="me-3">
                            <div class="rank-circle me-3"><?= $i ?></div>
                            <div>
                                <div><strong><?php echo getBrandName($pdo, $carInfo[0]['Brand']) . " " . $carInfo[0]['Name']; ?></strong></div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="stat text-white fs-4"><?= $speed["highest_speed"]?> km/h</div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="card text-light">
        <div class="card-body text-center">
            <h4>No top speeds recorded yet</h4>
            <p class="text-muted">Be the first to upload a top speed!</p>
            <a href="?add=new" class="btn btn-red">Upload Top Speed</a>
        </div>
    </div>
<?php endif; ?>

<?php require_once("inc/footer.php"); ?>