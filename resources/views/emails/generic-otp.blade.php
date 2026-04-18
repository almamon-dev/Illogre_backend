<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body style="font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f7f6; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="color: #1a1a1a;">{{ $purpose }}</h2>
        <p style="color: #4a4a4a; font-size: 16px;">Hello {{ $name }}, please use the verification code below to proceed.</p>
        
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 20px; text-align: center; margin: 30px 0; border-radius: 8px;">
            <span style="font-size: 32px; font-weight: 800; color: #16a34a; letter-spacing: 5px;">{{ $otp }}</span>
        </div>

        <p style="color: #6b7280; font-size: 14px;">This code will expire in 30 minutes. If you did not initiate this request, please ignore this email.</p>
        
        <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;">
        
        <p style="text-align: center; color: #9ca3af; font-size: 12px;">&copy; {{ date('Y') }} Tixolve. All rights reserved.</p>
    </div>
</body>
</html>
