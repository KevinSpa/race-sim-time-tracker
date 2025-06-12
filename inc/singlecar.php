<?php 
// Redirect users to the home page if they access this file directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Location: ../');
    exit();
}

$carID = $_GET['car'];

if (!is_numeric($carID) || $carID <= 0) {
    header("Location: brands");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM cars WHERE ID = ?");
$stmt->execute([$carID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    header("Location: brands");
    exit();
}

$carName = $row['Name'];
$carBrand = getBrandName($pdo, $row["Brand"]);
$carImage = $row['Image'];
$carYear = $row['Year'];
$carTopSpeed = getCarTopSpeed($pdo, $carID);

function getTrackID($pdo, $trackName) {
    $stmt = $pdo->prepare("SELECT ID FROM tracks WHERE Name = :name LIMIT 1");
    $stmt->execute(['name' => $trackName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['ID'] : 0;
}


$getTimes = $pdo->prepare("SELECT t.LapTime, t.SubmittedDate, tr.ID as TrackID, tr.Name AS TrackName 
                           FROM times t 
                           JOIN tracks tr ON t.TrackID = tr.ID 
                           WHERE t.CarID = ? 
                           ORDER BY t.LapTime ASC");

$getTimes->execute([$carID]);
$lapTimes = $getTimes->fetchAll(PDO::FETCH_ASSOC);
$totalLaps = count($lapTimes);


?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-light">
            <div class="card-body">
                <h3 class="mb-3 fw-bold"><?= $carBrand . " " . $carName ?></h3>
                <img src="uploads/cars/<?= $carImage ?>" class="img-fluid mb-3" alt="<?= $carName ?>">
                <div class="text-start">
                    <span class="card-subtitle fs-14">Brand</span>
                    <p><?= $carBrand ?></p>
                    <span class="card-subtitle fs-14">Model</span>
                    <p><?= $carName ?></p>
                    <span class="card-subtitle fs-14">Total Lap Times</span>
                    <p><?= $totalLaps ?></p>
                    <span class="card-subtitle fs-14">Top Speed</span>
                    <p><?= $carTopSpeed ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card text-light h-100">
            <div class="card-body">
                <h3 class="mb-3 fw-bold">Lap Times</h3>
                <p class="mb-4 fs-14 card-subtitle">All lap times for this car</p>
                <?php if ($lapTimes): ?>
                    <?php foreach ($lapTimes as $time): ?>
                        <a href="tracks?track=<?= $time['TrackID'] ?>" class="text-decoration-none text-light">
                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= $time['TrackName'] ?></strong><br>
                                        <small><?= $time['SubmittedDate'] ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="stat"><?= formatTime($time['LapTime']) ?></span>
                                    </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No lap times submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

