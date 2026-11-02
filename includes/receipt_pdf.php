<?php

function bikeMoReceiptPdf(
    string $orderCode,
    array $customer,
    array $items,
    float $grandTotal,
    string $status
): string {
    require_once __DIR__ . '/../vendor/autoload.php';

    $escape = static function ($value): string {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    };

    $money = static fn(float $value): string =>
        'PHP ' . number_format($value, 2);

    $rows = '';
    $rentalTotal = 0.0;
    $lateFeeTotal = 0.0;

    foreach ($items as $item) {
        $bikeName = $escape($item['bike_name'] ?? 'Bike');
        $duration = (int) ($item['duration_value'] ?? 1)
            . ' '
            . $escape($item['unit'] ?? 'days');

        $subtotal = (float) ($item['total'] ?? 0);
        $lateFee = (float) ($item['late_fee'] ?? 0);
        $lineTotal = $subtotal + $lateFee;

        $rentalTotal += $subtotal;
        $lateFeeTotal += $lateFee;

        $rows .= '
            <tr>
                <td>' . $bikeName . '</td>
                <td>' . $duration . '</td>
                <td class="amount">' . $money($subtotal) . '</td>
                <td class="amount">' . $money($lateFee) . '</td>
                <td class="amount total">' . $money($lineTotal) . '</td>
            </tr>
        ';
    }

    $safeOrderCode = $escape($orderCode);
    $safeCustomerName = $escape($customer['name'] ?? 'Customer');
    $safeCustomerEmail = $escape($customer['email'] ?? '');
    $safeStatus = $escape($status);
    $generatedAt = date('F j, Y · g:i A');

    $rentalTotalText = $money($rentalTotal);
    $lateFeeTotalText = $money($lateFeeTotal);
    $grandTotalText = $money($grandTotal);

    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <style>
            @page {
                margin: 32px;
            }

            body {
                font-family: "DejaVu Sans", sans-serif;
                color: #183047;
                font-size: 11px;
            }

            .header {
                background: #172b3d;
                color: #ffffff;
                padding: 22px;
                border-bottom: 6px solid #ff7028;
            }

            .brand {
                color: #ff8a3d;
                font-size: 23px;
                font-weight: bold;
                margin: 0;
            }

            .subtitle {
                color: #dce5ed;
                margin-top: 5px;
            }

            .receipt-title {
                margin-top: 25px;
                font-size: 20px;
                font-weight: bold;
            }

            .order {
                color: #607487;
                margin-top: 5px;
            }

            .details {
                width: 100%;
                margin-top: 20px;
                border-collapse: collapse;
            }

            .details td {
                width: 50%;
                padding: 10px;
                background: #f4f6f8;
                border: 1px solid #e0e6eb;
            }

            .label {
                color: #718294;
                font-size: 9px;
                text-transform: uppercase;
            }

            .value {
                margin-top: 4px;
                font-weight: bold;
            }

            .section-title {
                margin-top: 25px;
                margin-bottom: 10px;
                font-size: 14px;
                font-weight: bold;
            }

            .items {
                width: 100%;
                border-collapse: collapse;
            }

            .items th {
                background: #203c52;
                color: #ffffff;
                padding: 10px 7px;
                text-align: left;
                font-size: 9px;
            }

            .items td {
                padding: 10px 7px;
                border-bottom: 1px solid #dfe5e9;
            }

            .amount {
                text-align: right;
                white-space: nowrap;
            }

            .total {
                font-weight: bold;
            }

            .summary {
                width: 45%;
                margin-top: 18px;
                margin-left: auto;
                border-collapse: collapse;
            }

            .summary td {
                padding: 6px 4px;
            }

            .grand-total td {
                border-top: 2px solid #ff7028;
                color: #172b3d;
                font-size: 15px;
                font-weight: bold;
                padding-top: 12px;
            }

            .footer {
                margin-top: 40px;
                padding-top: 12px;
                border-top: 1px solid #dfe5e9;
                color: #718294;
                text-align: center;
                font-size: 9px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <p class="brand">BikeMo</p>
            <div class="subtitle">Bike Rental Service</div>
            <div class="receipt-title">Transaction Receipt</div>
            <div class="order">Order #{$safeOrderCode}</div>
        </div>

        <table class="details">
            <tr>
                <td>
                    <div class="label">Customer</div>
                    <div class="value">{$safeCustomerName}</div>
                </td>
                <td>
                    <div class="label">Email</div>
                    <div class="value">{$safeCustomerEmail}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">Order status</div>
                    <div class="value">{$safeStatus}</div>
                </td>
                <td>
                    <div class="label">Receipt date</div>
                    <div class="value">{$generatedAt}</div>
                </td>
            </tr>
        </table>

        <div class="section-title">Rental summary</div>

        <table class="items">
            <thead>
                <tr>
                    <th>Bike</th>
                    <th>Duration</th>
                    <th class="amount">Subtotal</th>
                    <th class="amount">Late fee</th>
                    <th class="amount">Total</th>
                </tr>
            </thead>
            <tbody>
                {$rows}
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td>Rental subtotal</td>
                <td class="amount">{$rentalTotalText}</td>
            </tr>
            <tr>
                <td>Late fees</td>
                <td class="amount">{$lateFeeTotalText}</td>
            </tr>
            <tr class="grand-total">
                <td>Grand total</td>
                <td class="amount">{$grandTotalText}</td>
            </tr>
        </table>

        <div class="footer">
            Thank you for choosing BikeMo.<br>
            Please keep this receipt for your records.
        </div>
    </body>
    </html>
    HTML;

    $options = new \Dompdf\Options();
    $options->set('defaultFont', 'DejaVu Sans');

    $pdf = new \Dompdf\Dompdf($options);
    $pdf->loadHtml($html, 'UTF-8');
    $pdf->setPaper('A4', 'portrait');
    $pdf->render();

    return $pdf->output();
}