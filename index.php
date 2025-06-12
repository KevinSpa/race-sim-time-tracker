<?php require_once("inc/header.php");

$totalCars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
$totalBrands = $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
$totalTracks = $pdo->query("SELECT COUNT(*) FROM tracks")->fetchColumn();
$totalLapTimes = $pdo->query("SELECT COUNT(*) FROM times")->fetchColumn();
?>

<div class="row justify-content-between mb-3">
    <div class="col-6">
        <img src="assets/img/home-title.png" class="home-title" alt="Title: Barrietto">

    </div>
    <div class="col-6 text-right">
        <a href="newtime" class="btn btn-red">+ Add time</a>
    </div>
</div>

<!-- Stats cards -->
<div class="row g-4 mb-4 justify-content-center">
    <div class="col-md-4 col-lg-3">
        <div class="card text-light h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold">Lap Times</h5>
                <p class="card-subtitle mb-2">Total recorded lap times</p>
                <h3 class="fw-bold"><?= $totalLapTimes ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-3">
        <div class="card text-light h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold">Total Tracks</h5>
                <p class="card-subtitle mb-2">Tracks you've raced on</p>
                <h3 class="fw-bold"><?= $totalTracks ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-3">
        <div class="card text-light h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold">Total Brands</h5>
                <p class="card-subtitle mb-2">Brands you've added</p>
                <h3 class="fw-bold"><?= $totalBrands ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-3">
        <div class="card text-light h-100">
            <div class="card-body">
                <h5 class="card-title fw-bold">Total Cars</h5>
                <p class="card-subtitle mb-2">Cars you've driven</p>
                <h3 class="fw-bold"><?= $totalCars ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Navigation cards -->
<div class="row g-4 justify-content-center">
    <?php
    $cards = [
        ['link' => 'brands', 'title' => 'Brands', 'icon' => '🏎️'],
        ['link' => 'tracks', 'title' => 'Tracks', 'icon' => '🏁'],
        ['link' => 'cars', 'title' => 'Cars', 'icon' => '🚗'],
        ['link' => 'topspeed', 'title' => 'Top Speeds', 'icon' => '⚡'],
        ['link' => 'newtime', 'title' => 'Submit New Time', 'icon' => '✅'],
        ['link' => 'times', 'title' => 'All Times', 'icon' => '⏱️'],
    ];

    foreach ($cards as $card) {
        echo "
        <div class='col-md-6 col-lg-4'>
            <a href='{$card['link']}' class='text-decoration-none trackLink'>
                <div class='card card-hover text-light text-center h-100' style='min-height: 250px'>
                    <div class='card-body d-flex flex-column justify-content-center'>
                        <h1 class='display-4'>{$card['icon']}</h1>
                        <h4 class='card-title mt-2'>{$card['title']}</h4>
                    </div>
                </div>
            </a>
        </div>";
    }
    ?>
</div>

<?php require_once("inc/footer.php"); ?>
