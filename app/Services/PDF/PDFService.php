<?php

namespace App\Services\PDF;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\PayrollRecord;
use App\Models\Sale;
use App\Services\Tenancy\TenantContext;
use ImranDev\UnicodePdf\Facades\UnicodePdf;

class PDFService
{
    /**
     * Generate PDF invoice stream/download response.
     */
    public function generateInvoicePDF(Invoice $invoice)
    {
        $tenant = TenantContext::getTenant() ?? $invoice->tenant;
        
        return UnicodePdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $tenant,
        ])
        ->title("Invoice #{$invoice->invoice_number}")
        ->a4('portrait')
        ->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Generate HTML output string for invoice.
     */
    public function renderInvoiceHtml(Invoice $invoice): string
    {
        $tenant = TenantContext::getTenant() ?? $invoice->tenant;

        return view('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $tenant,
        ])->render();
    }
}
