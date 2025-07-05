<?php
// Redirect users to the home page if they access this file directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Location: ../');
    exit();
}

function getBrandName($pdo, $brandId)
{
    $getBrand = $pdo->prepare("SELECT ID, Name FROM brands WHERE ID = :id");
    $getBrand->bindParam(':id', $brandId);
    $getBrand->execute();
    $result = $getBrand->fetch(PDO::FETCH_ASSOC);
    return $result["Name"];
}

function countCars($pdo) {
    $countCar = $pdo->prepare("SELECT COUNT(*) FROM cars");
    $countCar->execute();
    $car_count = $countCar->fetch(PDO::FETCH_ASSOC);
    return $car_count["COUNT(*)"];
}

function validate_car_form($name, $brand, $image, $pdo)
{
    $errors = array();

    // Check if all fields are filled
    if (empty($name)) {
        $errors[] = "Name is required";
    } else {
        // Check if car name already exists in database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE name = :name");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        $count = $stmt->fetchColumn();
        if ($count > 0) {
            $errors[] = "Car name already exists";
        }
    }

    // Check if brand is filled in
    if (empty($brand)) {
        $errors[] = "Brand is required";
    }

    // Check if image is filled in
    if (empty($image['name'])) {
        $errors[] = "Image is required";
    }

    // Check if image is valid
    if (!empty($image['name'])) {
        $allowed_extensions = array('jpg', 'jpeg', 'png');
        $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = "Only JPG, JPEG, and PNG files are allowed";
        }
        if ($image['size'] > 5 * 1024 * 1024) {
            $errors[] = "File size cannot exceed 5MB";
        }
    }

    return $errors;
}

function renameFile($image)
{
    // Get the file extension
    $extension = pathinfo($image['name'], PATHINFO_EXTENSION);

    // Generate a unique filename using a combination of a random string and the current timestamp
    $filename = uniqid() . '_' . time() . '.' . $extension;

    return $filename;
}

function formatTime($laptime)
{
    if (is_numeric($laptime)) {
        $seconds = floor($laptime);
        $milliseconds = round(($laptime - $seconds) * 1000);
        $timeFormat = gmdate("i:s", $seconds) . '.' . sprintf('%03d', $milliseconds);
        return $timeFormat;
    }
    return $laptime;
}

