<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Learning Platform</title>
    <link rel="shortcut icon" href="{{ asset('test/logos/man.png') }}" type="image/x-icon">
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
            @if (count($courses) > 0)
                @foreach ($courses as $course)
                    <div class="course-card">
                        <img src="{{ $course->cover_photo }}" alt="Course 1">
                        <h3>{{ $course->name }}</h3>
                        <p>{{ $course->description }}</p>
                        <a href="/course/1">View Course</a>
                    </div>
                @endforeach
            @else
                <div class="course-card">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQPZqU454l75xWEFMzbL5xfxPpL1ndiVA15UQ&s"
                        alt="Course 1">
                    <h3>Arabic Quran Study</h3>
                    <p>Immerse yourself in the study of the Quran while learning Arabic for a deeper understanding of
                        its teachings.</p>
                    <a href="https://al-dirassa.com/en/free-quranic-arabic-language-online-courses/">View Course</a>
                </div>

                <div class="course-card">
                    <img src="https://hidayahnetwork.com/wp-content/uploads/2020/10/Tips-To-Learn-Arabic-Fast.webp"
                        alt="Course 2">
                    <h3>Arabic Language Basics</h3>
                    <p>Start your journey in learning the Arabic language with essential grammar and vocabulary for
                        daily use.</p>
                    <a href="https://www.transparent.com/learn-arabic/phrases">View Course</a>
                </div>

                <div class="course-card">
                    <img src="https://qalamaurkagaz.com/wp-content/uploads/2020/08/banner2-1.jpg" alt="Course 3">
                    <h3>Introduction to Arabic Calligraphy</h3>
                    <p>Discover the beauty of Arabic calligraphy by learning the basics of traditional writing styles
                        and techniques.</p>
                    <a href="https://www.youtube.com/watch?v=8ROkgi6iV7I">View Course</a>
                </div>

            @endif
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Our Learning Platform. All Rights Reserved.</p>
    </footer>

</body>

</html>
