<?php
session_start();
include('../Functionalities/functions.php');
includeBootstrap(); // Function to include Bootstrap links
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-3">
            <h2 class="mb-0">Users</h2>
            <!-- Add User Button -->
            <button type="button" id="button" class="btn btn-primary btn-sm" onclick="window.location.href='add.php?tablename=users';">Add User</button>
        </div>
        <!-- Display the generated table -->
        <?php generateTable('users'); ?>
    </div>
</body>
</html>