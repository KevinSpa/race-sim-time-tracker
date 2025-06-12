<?php require_once("inc/header.php");

if (isset($_GET['car'])) {
    include_once("inc/singlecar.php");
} else {
    echo '<div class="row justify-content-between mb-3">
            <div class="col-6">
                <h2>Cars</h2> 
            </div>
            <div class="col-6 text-end">
                <a href="newcar" class="btn btn-red">+ Add Car</a>
            </div>
        </div>';

    $stmt = $pdo->prepare("SELECT * FROM cars ORDER BY times_submitted DESC");
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<div class="row g-4">';
    foreach ($cars as $car) {
        $brandName = getBrandName($pdo, $car["Brand"]);
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
                        <h5 class='card-title'>$brandName $carName</h5>
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
}

require_once("inc/footer.php");
?>
