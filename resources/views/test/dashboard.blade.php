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

    <link rel="stylesheet" href="{{ asset('test/css/index.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
