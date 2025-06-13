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
    $lapTimeQuery = $pdo->prepare("SELECT MIN(LapTime) as fastest_time FROM `times` WHERE TrackID = :trackid");

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
    $stmt = $pdo->prepare("SELECT `ID` FROM `cars` WHERE `ID` = :carID");
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

function pickCar($id, $pdo)
{
    // Get all cars from the database
    $stmt = $pdo->query("SELECT * FROM `cars` ORDER BY `Brand`");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<label for='car_id'>Car {$id}:</label>
    <select name='car_id{$id}' id='cars{$id}' onchange='getCar({$id})' class='form-select'>";
    foreach ($cars as $car) {
        $stmtID = $car['ID'];
        $stmtImage = $car['Image'];
        $stmtBrand = getBrandName($pdo, $car['Brand']);
        $stmtName = $car['Name'];
        echo "<option value='{$stmtID}_{$stmtImage}'>{$stmtBrand} {$stmtName}</option>";
    }
    echo "</select>";

    // Get the image from the first car in the database
    $setCarimg = $pdo->prepare("SELECT `Image` FROM `cars` ORDER BY `Brand` LIMIT 1");
    $setCarimg->execute();
    $setCarimg->bindColumn("Image", $firstCar);
    $setCarimg->fetch();
    echo "<p><img id='displayCar{$id}' class='preview' src='uploads/cars/{$firstCar}'></p>";
}

function pickTrack($pdo)
{
    // Get all tracks from the database
    $stmt = $pdo->query('SELECT * FROM tracks ORDER BY times_submitted DESC ');
    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<label for='track_id'>Track:</label>
        <select name='track_id' id='tracks' onchange='getTrack()' class='form-select'>";
    foreach ($tracks as $track) {
        $stmtID = $track['ID'];
        $stmtImage = $track['Image'];
        $stmtName = $track['Name'];
        echo "<option value='{$stmtID}_{$stmtImage}'>{$stmtName}</option>";
    }
    echo "</select>";

    $setTrackimg = $pdo->prepare("SELECT `Image` FROM `tracks` ORDER BY times_submitted DESC LIMIT 1");
    $setTrackimg->execute();
    $setTrackimg->bindColumn("Image", $firstTrack);
    $setTrackimg->fetch();

    echo "<p><img id='displayTrack' class='preview' src='uploads/tracks/{$firstTrack}'></p>";
}

function brandList($pdo) {
    // Get all cars from the database
    $stmt = $pdo->query("SELECT b.ID, b.Name, COUNT(c.Brand) AS brand_count
                            FROM brands b
                            LEFT JOIN cars c ON b.ID = c.Brand
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
    $pos = $pdo->prepare("SELECT speed FROM topspeed ORDER BY speed DESC LIMIT 1;");
    $pos->execute();
    $row = $pos->fetchAll();
    return $row[0]["speed"];
}

function getAmountOfFasterCars($pdo, $time) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `topspeed` WHERE speed > ?; ");
    $stmt->bindParam(1, $time);
    $stmt->execute();
    $row = $stmt->fetchAll();
    return $row[0]["COUNT(*)"];
}

function getCarInfo($pdo, $carID) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE ID = ?");
    $stmt->bindParam(1, $carID);
    $stmt->execute();
    $row = $stmt->fetchAll();
    return $row;
}

function getCarTopSpeed($pdo, $carID) {
    $stmt = $pdo->prepare("SELECT speed FROM topspeed WHERE carID = ?");
    $stmt->bindParam(1, $carID);
    $stmt->execute();
    $row = $stmt->fetchAll();
    if (count($row) == 0) {
        return "<span class=''>---</span>";
    } else {
        return "<span class='stat'>". $row[0]["speed"]. " </span>km/h";
    }
}
?>