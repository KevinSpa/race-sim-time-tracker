<?php require_once("inc/header.php"); ?>

<?php displaySuccessMessage('track'); ?>

<?php
// Display single track stats when set
if (isset($_GET['track'])) {
    include_once("inc/singletrack.php");
} else {
    echo '<div class="row justify-content-between mb-3">
            <div class="col-6">
                <h2>Tracks</h2> 
            </div>
            <div class="col-6 text-right">
                <a href="newtrack" class="btn btn-red">+ Add Track</a>
            </div>
        </div>';
    $stmt = $pdo->prepare("SELECT * FROM tracks WHERE DeletedDate IS NULL ORDER BY times_submitted DESC");
    $stmt->execute();

    // Fetch all results as an associative array
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Loop through the results and display them
    echo '<div class="row g-4">';
    foreach ($tracks as $track) {
        $trackId = $track['ID'];
        $trackName = $track['Name'];
        $country = $track['Country'];
        $lengthKm = number_format($track['Length'] / 1000, 3);
        $image = $track['Image'];
        $lapCount = $track['times_submitted'];

        echo "
    <div class='col-md-6 col-lg-4'>
        <a href='tracks?track=$trackId&sub=$lapCount' class='text-decoration-none trackLink'>
            <div class='card card-hover text-light h-100'>
                <div class='card-body'>
                    <h5 class='card-title'>$trackName</h5>
                    <p class='card-subtitle mb-2'>$country</p>
                    <img src='uploads/tracks/$image' class='card-img-top trackimg mb-3' alt='$trackName'>
                    <div class='d-flex justify-content-between text-center'>
                        <div class='card-stats'>
                            <small class='card-subtitle fs-12'>Length</small>
                            <p class='mb-0'>$lengthKm km</p>
                        </div>
                        <div class='card-stats'>
                            <small class='card-subtitle fs-12'>Turns</small>
                            <p class='mb-0'>$lengthKm km</p>
                        </div>
                        <div class='card-stats'>
                            <small class='card-subtitle fs-12'>Lap Times</small>
                            <p class='mb-0'>$lapCount</p>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>";
    }
    echo '</div>';

}

require_once("inc/footer.php"); ?>