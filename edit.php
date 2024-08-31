<?php
session_start();
include('functions.php');

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
            echo "No data found.";
            exit; // Stop further execution if no data is found
        }

        // Start form
        echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . urlencode($id) . '">';

        // Generate form fields
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

            echo '<div>';
            echo '<label for="' . $fieldName . '">' . ucfirst($fieldName) . ':</label>';
            echo '<input type="text" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $fieldValue . '">';
            echo '</div>';
        }

        // Add a hidden input for the primary key
        echo '<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">';
        echo '<div><input type="submit" value="Update"></div>';
        echo '</form>';

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
            // can we verify the admin here? Like maybe ask for the password and if the password is correct then only we update?
            // i have made a session of username, now when i click on update, the page will ask me to enter my password, if it is correct then we update?
            $stmt = mysqli_prepare($conn, "UPDATE $tablename SET name = ?, email = ?, password = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashedPassword, $id);

            if (mysqli_stmt_execute($stmt)) {
                echo "Record updated successfully";
            } else {
                echo "Error updating record: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
    } else {
        echo "ID not found in the URL.";
        exit; // Stop further execution if ID is not found
    }
}

// Call the function with the table name
generateEditForm('users');
?>