<?php require_once("inc/header.php");

$resultsPerPage = 27;

// Bepaal het totaal aantal resultaten en het aantal pagina's
$countTimes = $pdo->prepare("SELECT COUNT(*) AS TotalTimes FROM times");
$countTimes->execute();
$countResult = $countTimes->fetch(PDO::FETCH_ASSOC);
$totalResults = $countResult['TotalTimes'];
$totalPages = ceil($totalResults / $resultsPerPage);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $resultsPerPage;
$startResult = $offset + 1;
$endResult = min($offset + $resultsPerPage, $totalResults);

if ($page >= 1 && $page <= $totalPages) {

    $stmt = $pdo->prepare("SELECT t.LapTime, t.SubmittedDate, c.Name AS CarName, c.Image AS CarImage, c.Brand, tr.Name AS TrackName, tr.Image AS TrackImage ,tr.Country
                      FROM times t
                      INNER JOIN cars c ON t.CarID = c.ID
                      INNER JOIN tracks tr ON t.TrackID = tr.ID
                      ORDER BY t.LapTime
                      LIMIT $resultsPerPage OFFSET $offset");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<div class='row justify-content-between mb-3'>
            <div class='col-6'>
                <h2>Lap Times</h2> 
            </div>
            <div class='col-6 text-end'>
                <span class='fs-6'>Showing results $startResult - $endResult of $totalResults</span>
            </div>
        </div>";

    timesPagination($page, $totalPages);
    echo "<div class='row g-4'>";

    foreach ($results as $row) {
        $brandName = getBrandName($pdo, $row['Brand']);
        $lapTime = formatTime($row["LapTime"]);
        $trackImage = $row["TrackImage"];
        $carImage = $row["CarImage"];
        $carName = $row["CarName"];
        $trackName = $row["TrackName"];
        $country = $row["Country"];
        $date = $row["SubmittedDate"];

        echo "
        <div class='col-md-6 col-lg-4'>
            <div class='card text-light h-100'>
                <div class='card-body'>
                    <h5 class='card-title'>$brandName $carName</h5>
                    <p class='card-subtitle mb-2 text-muted'>Track: $trackName ($country)</p>
                    <div class='row mb-3'>
                        <div class='col-6'>
                            <img src='uploads/cars/$carImage' class='img-fluid rounded'>
                        </div>
                        <div class='col-6'>
                            <img src='uploads/tracks/$trackImage' class='img-fluid rounded'>
                        </div>
                    </div>
                    <div class='d-flex justify-content-between'>
                        <div class='card-stats-2'>
                            <p class='card-subtitle fs-12 mb-0'>Lap Time: $lapTime</p>
                        </div>
                        <div class='card-stats-2 text-right'>
                            <p class='card-subtitle fs-12 mb-0'>Submitted: $date</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }

    echo '</div>';
    timesPagination($page, $totalPages);

} else {
    echo "<div class='alert alert-danger'>Page not found!</div>";
}

require_once("inc/footer.php");
?>
