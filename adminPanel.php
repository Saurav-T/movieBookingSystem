<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap Admin Panel</title>
    <!-- Bootstrap 4.5 CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            overflow: hidden;
        }
        .sidebar {
            height: 100%;
            min-width: 200px;
            background-color: #343a40;
            color: #fff;
        }
        .sidebar .nav-link {
            color: #fff;
            transition: background-color 0.3s;
        }
        .sidebar .nav-link:hover {
            background-color: #495057;
        }
        .content-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar d-flex flex-column p-3">
        <h4 class="text-white">Admin Panel</h4>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="test1.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="users.php">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-page="settings.php">Settings</a>
            </li>
        </ul>
    </nav>

    <!-- Content Area -->
    <div class="content-area" id="content-area">
        <!-- Dynamic content will be loaded here -->
    </div>

    <!-- Bootstrap 4.5 JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // JavaScript to handle sidebar clicks and load content dynamically
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const page = this.getAttribute('data-page'); // Get the data-page attribute value
                loadContent(page); // Call the function to load the content
            });
        });

        function loadContent(page) {
            fetch(page) // Fetch the page using the path provided in data-page
                .then(response => response.text())
                .then(data => {
                    document.getElementById('content-area').innerHTML = data; // Load the content into the content area
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    document.getElementById('content-area').innerHTML = '<p class="text-danger">Error loading page.</p>';
                });
        }

        // Optionally, load a default page on first load
        window.onload = function () {
            loadContent('dashboard.php');
        };
    </script>

</body>
</html>