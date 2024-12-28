<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Our Learning Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Open+Sans:wght@300;400&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('test/css/login.css') }}">
</head>

<body>

    <div class="login-container">
        <div class="login-box">
            <h2>Login to Your Account</h2>

            @if (session('message'))
                <div style="color: red; margin-bottom: 15px;">
                    {{ session('message') }}
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf
                @if ($errors->any())
                    <div style="color: red; margin-bottom: 15px;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <input type="text" name="email" placeholder="Email" value="{{ old('email') }}" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="submit" value="Login">
            </form>

            <a href="/forgot-password" class="forgot-password">Forgot Password?</a>

            <div class="signup-link">
                Don't have an account? <a href="{{ route('register') }}">Sign up here</a>
            </div>
        </div>
    </div>

</body>

</html>
