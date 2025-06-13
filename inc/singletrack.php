<?php 
// Redirect users to the home page if they access this file directly
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Location: ../');
    exit();
}

$trackID = $_GET['track'];
// Check if track is nummeric
if (!ctype_digit($trackID) || $trackID <= 0) {
    header ("Location: tracks.php?error=invalid");
}

// Check if the trackID exists
$stmt = $pdo->prepare("SELECT * FROM `tracks` WHERE `ID` = ?");
$stmt->execute([$trackID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($stmt->rowCount() == 0) {
    // Track ID does not exist
    header("Location: tracks.php?track=non");
    exit;
}

// Get all submitted times for the selected track
$stmt = $pdo->prepare(" SELECT t1.CarID, 
                               t1.LapTime,
                               t1.SubmittedDate,
                               c.ID,
                               c.Name,
                               c.Brand,
                               c.Image
                        FROM `times` t1
                        JOIN (
                            SELECT CarID, MIN(LapTime) as FastestTime 
                            FROM `times` 
                            WHERE TrackID = ?
                            GROUP BY CarID
                        ) t2 ON t1.CarID = t2.CarID AND t1.LapTime = t2.FastestTime
                        JOIN cars c ON t1.CarID = c.ID
                        WHERE t1.TrackID = ?
                        ORDER BY t1.LapTime ASC"
                    );
$stmt->bindParam(1, $trackID);
$stmt->bindParam(2, $trackID);

$stmt->execute();
$times = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalUnique = $stmt->rowCount();
$totalSubmitted = isset($_GET['sub']) ? $_GET['sub'] : $totalUnique;
$totalDistance = $totalUnique * $row['Length'] / 1000;
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-light">
            <div class="card-body">
                <h3 class="mb-3 fw-bold"><?= $row['Name']; ?></h3>
                <img src="uploads/tracks/<?= $row['Image']; ?>" class="img-fluid mb-3 trackimg" alt="<?= $row['Name']; ?>">
                <div class="text-start">
                    <span class="card-subtitle fs-14">Country</span>
                    <p><?= $row['Country']; ?></p>
                    <span class="card-subtitle fs-14">Length</span>
                    <p><?= number_format($row['Length']/1000, 3); ?> km</p>
                    <span class="card-subtitle fs-14">Total times submitted</span>
                    <p><?= $totalSubmitted ?></p>
                    <span class="card-subtitle fs-14">Unique cars</span>
                    <p><?= $totalUnique ?></p>
                    <span class="card-subtitle fs-14">Total distance driven</span>
                    <p><?= $totalDistance ?> km</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card text-light h-100">
            <div class="card-body">
                <h3 class="mb-3 fw-bold">Lap Times</h3>
                <p class="mb-4 fs-14 card-subtitle">Fastest lap per car on this track</p>
                <?php if ($times): ?>
                    <?php 
                    $i = 0;
                    foreach ($times as $time): 
                        $i++;
                        $avg = round($row['Length'] / $time['LapTime'] * 3.6, 2);
                        $subDate = $time["SubmittedDate"];

                        // Bepaal klasse op basis van positie
                        $placeClass = '';
                        if ($i == 1) $placeClass = 'first-place';
                        elseif ($i == 2) $placeClass = 'second-place';
                        elseif ($i == 3) $placeClass = 'third-place';
                    ?>
                        <a href="cars?car=<?= $time['ID']?>" class="text-decoration-none text-light">
                            <div class="leaderboard-entry d-flex align-items-center justify-content-between <?= $placeClass ?>">
                                <div class="d-flex align-items-center">
                                    <img src="uploads/cars/<?php echo $time['Image']; ?>" alt="<?php echo $time['Name']; ?>" width="150">
                                    <div class="rank-circle me-3 mx-2"><?= $i ?></div>
                                    <div>
                                        <div><strong><?= getBrandName($pdo, $time['Brand']) . " " . $time['Name']; ?></strong></div>
                                        <small><?= $subDate ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="stat text-white"><?= formatTime($time['LapTime']); ?></div>
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