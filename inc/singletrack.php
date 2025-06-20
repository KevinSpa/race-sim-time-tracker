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
                        WHERE t1.TrackID = ? AND c.DeletedDate IS NULL
                        ORDER BY t1.LapTime ASC"
                    );
$stmt->bindParam(1, $trackID);
$stmt->bindParam(2, $trackID);

$stmt->execute();
$times = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalUnique = $stmt->rowCount();
$totalSubmitted = isset($_GET['sub']) ? $_GET['sub'] : $totalUnique;
$totalDistance = $totalUnique * $row['Length'] / 1000;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_track']) && (int)$_SESSION["user_id"] == 1) {
    $stmt = $pdo->prepare("UPDATE tracks SET DeletedDate = NOW() WHERE ID = ?");
    $stmt->execute([$trackID]);
    header("Location: tracks.php?deleted=success");
    exit();
}

if ($row['DeletedDate']) {
    echo "<div class='alert alert-danger'>Deze track is verwijderd.</div>";
    exit;
}

$lapHistoryStmt = $pdo->prepare("
    SELECT t.LapTime, t.SubmittedDate, c.Name AS CarName
    FROM times t
    JOIN cars c ON t.CarID = c.ID
    WHERE t.TrackID = ? AND c.DeletedDate IS NULL
    ORDER BY t.SubmittedDate ASC, t.LapTime ASC
");
$lapHistoryStmt->execute([$trackID]);
$lapHistory = $lapHistoryStmt->fetchAll(PDO::FETCH_ASSOC);

$chartLabels = [];
$chartData = [];
$chartCars = [];
$bestTime = null;

foreach ($lapHistory as $i => $entry) {
    if ($i === 0 || $entry['LapTime'] < $bestTime) {
        $bestTime = $entry['LapTime'];
        $chartLabels[] = $entry['SubmittedDate'];
        $chartData[] = $entry['LapTime'];
        $chartCars[] = $entry['CarName'];
    }
}
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
                    <?php if ((int)$_SESSION["user_id"] == 1 && !$row['DeletedDate']): ?>
                        <button id="deleteTrackBtn" class="btn btn-danger mt-2">Delete Track</button>
                        <form id="deleteTrackForm" method="post" action="" style="display:none;">
                            <input type="hidden" name="delete_track" value="1">
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Lap Time Progression</h4>
                <canvas id="lapTimeChart" height="100"></canvas>
                <small class="text-muted">Hover over de punten voor de auto die de tijd reed.</small>
            </div>
        </div>

        <div class="card text-light mt-4">
            <div class="card-body">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-6">
                        <h3 class="mb-3 fw-bold">Lap Times</h3>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="newtime.php?track=<?= $trackID ?>" class="btn btn-red mb-3">+ Submit time</a>
                    </div>
                </div>
                
                <p class="mb-4 fs-14 card-subtitle">Fastest lap per car on this track</p>
                <?php if ($times): ?>
                    <?php 
                    $i = 0;
                    foreach ($times as $time): 
                        $i++;
                        $avg = round($row['Length'] / $time['LapTime'] * 3.6, 2);
                        $subDate = $time["SubmittedDate"];

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


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
const ctx = document.getElementById('lapTimeChart').getContext('2d');
const chartLabels = <?= json_encode($chartLabels) ?>;
const chartData = <?= json_encode($chartData) ?>;
const chartCars = <?= json_encode($chartCars) ?>;

const lapTimeChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartLabels,
        datasets: [{
            label: 'Lap Time (s)',
            data: chartData,
            fill: false,
            borderColor: 'rgb(220,53,69)',
            backgroundColor: 'rgba(220,53,69,0.2)',
            tension: 0.2,
            pointRadius: 5,
            pointHoverRadius: 8,
        }]
    },
    options: {
        plugins: {
            datalabels: {
                anchor: 'end',
                align: 'top',
                color: '#fff',
                font: { weight: 'bold' },
                formatter: function(value) {
                    const minutes = String(Math.floor(value / 60)).padStart(2, '0');
                    const seconds = String(Math.floor(value % 60)).padStart(2, '0');
                    const millis = String(Math.round((value - Math.floor(value)) * 1000)).padStart(3, '0');
                    return `${minutes}:${seconds}:${millis}`;
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const idx = context.dataIndex;
                        const car = chartCars[idx];
                        const value = context.parsed.y;
                        const minutes = String(Math.floor(value / 60)).padStart(2, '0');
                        const seconds = String(Math.floor(value % 60)).padStart(2, '0');
                        const millis = String(Math.round((value - Math.floor(value)) * 1000)).padStart(3, '0');
                        return `Tijd: ${minutes}:${seconds}:${millis} (${car})`;
                    }
                }
            }
        },
        scales: {
            y: {
                title: { display: true, text: 'Lap Time (s)' },
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        const minutes = String(Math.floor(value / 60)).padStart(2, '0');
                        const seconds = String(Math.floor(value % 60)).padStart(2, '0');
                        const millis = String(Math.round((value - Math.floor(value)) * 1000)).padStart(3, '0');
                        return `${minutes}:${seconds}:${millis}`;
                    }
                }
            },
            x: {
                title: { display: true, text: 'Datum' }
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>

<script>
document.getElementById('deleteTrackBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: "This track will be marked as deleted and hidden everywhere.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteTrackForm').submit();
        }
    })
});
</script>