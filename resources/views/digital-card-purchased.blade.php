<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شراء بطاقة رقمية</title>
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

        strong {
            color: #007bff;
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

        <p>تهانينا على شرائك لبطاقة رقمية!</p>

        <p>كود البطاقة الرقمية الخاص بك هو: <strong>{{ $digitalCardCode }}</strong></p>

        <p>الرجاء الاحتفاظ بهذا الكود بأمان لاستخدامه لاحقًا.</p>

        <p>شكرًا لاختيارك خدمتنا. إذا كان لديكم أي استفسارات أو تحتاجون إلى المساعدة، فلا تترددوا في الاتصال بفريق الدعم لدينا.</p>

        <p class="signature">أطيب التحيات,<br>فريق دعم ITS NOLOGY</p>
    </div>
</body>
</html>
