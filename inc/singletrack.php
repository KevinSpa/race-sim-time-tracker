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
?>

<div class="row singleTrack">
    <div class="col-lg-2 text-center">
        <img src="uploads/tracks/<?= $row['Image']; ?>" class="trackimg">
    </div>
    <div class="col-lg-7">
        <h2><?= $row['Name']; ?></h2>
        <h5><?=  $row['Country']; ?></h5>
        
    </div>
    <div class="col-lg-3 d-flex align-items-end">
        <p>Track lenth: <span class="stat"><?= $row['Length'];?></span> meter<br>
        Total times submitted: <span class="stat"><?= $_GET['sub']?></span><br>
        Unique times submitted: <span class="stat"><?= $stmt->rowCount();?></span><br>
        Total distance: <span class="stat"><?= $mdriver = $stmt->rowCount() * $row['Length'] / 1000;?></span>km</p>
    </div>
</div>

<?php
// Display the times
$i = 0;
foreach ($times as $time) :
    $i++;
    $avg = round($row['Length'] / $time['LapTime'] * 3.6, 2);
    $subDate = $time["SubmittedDate"];

?>
<a href='cars?car=<?= $time['ID']?>' class='carLink'>
    <div class="row trackCarList mt-2">
        <div class="col-4 col-lg-2">
            <img src="uploads/cars/<?php echo $time['Image']; ?>" alt="<?php echo $time['Name']; ?>" class="w-100">
        </div>
        <div class="col-8 col-lg-4">
            <h2 class="preventLongText"><?php echo getBrandName($pdo, $time['Brand']) . " - ".$time['Name']; ?></h2>
            Average speed: <span class="stat"><?= $avg;?></span><br>
            Date submitted: <span class="stat"><?= $subDate?></span>
            <div class="d-block d-lg-none">
                <div class="col-10 col-lg-5 d-flex align-items-center">
                    <span class="top" id="top<?=$i; ?>">#<?=$i;?> -</span>
                    <span class="top px-2" id="top<?=$i; ?>"> <?= formatTime($time['LapTime']);?></span>
                </div>
            </div>
        </div>
        <div class="col-5 d-flex align-items-center d-none d-lg-block">
            <span class="top" id="top<?=$i; ?>"><?= formatTime($time['LapTime']);?></span>
        </div>
        <div class="col-1 d-flex align-items-end d-none d-lg-block">
            <span class="top" id="top<?=$i; ?>">#<?=$i;?></span>
        </div>
    </div>
</a>
<?php 
endforeach;
?>