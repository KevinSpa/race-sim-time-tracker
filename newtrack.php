<?php require_once("inc/header.php");

if (isset($_GET['error'])) {
    echo "Track " . trackError($_GET['error']);
}

if ((int)$_SESSION["user_id"] == 1) {
    ?>
    <form method="post" action="" enctype="multipart/form-data" id="uploadTrack">
        <label for="name">Track Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="country">Country:</label>
        <input type="text" id="country" name="country" required>

        <label for="length">Length in Meters:</label>
        <input type="number" id="length" name="length" required>

        <label for="image">Image:</label>
        <input type="file" id="imageInput" onchange="previewImage(event)" name="image" accept="image/*" required>
        <img id="preview" src="#" alt="Preview Image" class="w-100">
        <br><br>
        <input type="submit" value="Submit">
    </form>

    <?php
}
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $country = filter_input(INPUT_POST, "country", FILTER_SANITIZE_SPECIAL_CHARS);
    $length = filter_input(INPUT_POST, "length", FILTER_SANITIZE_SPECIAL_CHARS);
    $image = $_FILES['image'];

    // Validate the input data
    $errors = array();
    if (empty($name)) {
        $errors[] = "Name is required.";
    }
    if (empty($country)) {
        $errors[] = "Country is required.";
    }
    if (empty($length) || !is_numeric($length)) {
        $errors[] = "Length must be a numeric value.";
    }
    if (empty($image)) {
        $errors[] = "An image file is required.";
    }

    // If there are no validation errors, upload the new track
    if (empty($errors)) {
        $image_name = renameFile($image);
        $target_dir = "uploads/tracks/";
        $target_file = $target_dir . $image_name;
        move_uploaded_file($image["tmp_name"], $target_file);

        // Prepare a PDO statement to insert the new track into the database
        $stmt = $pdo->prepare("INSERT INTO Tracks (Name, Country, Image, Length) VALUES (:name, :country, :image, :length)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":country", $country);
        $stmt->bindParam(":image", $image_name);
        $stmt->bindParam(":length", $length);
        $stmt->execute();

        // Redirect the user back to the page that displays all tracks
        header("Location: tracks");
        exit();
    } else {
        // Display errors
        echo "<ul class='text-danger'>";
        foreach ($errors as $error) {
            echo '<li class="error">' . $error . '</li>';
        }
        echo "</ul>";
    }
} ?>


<?php require_once ("inc/footer.php"); ?>