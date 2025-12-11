<?php

namespace App\Traits;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

trait GeneratesInvoicePdf
{
    /**
     * Generate PDF response for an invoice.
     *
     * @param Invoice $invoice
     * @return Response
     */
    public function generatePdfResponse(Invoice $invoice): Response
    {
        // Ensure relationships are loaded
        $invoice->load(['resident.banjar', 'resident.residentStatus']);

        // Define invoice number if not present (using year-month-id format if needed, or just id)
        // Assuming there isn't a dedicated invoice_number column yet, we use ID padded.
        // Or if there is, we use it. The view handles the fallback.

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
        $pdf->setPaper('a5', 'landscape');

        $filename = 'invoice-' . ($invoice->invoice_number ?? str_pad($invoice->id, 6, '0', STR_PAD_LEFT)) . '.pdf';

        return $pdf->download($filename);
    }
}
