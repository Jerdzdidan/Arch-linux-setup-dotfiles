<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->subject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .email-wrapper {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .email-header {
            background: linear-gradient(135deg, #1a3a6b, #2d5aa0);
            color: #ffffff;
            padding: 30px 30px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .email-header .school-name {
            font-size: 14px;
            opacity: 0.85;
            margin-top: 6px;
        }

        .email-body {
            padding: 30px;
            line-height: 1.7;
            font-size: 15px;
        }

        .email-body h2 {
            margin-top: 0;
            color: #1a3a6b;
            font-size: 22px;
            border-bottom: 2px solid #e8edf3;
            padding-bottom: 12px;
        }

        .email-body .content {
            margin-top: 16px;
        }

        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
            border-top: 1px solid #eee;
        }

        .email-footer p {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="{{ asset('img/logo/arellano_logo.png') }}" alt="Arellano University Logo" style="height: 60px; margin-bottom: 12px; display: block; margin-left: auto; margin-right: auto;">
            <h1>Arellano University</h1>
            <div class="school-name">Academic Information System</div>
        </div>

        <div class="email-body">
            <h2>{{ $announcement->subject }}</h2>
            <div class="content">
                {!! $announcement->body !!}
            </div>
        </div>

        <div class="email-footer">
            <p>This is an automated message from the AU Academic Information System.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>

</html>