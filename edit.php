<?php
session_start();
include('functions.php');
includeBootstrap();

function generateEditForm($tablename) {
    // Check if ID is set
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']); // Cast ID to integer to avoid SQL injection

        // Get database connection
        $conn = getDbConnection();
        
        // Prepare and execute SQL query
        $stmt = mysqli_prepare($conn, "SELECT * FROM $tablename WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $fields = mysqli_fetch_fields($result);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) {
            echo '<div class="container mt-4"><div class="alert alert-warning" role="alert">No data found.</div></div>';
            exit; // Stop further execution if no data is found
        }

        // Handle form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sanitize and validate input
            $name = htmlspecialchars($_POST['name']);
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];

            // Hash password if it's not empty
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            } else {
                // Retain old password if no new password is provided
                $hashedPassword = $row['password'];
            }

            // Update record in the database
            $conn = getDbConnection();
            $stmt = mysqli_prepare($conn, "UPDATE $tablename SET name = ?, email = ?, password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashedPassword, $id);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['alert'] = '<div class="alert alert-success" role="alert">Record updated successfully!</div>';
            } else {
                $_SESSION['alert'] = '<div class="alert alert-danger" role="alert">Error updating record: ' . mysqli_error($conn) . '</div>';
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . urlencode($id)); // Redirect to refresh page
            exit; // Prevent further code execution
        }

        // Display the form and alerts within the card
        echo '<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">';
        echo '<div class="card p-4" style="width: 350px;">';

        echo '<h5 class="card-title text-center mb-4">Edit Record</h5>';
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . urlencode($id) . '">';

        foreach ($fields as $field) {
            // Check if the field is a primary key or foreign key
            $isPrimaryKey = (bool)($field->flags & MYSQLI_PRI_KEY_FLAG);
            $isForeignKey = (bool)($field->flags & MYSQLI_SET_FLAG);

            // Skip primary key and foreign keys
            if ($isPrimaryKey || $isForeignKey) {
                continue;
            }

            $fieldName = htmlspecialchars($field->name);
            $fieldValue = htmlspecialchars($row[$field->name]);

            // Determine input type
            $inputType = stripos($fieldName, 'password') !== false ? 'password' : 'text';

            // Generate form input for each field with Bootstrap classes
            echo '<div class="form-group">';
            echo '<label for="' . $fieldName . '">' . ucfirst($fieldName) . ':</label>';
            echo '<input type="' . $inputType . '" class="form-control form-control-sm" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $fieldValue . '" required>';
            echo '</div>';
        }

        // Add a hidden input for the primary key
        echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
        echo '<button type="submit" class="btn btn-primary btn-block btn-sm">Update Record</button>';
        echo '</form>';

        // Display alert message if exists
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']); // Clear alert after displaying
        }

        echo '</div>';
        echo '</div>';

    } else {
        echo '<div class="container mt-4"><div class="alert alert-warning" role="alert">ID not found in the URL.</div></div>';
        exit; // Stop further execution if ID is not found
    }
}

// Call the function with the table name
generateEditForm('users');
?>
