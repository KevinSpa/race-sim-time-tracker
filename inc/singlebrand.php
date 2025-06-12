<?php
// Redirect users to the home page if they access this file directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Location: ../');
    exit();
}

$brand_id = $_GET['brand'];

// Check of brand geldig is
$getBrands = $pdo->prepare("SELECT COUNT(*) FROM brands");
$getBrands->execute();
$brand_count = $getBrands->fetch(PDO::FETCH_ASSOC);

if ($brand_id > 0 && $brand_id <= $brand_count["COUNT(*)"]) {
    // Haal merkinformatie op
    $getBrandDetails = $pdo->prepare("SELECT * FROM brands WHERE ID = :brand_id");
    $getBrandDetails->bindParam(":brand_id", $brand_id);
    $getBrandDetails->execute();
    $brandDetails = $getBrandDetails->fetch(PDO::FETCH_ASSOC);

    // Header met logo + naam
    echo "<div class='col-12 border-bottom pb-2 mb-4'>
            <div class='row'>
                <div class='col-2 col-md-1'>
                    <img src='../logo/{$brandDetails["logo"]}' class='w-100'>
                </div>
                <div class='col-10 d-flex align-items-center'>
                    <h1>{$brandDetails["Name"]}</h1>
                </div>
            </div>
        </div>";

    // Haal alle auto's van dit merk op
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE Brand = :brand_id ORDER BY times_submitted DESC");
    $stmt->bindParam(":brand_id", $brand_id);
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Cards layout
    echo '<div class="row g-4">';
    foreach ($cars as $car) {
        $carId = $car['ID'];
        $carName = $car['Name'];
        $image = $car['Image'];
        $date = $car['CreatedDate'];
        $submitted = $car['times_submitted'];

        echo "
        <div class='col-md-6 col-lg-4'>
            <a href='cars?car=$carId' class='text-decoration-none trackLink'>
                <div class='card card-hover text-light h-100'>
                    <div class='card-body'>
                        <h5 class='card-title'>{$brandDetails["Name"]} $carName</h5>
                        <img src='uploads/cars/$image' class='card-img-top carImg mb-3' alt='$carName'>
                        <div class='d-flex justify-content-between'>
                            <div class='card-stats-2'>
                                <p class='card-subtitle fs-12 mb-0'>$submitted lap times</p>
                            </div>
                            <div class='card-stats-2 text-right'>
                                <p class='card-subtitle fs-12 mb-0'>Added on: $date</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>";
    }
    echo '</div>';
} else {
    header("Location: brands");
    exit();
}
?>
