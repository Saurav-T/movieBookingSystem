
<?php
session_start();
include('functions.php');
includeBootstrap();
// Function to dynamically generate a form and insert values into the specified table
function addValue($tablename)
{
    // Get database connection
    $conn = getDbConnection();

    // Fetch table structure
    $sql = "SELECT * FROM $tablename LIMIT 1"; // Use LIMIT 1 to fetch only the structure
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }

    // Fetch the fields of the table
    $fields = mysqli_fetch_fields($result);
    mysqli_free_result($result); // Free result as it's not needed beyond fetching fields

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Prepare arrays to store column names and values
        $columns = [];
        $placeholders = [];
        $values = [];
        $emailValue = '';

        // Loop through fields to collect values from POST, excluding primary and foreign keys
        foreach ($fields as $field) {
            $fieldName = $field->name;
            $isPrimaryKey = ($field->flags & MYSQLI_PRI_KEY_FLAG);
            $isForeignKey = ($field->flags & MYSQLI_MULTIPLE_KEY_FLAG); // Simplified foreign key check

            // Skip primary keys and foreign keys
            if ($isPrimaryKey || $isForeignKey) {
                continue;
            }

            // Collect field names and placeholders for prepared statement
            $columns[] = $fieldName;
            $placeholders[] = '?';

            // Check if the field is a password field and hash it before adding
            if (stripos($fieldName, 'password') !== false) {
                // Hash the password using bcrypt
                $hashedPassword = password_hash($_POST[$fieldName], PASSWORD_BCRYPT);
                $values[] = $hashedPassword;
            } else {
                // Fetch value from POST request
                $value = $_POST[$fieldName] ?? '';
                $values[] = $value;

                // Store the email value to check for duplicacy
                if (stripos($fieldName, 'email') !== false) {
                    $emailValue = $value;
                }
            }
        }

        // Check for duplicate email if an email field is found
        if ($emailValue !== '') {
            $checkEmailSql = "SELECT * FROM $tablename WHERE email = ?";
            $stmt = mysqli_prepare($conn, $checkEmailSql);
            mysqli_stmt_bind_param($stmt, 's', $emailValue);
            mysqli_stmt_execute($stmt);
            $emailCheckResult = mysqli_stmt_get_result($stmt);

            // If an email already exists, display an error message
            if (mysqli_num_rows($emailCheckResult) > 0) {
                $_SESSION['alert'] = '<div class="alert alert-warning" role="alert">This email already exists. Please use a different email.</div>';
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                header("Location: " . $_SERVER['PHP_SELF'] . "?tablename=" . urlencode($tablename));
                exit; // Exit to prevent inserting duplicate record
            }

            mysqli_stmt_close($stmt);
        }

        // Create SQL INSERT query dynamically
        $columnsList = implode(", ", $columns);
        $placeholdersList = implode(", ", $placeholders);
        $stmt = mysqli_prepare($conn, "INSERT INTO $tablename ($columnsList) VALUES ($placeholdersList)");

        // Dynamically generate bind types (e.g., "sss" for three strings)
        $types = str_repeat('s', count($values)); // Assuming all fields are strings for simplicity
        mysqli_stmt_bind_param($stmt, $types, ...$values);

        // Execute statement and check for errors
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['alert'] = '<div class="alert alert-success" role="alert">Record added successfully!</div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-danger" role="alert">Error adding record: ' . mysqli_error($conn) . '</div>';
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: " . $_SERVER['PHP_SELF'] . "?tablename=" . urlencode($tablename));
        exit; // Redirect to display the message
    }

    // Display the form and alerts within the card
    echo '<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">';
    echo '<div class="card p-4" style="width: 350px;">';

    echo '<h5 class="card-title text-center mb-4">Add Record</h5>';
    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?tablename=' . urlencode($tablename) . '">';
    foreach ($fields as $field) {
        $fieldName = htmlspecialchars($field->name);
        $isPrimaryKey = ($field->flags & MYSQLI_PRI_KEY_FLAG);
        $isForeignKey = ($field->flags & MYSQLI_MULTIPLE_KEY_FLAG); // Simplified foreign key check

        // Skip primary keys and foreign keys
        if ($isPrimaryKey || $isForeignKey) {
            continue;
        }

        // Determine input type
        $inputType = stripos($fieldName, 'password') !== false ? 'password' : 'text';

        // Generate form input for each field with Bootstrap classes
        echo '<div class="form-group">';
        echo '<label for="' . $fieldName . '">' . ucfirst($fieldName) . ':</label>';
        echo '<input type="' . $inputType . '" class="form-control form-control-sm" id="' . $fieldName . '" name="' . $fieldName . '" required>';
        echo '</div>';
    }
    echo '<button type="submit" class="btn btn-primary btn-block btn-sm">Add Record</button>';
    echo '</form>';
       // Display alert message if exists
       if (isset($_SESSION['alert'])) {
        echo $_SESSION['alert'];
        unset($_SESSION['alert']); // Clear alert after displaying
    }
    echo '</div>';
    echo '</div>';

}

// Call the function with the table name passed via query string
if (isset($_GET['tablename'])) {
    addValue($_GET['tablename']);
} else {
    echo '<div class="container mt-4"><div class="alert alert-warning" role="alert">Table name not provided.</div></div>';
}
?>

