<?php require_once("inc/header.php");

if (isset($_GET['error'])) {
    echo "Brand " . trackError($_GET['error']);
}

if ((int)$_SESSION["user_id"] == 1) {
    ?>
    <form method="post" action="" enctype="multipart/form-data" id="uploadBrand">
        <label for="name">Brand Name: </label>
        <input type="text" id="name" name="name" required>

        <label for="image">Image:</label>
        <input type="file" id="imageInput" onchange="previewImage(event)" name="image" accept="image/*" required>
        <img id="preview" src="#" alt="Preview Image" class="previewCarImage">
        <br><br>
        <input type="submit" value="Submit">
    </form>

    <?php
}
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $brandLogo = $_FILES['image'];

    $errors = array();

    // Name checks
    if (empty($name)) {
        $errors[] = "Name is required.";
    } else {
        $checkBrandName = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE name = :name");
        $checkBrandName->bindParam(":name", $name, PDO::PARAM_STR);
        $checkBrandName->execute();
        $count = $checkBrandName->fetchColumn();

        if ($count > 0) {
            $errors[] = "This brand already exists in the database!";
        }
    }

    // Image checks
    if (empty($brandLogo)) {
        $errors[] = "An image file is required.";
    } else {
        if ($brandLogo["type"] == "image/png"){
            list($width, $height) = getimagesize($brandLogo["tmp_name"]);

            // if ($width !== 400 && $height !== 400) {
            //     $errors[] = "Image resolution must bee 400x400!";
            // }
        } else {
            $errors[] = "Image must be a .png image!";
        }
    }

    // If there are no validation errors, upload the new brand
    if (empty($errors)) {
        $extension = pathinfo($brandLogo['name'], PATHINFO_EXTENSION);
        $image_name = cleanString($name). '.' . $extension;

        $target_dir = "logo/";
        $target_file = $target_dir . $image_name;
        move_uploaded_file($brandLogo["tmp_name"], $target_file);

        // Prepare a PDO statement to insert the new brand into the database
        $stmt = $pdo->prepare("INSERT INTO brands (Name, logo) VALUES (:name, :image)");
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":image", $image_name);
        $stmt->execute();

        // Redirect the user back to the page that displays all tracks
        echo "<h3 class='text-success'>Successfully created new brand: <b>{$name}</b></h3>";
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