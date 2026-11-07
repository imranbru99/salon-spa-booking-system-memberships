<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\StaffProfile;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\Booking\BookingService;
use App\Services\Payment\PaymentGatewayManager;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    /**
     * API Token / Auth Demo Endpoint
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        // Return user info and simulated bearer token
        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully',
            'token' => 'demo_token_' . md5($user->id . time()),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'tenant_id' => $user->tenant_id,
            ],
        ]);
    }

    /**
     * Get Branches
     */
    public function branches(): JsonResponse
    {
        $branches = Branch::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $branches]);
    }

    /**
     * Get Services
     */
    public function services(Request $request): JsonResponse
    {
        $query = Service::where('is_active', true)->with('category');
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        return response()->json(['success' => true, 'data' => $query->paginate(20)]);
    }

    /**
     * Get Staff
     */
    public function staff(): JsonResponse
    {
        $staff = StaffProfile::where('status', 'active')->with(['user', 'branch'])->get();
        return response()->json(['success' => true, 'data' => $staff]);
    }

    /**
     * Get Customers
     */
    public function customers(): JsonResponse
    {
        $customers = Customer::paginate(20);
        return response()->json(['success' => true, 'data' => $customers]);
    }

    /**
     * Get Appointments
     */
    public function appointments(Request $request): JsonResponse
    {
        $query = Appointment::with(['customer', 'branch', 'services.service']);
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        return response()->json(['success' => true, 'data' => $query->paginate(20)]);
    }

    /**
     * Create Appointment via API
     */
    public function createAppointment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'required|exists:services,id',
            'customer_id' => 'required|exists:customers,id',
            'staff_id' => 'nullable|exists:staff_profiles,id',
            'appointment_date' => 'required|date',
            'start_time' => 'required',
            'customer_notes' => 'nullable|string',
        ]);

        $bookingService = new BookingService();
        $appointment = $bookingService->createAppointment($validated, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'data' => $appointment,
        ], 201);
    }

    /**
     * Get Packages
     */
    public function packages(): JsonResponse
    {
        $packages = ServicePackage::where('is_active', true)->with('items.service')->get();
        return response()->json(['success' => true, 'data' => $packages]);
    }

    /**
     * Get Memberships
     */
    public function memberships(): JsonResponse
    {
        $memberships = MembershipPlan::where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $memberships]);
    }

    /**
     * Check Availability
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:staff_profiles,id',
            'date' => 'required|date',
        ]);

        $bookingService = new BookingService();
        $slots = $bookingService->getAvailableSlots(
            (int) $validated['branch_id'],
            (int) $validated['service_id'],
            !empty($validated['staff_id']) ? (int) $validated['staff_id'] : null,
            $validated['date']
        );

        return response()->json(['success' => true, 'slots' => $slots]);
    }

    /**
     * AI Business Assistant Query API
     */
    public function askAIAssistant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $aiService = new AIService();
        $result = $aiService->askBusinessAssistant($validated['prompt']);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Payment Webhook Bridge
     */
    public function handlePaymentWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $provider = $request->input('provider', 'stripe');

        $gatewayManager = new PaymentGatewayManager();
        $result = $gatewayManager->handleWebhook($provider, $payload);

        return response()->json(['success' => true, 'result' => $result]);
    }
}
