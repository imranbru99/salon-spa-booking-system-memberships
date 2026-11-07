<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 20px; font-size: 13px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #ec4899; padding-bottom: 15px; margin-bottom: 20px; }
        .company-name { font-size: 22px; font-weight: bold; color: #831843; }
        .inv-title { font-size: 18px; font-weight: bold; color: #4b5563; text-align: right; }
        .details-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .details-table td { vertical-align: top; width: 50%; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #fdf2f8; color: #831843; border-bottom: 2px solid #fbcfe8; padding: 8px; text-align: left; }
        .items-table td { border-bottom: 1px solid #f3f4f6; padding: 8px; }
        .totals { width: 40%; margin-left: auto; margin-bottom: 20px; border-collapse: collapse; }
        .totals td { padding: 6px; }
        .totals .grand-total { font-size: 15px; font-weight: bold; border-top: 2px solid #831843; color: #831843; }
        .footer { text-align: center; color: #9ca3af; font-size: 11px; margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company-name">{{ $tenant->name ?? 'Glow & Grace Salon and Spa' }}</div>
            <div>{{ $invoice->branch->address ?? ($tenant->address ?? 'Luxury Beauty Boulevard') }}</div>
            <div>{{ $invoice->branch->phone ?? ($tenant->phone ?? '+1 (555) 019-2834') }}</div>
            <div>{{ $tenant->email ?? 'concierge@glowgrace.test' }}</div>
        </div>
        <div style="text-align: right;">
            <div class="inv-title">TAX INVOICE</div>
            <div><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</div>
            <div><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</div>
            <div><strong>Status:</strong> <span style="text-transform: uppercase; color: #059669; font-weight: bold;">{{ $invoice->status }}</span></div>
        </div>
    </div>

    <table class="details-table">
        <tr>
            <td>
                <strong>Billed To:</strong><br>
                {{ $invoice->customer ? $invoice->customer->full_name : 'Guest Customer' }}<br>
                @if($invoice->customer && $invoice->customer->email)
                    {{ $invoice->customer->email }}<br>
                @endif
                @if($invoice->customer && $invoice->customer->phone)
                    {{ $invoice->customer->phone }}
                @endif
            </td>
            <td style="text-align: right;">
                <strong>Branch:</strong><br>
                {{ $invoice->branch->name ?? 'Flagship Branch' }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Unit Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($invoice->sale && $invoice->sale->items->isNotEmpty())
                @foreach($invoice->sale->items as $item)
                    <tr>
                        <td>{{ $item->item_name }} ({{ ucfirst($item->item_type) }})</td>
                        <td style="text-align: center;">{{ number_format($item->quantity, 0) }}</td>
                        <td style="text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>Salon & Spa Treatment Session</td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
                    <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal:</td>
            <td style="text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
        <tr>
            <td>Discount:</td>
            <td style="text-align: right; color: #dc2626;">-${{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        @endif
        @if($invoice->tax_amount > 0)
        <tr>
            <td>Tax / VAT:</td>
            <td style="text-align: right;">+${{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>Total:</td>
            <td style="text-align: right;">${{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Paid Amount:</td>
            <td style="text-align: right; color: #059669; font-weight: bold;">${{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        Thank you for choosing {{ $tenant->name ?? 'Glow & Grace Salon and Spa' }}! For inquiries, email {{ $tenant->email ?? 'support@glowgrace.test' }}.
    </div>
</body>
</html>
