<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bukti Pembayaran</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: sans-serif;
            font-size: 11px; /* Reduced base font size */
            color: #333;
            line-height: 1.2;
            margin: 0;
            padding: 10px; /* Reduced body padding */
        }

        .container {
            width: 100%;
            display: block;
        }

        .receipt-box {
            border: 2px dashed #999;
            padding: 15px; /* Reduced box padding */
            background-color: #fff;
            margin: 0 auto;
            max-width: 98%;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #333; /* Thinner border */
            padding-bottom: 5px; /* Reduced padding */
            margin-bottom: 15px; /* Reduced margin */
        }

        .header h1 {
            margin: 0;
            font-size: 16px; /* Reduced title size */
            text-transform: uppercase;
            color: #111;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #666;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px; /* Reduced margin */
        }

        table.info-table td {
            padding: 2px 0; /* Reduced cell padding */
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 130px; /* Slightly compact label width */
            color: #555;
        }

        .amount-section {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px; /* Reduced padding */
            text-align: center;
            margin: 10px 0; /* Reduced margin */
        }

        .amount-value {
            font-size: 18px; /* Reduced amount size */
            font-weight: bold;
            color: #2d3748;
        }

        .status-stamp {
            display: inline-block;
            margin-left: 10px;
            padding: 2px 8px;
            border: 2px solid;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            border-radius: 4px;
            transform: rotate(-5deg);
        }

        .status-paid {
            color: #166534;
            border-color: #166534;
            background-color: #dcfce7;
        }

        .footer {
            text-align: right;
            margin-top: 10px;
        }

        .signature-line {
            margin-top: 30px; /* Reduced signature space */
            border-top: 1px solid #333;
            padding-top: 2px;
            font-weight: bold;
            display: inline-block;
            min-width: 120px;
            text-align: center;
        }

        .meta {
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 3px;
            font-size: 8px;
            color: #aaa;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="receipt-box">
            <div class="header">
                <h1>Bukti Pembayaran</h1>
                <p>Desa Adat Sangket</p>
                <p>Kecamatan Sukasada, Kabupaten Buleleng</p>
            </div>

            <table class="info-table">
                <tr>
                    <td class="label">ID Pembayaran</td>
                    <td width="20">:</td>
                    <td>#{{ $payment->id }}</td>
                    <td class="label" style="text-align: right; width: 100px;">Tanggal</td>
                    <td width="20" style="text-align: right;">:</td>
                    <td style="text-align: right; width: 120px;">{{ $payment->date->format('d/m/Y') }}</td>
                </tr>
            </table>

            <table class="info-table">
                <tr>
                    <td class="label">Diterima Dari</td>
                    <td width="20">:</td>
                    <td>
                        <strong>{{ $payment->invoice->resident->name }}</strong>
                        <span style="color: #666; font-size: 11px;">
                            ({{ $payment->invoice->resident->residentStatus->name ?? '-' }})
                        </span>
                        <br>
                        <span style="font-size: 11px; color: #666;">
                            {{ $payment->invoice->resident->banjar->name ?? '' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Pembayaran</td>
                    <td width="20">:</td>
                    <td>Iuran Bulan {{ $payment->invoice->invoice_date->format('F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Metode</td>
                    <td width="20">:</td>
                    <td>{{ ucfirst($payment->method) }}</td>
                </tr>
            </table>

            <div class="amount-section">
                <span class="amount-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                @if($payment->status === 'paid')
                    <span class="status-stamp status-paid">LUNAS</span>
                @endif
            </div>

            <table width="100%">
                <tr>
                    <td width="60%">
                        <div class="meta">
                            Dicetak otomatis: {{ now()->format('d/m/Y H:i:s') }}
                        </div>
                    </td>
                    <td width="40%" align="right">
                        <div class="footer">
                            <p style="margin: 0 0 40px 0;">Penerima,</p>
                            <div class="signature-line">
                                {{ $payment->user->name ?? 'Admin / Operator' }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>