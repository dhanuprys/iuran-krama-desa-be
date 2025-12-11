<?php

namespace App\Http\Controllers\Api\Krama;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use \App\Traits\GeneratesReceiptPdf;

    /**
     * Download the receipt PDF.
     * Krama can only download receipts for their own invoices (via resident).
     */
    public function download(Payment $payment)
    {
        $user = auth()->user();
        if (!$user->resident) {
            abort(403, 'User is not linked to a resident.');
        }

        // Verify the payment belongs to an invoice for this resident
        if ($payment->invoice->resident_id !== $user->resident->id) {
            abort(403, 'Unauthorized access to this receipt.');
        }

        return $this->downloadReceipt($payment);
    }
}
