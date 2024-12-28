<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Learning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('test/css/signup.css') }}">
</head>

<body>

    <div class="signup-container">
        <h2>Create an Account</h2>
        <form action="{{ route('register.store') }}" method="POST">
            @csrf
            <div>
                <input type="text" name="name" placeholder="Full Name" required>
                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <input type="email" name="email" placeholder="Email" required>
                @error('email')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required>
                @error('password')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            <div>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
                @error('password_confirmation')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="{{ route('login') }}">Login here</a>.</p>
    </div>

</body>

</html>
