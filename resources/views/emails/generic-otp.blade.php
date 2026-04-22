<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
            font-family: "Neuemontreal", "Helvetica Neue", Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f2f2f2;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #d5e0d5;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: #14a800;
            margin-bottom: 25px;
            letter-spacing: -1px;
        }
        .heading {
            font-size: 20px;
            font-weight: 600;
            color: #001e00;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .text {
            font-size: 15px;
            color: #001e00;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .footer-text {
            font-size: 14px;
            color: #5e6d55;
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 15px;
        }
        .link {
            color: #14a800;
            text-decoration: none;
            font-weight: 500;
        }
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="logo">tixolve</div>
            
            <div class="heading">Verify your identity</div>

            <div class="text">Hi {{ $name }},</div>

            <div class="text">
                To help keep your Tixolve account secure, please use the following verification code to complete your action. This code is valid for <strong>60 minutes</strong>.
                <div style="margin-top: 10px; font-size: 24px; font-weight: 700; color: #14a800; letter-spacing: 2px;">{{ $otp }}</div>
            </div>

            <div class="text" style="font-size: 14px; color: #5e6d55;">
                <strong>Didn't request this?</strong> If you didn't take this action, please <a href="#" class="link">contact support</a> immediately to secure your account.
            </div>

            <div class="footer-text">
                Thanks for choosing Tixolve,<br>
                <strong>The Tixolve Team</strong>
            </div>
        </div>
    </div>
</body>
</html>
