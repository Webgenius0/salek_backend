<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Learning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('test/css/welcome.css') }}">
</head>

<body>

    <header>
        <h1>Welcome to Our Learning Platform</h1>
        <p>Explore, purchase, and master courses to elevate your skills</p>
    </header>

    <div class="main-container">
        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('login') }}">Login</a>
            <a href="{{ route('register') }}">Sign Up</a>
        </div>

        <!-- Courses Section -->
        <div class="course-section">
            <div class="course-card">
                <img src="https://via.placeholder.com/300x200" alt="Course 1">
                <h3>Web Development Mastery</h3>
                <p>Learn the latest web technologies and frameworks to build modern websites.</p>
                <a href="/course/1">View Course</a>
            </div>

            <div class="course-card">
                <img src="https://via.placeholder.com/300x200" alt="Course 2">
                <h3>Data Science Essentials</h3>
                <p>Unlock the power of data with this comprehensive data science course.</p>
                <a href="/course/2">View Course</a>
            </div>

            <div class="course-card">
                <img src="https://via.placeholder.com/300x200" alt="Course 3">
                <h3>Graphic Design Basics</h3>
                <p>Start your journey into the world of design with this beginner-friendly course.</p>
                <a href="/course/3">View Course</a>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Our Learning Platform. All Rights Reserved.</p>
    </footer>

</body>

</html>
