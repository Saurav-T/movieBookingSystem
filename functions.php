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

function generatetable($tablename) {
    $conn = getDbConnection();

    // Escaping the table name to prevent SQL injection (though not foolproof)
    $tablename = mysqli_real_escape_string($conn, $tablename);

    $sql = "SELECT * FROM $tablename";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }

    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Name</th>";
    echo "<th>Email</th>";
    echo "<th>Password</th>";
    echo "<th>Edit</th>";
    echo "<th>Delete</th>";
    echo "</tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td>" . htmlspecialchars($row['password'], ENT_QUOTES, 'UTF-8') . "</td>";
        echo "<td><a href='edit.php?id=" . urlencode($row['id']) . "'>Edit</a></td>";
        echo "<td><a href='delete.php?id=" . urlencode($row['id']) . "'>Delete</a></td>";
        echo "</tr>";
    }

    echo "</table>";

    mysqli_free_result($result);
    mysqli_close($conn);
}

?>