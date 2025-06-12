<?php require_once("inc/header.php");

if (isset($_GET['error'])) {
    echo "Car " . trackError($_GET['error']);
}

if ((int)$_SESSION["user_id"] == 1) {
    ?>
    <form method="post" action="" enctype="multipart/form-data" id="uploadTrack">
    <h4>Add new car:</h4>
    <div class="row">
        <div class="col-8">
            <?php brandList($pdo); ?>
        </div>
        <div class="col-4">
            <input type="checkbox" name="checknewbrand" id="checknewbrand"> Add a new brand?
            <a href="newbrand" id="newbrand" style="display: none" class="btn btn-outline-green mt-2">Add new brand</a>
        </div>
    </div>

    <label for="name">Name:</label>
    <input type="text" id="name" name="name">

    <label for="image">Image:</label>
    <input type="file" id="imageInput" onchange="previewImage(event)" name="image">
    <img id="preview" src="#" alt="Preview Image" class="previewCarImage">
    <br>
    <input type="submit" value="Submit">

    <?php
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if form is submitted
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $brand = filter_input(INPUT_POST, "brand_id", FILTER_SANITIZE_SPECIAL_CHARS);
    $image = $_FILES['image'];

    // Validate form data
    $errors = validate_car_form($name, $brand, $image, $pdo);

    if (empty($errors)) {
        // Rename and move uploaded file
        $image_name = renameFile($image);
        $target_dir = "uploads/cars/";
        $target_file = $target_dir . $image_name;
        move_uploaded_file($image["tmp_name"], $target_file);

        // Insert data into database
        $sql = "INSERT INTO cars (name, brand, image) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $brand, $image_name]);

        echo "<p class='text-success'>Car successfully created!</p>";
    } else {
        // Display errors
        echo "<ul class='text-danger'>";
        foreach ($errors as $error) {
            echo '<li class="error">' . $error . '</li>';
        }
        echo "</ul>";
    }
}
?>
    </form>

    <script type="text/javascript">
        const checkbox = document.getElementById('checknewbrand');
        const inputContainer = document.getElementById('newbrand');
        const brandselect = document.getElementById('brand_id');

        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                inputContainer.style.display = 'block';
                brandselect.disabled = true;
            } else {
                inputContainer.style.display = 'none';
                brandselect.disabled = false;
            }
        });
    </script>

<?php require_once("inc/footer.php"); ?>