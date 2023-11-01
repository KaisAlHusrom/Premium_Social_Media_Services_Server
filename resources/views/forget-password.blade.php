<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحقق من البريد الإلكتروني</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
        }

        p {
            font-size: 18px;
        }

        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
        }

        a:hover {
            background-color: #0056b3;
        }

        .signature {
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>عزيزنا العميل،</p>

        <p>لتحديث كلمة السر:</p>
        <a href="{{ $resetLink }}">اضغط هنا</a>

        <p>إذا كان لديك أي استفسارات أو تحتاج إلى مساعدة، فلا تتردد في الاتصال بفريق الدعم لدينا.</p>

        <p class="signature">أطيب التحيات,<br>فريق دعم ITS NOLOGY</p>
    </div>
</body>
</html>
