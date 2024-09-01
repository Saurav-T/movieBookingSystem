<?php
session_start();
include('../Functionalities/functions.php');

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Convert the 'id' parameter to an integer to prevent SQL injection

    // Get a connection to the database
    $conn = getDbConnection();

    // Prepare the SQL statement to delete the record with the specified ID
    $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    // Execute the statement and check if the deletion was successful
    if (mysqli_stmt_execute($stmt)) {
        // If successful, set a success message
        $message = '<div class="alert alert-success" role="alert">Record deleted successfully!</div>';
    } else {
        // If there's an error, set an error message
        $message = '<div class="alert alert-danger" role="alert">Error deleting record: ' . mysqli_error($conn) . '</div>';
    }

    // Close the statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    // If the 'id' parameter is missing, set a warning message
    $message = '<div class="alert alert-warning" role="alert">No record ID specified for deletion.</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Record</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Display the message (success or error) -->
        <?= isset($message) ? $message : ''; ?>
        
        <!-- Go Back button to navigate back to the table page -->
        <a href="adminPanel.php?page=users.php" class="btn btn-primary">Go Back</a> <!-- Replace 'admin_panel.php' with the correct admin panel page if needed -->
    </div>

    <!-- Optional Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
