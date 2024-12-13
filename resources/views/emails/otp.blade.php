<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 20px;
        }

        .email-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .email-body {
            padding: 30px;
            text-align: center;
            line-height: 1.6;
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #4CAF50;
            background-color: #eaf5ea;
            padding: 15px 25px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .email-footer {
            background-color: #f4f7fc;
            text-align: center;
            padding: 15px;
            color: #888;
            font-size: 12px;
        }

        .email-footer a {
            color: #4CAF50;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Your OTP Code</h1>
        </div>
        <div class="email-body">
            <p>Hello,</p>
            <p>We received a request to log in to your account. To complete your login, please use the one-time password
                (OTP) below:</p>

            <div class="otp-code">
                {{ $otp }}
            </div>

            <p>This code is valid for a limited time. Please use it within the next 10 minutes.</p>
            <p>If you did not request this, please ignore this email.</p>
        </div>
        <div class="email-footer">
            <p>Thank you for choosing our service!</p>
            <p>If you have any questions, feel free to <a href="mailto:support@yourdomain.com">contact us</a>.</p>
        </div>
    </div>
</body>

</html>
