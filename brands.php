<?php
require_once("inc/header.php");

if (isset($_GET['error'])) {
    echo "Brand " . trackError($_GET['error']);
}

if (isset($_GET['brand'])) {
    include_once("inc/singlebrand.php");
} else {
    echo '<div class="row justify-content-between mb-3">
            <div class="col-6">
                <h2>Brands</h2> 
            </div>
            <div class="col-6 text-end">
                <a href="newbrand" class="btn btn-red">+ Add Brand</a>
            </div>
        </div>';

    $stmt = $pdo->prepare("SELECT b.ID, b.Name, b.logo, COUNT(c.Brand) AS brand_count
                            FROM brands b
                            LEFT JOIN cars c ON b.ID = c.Brand
                            GROUP BY b.ID
                            ORDER BY brand_count DESC;");
    $stmt->execute();
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<div class="row g-4">';
    // If car bran name needed add: <h5 class='card-title'>All Cars</h5>
    // All Cars Card
    $totalCars = countCars($pdo);
    echo "
    <div class='col-md-6 col-lg-3'>
        <a href='cars' class='text-decoration-none trackLink'>
            <div class='card card-hover text-light h-100'>
                <div class='card-body text-center'>
                    
                    <img src='logo/all-logo.png' class='card-img-top brandImg mb-3' alt='All Cars'>
                    <p><span class='stat'>$totalCars</span> cars total</p>
                </div>
            </div>
        </a>
    </div>";

    // Brand cards
    foreach ($brands as $brand) {
        $brandId = $brand['ID'];
        $brandName = $brand['Name'];
        $brandLogo = $brand['logo'];
        $brandCount = $brand['brand_count'];

        echo "
        <div class='col-md-6 col-lg-3'> 
            <a href='brands?brand=$brandId' class='text-decoration-none trackLink'>
                <div class='card card-hover text-light h-100'>
                    <div class='card-body text-center'>
                        <img src='logo/$brandLogo' class='card-img-top brandImg mb-3' alt='$brandName'>
                        <p><span class='stat'>$brandCount</span> cars</p>
                    </div>
                </div>
            </a>
        </div>";
    }

    echo '</div>';
}

require_once("inc/footer.php");
?>
