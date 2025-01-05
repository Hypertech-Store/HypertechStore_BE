<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thành công</title>
</head>
<body>
<h1>Xin chào {{ $orderData['khach_hang_name'] }},</h1>
<p>Cảm ơn bạn đã mua sắm tại cửa hàng của chúng tôi!</p>
<p>Chi tiết đơn hàng:</p>
<ul>
    <li>Mã đơn hàng: {{ $orderData['order_id'] }}</li>
    <li>Tổng tiền: {{ number_format($orderData['total'], 0, ',', '.') }} VND</li>
    <li>Thời gian thanh toán: {{ $orderData['payment_time'] }}</li>
</ul>
<p>Chúc bạn một ngày tốt lành!</p>
</body>
</html>
