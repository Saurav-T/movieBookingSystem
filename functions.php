<?php 

// Establish Database Connection
function getDbConnection() {
    $servername = "localhost"; // Database server name
    $username = "root";        // Database username
    $password = "";    // Database password
    $dbname = "mbs"; // Database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    // Returns Connection Object
    return $conn;
}

function generateTable($tablename) {
    $conn = getDbConnection();

    // Escaping the table name to prevent SQL injection (though not foolproof)
    $tablename = mysqli_real_escape_string($conn, $tablename);

    $sql = "SELECT * FROM $tablename";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }

    // Get table fields
    $fields = mysqli_fetch_fields($result);

    // Start table
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>";

    // Generate table headers
    foreach ($fields as $field) {
        // Skip primary key and foreign keys if needed
        $isPrimaryKey = (bool)($field->flags & MYSQLI_PRI_KEY_FLAG);
        if ($isPrimaryKey) {
            continue;
        }
        echo "<th>" . htmlspecialchars(ucfirst($field->name), ENT_QUOTES, 'UTF-8') . "</th>";
    }
    echo "<th>Edit</th>";
    echo "<th>Delete</th>";
    echo "</tr>";

    // Generate table rows
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";

        foreach ($fields as $field) {
            // Skip primary key and foreign keys if needed
            $isPrimaryKey = (bool)($field->flags & MYSQLI_PRI_KEY_FLAG);
            if ($isPrimaryKey) {
                continue;
            }
            $fieldName = $field->name;
            echo "<td>" . htmlspecialchars($row[$fieldName], ENT_QUOTES, 'UTF-8') . "</td>";
        }

        // Add Edit and Delete links
        $id = urlencode($row['id']); // Assumes 'id' is the primary key
        echo "<td><a href='edit.php?id=$id'>Edit</a></td>";
        echo "<td><a href='delete.php?id=$id'>Delete</a></td>";

        echo "</tr>";
    }

    // End table
    echo "</table>";

    mysqli_free_result($result);
    mysqli_close($conn);
}

?>

