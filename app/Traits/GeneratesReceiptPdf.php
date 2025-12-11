<?php

namespace App\Traits;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

trait GeneratesReceiptPdf
{
    /**
     * Generate and download the receipt PDF.
     */
    public function downloadReceipt(Payment $payment): Response
    {
        // Load necessary relationships for the PDF
        $payment->load([
            'invoice.resident.residentStatus',
            'invoice.resident.banjar',
            'user' // The collector/admin who processed it
        ]);

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'title' => 'Bukti Pembayaran - ' . $payment->invoice->invoice_date->format('F Y'),
        ]);

        $pdf->setPaper('a5', 'landscape');

        $filename = 'receipt-' . $payment->id . '-' . $payment->date->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
