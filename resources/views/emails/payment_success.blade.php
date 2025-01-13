<!DOCTYPE html>
<html lang="en">
<head>
    <title>Thanh toán thành công</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; width: 100%; }
        body { background-color: #f8f9fa; margin: 0 !important; padding: 0 !important; font-family: Arial, sans-serif; }
        .mobile-center { text-align: center !important; }

        /* Styling for table */
        table, th, td {
            border: 2px solid #ddd;
           
        }
        td {
            padding: 10px;
            text-align: left;
        }
        th {
            padding: 10px;
            background-color: #F44336;
            color: white;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Footer styling */
        .footer {
            background-color: #F44336;
            color: white;
            text-align: center;
            padding: 20px;
           
        }

        /* Header section style */
        .header {
            background-color: #F44336;
            color: #ffffff;
            padding: 20px;
           
            text-align: center;
        }

        .greeting {
            font-size: 24px;
            font-weight: bold;
            color: white;
        }

        /* Body container style */
        .body-container {
            background-color: #ffffff;
            margin-top: 20px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Address section style */
        .address {
            padding: 15px;
            background-color: #f8f9fa;
            margin-bottom: 20px;
           
        }

        .address h5 {
            margin-bottom: 15px;
        }

        .address p {
            margin-bottom: 5px;
        }

        /* Estimated Delivery section style */
        .delivery {
            padding: 15px;
            background-color: #f8f9fa;
           
            text-align: left;
        }

        .delivery h5 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Greeting Section -->
<div class="header">
    <p class="greeting">Xin chào, {{ $mailData['khach_hang_name'] }}</p>
</div>

<!-- Body Section -->
<div class="container body-container">

    <!-- Success message -->
    <div class="row">
        <div class="col-12 text-center">
            <h2 class="my-3 text-primary">Cảm ơn bạn đã mua sắm tại cửa hàng chúng tôi!</h2>
            <p>"Chúng tôi hy vọng Quý khách sẽ hài lòng với sản phẩm và rất mong được tiếp tục đồng hành cùng Quý khách trong những lần mua sắm sắp tới!"</p>
        </div>
    </div>

    <!-- Order details -->
    <div class="row">
        <div class="col-12">
            <table>
                <thead>
                    <tr>
                        <th colspan="2">Thông tin đơn hàng</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Mã đơn hàng</strong></td>
                        <td>#{{ $mailData['ma_don_hang'] }}</td>
                    </tr>
                    @foreach ($mailData['chi_tiet_san_pham'] as $chiTiet)
                    <tr>
                        <td>{{ $chiTiet['ten_san_pham'] }} ({{ $chiTiet['so_luong'] }})</td>
                        <td>{{ number_format((float)$chiTiet['gia'], 0, ',', '.') }} VNĐ</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td><strong>Phí vận chuyển</strong></td>
                        <td>{{ number_format((float)$mailData['phi_van_chuyen'], 0, ',', '.') }} VNĐ</td>
                    </tr>
                    <tr>
                        <td><strong>Giảm giá</strong></td>
                        <td>{{ $mailData['giam_gia'] ? number_format($mailData['giam_gia'], 0) . '%' : '0%' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tổng tiền</strong></td>
                        <td><strong>{{ number_format($orderData['total'], 0, ',', '.') }} VNĐ</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Shipping Address Section -->
    <div class="address">
        <h5>Địa chỉ giao hàng</h5>
        <p>{{ $orderData['dia_chi_giao_hang'] }}</p>
    </div>

    <!-- Estimated Delivery Section -->
    <div class="delivery">
        <h5>Ngày giao hàng dự kiến</h5>
        <p>{{ $orderData['ngay_giao_du_kien'] }}</p>
    </div>

</div>

<!-- Footer Section -->
<div class="footer">
    <p>&copy; <span id="current-year"></span> Hypertech Store. All rights reserved.</p>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<!-- JavaScript to update the year automatically -->
<script>
    // Update the current year in the footer
    document.getElementById("current-year").textContent = new Date().getFullYear();
</script>

</body>
</html>
