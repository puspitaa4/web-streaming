<?php
// Simulasi upload file dan logika pembayaran
// Pastikan pengguna sudah login
$paymentData = [
    'bca' => [
        'name' => 'Bank BCA',
        'image' => 'https://2.bp.blogspot.com/-lOAvxqPQ23s/WgO53cUvDOI/AAAAAAAAEoo/hiWQzddn0Vcu6FTQFU3iPnfe0jBqqvZowCLcBGAs/s1600/bca.jpg'
    ],
    'bri' => [
        'name' => 'Bank BRI',
        'image' => 'https://www.freelogovectors.net/wp-content/uploads/2023/02/bri-logo-freelogovectors.net_.png'
    ],
    'dana' => [
        'name' => 'DANA',
        'image' => 'https://career.amikom.ac.id/images/company/cover/1637497527.jpeg'
    ],
    'qris' => [
        'name' => 'QRIS',
        'image' => 'https://seeklogo.com/images/Q/quick-response-code-indonesia-standard-qris-logo-F300D5EB32-seeklogo.com.png'
    ],
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pembayaran Langganan</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #282828;
            font-family: Arial, sans-serif;
        }
        .payment-option {
            display: flex;
            align-items: center;
            cursor: pointer;
            margin-botton: 10px;
        }

        .payment-option input[type="radio"] {
            margin-right: 10px; /* Spasi antara radio button dan label */
        }
        img {
            width: 50px;
            height: 30px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <h2 class="text-center mb-4 text-white">Form Pembayaran Langganan</h2>
    <?php if (isset($uploadSuccess) && !$uploadSuccess): ?>
        <div class="alert alert-danger" role="alert">
            Transaksi Gagal. Silakan coba lagi.
        </div>
    <?php endif; ?>
    
    <form id="paymentForm" method="POST" enctype="multipart/form-data">
        <div class="form-group text-white">
            <label for="paymentMethod"><strong>Pilih Metode Pembayaran:</strong></label>
            <div class="payment-option-container">
                <?php foreach ($paymentData as $key => $method): ?>
                    <?php $paymentId = "payment_" . $key;?>
                    <div class="payment-option">
                        <input type="radio" class="payment-option" name="payment" value="<?php echo $method['name'];?>" id="<?php echo $paymentId; ?>">
                        <label for="<?php echo $paymentId;?>">
                            <img src="<?php echo $method['image']; ?>" alt="<?php echo $method['name']; ?>">
                            <?php echo $method['name']; ?>
                        </label>
                        </input>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" onclick="subscribe()">Bayar</button>
    </form>
</div>

<script>
    function subscribe() {
        if (confirm('Do you want to subscribe?')) {
            fetch('subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'subscribe' })
            }).then(response => response.json())
              .then(data => {
                  if (data.status === 'success') {
                      alert(data.message);
                      window.location = "langganan.php";
                  } else {
                      alert(data.message);
                  }
              });
        }
    }

</script>
</body>
</html>
