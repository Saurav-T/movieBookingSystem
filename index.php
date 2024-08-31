<?php
session_start();
// Include the file containing the database connection function
include('functions.php');

// Initialize an empty error variable
$error = '';

if (isset($_POST['submit'])) {
    // Sanitize input data
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

    // Get the database connection using the existing function
    $conn = getDbConnection();

    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanitize the table and column names to prevent SQL injection
    $tablename = 'admin';
    $usernameColumn = 'username';
    $passwordColumn = 'password';

    // Prepare the SQL statement to fetch the stored password hash
    $sql = "SELECT $passwordColumn FROM $tablename WHERE $usernameColumn = ?";
    $stmt = $conn->prepare($sql);

    // Check if the prepare statement was successful
    if (!$stmt) {
        $conn->close();
        die("Prepare failed: " . $conn->error);
    }

    // Bind the username parameter to the placeholder
    $stmt->bind_param('s', $username);

    // Execute the statement
    $stmt->execute();

    // Store the result
    $stmt->store_result();

    // Bind the result to a variable
    $stmt->bind_result($storedPasswordHash);

    // Initialize password validity flag
    $isPasswordValid = false;

    // Fetch the result and verify the password
    if ($stmt->fetch()) {
        $isPasswordValid = password_verify($password, $storedPasswordHash);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Handle login success or failure
    if ($isPasswordValid) {
        // Set the session variable
        $_SESSION['username'] = $username;
        // Redirect to the dashboard page
        header("Location: adminPanel.php");
        exit; // Ensure no further code is executed after redirection
    } else {
        $error = "Invalid credentials. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 400px;
        }
        .form-container {
            padding: 2rem;
            background: #ffffff;
            border-radius: .25rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-title {
            margin-bottom: 1.5rem;
        }
        .error-message {
            color: #dc3545; /* Bootstrap's danger color */
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title text-center">Login</h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <button type="submit" name="submit" class="btn btn-primary btn-block">Log In</button>
                <p class="mt-3 text-center">Don't have an account? <a href="register.php">Sign Up</a></p>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>