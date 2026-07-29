<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your account has been created</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a2840; margin: 0; padding: 0; background: #f5f7fa; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.08); overflow: hidden; }
        .header { background: linear-gradient(120deg, #003087 0%, #0a1a33 100%); padding: 28px; text-align: center; }
        .header img { height: 48px; margin-bottom: 12px; }
        .header h1 { color: #fff; font-size: 1.1rem; margin: 0; font-weight: 700; }
        .body { padding: 32px 28px; }
        .body p { font-size: .95rem; line-height: 1.6; margin: 0 0 16px; color: #334155; }
        .body .name { font-weight: 700; color: #003087; }
        .btn { display: inline-block; background: #003087; color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; font-size: .95rem; }
        .btn:hover { background: #0a1a33; }
        .footer { padding: 20px 28px; text-align: center; font-size: .78rem; color: #7b8794; border-top: 1px solid #e4e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="{{ asset('deped_logo.png') }}" alt="DepEd">
            <h1>Schools Division Office of Cavite Province</h1>
        </div>
        <div class="body">
            <p>Hello <span class="name">{{ $invitation->name }}</span>,</p>
            <p>An account has been created for you in the <strong>DepEd Cavite Personnel Appointment Management System</strong>.</p>
            <p>Please confirm your account by clicking the button below. This link will expire on <strong>{{ $invitation->expires_at->format('F j, Y') }}</strong>.</p>
            <p style="text-align:center; margin-top:24px;">
                <a href="{{ $acceptUrl }}" class="btn">Confirm Account</a>
            </p>
            <p style="font-size:.85rem; color:#7b8794; margin-top:20px;">If the button doesn't work, copy and paste this link into your browser:<br>{{ $acceptUrl }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} DepEd — Schools Division Office of Cavite Province
        </div>
    </div>
</body>
</html>
