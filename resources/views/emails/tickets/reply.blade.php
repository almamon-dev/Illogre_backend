<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
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
            padding: 50px 60px;
        }
        .logo {
            font-size: 32px;
            font-weight: 700;
            color: #14a800;
            margin-bottom: 45px;
            letter-spacing: -1.5px;
        }
        .heading {
            font-size: 24px;
            font-weight: 400;
            color: #001e00;
            margin-bottom: 30px;
            line-height: 1.3;
        }
        .text {
            font-size: 16px;
            color: #001e00;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .text p {
            margin-top: 0;
            margin-bottom: 1em;
        }
        .text p:last-child {
            margin-bottom: 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 16px;
            color: #001e00;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="logo">{{ config('app.name') }}</div>
            
            <div class="heading">Hello {{ $ticket->customer_name }},</div>

            <div class="text">
                {!! $replyText !!}
            </div>

            <div class="footer">
                Thanks,<br>
                The {{ config('app.name') }} Team
            </div>
        </div>
    </div>
</body>
</html>
