<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 20px;
            margin: 0 auto;
            max-width: 800px;
            min-height: 1200px;
        }

        .logo {
            text-align: center;
            margin: 10px 0;
        }

        .bill-to {
            background-color: #ffde59;
            display: inline-block;
            padding: 5px 10px;
            font-weight: bold;
            margin-top: 15px;
        }

        .bill-info {
            margin-top: 5px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        td {

            border-bottom: 1px solid rgb(203, 203, 203);
        }

        th,
        td {
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #ffde59;
            font-weight: bold;
        }

        .summary td {
            border: none;
            padding: 5px;
        }

        .summary .label {
            font-weight: bold;
            text-align: right;
            width: 85%;
        }

        .summary .value {
            text-align: right;
            width: 15%;
        }

        .summary tr.total-row {
            background-color: #ffde59;
        }
    </style>
</head>

<body>
    <div class="container" id="capture">

        <div style="width: 100%; margin-bottom: 10px;">
            <table style="width: 100%; border: none; border-collapse: collapse;">
                <tr>
                    <td style="border:none!important; text-align: left; vertical-align: top; width: 30%; vertical-align: middle;">
                        <div style="font-size: 14px;"><strong>My Power</strong></div>
                        <div style="font-size: 12px;">+977 9843293275</div>
                        <div style="font-size: 12px;">Kathmandu</div>
                    </td>
                    <td style="border:none!important; text-align: left; vertical-align: top; width: 40%;">
                        <div class="logo">
                            <img src="{{ asset('logo/light.svg') }}" alt="Logo" height="60">
                        </div>
                    </td>
                    <td style="border:none!important; text-align: right; vertical-align: top; width: 30%; vertical-align: middle;">
                        <div style="font-size: 12px;">Date:
                            {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d') }}</div>
                        <div style="font-size: 12px;">Miti:
                            {{ getNepaliDate(\Carbon\Carbon::parse($order->order_date)->format('Y-m-d')) }}</div>
                    </td>
                </tr>
            </table>
        </div>



        <div class="bill-to">Estimate</div>

        <div class="bill-info">
            <strong>Order:</strong> #{{ $order->orderid }}<br>
            <strong>Name:</strong> {{ $order->user->name }}<br>
            <strong>Shop Name:</strong> {{ $order->user->shop_name ?? 'N/A' }}<br>
            <strong>Address:</strong> {{ $order->user->address }}<br>
            <strong>Contact:</strong> {{ $order->user->contact ?? '-' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Image</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><img src="{{ asset('storage/' . ($item->product->images[0]['image'] ?? ($item->product->image ?? $item->product->image_2))) }}"
                                alt="{{ $item->product->name }}" style="max-width: 40px; max-height: 40px;"
                                crossOrigin="anonymous" /></td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 0) }}</td>
                        <td>{{ number_format($item->quantity * $item->price, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td class="label">Total</td>
                <td class="value">Rs. {{ number_format($order->total, 0) }}</td>
            </tr>
            <tr>
                <td class="label">Discount ({{ $order->discount_percentage }}%)</td>
                <td class="value">{{ number_format($order->discount, 0) }}%</td>
            </tr>
            <tr class="total-row">
                <td class="label">Discounted</td>
                <td class="value">Rs. {{ number_format($order->net_total, 0) }}</td>
            </tr>
        </table>

    </div>

    <script>
      window.onload = () => {
          const target = document.getElementById('capture');
  
          // Step 1: Wait for all images inside the capture area to load
          const images = target.querySelectorAll("img");
          const imagePromises = Array.from(images).map(img => {
              return new Promise(resolve => {
                  if (img.complete && img.naturalHeight !== 0) {
                      resolve(); // Already loaded
                  } else {
                      img.onload = img.onerror = () => resolve(); // Wait for load or fail
                  }
              });
          });
  
          // Step 2: Once all images are ready
          Promise.all(imagePromises).then(() => {
              html2canvas(target, {
                  scale: 2,
                  useCORS: true,
                  allowTaint: false,
                  logging: true
              }).then(canvas => {
                  const link = document.createElement('a');
                  link.href = canvas.toDataURL('image/png');
                  link.download = 'order-{{ $order->id }}.png';
                  link.click();
                  setTimeout(() => window.close(), 200); // Slight delay before closing
              }).catch(error => {
                  console.error("Canvas generation failed:", error);
                  alert("Something went wrong while generating the image. Check console.");
              });
          });
      };
  </script>  

</body>

</html>
