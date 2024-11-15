<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
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
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #333;
        }

        .content p {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
        }

        .button-container {
            text-align: center;
            padding-top: 20px;
        }

        .button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            text-align: center;
            display: inline-block;
            margin-top: 10px;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Xin chào,</h1>
        </div>
        <div class="content">
            <p>Bạn vừa yêu cầu đặt lại mật khẩu. Vui lòng nhấn vào nút dưới đây để thay đổi mật khẩu của bạn:</p>
            <div class="button-container">
                <a href="{{ 'http://localhost:5173/cap-nhat-mat-khau?token=' . $token }}" class="button">Đặt lại mật
                    khẩu</a>
            </div>
            <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
        </div>
        <div class="footer">
            <p>Trân trọng,</p>
            <p>Đội ngũ hỗ trợ</p>
        </div>
    </div>
</body>

</html>
