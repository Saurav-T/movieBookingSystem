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

    // Start table with Bootstrap classes
    echo "<table class='table table-bordered table-hover'>";
    echo "<thead class='thead-light'><tr>";

    // Generate table headers
    foreach ($fields as $field) {
        // Skip primary key and foreign keys if needed
        $isPrimaryKey = (bool)($field->flags & MYSQLI_PRI_KEY_FLAG);
        if ($isPrimaryKey) {
            continue;
        }
        echo "<th>" . htmlspecialchars(ucfirst($field->name), ENT_QUOTES, 'UTF-8') . "</th>";
    }
    echo "<th>Actions</th>";
    echo "</tr></thead>";

    // Generate table rows
    echo "<tbody>";
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

        // Add Edit and Delete links styled as buttons
        $id = urlencode($row['id']); // Assumes 'id' is the primary key
        echo "<td>
                <a href='edit.php?id=$id' class='btn btn-warning btn-sm'>Edit</a>
                <a href='delete.php?id=$id' class='btn btn-danger btn-sm'>Delete</a>
              </td>";

        echo "</tr>";
    }
    echo "</tbody>";

    // End table
    echo "</table>";

    mysqli_free_result($result);
    mysqli_close($conn);
}
function includeBootstrap(){
    echo "
    <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css' rel='stylesheet'>
    <script src='https://code.jquery.com/jquery-3.5.1.slim.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>
    ";
}

?>

