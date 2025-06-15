<?php require_once("inc/header.php");

// Define log file
$log_file = "error_log_tracks.txt";

// Log function
function log_error($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

log_error("Track creation page accessed");

if (isset($_GET['error'])) {
    echo "Track " . trackError($_GET['error']);
}

if ((int)$_SESSION["user_id"] == 1) {
    ?>
    <form method="post" action="" enctype="multipart/form-data" id="uploadTrack" class="card p-4 mb-4">
        <h2 class="mb-3">Add New Track</h2>
        
        <div class="mb-3">
            <label for="name" class="form-label">Track Name:</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="country" class="form-label">Country:</label>
            <input type="text" id="country" name="country" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="length" class="form-label">Length in Meters:</label>
            <input type="number" id="length" name="length" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="imageInput" class="form-label">Image:</label>
            <input type="file" id="imageInput" onchange="previewImage(event)" name="image" accept="image/*" class="form-control" required>
            <div class="mt-3">
                <img id="preview" src="#" alt="Preview Image" class="img-fluid" style="max-width: 300px; display: none;">
            </div>
        </div>
        
        <div>
            <input type="submit" value="Create Track" class="btn btn-primary">
        </div>
    </form>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const file = event.target.files[0];
            
            if (file) {
                preview.style.display = 'block';
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>

    <?php
}
// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log form submission
    log_error("Form submitted with POST data: " . json_encode($_POST));
    
    try {
        // Get the form data
        $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
        $country = filter_input(INPUT_POST, "country", FILTER_SANITIZE_SPECIAL_CHARS);
        $length = filter_input(INPUT_POST, "length", FILTER_SANITIZE_SPECIAL_CHARS);
        
        // Log received data
        log_error("Filtered input data - Name: $name, Country: $country, Length: $length");
        
        // Image validation logging
        if (!isset($_FILES['image'])) {
            log_error("Error: No image file uploaded");
        } else {
            $image = $_FILES['image'];
            log_error("Image details: " . json_encode($image));
            
            // Check image error code
            if ($image['error'] !== 0) {
                log_error("Upload error code: " . $image['error']);
            }
        }

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

        // Log validation errors if any
        if (!empty($errors)) {
            log_error("Validation errors: " . json_encode($errors));
        }

        // If there are no validation errors, upload the new track
        if (empty($errors)) {
            log_error("Validation passed, proceeding with file upload");
            
            // Check upload directory permissions
            $target_dir = "uploads/tracks/";
            if (!is_dir($target_dir)) {
                log_error("Directory does not exist: $target_dir");
                mkdir($target_dir, 0755, true);
                log_error("Created directory: $target_dir");
            }
            
            if (!is_writable($target_dir)) {
                log_error("Directory not writable: $target_dir");
                $errors[] = "Server configuration issue: Upload directory not writable.";
            } else {
                log_error("Directory is writable: $target_dir");
            }
            
            try {
                $image_name = renameFile($image);
                log_error("New image name generated: $image_name");
                
                $target_file = $target_dir . $image_name;
                
                // Attempt to move the file
                if (move_uploaded_file($image["tmp_name"], $target_file)) {
                    log_error("File successfully uploaded to: $target_file");
                } else {
                    log_error("Failed to move uploaded file. Error: " . error_get_last()['message']);
                    $errors[] = "Failed to upload image file.";
                }
                
                if (empty($errors)) {
                    // Database insertion
                    try {
                        log_error("Preparing database insertion");
                        // Prepare a PDO statement to insert the new track into the database
                        $stmt = $pdo->prepare("INSERT INTO tracks (Name, Country, Image, Length) VALUES (:name, :country, :image, :length)");
                        $stmt->bindParam(":name", $name);
                        $stmt->bindParam(":country", $country);
                        $stmt->bindParam(":image", $image_name);
                        $stmt->bindParam(":length", $length);
                        
                        if ($stmt->execute()) {
                            log_error("Database insertion successful");
                            // Redirect the user back to the page that displays all tracks
                            log_error("Redirecting to tracks page");
                            header("Location: tracks");
                            exit();
                        } else {
                            log_error("Database insertion failed: " . json_encode($stmt->errorInfo()));
                            $errors[] = "Failed to add track to database.";
                        }
                    } catch (PDOException $e) {
                        log_error("Database error: " . $e->getMessage());
                        $errors[] = "Database error: " . $e->getMessage();
                    }
                }
            } catch (Exception $e) {
                log_error("File processing error: " . $e->getMessage());
                $errors[] = "Error processing file: " . $e->getMessage();
            }
        }
    } catch (Exception $e) {
        log_error("Unexpected error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        $errors[] = "An unexpected error occurred: " . $e->getMessage();
    }
    
    // Display errors if any occurred
    if (!empty($errors)) {
        echo "<ul class='text-danger'>";
        foreach ($errors as $error) {
            echo '<li class="error">' . $error . '</li>';
        }
        echo "</ul>";
    }
} ?>


<?php require_once ("inc/footer.php"); ?>