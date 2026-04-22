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
        .support-link {
            color: #14a800;
            text-decoration: underline;
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
            <div class="logo">tixolve</div>
            
            <div class="heading">Invitation to join the team as a Support Agent.</div>

            <div class="text">Hello,</div>

            <div class="text">
                You've been invited by <strong>{{ $managerName }}</strong> to join the team on Tixolve as a <strong>Support Agent</strong>. 
                Please click the button below to accept the invitation and activate your account.
            </div>

            <div style="text-align: center; margin: 35px 0;">
                <a href="{{ $acceptUrl }}" 
                   style="background-color: #14a800; color: #ffffff; padding: 14px 30px; text-decoration: none; font-size: 16px; font-weight: 500; border-radius: 25px; display: inline-block;">
                    Accept Invitation
                </a>
            </div>

            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #eeeeee;">
                <div class="text" style="font-size: 14px; margin-bottom: 10px; color: #666;">Once activated, use these credentials:</div>
                <div class="text" style="margin-bottom: 8px;"><strong>Email:</strong> {{ $email }}</div>
                <div class="text" style="margin-bottom: 0;"><strong>Temporary Password:</strong> {{ $temporaryPassword }}</div>
            </div>

            <div class="text">
                Please contact <a href="#" class="support-link">Tixolve Support</a> if you did not authorize this change or need assistance.
            </div>

            <div class="footer">
                Thanks,<br>
                The Tixolve Team
            </div>
        </div>
    </div>
</body>
</html>
