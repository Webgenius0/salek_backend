<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Admin Panel</title>
    <link rel="shortcut icon" href="{{ asset('test/logos/man.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@300;400&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #2f3542;
            color: white;
            padding-top: 20px;
            transition: 0.3s;
        }

        .sidebar a {
            display: block;
            color: #fff;
            padding: 15px 20px;
            text-decoration: none;
            font-size: 18px;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #1e2a35;
            color: #4CAF50;
        }

        .sidebar .logo {
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .sidebar .logo span {
            color: #4CAF50;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .top-bar .search-bar input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .top-bar .user-actions {
            display: flex;
            align-items: center;
        }

        .top-bar .user-actions a {
            margin-left: 20px;
            font-size: 18px;
            color: #333;
            text-decoration: none;
        }

        .top-bar .user-actions a:hover {
            color: #4CAF50;
        }

        /* Dashboard Widgets */
        .dashboard-widgets {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .widget {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .widget h3 {
            margin-top: 0;
            color: #4CAF50;
            font-size: 20px;
        }

        .widget p {
            font-size: 16px;
            color: #666;
        }

        .widget .stats {
            font-size: 32px;
            color: #333;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #2f3542;
            color: white;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <span>My</span> Dashboard
        </div>
        <a href="#">Dashboard</a>
        <a href="#">Users</a>
        <a href="#">Courses</a>
        <a href="#">Reports</a>
        <a href="#">Settings</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="search-bar">
                <input type="text" placeholder="Search...">
            </div>
            <div class="user-actions">
                <a href="#">Notifications</a>
                <a href="#">Profile</a>
                <a href="{{ route('logout') }}">Logout</a>
            </div>
        </div>

        <!-- Dashboard Widgets -->
        <div class="dashboard-widgets">
            <div class="widget">
                <h3>Total Users</h3>
                <p>Number of active users</p>
                <div class="stats">1,200</div>
            </div>
            <div class="widget">
                <h3>Total Courses</h3>
                <p>Number of courses available</p>
                <div class="stats">25</div>
            </div>
            <div class="widget">
                <h3>Revenue</h3>
                <p>Total revenue generated</p>
                <div class="stats">$15,300</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; 2024 My Dashboard. All rights reserved.
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", (event) => {
            const userId = @json($userId);
            Echo.private(`message-sent.${userId}`)
                .listen('MessageSend', (event) => {
                    console.log('Find opponent:' + event.message);
                    alert(
                        `Message from ${event.sender_id} to ${event.receiver_id}: ${event.message} at ${event.timestamp}`
                    );
                });
        });
    </script>

</body>

</html>