function getFastestTime($pdo, $trackID)
{
    // Prepare a SQL query to find the minimum LapTime for a specific TrackID.
    $lapTimeQuery = $pdo->prepare("SELECT MIN(t.LapTime) as fastest_time 
                                  FROM `times` t
                                  JOIN cars c ON t.CarID = c.ID 
                                  WHERE t.TrackID = :trackid AND c.DeletedDate IS NULL");

    $lapTimeQuery->bindParam(":trackid", $trackID);
    $lapTimeQuery->execute();
    $lapTimeData = $lapTimeQuery->fetch(PDO::FETCH_ASSOC);

    // Extract the 'fastest_time' value from the result and convert it to a float.
    $fastestTime = floatval($lapTimeData['fastest_time']);

    // Format the fastest lap time.
    $formattedFastestTime = formatTime($fastestTime);

    return $formattedFastestTime;
}

function checkCar($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT `ID` FROM `cars` WHERE `ID` = :carID AND DeletedDate IS NULL");
    $stmt->bindParam(":carID", $id);
    $stmt->execute();
    $stmt->fetch();
    $result = $stmt->rowCount();
    if ($result == 1) {
        return true;
    } else {
        return false;
    }
}

function trackError($error)
{
    if ($error == "invalid") {
        $error = "is not valid!";
    } else if ($error == "none") {
        $error = "not found!";
    }
    return $error;
}

function pickCar($id, $pdo, $selectedCarID = null)
{
    $stmt = $pdo->query("SELECT * FROM `cars` WHERE DeletedDate IS NULL ORDER BY `Brand`");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<label for='car_id'>Car {$id}:</label>
    <select name='car_id{$id}' id='cars{$id}' onchange='updateCarImage({$id})' class='form-select'>";
    foreach ($cars as $car) {
        $stmtID = $car['ID'];
        $stmtImage = $car['Image'];
        $stmtBrand = getBrandName($pdo, $car['Brand']);
        $stmtName = $car['Name'];
        $selected = ($selectedCarID == $stmtID) ? "selected" : "";
        echo "<option value='{$stmtID}' data-img='{$stmtImage}' $selected>{$stmtBrand} {$stmtName}</option>";
    }
    echo "</select>";
    // Toon de afbeelding
    $defaultImage = '';
    foreach ($cars as $car) {
        if ($selectedCarID == $car['ID']) {
            $defaultImage = $car['Image'];
            break;
        }
    }
    if (!$defaultImage && count($cars) > 0) {
        $defaultImage = $cars[0]['Image'];
    }
    echo "<div id='carImage{$id}' class='mt-2'><img src='uploads/cars/{$defaultImage}' alt='' class='preview'></div>";
}

function pickTrack($pdo, $selectedTrackID = null)
{
    $stmt = $pdo->query('SELECT * FROM tracks WHERE DeletedDate IS NULL ORDER BY times_submitted DESC ');
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<label for='track_id'>Track:</label>
        <select name='track_id' id='tracks' onchange='updateTrackImage()' class='form-select'>";
    foreach ($tracks as $track) {
        $stmtID = $track['ID'];
        $stmtImage = $track['Image'];
        $stmtName = $track['Name'];
        $selected = ($selectedTrackID == $stmtID) ? "selected" : "";
        echo "<option value='{$stmtID}' data-img='{$stmtImage}' $selected>{$stmtName}</option>";
    }
    echo "</select>";
    // Toon de afbeelding
    $defaultImage = '';
    foreach ($tracks as $track) {
        if ($selectedTrackID == $track['ID']) {
            $defaultImage = $track['Image'];
            break;
        }
    }
    if (!$defaultImage && count($tracks) > 0) {
        $defaultImage = $tracks[0]['Image'];
    }
    echo "<div id='trackImage' class='mt-2'><img src='uploads/tracks/{$defaultImage}' alt='' class='preview'></div>";
}

function brandList($pdo) {
    // Get all cars from the database
    $stmt = $pdo->query("SELECT b.ID, b.Name, COUNT(c.Brand) AS brand_count
                            FROM brands b
                            LEFT JOIN cars c ON b.ID = c.Brand AND c.DeletedDate IS NULL
                            GROUP BY b.Name
                            ORDER BY brand_count DESC;");
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<label for='car_id'>Brand:</label>
    <select name='brand_id' id='brand_id' class='form-select' style='margin-bottom: 20px'>";
    foreach ($brands as $brand) {
        $stmtID = $brand['ID'];
        $stmtName = $brand['Name'];
        echo "<option value='{$stmtID}'>{$stmtName}</option>";
    }
    echo "</select>";
}

function cleanString($str) {
    // Remove special characters, except dashes and alphanumeric characters
    $str = preg_replace('/[^a-zA-Z0-9\s-]/', '', $str);

    // Replace spaces with dashes
    $str = str_replace(' ', '-', $str);

    // Remove multiple consecutive dashes
    $str = preg_replace('/-+/', '-', $str);

    // Trim any leading or trailing dashes
    $str = trim($str, '-');

    return $str;
}

function timesPagination($page, $totalPages) {
    // Add buttons based on page
    $nextPage = $page + 1;
    echo "<div class='pagination row d-flex justify-content-center'>";
    echo "<span class='page-numbers text-center'>Page $page of $totalPages</span>";

    echo "<div class='col-12 text-center'>";

    if ($page > 1) {
        echo "<div class='col-12 text-center'>";
        echo "<a href='?page=1' class='page-numbers'>First </a>";
        echo "<a href='?page=" . ($page - 1) . "' class='page-numbers'>&laquo; Previous</a>";
        echo "</div>";
    }

    if ($page < $totalPages) {
        echo "<div class='col-12 text-center'>";
        echo "<a href='?page=" . ($page + 1) . "' class='page-numbers'>Next &raquo; </a>";
        echo "<a href='?page=$totalPages' class='page-numbers'>Last</a>";
        echo "</div>";
    }
    echo "</div></div>";
}

function isNumericString($str) {
    // Use a regular expression to check if the string matches the pattern
    return preg_match("/^[0-9,.]+$/", $str) === 1;
}

function getHighestTopSpeed($pdo) {
    $pos = $pdo->prepare("SELECT speed FROM topspeed ts 
                         JOIN cars c ON ts.carID = c.ID 
                         WHERE c.DeletedDate IS NULL 
                         ORDER BY speed DESC LIMIT 1;");
    $pos->execute();
    $row = $pos->fetchAll();
    return $row[0]["speed"];
}

function getAmountOfFasterCars($pdo, $time) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `topspeed` ts
                          JOIN cars c ON ts.carID = c.ID 
                          WHERE ts.speed > ? AND c.DeletedDate IS NULL;");
    $stmt->bindParam(1, $time);
    $stmt->execute();
    $row = $stmt->fetchAll();
    return $row[0]["COUNT(*)"];
}

function getCarInfo($pdo, $carID) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE ID = ? AND DeletedDate IS NULL");
    $stmt->bindParam(1, $carID);
    $stmt->execute();
    $row = $stmt->fetchAll();
    return $row;
}

function getCarTopSpeed($pdo, $carID) {
    $stmt = $pdo->prepare("SELECT speed FROM topspeed ts
                          JOIN cars c ON ts.carID = c.ID
                          WHERE ts.carID = ? AND c.DeletedDate IS NULL");
    $stmt->bindParam(1, $carID);
    $stmt->execute();
    $row = $stmt->fetchAll();
    if (count($row) == 0) {
        return "<span class=''>---</span>";
    } else {
        return "<span class='stat'>". $row[0]["speed"]. " </span>km/h";
    }
}

function displaySuccessMessage($type) {
    if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
        $message = '';
        
        switch($type) {
            case 'car':
                $message = 'The car was successfully deleted.';
                break;
            case 'track':
                $message = 'The track was successfully deleted.';
                break;
            default:
                $message = 'Item was successfully deleted.';
        }
        
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '$message',
                    confirmButtonColor: '#3085d6'
                });
            });
        </script>";
    }
}
?>