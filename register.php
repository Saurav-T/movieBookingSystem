<?php
include('Functionalities/functions.php'); // Correct the path if needed

$error = '';
$determiningNumber = 0; // Initialize

if (isset($_POST['submit'])) {
    // Sanitize input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Get database connection
    $conn = getDbConnection();

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
        $error = "Could not connect to the database. Try again later.";
        $determiningNumber = 0;
    } else {
        // Prepare the SELECT statement to check if the email already exists
        $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists. Please try another.";
            $determiningNumber = -1;
        } else {
            // Prepare the INSERT statement
            $stmt = $conn->prepare("INSERT INTO admin (email, username, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $email, $username, $hashed_password);

            if ($stmt->execute()) {
                $msg = "Registration Successful. Redirect to <a href='index.php'>Login</a>?";
                $determiningNumber = 1;
            } else {
                $error = "Error: " . $stmt->error;
                $determiningNumber = -1; // Set to -1 on error
            }
        }

        // Close the prepared statement and the connection
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
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
            <h2 class="form-title text-center">Register</h2>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <?php if ($determiningNumber == -1): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif;?>
                <?php if ($determiningNumber == 1):?>
                    <div class="alert alert-success" role="alert">
                        <?php echo htmlspecialchars($msg);?>
                    </div>
                <?php endif;?>
                <button type="submit" name="submit" class="btn btn-primary btn-block">Submit</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
