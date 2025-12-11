<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #d97706;
            /* primary amber-600 */
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .invoice-details {
            margin-bottom: 40px;
            overflow: hidden;
        }

        .invoice-details .left {
            float: left;
            width: 50%;
        }

        .invoice-details .right {
            float: right;
            width: 40%;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
        }

        .text-right {
            text-align: right;
        }

        .total-row td {
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: none;
            font-size: 16px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @if($invoice->status === 'PAID')
        <div class="watermark">LUNAS</div>
    @else
        <div class="watermark">BELUM LUNAS</div>
    @endif

    <div class="header">
        <h1>Desa Adat Sangket</h1>
        <p>Jl. Raya Sangket, Sukasada, Buleleng, Bali</p>
        <p>Telp: (0362) 123456 | Email: info@desa-sangket.id</p>
    </div>

    <div class="invoice-details">
        <div class="left">
            <strong>Kepada Yth:</strong><br>
            {{ $invoice->resident->name }}<br>
            NIK: {{ $invoice->resident->nik }}<br>
            Banjar: {{ $invoice->resident->banjar->name ?? '-' }}
        </div>
        <div class="right">
            <strong>INVOICE</strong><br>
            #{{ $invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}<br>
            Tanggal: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d F Y') }}<br>
            Status:
            <span class="status-badge {{ $invoice->status === 'PAID' ? 'status-paid' : 'status-unpaid' }}">
                {{ $invoice->status === 'PAID' ? 'LUNAS' : 'BELUM LUNAS' }}
            </span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Iuran Wajib ({{ $invoice->resident->residentStatus->name ?? 'Krama' }})</td>
                <td class="text-right">Rp {{ number_format($invoice->iuran_amount, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->peturunan_amount > 0)
                <tr>
                    <td>Peturunan</td>
                    <td class="text-right">Rp {{ number_format($invoice->peturunan_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($invoice->dedosan_amount > 0)
                <tr>
                    <td>Dedosan</td>
                    <td class="text-right">Rp {{ number_format($invoice->dedosan_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td>Total Tagihan</td>
                <td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Terima kasih atas partisipasi Anda dalam pembangunan Desa Adat Sangket.</p>
        <p>Bukti pembayaran ini sah dan diterbitkan secara komputerisasi.</p>
    </div>
</body>

</html>