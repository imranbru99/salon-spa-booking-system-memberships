<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\PDF\PDFService;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerPortalController extends Controller
{
    public function index(Request $request)
    {
        // For demo portal, default to Victoria Sterling
        $customer = Customer::where('email', 'customer@example.test')->first() ?? Customer::first();

        if (!$customer) {
            abort(404, 'No demo customer found.');
        }

        $upcomingAppointments = Appointment::where('customer_id', $customer->id)
            ->where('appointment_date', '>=', Carbon::today())
            ->whereNotIn('status', ['cancelled'])
            ->with(['branch', 'services.service', 'services.staff.user'])
            ->orderBy('appointment_date')
            ->get();

        $pastAppointments = Appointment::where('customer_id', $customer->id)
            ->where('appointment_date', '<', Carbon::today())
            ->with(['branch', 'services.service', 'services.staff.user'])
            ->orderByDesc('appointment_date')
            ->limit(10)
            ->get();

        $memberships = $customer->customerMemberships()
            ->with('plan')
            ->orderByDesc('id')
            ->get();

        $packages = $customer->customerPackages()
            ->with('package.items.service')
            ->orderByDesc('id')
            ->get();

        $invoices = Invoice::where('customer_id', $customer->id)
            ->orderByDesc('invoice_date')
            ->get();

        return view('portal.dashboard', compact('customer', 'upcomingAppointments', 'pastAppointments', 'memberships', 'packages', 'invoices'));
    }

    public function downloadInvoice(Invoice $invoice)
    {
        $pdfService = new PDFService();
        return $pdfService->generateInvoicePDF($invoice);
    }
}
