<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice #{{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
        }

        .container {
            width: 100%;
            display: block;
        }

        .invoice-box {
            border: 2px dashed #999;
            padding: 15px;
            background-color: #fff;
            margin: 0 auto;
            max-width: 98%;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #111;
        }

        .header p {
            margin: 2px 0 0;
            font-size: 10px;
            color: #666;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 80px;
            color: #555;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #eee;
        }

        .items-table th {
            background-color: #f3f4f6;
            padding: 5px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
        }

        .items-table td {
            padding: 5px;
            border-bottom: 1px solid #eee;
        }

        .items-table .amount-col {
            text-align: right;
            width: 120px;
        }

        .total-row td {
            font-weight: bold;
            background-color: #f9fafb;
            border-top: 1px solid #333;
        }

        .status-stamp {
            display: inline-block;
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

        .status-unpaid {
            color: #991b1b;
            border-color: #991b1b;
            background-color: #fee2e2;
        }

        .footer {
            text-align: right;
            margin-top: 10px;
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
        <div class="invoice-box">
            <div class="header">
                <h1>Desa Adat Sangket</h1>
                <p>Jl. Raya Sangket, Sukasada, Buleleng, Bali</p>
                <div style="position: absolute; top: 15px; right: 15px;">
                    @if($invoice->status === 'PAID')
                        <div class="status-stamp status-paid">LUNAS</div>
                    @else
                        <div class="status-stamp status-unpaid">BELUM LUNAS</div>
                    @endif
                </div>
            </div>

            <!-- Info Section -->
            <table class="info-table">
                <tr>
                    <td width="60%" valign="top">
                        <table width="100%">
                            <tr>
                                <td class="label">Kepada Yth</td>
                                <td width="10">:</td>
                                <td>
                                    <strong>{{ $invoice->resident->name }}</strong><br>
                                    <span style="color: #666; font-size: 10px;">
                                        {{ $invoice->resident->banjar->name ?? '' }} • NIK:
                                        {{ $invoice->resident->nik }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="40%" valign="top">
                        <table width="100%">
                            <tr>
                                <td class="label" style="text-align: right;">No. Invoice</td>
                                <td width="10" align="right">:</td>
                                <td width="80" align="right">
                                    #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</td>
                            </tr>
                            <tr>
                                <td class="label" style="text-align: right;">Tanggal</td>
                                <td width="10" align="right">:</td>
                                <td align="right">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Items Section -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Keterangan</th>
                        <th class="amount-col">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Iuran Wajib ({{ $invoice->resident->residentStatus->name ?? 'Krama' }})</td>
                        <td class="amount-col">Rp {{ number_format($invoice->iuran_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->peturunan_amount > 0)
                        <tr>
                            <td>Peturunan</td>
                            <td class="amount-col">Rp {{ number_format($invoice->peturunan_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($invoice->dedosan_amount > 0)
                        <tr>
                            <td>Dedosan</td>
                            <td class="amount-col">Rp {{ number_format($invoice->dedosan_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td style="text-align: right;">TOTAL TAGIHAN</td>
                        <td class="amount-col" style="font-size: 14px;">Rp
                            {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="footer">
                <p style="font-size: 9px; margin-bottom: 2px;">Terima kasih atas partisipasi Anda.</p>
                <div class="meta">
                    Dicetak otomatis pada: {{ now()->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>