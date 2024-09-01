<?php
session_start();
include('../Functionalities/functions.php');
includeBootstrap();

function generateEditForm($tablename) {
    // Check if the 'id' parameter is set in the URL
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']); // Convert the 'id' parameter to an integer to avoid security issues like SQL injection

        // Establish a connection to the database
        $conn = getDbConnection();
        
        // Prepare and execute the SQL query to fetch the record by ID
        $stmt = mysqli_prepare($conn, "SELECT * FROM $tablename WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_fields($result);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // If no data is found, display a warning message and stop further execution
        if (!$row) {
            echo '<div class="container mt-4"><div class="alert alert-warning" role="alert">No data found.</div></div>';
            exit;
        }

        // Handle form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sanitize and validate user input
            $name = htmlspecialchars($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? ''; // Check if a new password was provided

            // Hash the password only if a new one is entered
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            } else {
                // Keep the existing password if the user did not enter a new one
                $hashedPassword = $row['password'];
            }

            // Update the record in the database
            $conn = getDbConnection();
            $stmt = mysqli_prepare($conn, "UPDATE $tablename SET name = ?, email = ?, password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashedPassword, $id);

            // Check if the update was successful and set an appropriate session message
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['alert'] = '<div class="alert alert-success" role="alert">Record updated successfully!</div>';
            } else {
                $_SESSION['alert'] = '<div class="alert alert-danger" role="alert">Error updating record: ' . mysqli_error($conn) . '</div>';
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            // Redirect to the same page to refresh the data and avoid resubmission on page reload
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($id));
            exit; // Prevent any further code from running after the redirect
        }

        // Display the edit form inside a styled card
        echo '<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">';
        echo '<div class="card p-4" style="width: 350px;">';

        echo '<h5 class="card-title text-center mb-4">Edit Record</h5>';
        echo '<form id="editForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . urlencode($id) . '">';

        // Loop through each field in the database row to generate form inputs
        foreach ($fields as $field) {
            // Check if the field is a primary key or a foreign key to skip those fields
            $isPrimaryKey = (bool)($field->flags & MYSQLI_PRI_KEY_FLAG);
            $isForeignKey = (bool)($field->flags & MYSQLI_SET_FLAG);

            // Skip primary and foreign key fields as they shouldn't be edited
            if ($isPrimaryKey || $isForeignKey) {
                continue;
            }

            $fieldName = htmlspecialchars($field->name);
            $fieldValue = htmlspecialchars($row[$field->name]);

            // Skip the password field from being displayed directly in the form
            if (stripos($fieldName, 'password') !== false) {
                continue;
            }

            // Generate a standard text input for each field
            echo '<div class="form-group">';
            echo '<label for="' . $fieldName . '">' . ucfirst($fieldName) . ':</label>';
            echo '<input type="text" class="form-control form-control-sm" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $fieldValue . '" required>';
            echo '</div>';
        }

        // Include a hidden input to hold the primary key value
        echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
        // The main button to submit the form and update the record
        echo '<button type="submit" class="btn btn-primary btn-block btn-sm" onclick="return confirmUpdate()">Update Record</button>';

        // Add a button to reveal the hidden password change field below the update button
        echo '<button type="button" class="btn btn-link p-0 mt-2" id="changePasswordBtn">Change Password</button>';
        // The hidden password field that is shown only if the user clicks "Change Password"
        echo '<div id="passwordField" class="form-group d-none">';
        echo '<label for="password">New Password:</label>';
        echo '<input type="password" class="form-control form-control-sm" id="password" name="password">';
        echo '</div>';

        echo '</form>';

        // Display any alert messages stored in the session
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']); // Clear the alert after displaying it
        }

        echo '</div>';
        echo '</div>';

        // Add JavaScript to handle the confirmation prompt and toggle password field visibility
        echo '<script>
                // Function to show a confirmation dialog when updating the record
                function confirmUpdate() {
                    return confirm("Are you sure you want to update the record?");
                }

                // Toggle the visibility of the password field when the "Change Password" button is clicked
                document.getElementById("changePasswordBtn").addEventListener("click", function() {
                    var passwordField = document.getElementById("passwordField");
                    if (passwordField.classList.contains("d-none")) {
                        passwordField.classList.remove("d-none");
                    } else {
                        passwordField.classList.add("d-none");
                    }
                });
              </script>';

    } else {
        // Display a warning message if the ID is not found in the URL
        echo '<div class="container mt-4"><div class="alert alert-warning" role="alert">ID not found in the URL.</div></div>';
        exit; // Stop further execution if the ID is missing
    }
}

// Call the function to generate the form for the 'users' table
generateEditForm('users');
?>
