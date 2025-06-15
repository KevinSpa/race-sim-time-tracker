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
$carTopSpeed = getCarTopSpeed($pdo, $carID);

function getTrackID($pdo, $trackName) {
    $stmt = $pdo->prepare("SELECT ID FROM tracks WHERE Name = :name LIMIT 1");
    $stmt->execute(['name' => $trackName]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['ID'] : 0;
}


$getTimes = $pdo->prepare("SELECT t.LapTime, t.SubmittedDate, tr.ID as TrackID, tr.Name AS TrackName, tr.Image AS TrackImage
                           FROM times t 
                           JOIN tracks tr ON t.TrackID = tr.ID 
                           WHERE t.CarID = ? AND tr.DeletedDate IS NULL
                           ORDER BY t.LapTime ASC");

$getTimes->execute([$carID]);
$lapTimes = $getTimes->fetchAll(PDO::FETCH_ASSOC);
$totalLaps = count($lapTimes);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_car']) && (int)$_SESSION["user_id"] == 1) {
    $stmt = $pdo->prepare("UPDATE cars SET DeletedDate = NOW() WHERE ID = ?");
    $stmt->execute([$carID]);
    header("Location: cars.php?deleted=success");
    exit();
}

if ($row['DeletedDate']) {
    echo "<div class='alert alert-danger'>Deze auto is verwijderd.</div>";
    exit;
}
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
                <?php if ((int)$_SESSION["user_id"] == 1 && !$row['DeletedDate']): ?>
                    <button id="deleteCarBtn" class="btn btn-danger mt-2">Delete Car</button>
                    <form id="deleteCarForm" method="post" action="" style="display:none;">
                        <input type="hidden" name="delete_car" value="1">
                    </form>
                <?php endif; ?>
                <script>
                document.getElementById('deleteCarBtn')?.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This car will be marked as deleted and hidden everywhere.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('deleteCarForm').submit();
                        }
                    })
                });
                </script>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card text-light h-100">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-3 fw-bold">Lap Times</h3>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="newtime.php?car=<?= $carID ?>" class="btn btn-red mb-3">+ Submit time</a>
                    </div>
                </div>
                
                <p class="mb-4 fs-14 card-subtitle">All lap times for this car</p>
                <?php if ($lapTimes): ?>
                    <?php foreach ($lapTimes as $time): ?>
                        <a href="tracks?track=<?= $time['TrackID'] ?>" class="text-decoration-none text-light">
                            <div class="border-bottom py-2 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="uploads/tracks/<?= htmlspecialchars($time['TrackImage']) ?>" alt="<?= htmlspecialchars($time['TrackName']) ?>" class="me-3" style="max-width:100px; max-height:48px; object-fit:contain; border-radius:8px;">
                                    <div>
                                        <strong><?= $time['TrackName'] ?></strong><br>
                                        <small><?= $time['SubmittedDate'] ?></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="stat text-white"><?= formatTime($time['LapTime']) ?></span>
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

