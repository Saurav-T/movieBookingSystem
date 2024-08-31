<?php
include('functions.php');

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
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    echo "ID not found in the URL.";
    exit; // Stop further execution if ID is not found
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . urlencode($id); ?>">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
        
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
        
        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        
        <input type="submit" id="submit" name="submit" value="Submit">
    </form>

    <?php
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
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
    ?>
</body>
</html>
