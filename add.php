<?php
session_start();
include('functions.php');

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
            $values[] = $_POST[$fieldName] ?? ''; // Fetch value from POST request
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
            echo "Record added successfully!";
        } else {
            echo "Error adding record: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    }

    // Generate form based on fetched fields
    echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?tablename=' . urlencode($tablename) . '">';
    foreach ($fields as $field) {
        $fieldName = htmlspecialchars($field->name);
        $isPrimaryKey = ($field->flags & MYSQLI_PRI_KEY_FLAG);
        $isForeignKey = ($field->flags & MYSQLI_MULTIPLE_KEY_FLAG); // Simplified foreign key check

        // Skip primary keys and foreign keys
        if ($isPrimaryKey || $isForeignKey) {
            continue;
        }

        // Generate form input for each field
        echo '<div>';
        echo '<label for="' . $fieldName . '">' . ucfirst($fieldName) . ':</label>';
        echo '<input type="text" id="' . $fieldName . '" name="' . $fieldName . '" required>';
        echo '</div>';
    }
    echo '<div><input type="submit" value="Add Record"></div>';
    echo '</form>';

    mysqli_close($conn);
}

// Call the function with the table name passed via query string
if (isset($_GET['tablename'])) {
    addValue($_GET['tablename']);
} else {
    echo "Table name not provided.";
}