<?php require_once("inc/header.php");

$resultsPerPage = 27;

$sortOptions = [
    'newest' => ['label' => 'Nieuw → Oud', 'sql' => 't.SubmittedDate DESC'],
    'oldest' => ['label' => 'Oud → Nieuw', 'sql' => 't.SubmittedDate ASC'],
    'fastest' => ['label' => 'Snelst → Sloomst', 'sql' => 't.LapTime ASC'],
    'slowest' => ['label' => 'Sloomst → Snelst', 'sql' => 't.LapTime DESC'],
    'track' => ['label' => 'Tracknaam', 'sql' => 'tr.Name ASC'],
    'brand' => ['label' => 'Brandnaam', 'sql' => 'c.Brand ASC'],
];

$sort = isset($_GET['sort']) && isset($sortOptions[$_GET['sort']]) ? $_GET['sort'] : 'newest';
$orderBy = $sortOptions[$sort]['sql'];

$countTimes = $pdo->prepare("SELECT COUNT(*) AS TotalTimes FROM times t
                            JOIN cars c ON t.CarID = c.ID 
                            JOIN tracks tr ON t.TrackID = tr.ID
                            WHERE c.DeletedDate IS NULL AND tr.DeletedDate IS NULL");
$countTimes->execute();
$countResult = $countTimes->fetch(PDO::FETCH_ASSOC);
$totalResults = $countResult['TotalTimes'];
$totalPages = ceil($totalResults / $resultsPerPage);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $resultsPerPage;
$startResult = $offset + 1;
$endResult = min($offset + $resultsPerPage, $totalResults);

function renderPagination($currentPage, $totalPages, $sort) {
    if ($totalPages <= 1) return;

    $html = '<nav aria-label="Lap times pagination"><ul class="pagination justify-content-center mt-4">';
    $range = 2;

    // Previous
    $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
    $html .= '<li class="page-item'.$prevDisabled.'"><a class="page-link" href="?page='.($currentPage-1).'&sort='.$sort.'" tabindex="-1">&laquo;</a></li>';

    // First page
    if ($currentPage > $range + 1) {
        $html .= '<li class="page-item"><a class="page-link" href="?page=1&sort='.$sort.'">1</a></li>';
        if ($currentPage > $range + 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Page numbers
    for ($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++) {
        $active = $i == $currentPage ? ' active' : '';
        $html .= '<li class="page-item'.$active.'"><a class="page-link" href="?page='.$i.'&sort='.$sort.'">'.$i.'</a></li>';
    }

    // Last page
    if ($currentPage < $totalPages - $range) {
        if ($currentPage < $totalPages - $range - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="?page='.$totalPages.'&sort='.$sort.'">'.$totalPages.'</a></li>';
    }

    // Next
    $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
    $html .= '<li class="page-item'.$nextDisabled.'"><a class="page-link" href="?page='.($currentPage+1).'&sort='.$sort.'">&raquo;</a></li>';

    $html .= '</ul></nav>';
    echo $html;
}

if ($page >= 1 && $page <= $totalPages) {

    $stmt = $pdo->prepare("SELECT t.LapTime, t.SubmittedDate, t.CarID, t.TrackID, c.Name AS CarName, c.Image AS CarImage, c.Brand, tr.Name AS TrackName, tr.Image AS TrackImage ,tr.Country
                      FROM times t
                      INNER JOIN cars c ON t.CarID = c.ID
                      INNER JOIN tracks tr ON t.TrackID = tr.ID
                      WHERE c.DeletedDate IS NULL AND tr.DeletedDate IS NULL
                      ORDER BY $orderBy
                      LIMIT $resultsPerPage OFFSET $offset");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<div class='row justify-content-between align-items-center mb-3'>
            <div class='col-md-6'>
                <h2>Lap Times</h2> 
            </div>
            <div class='col-md-6 text-end'>
                <span class='fs-6 me-3'>Showing results $startResult - $endResult of $totalResults</span>
                <form method='get' class='d-inline'>
                    <label for='sort' class='form-label me-2 mb-0'>Sorteren op:</label>
                    <select name='sort' id='sort' class='form-select d-inline w-auto' onchange='this.form.submit()'>";
    foreach ($sortOptions as $key => $option) {
        $selected = $sort === $key ? 'selected' : '';
        echo "<option value=\"$key\" $selected>{$option['label']}</option>";
    }
    echo "          </select>
                    <input type='hidden' name='page' value='1'>
                </form>
            </div>
        </div>";

    renderPagination($page, $totalPages, $sort);
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
        $carID = $row["CarID"];
        $trackID = $row["TrackID"];

        echo "
        <div class='col-md-6 col-lg-4'>
            <div class='card text-light h-100'>
                <div class='card-body'>
                    <h5 class='card-title'>$brandName $carName</h5>
                    <p class='card-subtitle mb-2'>Track: $trackName ($country)</p>
                    <div class='row mb-3'>
                        <div class='col-6'>
                            <a href='cars?car=$carID' class='text-decoration-none'>
                                <img src='uploads/cars/$carImage' class='img-fluid rounded card-hover' style='cursor: pointer;' alt='$carName' title='View $brandName $carName'>
                            </a>
                        </div>
                        <div class='col-6'>
                            <a href='tracks?track=$trackID' class='text-decoration-none'>
                                <img src='uploads/tracks/$trackImage' class='img-fluid rounded card-hover' style='cursor: pointer;' alt='$trackName' title='View $trackName track'>
                            </a>
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
    renderPagination($page, $totalPages, $sort);

} else {
    echo "<div class='alert alert-danger'>Page not found!</div>";
}

require_once("inc/footer.php");
?>
