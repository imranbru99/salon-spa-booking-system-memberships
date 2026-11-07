<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\CashRegister;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerMembership;
use App\Models\CustomerPackage;
use App\Models\Equipment;
use App\Models\GiftCard;
use App\Models\Invoice;
use App\Models\LoyaltyAccount;
use App\Models\MembershipPlan;
use App\Models\PackageItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Review;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\StaffProfile;
use App\Models\StaffSchedule;
use App\Models\SubscriptionPlan;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\TenantDomain;
use App\Models\TenantSubscription;
use App\Models\TreatmentRoom;
use App\Models\User;
use App\Models\WebsitePage;
use App\Models\WebsiteSection;
use App\Services\Modules\ModuleRegistry;
use App\Services\Modules\ModuleService;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $demoPassword = env('DEMO_PASSWORD', 'Password123!');
        $hashedPassword = Hash::make($demoPassword);

        // ==========================================
        // 1. SAAS PLATFORM SUBSCRIPTION PLANS
        // ==========================================
        $starterPlan = SubscriptionPlan::create([
            'name' => 'Starter Salon',
            'slug' => 'starter',
            'description' => 'Ideal for independent beauty studios and solo stylists.',
            'price_monthly' => 29.00,
            'price_yearly' => 290.00,
            'max_branches' => 1,
            'max_staff' => 3,
            'max_bookings_per_month' => 300,
            'features' => ['Online Booking', 'Client CRM', 'Appointment Reminders', 'Basic Reports'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $proPlan = SubscriptionPlan::create([
            'name' => 'Professional Salon & Spa',
            'slug' => 'professional',
            'description' => 'Comprehensive management for thriving salons, day spas, and wellness studios.',
            'price_monthly' => 79.00,
            'price_yearly' => 790.00,
            'max_branches' => 3,
            'max_staff' => 15,
            'max_bookings_per_month' => 2000,
            'features' => ['Multi-branch', 'POS & Payments', 'Prepaid Packages', 'Memberships', 'Inventory Tracking', 'Staff Commissions'],
            'is_active' => true,
            'is_popular' => true,
            'sort_order' => 2,
        ]);

        $enterprisePlan = SubscriptionPlan::create([
            'name' => 'Enterprise Luxury Chain',
            'slug' => 'enterprise',
            'description' => 'Unlimited scaling for luxury spa resorts, franchises, and international salon chains.',
            'price_monthly' => 199.00,
            'price_yearly' => 1990.00,
            'max_branches' => 25,
            'max_staff' => 100,
            'max_bookings_per_month' => 20000,
            'features' => ['Unlimited Staff', 'AI Assistant & Forecasts', 'Hardware Attendance Hub', 'Double-entry Accounting', 'Custom API & Webhooks', 'Dedicated Account Manager'],
            'is_active' => true,
            'sort_order' => 3,
        ]);

        // ==========================================
        // 2. DEMO TENANT: GLOW & GRACE SALON AND SPA
        // ==========================================
        $tenant = Tenant::create([
            'plan_id' => $proPlan->id,
            'name' => 'Glow & Grace Salon and Spa',
            'slug' => 'glowgrace',
            'business_type' => 'luxury_spa',
            'email' => 'concierge@glowgrace.test',
            'phone' => '+1 (555) 019-2834',
            'address' => '742 Evergreen Botanical Promenade, Suite 400',
            'city' => 'Beverly Hills',
            'country' => 'US',
            'currency' => 'USD',
            'timezone' => 'America/Los_Angeles',
            'locale' => 'en',
            'primary_color' => '#831843',
            'secondary_color' => '#f43f5e',
            'status' => 'active',
            'trial_ends_at' => Carbon::now()->addMonths(6),
            'is_demo' => true,
            'settings' => [
                'tax_rate' => 8.5,
                'allow_online_booking' => true,
                'require_deposit' => true,
                'deposit_percent' => 20,
                'slot_interval' => 30,
            ],
        ]);

        TenantDomain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'glowgrace.localhost',
            'is_primary' => true,
            'is_verified' => true,
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $proPlan->id,
            'billing_cycle' => 'monthly',
            'price' => 79.00,
            'currency' => 'USD',
            'starts_at' => Carbon::now()->subMonths(3),
            'ends_at' => Carbon::now()->addMonths(9),
            'status' => 'active',
        ]);

        // Enable all modules for demo tenant
        foreach (ModuleRegistry::all() as $moduleKey => $meta) {
            ModuleService::enable($moduleKey, $tenant->id);
        }

        // Set tenant context for model creation
        TenantContext::setTenant($tenant);

        // ==========================================
        // 3. DEMO ACCOUNTS & ROLES
        // ==========================================
        // Platform Admin (Platform level)
        User::create([
            'tenant_id' => null,
            'name' => 'Alexander Vance (Platform Admin)',
            'email' => 'platform@example.test',
            'password' => $hashedPassword,
            'role' => 'super_admin',
            'phone' => '+1 (555) 100-0001',
            'is_active' => true,
        ]);

        // Business Owner
        $ownerUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Eleanor Vance (Business Owner)',
            'email' => 'owner@glowgrace.test',
            'password' => $hashedPassword,
            'role' => 'business_owner',
            'phone' => '+1 (555) 200-0001',
            'is_active' => true,
        ]);

        // ==========================================
        // 4. BRANCHES & RESOURCES
        // ==========================================
        $flagshipBranch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Flagship Downtown Salon & Spa Sanctuary',
            'slug' => 'downtown-flagship',
            'code' => 'DT-01',
            'address' => '742 Evergreen Promenade, Suite 400',
            'city' => 'Beverly Hills',
            'state' => 'CA',
            'postal_code' => '90210',
            'country' => 'US',
            'phone' => '+1 (555) 019-2834',
            'email' => 'downtown@glowgrace.test',
            'is_main' => true,
            'is_active' => true,
            'business_hours' => [
                'monday' => ['open' => '09:00', 'close' => '19:00'],
                'tuesday' => ['open' => '09:00', 'close' => '19:00'],
                'wednesday' => ['open' => '09:00', 'close' => '19:00'],
                'thursday' => ['open' => '09:00', 'close' => '20:00'],
                'friday' => ['open' => '09:00', 'close' => '20:00'],
                'saturday' => ['open' => '08:30', 'close' => '19:00'],
                'sunday' => ['open' => '10:00', 'close' => '17:00'],
            ],
        ]);

        $resortBranch = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Ocean Vista Coastal Spa Retreat',
            'slug' => 'ocean-vista',
            'code' => 'OV-02',
            'address' => '120 Ocean View Cliffs Drive',
            'city' => 'Malibu',
            'state' => 'CA',
            'postal_code' => '90265',
            'country' => 'US',
            'phone' => '+1 (555) 019-4567',
            'email' => 'malibu@glowgrace.test',
            'is_main' => false,
            'is_active' => true,
            'business_hours' => [
                'monday' => ['open' => '10:00', 'close' => '18:00'],
                'tuesday' => ['open' => '10:00', 'close' => '18:00'],
                'wednesday' => ['open' => '10:00', 'close' => '18:00'],
                'thursday' => ['open' => '10:00', 'close' => '19:00'],
                'friday' => ['open' => '09:00', 'close' => '20:00'],
                'saturday' => ['open' => '09:00', 'close' => '20:00'],
                'sunday' => ['open' => '10:00', 'close' => '18:00'],
            ],
        ]);

        // Treatment Rooms
        $room1 = TreatmentRoom::create(['tenant_id' => $tenant->id, 'branch_id' => $flagshipBranch->id, 'name' => 'Rose Quartz Hydrotherapy Suite', 'code' => 'R-101', 'capacity' => 1, 'is_active' => true]);
        $room2 = TreatmentRoom::create(['tenant_id' => $tenant->id, 'branch_id' => $flagshipBranch->id, 'name' => 'Amber Glow Aromatherapy Lounge', 'code' => 'R-102', 'capacity' => 2, 'is_active' => true]);
        $room3 = TreatmentRoom::create(['tenant_id' => $tenant->id, 'branch_id' => $flagshipBranch->id, 'name' => 'Diamond Aesthetic Laser Suite', 'code' => 'R-103', 'capacity' => 1, 'is_active' => true]);
        $room4 = TreatmentRoom::create(['tenant_id' => $tenant->id, 'branch_id' => $resortBranch->id, 'name' => 'Ocean Breeze Vichy Shower Room', 'code' => 'M-201', 'capacity' => 1, 'is_active' => true]);

        // Equipment
        Equipment::create(['tenant_id' => $tenant->id, 'branch_id' => $flagshipBranch->id, 'name' => 'HydraFacial Elite MD Machine', 'code' => 'EQ-HYD-01', 'serial_number' => 'HF-2026-9938', 'status' => 'available']);
        Equipment::create(['tenant_id' => $tenant->id, 'branch_id' => $flagshipBranch->id, 'name' => 'Dermalux Tri-Wave LED Phototherapy', 'code' => 'EQ-LED-02', 'serial_number' => 'DL-9941', 'status' => 'available']);
        Equipment::create(['tenant_id' => $tenant->id, 'branch_id' => $resortBranch->id, 'name' => 'Endermologie Cellular Body Sculptor', 'code' => 'EQ-END-03', 'serial_number' => 'LPG-8821', 'status' => 'available']);

        // Cash Registers
        $cashRegister = CashRegister::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $flagshipBranch->id,
            'name' => 'Main Reception Register #1',
            'status' => 'open',
            'opened_by' => $ownerUser->id,
            'opening_cash' => 350.00,
            'opened_at' => Carbon::today()->setHour(8)->setMinute(30),
        ]);

        // Branch Manager Account
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Camilla Hayes (Branch Manager)',
            'email' => 'manager@glowgrace.test',
            'password' => $hashedPassword,
            'role' => 'branch_manager',
            'branch_id' => $flagshipBranch->id,
            'phone' => '+1 (555) 200-0002',
            'is_active' => true,
        ]);

        // Receptionist Account
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Chloe Bennett (Lead Receptionist)',
            'email' => 'reception@glowgrace.test',
            'password' => $hashedPassword,
            'role' => 'receptionist',
            'branch_id' => $flagshipBranch->id,
            'phone' => '+1 (555) 200-0003',
            'is_active' => true,
        ]);

        // Cashier Account
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Lucas Miller (Head Cashier)',
            'email' => 'cashier@glowgrace.test',
            'password' => $hashedPassword,
            'role' => 'cashier',
            'branch_id' => $flagshipBranch->id,
            'phone' => '+1 (555) 200-0004',
            'is_active' => true,
        ]);

        // Inventory Manager Account
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Marcus Rivera (Inventory Lead)',
            'email' => 'inventory@glowgrace.test',
            'password' => $hashedPassword,
            'role' => 'inventory_manager',
            'branch_id' => $flagshipBranch->id,
            'phone' => '+1 (555) 200-0005',
            'is_active' => true,
        ]);

        // Accountant Account
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Sophia Chang (Financial Controller)',
            'email' => 'accountant@glowgrace.test',
            'password' => $hashedPassword,
            'role' => 'accountant',
            'branch_id' => $flagshipBranch->id,
            'phone' => '+1 (555) 200-0006',
            'is_active' => true,
        ]);

        // ==========================================
        // 5. 15 STAFF PROFILES & SPECIALISTS
        // ==========================================
        $staffRoster = [
            ['name' => 'Julian Ross', 'email' => 'stylist@glowgrace.test', 'title' => 'Master Creative Hair Director', 'role' => 'stylist', 'rate' => 25.0, 'salary' => 4500.00],
            ['name' => 'Maya Lin', 'email' => 'therapist@glowgrace.test', 'title' => 'Lead Holistic Spa Therapist', 'role' => 'spa_therapist', 'rate' => 20.0, 'salary' => 4200.00],
            ['name' => 'Gabriel Moreau', 'email' => 'gabriel@glowgrace.test', 'title' => 'Senior Balayage & Color Specialist', 'role' => 'stylist', 'rate' => 22.0, 'salary' => 4000.00],
            ['name' => 'Aria Montgomery', 'email' => 'aria@glowgrace.test', 'title' => 'Medical Aesthetician & Skin Artisan', 'role' => 'beautician', 'rate' => 20.0, 'salary' => 3800.00],
            ['name' => 'Liam O\'Connor', 'email' => 'liam@glowgrace.test', 'title' => 'Master Barber & Grooming Consultant', 'role' => 'hairdresser', 'rate' => 18.0, 'salary' => 3600.00],
            ['name' => 'Seraphina DuBois', 'email' => 'seraphina@glowgrace.test', 'title' => 'Celebrity Makeup & Bridal Stylist', 'role' => 'makeup_artist', 'rate' => 25.0, 'salary' => 4200.00],
            ['name' => 'Kenji Takahashi', 'email' => 'kenji@glowgrace.test', 'title' => 'Deep Tissue & Shiatsu Specialist', 'role' => 'massage_therapist', 'rate' => 22.0, 'salary' => 4100.00],
            ['name' => 'Zara Larsson', 'email' => 'zara@glowgrace.test', 'title' => 'Luxury Nail Artist & Extension Designer', 'role' => 'nail_technician', 'rate' => 18.0, 'salary' => 3400.00],
            ['name' => 'Elena Rostova', 'email' => 'elena@glowgrace.test', 'title' => 'Aromatherapy & Herbal Wrap Therapist', 'role' => 'spa_therapist', 'rate' => 20.0, 'salary' => 3900.00],
            ['name' => 'Dante Rossi', 'email' => 'dante@glowgrace.test', 'title' => 'Keratin & Scalp Health Specialist', 'role' => 'stylist', 'rate' => 20.0, 'salary' => 3800.00],
            ['name' => 'Isabella Santos', 'email' => 'isabella@glowgrace.test', 'title' => 'Brow Arch Architect & Lash Artist', 'role' => 'beautician', 'rate' => 18.0, 'salary' => 3500.00],
            ['name' => 'Nia Washington', 'email' => 'nia@glowgrace.test', 'title' => 'Ayurvedic Bodywork Practitioner', 'role' => 'massage_therapist', 'rate' => 22.0, 'salary' => 4000.00],
            ['name' => 'Sven Lindholm', 'email' => 'sven@glowgrace.test', 'title' => 'Precision Fade & Beard Sculptor', 'role' => 'hairdresser', 'rate' => 18.0, 'salary' => 3500.00],
            ['name' => 'Amira Al-Mansoor', 'email' => 'amira@glowgrace.test', 'title' => 'Hammam Ritual & Scrub Artisan', 'role' => 'spa_therapist', 'rate' => 20.0, 'salary' => 3900.00],
            ['name' => 'Celeste Sterling', 'email' => 'celeste@glowgrace.test', 'title' => 'Collagen Infusion & LED Therapist', 'role' => 'beautician', 'rate' => 22.0, 'salary' => 4100.00],
        ];

        $staffModels = [];
        foreach ($staffRoster as $idx => $st) {
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $st['name'],
                'email' => $st['email'],
                'password' => $hashedPassword,
                'role' => $st['role'],
                'branch_id' => ($idx % 3 === 0) ? $resortBranch->id : $flagshipBranch->id,
                'phone' => '+1 (555) 300-' . str_pad((string)($idx + 1), 4, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);

            $profile = StaffProfile::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'employee_code' => 'STF-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT),
                'job_title' => $st['title'],
                'bio' => "Distinguished {$st['title']} with over a decade of luxury beauty, salon and wellness experience.",
                'commission_rate' => $st['rate'],
                'employment_type' => 'full_time',
                'base_salary' => $st['salary'],
                'joining_date' => Carbon::now()->subMonths(18 - $idx),
                'status' => 'active',
                'emergency_contact' => '+1 (555) 999-8888',
            ]);

            // Schedules for each day
            for ($d = 1; $d <= 6; $d++) {
                StaffSchedule::create([
                    'tenant_id' => $tenant->id,
                    'staff_id' => $profile->id,
                    'branch_id' => $profile->branch_id,
                    'day_of_week' => $d,
                    'start_time' => '09:00:00',
                    'end_time' => '18:00:00',
                    'break_start' => '13:00:00',
                    'break_end' => '14:00:00',
                    'is_working' => true,
                ]);
            }

            $staffModels[] = $profile;
        }

        // ==========================================
        // 6. SERVICE CATEGORIES & 30 SERVICES
        // ==========================================
        $categoriesData = [
            ['name' => 'Hair Couture & Styling', 'slug' => 'hair-couture', 'desc' => 'Haute coiffure, precision cuts, and styling rituals.'],
            ['name' => 'Artisan Hair Color & Balayage', 'slug' => 'hair-color', 'desc' => 'Bespoke highlights, gloss glazing, and corrective color.'],
            ['name' => 'Facial & Skin Rejuvenation', 'slug' => 'facial-skin', 'desc' => 'Clinical skin health, hydro-infusions, and organic lifting.'],
            ['name' => 'Spa Massage & Bodywork', 'slug' => 'spa-massage', 'desc' => 'Therapeutic relaxation, Swedish, hot stone, and deep tissue.'],
            ['name' => 'Nail Studio & Manicure', 'slug' => 'nail-studio', 'desc' => 'Russian manicure, gel extensions, and nail couture.'],
            ['name' => 'Eye & Brow Architecture', 'slug' => 'lash-brow', 'desc' => 'Lamination, micro-pigmentation, and silk lash extensions.'],
            ['name' => 'Wellness & Hydrotherapy', 'slug' => 'wellness-hydro', 'desc' => 'Detox steam therapy, herbal baths, and holistic wellbeing.'],
            ['name' => 'Executive Men\'s Barbering', 'slug' => 'mens-barbering', 'desc' => 'Gentlemen\'s precision cut, hot towel shave, and beard sculpting.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = ServiceCategory::create([
                'tenant_id' => $tenant->id,
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'description' => $cat['desc'],
                'is_active' => true,
            ]);
        }

        $servicesCatalog = [
            // Hair
            ['cat' => 'hair-couture', 'name' => 'Signature Haute Coiffure Cut & Blowout', 'slug' => 'signature-cut-blowout', 'price' => 125.00, 'sale' => null, 'dur' => 60, 'dep' => 30.00],
            ['cat' => 'hair-couture', 'name' => 'Botanical Scalp Detox & Deep Conditioning', 'slug' => 'botanical-scalp-detox', 'price' => 95.00, 'sale' => 85.00, 'dur' => 45, 'dep' => 20.00],
            ['cat' => 'hair-couture', 'name' => 'Brazilian Keratin Silk Therapy', 'slug' => 'brazilian-keratin-silk', 'price' => 280.00, 'sale' => null, 'dur' => 150, 'dep' => 75.00],
            ['cat' => 'hair-couture', 'name' => 'Bridal Royalty Hair Design & Veil Setting', 'slug' => 'bridal-hair-design', 'price' => 220.00, 'sale' => null, 'dur' => 90, 'dep' => 50.00],
            
            // Color
            ['cat' => 'hair-color', 'name' => 'Bespoke Parisian Sun-Kissed Balayage', 'slug' => 'parisian-balayage', 'price' => 250.00, 'sale' => 225.00, 'dur' => 180, 'dep' => 60.00],
            ['cat' => 'hair-color', 'name' => 'Platinum Lightening & Opal Gloss Toner', 'slug' => 'platinum-opal-gloss', 'price' => 195.00, 'sale' => null, 'dur' => 120, 'dep' => 50.00],
            ['cat' => 'hair-color', 'name' => 'Regrowth Color Touch-Up & Shine Glaze', 'slug' => 'color-touch-up-glaze', 'price' => 110.00, 'sale' => null, 'dur' => 75, 'dep' => 25.00],
            ['cat' => 'hair-color', 'name' => 'Organic Grey Blending Treatment', 'slug' => 'organic-grey-blending', 'price' => 135.00, 'sale' => null, 'dur' => 60, 'dep' => 30.00],

            // Facials
            ['cat' => 'facial-skin', 'name' => 'Diamond Hydra-Infusion Glow Facial', 'slug' => 'diamond-hydra-infusion', 'price' => 175.00, 'sale' => 155.00, 'dur' => 60, 'dep' => 40.00],
            ['cat' => 'facial-skin', 'name' => '24K Gold Cellular Anti-Aging Ritual', 'slug' => '24k-gold-cellular-facial', 'price' => 260.00, 'sale' => null, 'dur' => 75, 'dep' => 60.00],
            ['cat' => 'facial-skin', 'name' => 'Custom Dermaplaning & Peptide Infusion', 'slug' => 'dermaplaning-peptide', 'price' => 140.00, 'sale' => null, 'dur' => 60, 'dep' => 35.00],
            ['cat' => 'facial-skin', 'name' => 'Clarifying Marine Oxygen Skin Detox', 'slug' => 'marine-oxygen-skin-detox', 'price' => 150.00, 'sale' => null, 'dur' => 60, 'dep' => 30.00],

            // Spa Massage
            ['cat' => 'spa-massage', 'name' => 'Deep Tissue Therapeutic Restoration', 'slug' => 'deep-tissue-restoration', 'price' => 165.00, 'sale' => null, 'dur' => 60, 'dep' => 40.00],
            ['cat' => 'spa-massage', 'name' => 'Heated Basalt Volcanic Stone Massage', 'slug' => 'heated-volcanic-stone', 'price' => 185.00, 'sale' => 165.00, 'dur' => 75, 'dep' => 45.00],
            ['cat' => 'spa-massage', 'name' => 'Lavender & Rose Aromatherapy Massage', 'slug' => 'lavender-rose-aromatherapy', 'price' => 150.00, 'sale' => null, 'dur' => 60, 'dep' => 35.00],
            ['cat' => 'spa-massage', 'name' => 'Couples Harmony Serenity Suite Massage', 'slug' => 'couples-harmony-suite', 'price' => 320.00, 'sale' => 295.00, 'dur' => 75, 'dep' => 80.00],

            // Nails
            ['cat' => 'nail-studio', 'name' => 'Russian Dry Couture Manicure', 'slug' => 'russian-dry-manicure', 'price' => 75.00, 'sale' => null, 'dur' => 60, 'dep' => 20.00],
            ['cat' => 'nail-studio', 'name' => 'Luxury Rose Water & Paraffin Pedicure', 'slug' => 'rose-water-paraffin-pedicure', 'price' => 90.00, 'sale' => 80.00, 'dur' => 60, 'dep' => 25.00],
            ['cat' => 'nail-studio', 'name' => 'Apres Gel-X Sculpted Nail Extensions', 'slug' => 'apres-gel-x-extensions', 'price' => 130.00, 'sale' => null, 'dur' => 90, 'dep' => 35.00],
            ['cat' => 'nail-studio', 'name' => 'Minimalist Hand-Painted Nail Art Master', 'slug' => 'hand-painted-nail-art', 'price' => 45.00, 'sale' => null, 'dur' => 30, 'dep' => 15.00],

            // Lash & Brow
            ['cat' => 'lash-brow', 'name' => 'Keratin Lash Lift & Midnight Tint', 'slug' => 'keratin-lash-lift-tint', 'price' => 95.00, 'sale' => null, 'dur' => 45, 'dep' => 25.00],
            ['cat' => 'lash-brow', 'name' => 'Fluffy Feather Brow Lamination & Shape', 'slug' => 'feather-brow-lamination', 'price' => 85.00, 'sale' => 75.00, 'dur' => 45, 'dep' => 20.00],
            ['cat' => 'lash-brow', 'name' => 'Russian Volume Silk Eyelash Set', 'slug' => 'russian-volume-silk-lashes', 'price' => 195.00, 'sale' => null, 'dur' => 105, 'dep' => 50.00],

            // Wellness & Hydro
            ['cat' => 'wellness-hydro', 'name' => 'Dead Sea Mud & Seaweed Body Wrap', 'slug' => 'dead-sea-mud-wrap', 'price' => 170.00, 'sale' => null, 'dur' => 60, 'dep' => 40.00],
            ['cat' => 'wellness-hydro', 'name' => 'Organic Turkish Eucalyptus Hammam', 'slug' => 'eucalyptus-hammam-ritual', 'price' => 190.00, 'sale' => 175.00, 'dur' => 75, 'dep' => 50.00],
            ['cat' => 'wellness-hydro', 'name' => 'Sound Bath & Chakra Alignment Therapy', 'slug' => 'sound-bath-chakra', 'price' => 110.00, 'sale' => null, 'dur' => 50, 'dep' => 30.00],

            // Men's Barbering
            ['cat' => 'mens-barbering', 'name' => 'The Sovereign Executive Cut & Scalp Rub', 'slug' => 'sovereign-executive-cut', 'price' => 75.00, 'sale' => null, 'dur' => 45, 'dep' => 20.00],
            ['cat' => 'mens-barbering', 'name' => 'Traditional Straight-Razor Hot Towel Shave', 'slug' => 'straight-razor-hot-towel', 'price' => 65.00, 'sale' => null, 'dur' => 40, 'dep' => 15.00],
            ['cat' => 'mens-barbering', 'name' => 'Beard Sculpting & Botanical Oil Treatment', 'slug' => 'beard-sculpting-treatment', 'price' => 50.00, 'sale' => null, 'dur' => 30, 'dep' => 15.00],
            ['cat' => 'mens-barbering', 'name' => 'Gentleman\'s Complete Grooming Package', 'slug' => 'gentlemans-complete-grooming', 'price' => 145.00, 'sale' => 130.00, 'dur' => 90, 'dep' => 40.00],
        ];

        $serviceModels = [];
        foreach ($servicesCatalog as $svc) {
            $catId = $categories[$svc['cat']]->id;
            $service = Service::create([
                'tenant_id' => $tenant->id,
                'category_id' => $catId,
                'name' => $svc['name'],
                'slug' => $svc['slug'],
                'description' => "Indulgent {$svc['name']} performed with organic botanicals and world-class care.",
                'price' => $svc['price'],
                'sale_price' => $svc['sale'],
                'duration_minutes' => $svc['dur'],
                'buffer_before' => 5,
                'buffer_after' => 10,
                'deposit_required' => true,
                'deposit_amount' => $svc['dep'],
                'commission_rate' => 20.0,
                'is_active' => true,
                'is_featured' => $svc['sale'] !== null,
                'preparation_instructions' => 'Please arrive 10 minutes prior to your scheduled treatment time.',
                'aftercare_instructions' => 'Stay hydrated and avoid direct sunlight for 12 hours after treatment.',
            ]);

            // Associate with staff & branches
            $service->branches()->sync([$flagshipBranch->id, $resortBranch->id]);

            // Assign 3 random staff
            $assignedStaffIds = collect($staffModels)->random(min(4, count($staffModels)))->pluck('id')->toArray();
            $service->staff()->sync($assignedStaffIds);

            $serviceModels[] = $service;
        }

        // ==========================================
        // 7. PREPAID PACKAGES & MEMBERSHIP PLANS
        // ==========================================
        // Packages
        $pkg1 = ServicePackage::create([
            'tenant_id' => $tenant->id,
            'name' => 'Glow Bridal Splendor Package',
            'slug' => 'glow-bridal-splendor',
            'description' => 'Complete head-to-toe beauty package for the bride-to-be: 2 Facials, 1 Hair Design, 1 Manicure, 1 Pedicure.',
            'price' => 495.00,
            'validity_days' => 180,
            'is_active' => true,
            'is_featured' => true,
        ]);
        PackageItem::create(['package_id' => $pkg1->id, 'service_id' => $serviceModels[8]->id, 'quantity' => 2]); // Facials
        PackageItem::create(['package_id' => $pkg1->id, 'service_id' => $serviceModels[3]->id, 'quantity' => 1]); // Bridal hair
        PackageItem::create(['package_id' => $pkg1->id, 'service_id' => $serviceModels[16]->id, 'quantity' => 1]); // Mani
        PackageItem::create(['package_id' => $pkg1->id, 'service_id' => $serviceModels[17]->id, 'quantity' => 1]); // Pedi

        $pkg2 = ServicePackage::create([
            'tenant_id' => $tenant->id,
            'name' => 'Serenity Detox Spa Day Retreat',
            'slug' => 'serenity-detox-spa-day',
            'description' => 'A full day of restorative relaxation: 1 Volcanic Stone Massage, 1 Marine Oxygen Facial, 1 Body Wrap.',
            'price' => 420.00,
            'validity_days' => 90,
            'is_active' => true,
            'is_featured' => true,
        ]);
        PackageItem::create(['package_id' => $pkg2->id, 'service_id' => $serviceModels[13]->id, 'quantity' => 1]);
        PackageItem::create(['package_id' => $pkg2->id, 'service_id' => $serviceModels[11]->id, 'quantity' => 1]);
        PackageItem::create(['package_id' => $pkg2->id, 'service_id' => $serviceModels[23]->id, 'quantity' => 1]);

        $pkg3 = ServicePackage::create([
            'tenant_id' => $tenant->id,
            'name' => 'Ultimate Hair Transformation Trio',
            'slug' => 'ultimate-hair-trio',
            'description' => 'Complete hair metamorphosis: 1 Parisian Balayage, 1 Keratin Treatment, 1 Signature Cut.',
            'price' => 540.00,
            'validity_days' => 120,
            'is_active' => true,
        ]);
        PackageItem::create(['package_id' => $pkg3->id, 'service_id' => $serviceModels[4]->id, 'quantity' => 1]);
        PackageItem::create(['package_id' => $pkg3->id, 'service_id' => $serviceModels[2]->id, 'quantity' => 1]);
        PackageItem::create(['package_id' => $pkg3->id, 'service_id' => $serviceModels[0]->id, 'quantity' => 1]);

        $pkg4 = ServicePackage::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gentlemen\'s Executive Ritual',
            'slug' => 'gentlemans-executive-ritual',
            'description' => '4 Sovereign Cuts, 2 Traditional Shaves, and 2 Scalp Treatments.',
            'price' => 320.00,
            'validity_days' => 180,
            'is_active' => true,
        ]);
        PackageItem::create(['package_id' => $pkg4->id, 'service_id' => $serviceModels[26]->id, 'quantity' => 4]);
        PackageItem::create(['package_id' => $pkg4->id, 'service_id' => $serviceModels[27]->id, 'quantity' => 2]);

        $pkg5 = ServicePackage::create([
            'tenant_id' => $tenant->id,
            'name' => 'Deluxe Mani-Pedi Indulgence (Series of 5)',
            'slug' => 'deluxe-mani-pedi-5',
            'description' => 'Enjoy 5 complete Russian manicures and Rose Water pedicures with priority scheduling.',
            'price' => 690.00,
            'validity_days' => 365,
            'is_active' => true,
        ]);
        PackageItem::create(['package_id' => $pkg5->id, 'service_id' => $serviceModels[16]->id, 'quantity' => 5]);
        PackageItem::create(['package_id' => $pkg5->id, 'service_id' => $serviceModels[17]->id, 'quantity' => 5]);

        // Membership Plans
        $memSilver = MembershipPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'Silver Beauty Club',
            'slug' => 'silver-beauty-club',
            'description' => 'Essential monthly perks, 10% discount across all services, and birthday gift.',
            'price' => 49.00,
            'billing_frequency' => 'monthly',
            'discount_percentage' => 10.0,
            'free_service_credits' => 0,
            'priority_booking' => false,
            'is_active' => true,
            'features' => ['10% Off All Services', '10% Off Retail Products', 'Birthday Beauty Treat'],
        ]);

        $memGold = MembershipPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gold Wellness Sanctuary',
            'slug' => 'gold-wellness-sanctuary',
            'description' => '15% savings, 1 monthly complimentary HydraFacial or Massage credit, and priority reservations.',
            'price' => 99.00,
            'billing_frequency' => 'monthly',
            'discount_percentage' => 15.0,
            'free_service_credits' => 1,
            'priority_booking' => true,
            'is_active' => true,
            'features' => ['15% Off All Services', '1 Monthly Complimentary Facial Credit', 'Priority Weekend Bookings', 'Exclusive Product Samples'],
        ]);

        $memPlatinum = MembershipPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'Platinum Royal Spa Membership',
            'slug' => 'platinum-royal-spa',
            'description' => '20% savings, 2 free premium service credits monthly, private suite access, and valet concierge.',
            'price' => 199.00,
            'billing_frequency' => 'monthly',
            'discount_percentage' => 20.0,
            'free_service_credits' => 2,
            'priority_booking' => true,
            'is_active' => true,
            'features' => ['20% Off All Services & Retail', '2 Monthly Free Premium Spa Sessions', 'Complimentary Champagne & Refreshments', 'Private Suite Guarantee'],
        ]);

        $memDiamond = MembershipPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'VIP Diamond Annual Circle',
            'slug' => 'vip-diamond-annual',
            'description' => 'The pinnacle of luxury: 25% savings, 12 complimentary sessions per year, and 24/7 dedicated concierge.',
            'price' => 1899.00,
            'billing_frequency' => 'annual',
            'discount_percentage' => 25.0,
            'free_service_credits' => 12,
            'priority_booking' => true,
            'is_active' => true,
            'features' => ['25% Off All Services & Products', '12 Complimentary Luxury Treatments', 'Bring a Guest for 50% Off Once a Month', 'Personal VIP Stylist Assigned'],
        ]);

        // ==========================================
        // 8. 50 REALISTIC CUSTOMERS
        // ==========================================
        // Primary Demo Customer Account
        $demoCustomerUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Victoria Sterling (VIP Client)',
            'email' => 'customer@example.test',
            'password' => $hashedPassword,
            'role' => 'customer',
            'phone' => '+1 (555) 400-0001',
            'is_active' => true,
        ]);

        $demoCustomer = Customer::create([
            'tenant_id' => $tenant->id,
            'user_id' => $demoCustomerUser->id,
            'first_name' => 'Victoria',
            'last_name' => 'Sterling',
            'email' => 'customer@example.test',
            'phone' => '+1 (555) 400-0001',
            'date_of_birth' => '1992-06-15',
            'gender' => 'female',
            'address' => '9440 Wilshire Blvd, Beverly Hills, CA',
            'preferred_branch_id' => $flagshipBranch->id,
            'preferred_staff_id' => $staffModels[0]->id,
            'segment' => 'vip',
            'loyalty_points' => 850,
            'notes' => 'Prefers chamomile tea with organic honey upon arrival. Sensitive to lavender.',
            'is_active' => true,
        ]);

        LoyaltyAccount::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $demoCustomer->id,
            'current_points' => 850,
            'total_earned' => 1250,
            'total_redeemed' => 400,
            'tier' => 'gold',
        ]);

        CustomerMembership::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $demoCustomer->id,
            'membership_plan_id' => $memPlatinum->id,
            'status' => 'active',
            'starts_at' => Carbon::now()->subMonths(2)->toDateString(),
            'ends_at' => Carbon::now()->addMonths(10)->toDateString(),
            'auto_renew' => true,
            'remaining_free_credits' => 3,
        ]);

        // Generate 49 more customers
        $customerNames = [
            ['Hannah', 'Abbott'], ['Beatrice', 'Sinclair'], ['Gwendolyn', 'Pierce'], ['Sebastian', 'Cole'],
            ['Genevieve', 'Dupont'], ['Penelope', 'Cruz'], ['Oliver', 'Thorne'], ['Clara', 'Oswald'],
            ['Serena', 'van der Woodsen'], ['Blair', 'Waldorf'], ['Felix', 'Wright'], ['Audrey', 'Hepburn'],
            ['Margot', 'Robbie'], ['Damian', 'Wayne'], ['Daphne', 'Bridgerton'], ['Eloise', 'Bridgerton'],
            ['Anthony', 'Bridgerton'], ['Charlotte', 'York'], ['Miranda', 'Hobbes'], ['Samantha', 'Jones'],
            ['Julian', 'Assange'], ['Harrison', 'Ford'], ['Vivienne', 'Westwood'], ['Stella', 'McCartney'],
            ['Alexander', 'McQueen'], ['Christophe', 'Robin'], ['Gisele', 'Bundchen'], ['Naomi', 'Campbell'],
            ['Kendall', 'Jenner'], ['Gigi', 'Hadid'], ['Bella', 'Hadid'], ['Karlie', 'Kloss'],
            ['Cara', 'Delevingne'], ['Adwoa', 'Aboah'], ['Kaia', 'Gerber'], ['Irina', 'Shayk'],
            ['Jourdan', 'Dunn'], ['Lily', 'Aldridge'], ['Candice', 'Swanepoel'], ['Rosie', 'Huntington'],
            ['Heidi', 'Klum'], ['Tyra', 'Banks'], ['Cindy', 'Crawford'], ['Claudia', 'Schiffer'],
            ['Christy', 'Turlington'], ['Linda', 'Evangelista'], ['Helena', 'Christensen'], ['Amber', 'Valletta'],
            ['Shalom', 'Harlow']
        ];

        $customerModels = [$demoCustomer];
        foreach ($customerNames as $idx => $cname) {
            $cEmail = strtolower($cname[0] . '.' . $cname[1] . ($idx + 10) . '@client.test');
            $segment = match ($idx % 5) {
                0 => 'vip',
                1 => 'returning',
                2 => 'regular',
                3 => 'new',
                default => 'regular',
            };

            $cust = Customer::create([
                'tenant_id' => $tenant->id,
                'user_id' => null,
                'first_name' => $cname[0],
                'last_name' => $cname[1],
                'email' => $cEmail,
                'phone' => '+1 (555) 400-' . str_pad((string)($idx + 2), 4, '0', STR_PAD_LEFT),
                'date_of_birth' => Carbon::now()->subYears(20 + ($idx % 30))->toDateString(),
                'gender' => ($idx % 8 === 0) ? 'male' : 'female',
                'preferred_branch_id' => ($idx % 2 === 0) ? $flagshipBranch->id : $resortBranch->id,
                'preferred_staff_id' => $staffModels[$idx % count($staffModels)]->id,
                'segment' => $segment,
                'loyalty_points' => ($idx * 35) % 1500,
                'notes' => "Preferred client note for {$cname[0]}.",
                'is_active' => true,
            ]);

            LoyaltyAccount::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $cust->id,
                'current_points' => $cust->loyalty_points,
                'total_earned' => $cust->loyalty_points + 200,
                'total_redeemed' => 200,
                'tier' => $cust->loyalty_points > 500 ? 'gold' : 'silver',
            ]);

            // Give some customers memberships or packages
            if ($idx % 4 === 0) {
                CustomerMembership::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $cust->id,
                    'membership_plan_id' => ($idx % 2 === 0) ? $memGold->id : $memSilver->id,
                    'status' => 'active',
                    'starts_at' => Carbon::now()->subMonths(1)->toDateString(),
                    'ends_at' => Carbon::now()->addMonths(11)->toDateString(),
                    'auto_renew' => true,
                    'remaining_free_credits' => 1,
                ]);
            }

            if ($idx % 5 === 0) {
                CustomerPackage::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $cust->id,
                    'package_id' => $pkg2->id,
                    'purchase_date' => Carbon::now()->subDays(20)->toDateString(),
                    'expires_at' => Carbon::now()->addDays(70)->toDateString(),
                    'total_credits' => 3,
                    'remaining_credits' => 2,
                    'status' => 'active',
                ]);
            }

            $customerModels[] = $cust;
        }

        // ==========================================
        // 9. 100 HISTORICAL & 20 UPCOMING APPOINTMENTS
        // ==========================================
        $appointmentStatuses = ['completed', 'completed', 'completed', 'completed', 'cancelled', 'no_show'];
        $timeSlots = ['09:00:00', '10:30:00', '12:00:00', '13:30:00', '15:00:00', '16:30:00'];

        // 100 Historical appointments
        for ($i = 1; $i <= 100; $i++) {
            $daysAgo = ($i % 90) + 1;
            $date = Carbon::now()->subDays($daysAgo)->toDateString();
            $time = $timeSlots[$i % count($timeSlots)];
            $service = $serviceModels[$i % count($serviceModels)];
            $staff = $staffModels[$i % count($staffModels)];
            $customer = $customerModels[$i % count($customerModels)];
            $branch = ($i % 2 === 0) ? $flagshipBranch : $resortBranch;
            $status = $appointmentStatuses[$i % count($appointmentStatuses)];

            $endTime = Carbon::parse("{$date} {$time}")->addMinutes($service->duration_minutes)->format('H:i:s');
            $ref = 'BK-' . Carbon::parse($date)->format('Ymd') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);

            $appt = Appointment::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'booking_reference' => $ref,
                'appointment_date' => $date,
                'start_time' => $time,
                'end_time' => $endTime,
                'subtotal' => $service->price,
                'discount_amount' => 0.0,
                'tax_amount' => round($service->price * 0.085, 2),
                'total_amount' => round($service->price * 1.085, 2),
                'deposit_amount' => $service->deposit_amount,
                'paid_amount' => $status === 'completed' ? round($service->price * 1.085, 2) : $service->deposit_amount,
                'status' => $status,
                'payment_status' => $status === 'completed' ? 'paid' : ($status === 'cancelled' ? 'refunded' : 'partially_paid'),
                'channel' => ($i % 3 === 0) ? 'online' : (($i % 3 === 1) ? 'receptionist' : 'walk_in'),
                'created_by' => $ownerUser->id,
            ]);

            AppointmentService::create([
                'appointment_id' => $appt->id,
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'room_id' => ($branch->id === $flagshipBranch->id) ? $room1->id : $room4->id,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'start_time' => $time,
                'end_time' => $endTime,
                'status' => $status,
            ]);

            // If completed, record payment & invoice
            if ($status === 'completed') {
                Payment::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'payable_type' => Appointment::class,
                    'payable_id' => $appt->id,
                    'amount' => $appt->total_amount,
                    'currency' => 'USD',
                    'method' => ($i % 2 === 0) ? 'card' : 'cash',
                    'transaction_reference' => 'TXN-' . strtoupper(Str::random(8)),
                    'status' => 'completed',
                ]);

                Invoice::create([
                    'tenant_id' => $tenant->id,
                    'branch_id' => $branch->id,
                    'customer_id' => $customer->id,
                    'appointment_id' => $appt->id,
                    'invoice_number' => 'INV-' . Carbon::parse($date)->format('Ymd') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                    'invoice_date' => $date,
                    'due_date' => $date,
                    'subtotal' => $appt->subtotal,
                    'tax_amount' => $appt->tax_amount,
                    'total_amount' => $appt->total_amount,
                    'paid_amount' => $appt->paid_amount,
                    'status' => 'paid',
                ]);
            }
        }

        // 20 Upcoming Appointments
        for ($j = 1; $j <= 20; $j++) {
            $daysAhead = ($j % 14) + 1;
            $date = Carbon::now()->addDays($daysAhead)->toDateString();
            $time = $timeSlots[$j % count($timeSlots)];
            $service = $serviceModels[$j % count($serviceModels)];
            $staff = $staffModels[$j % count($staffModels)];
            $customer = $customerModels[$j % count($customerModels)];
            $branch = ($j % 2 === 0) ? $flagshipBranch : $resortBranch;

            $endTime = Carbon::parse("{$date} {$time}")->addMinutes($service->duration_minutes)->format('H:i:s');
            $ref = 'BK-' . Carbon::parse($date)->format('Ymd') . '-' . str_pad((string)($j + 100), 4, '0', STR_PAD_LEFT);

            $appt = Appointment::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $branch->id,
                'customer_id' => $customer->id,
                'booking_reference' => $ref,
                'appointment_date' => $date,
                'start_time' => $time,
                'end_time' => $endTime,
                'subtotal' => $service->price,
                'discount_amount' => 0.0,
                'tax_amount' => round($service->price * 0.085, 2),
                'total_amount' => round($service->price * 1.085, 2),
                'deposit_amount' => $service->deposit_amount,
                'paid_amount' => $service->deposit_amount,
                'status' => 'confirmed',
                'payment_status' => 'partially_paid',
                'channel' => 'online',
                'created_by' => $ownerUser->id,
            ]);

            AppointmentService::create([
                'appointment_id' => $appt->id,
                'service_id' => $service->id,
                'staff_id' => $staff->id,
                'room_id' => ($branch->id === $flagshipBranch->id) ? $room2->id : $room4->id,
                'price' => $service->price,
                'duration_minutes' => $service->duration_minutes,
                'start_time' => $time,
                'end_time' => $endTime,
                'status' => 'pending',
            ]);
        }

        // ==========================================
        // 10. 10 SUPPLIERS & 25 RETAIL PRODUCTS
        // ==========================================
        $suppliersData = [
            ['name' => 'Oribe Luxury Haircare Intl', 'contact' => 'Laurent Mercier', 'email' => 'orders@oribe-lux.test', 'phone' => '+1 (800) 555-0101'],
            ['name' => 'Kerastase Paris Professional', 'contact' => 'Amelie Bernard', 'email' => 'distribution@kerastase.test', 'phone' => '+1 (800) 555-0102'],
            ['name' => 'Valmont Geneve Skincare', 'contact' => 'Marc Dupont', 'email' => 'suisse@valmont.test', 'phone' => '+1 (800) 555-0103'],
            ['name' => 'Biologique Recherche Laboratoires', 'contact' => 'Claire Delacroix', 'email' => 'pro@biologique.test', 'phone' => '+1 (800) 555-0104'],
            ['name' => 'Davines Sustainable Beauty', 'contact' => 'Gianluca Conti', 'email' => 'italia@davines.test', 'phone' => '+1 (800) 555-0105'],
            ['name' => 'Joanna Vargas Aesthetic Formulas', 'contact' => 'Jessica Cole', 'email' => 'wholesale@joannavargas.test', 'phone' => '+1 (800) 555-0106'],
            ['name' => 'Olaplex Bond Building Tech', 'contact' => 'David Kim', 'email' => 'accounts@olaplex.test', 'phone' => '+1 (800) 555-0107'],
            ['name' => 'CND Creative Nail Design', 'contact' => 'Rachel Miller', 'email' => 'orders@cnd.test', 'phone' => '+1 (800) 555-0108'],
            ['name' => 'Moroccanoil Argan Collections', 'contact' => 'Ilan Levi', 'email' => 'salon@moroccanoil.test', 'phone' => '+1 (800) 555-0109'],
            ['name' => 'Aromatherapy Associates London', 'contact' => 'Fiona Campbell', 'email' => 'london@aromatherapy.test', 'phone' => '+1 (800) 555-0110'],
        ];

        $suppliers = [];
        foreach ($suppliersData as $sup) {
            $suppliers[] = Supplier::create([
                'tenant_id' => $tenant->id,
                'name' => $sup['name'],
                'contact_person' => $sup['contact'],
                'email' => $sup['email'],
                'phone' => $sup['phone'],
                'is_active' => true,
            ]);
        }

        // Product categories
        $prodCatHair = ProductCategory::create(['tenant_id' => $tenant->id, 'name' => 'Luxury Hair Rituals', 'slug' => 'luxury-hair', 'is_active' => true]);
        $prodCatSkin = ProductCategory::create(['tenant_id' => $tenant->id, 'name' => 'High-Performance Skincare', 'slug' => 'skincare', 'is_active' => true]);
        $prodCatSpa = ProductCategory::create(['tenant_id' => $tenant->id, 'name' => 'Aromatherapy & Body Oils', 'slug' => 'body-oils', 'is_active' => true]);
        $prodCatNail = ProductCategory::create(['tenant_id' => $tenant->id, 'name' => 'Nail & Hand Elixirs', 'slug' => 'nail-elixirs', 'is_active' => true]);

        $productsData = [
            ['name' => 'Oribe Gold Lust Repair & Restore Shampoo', 'sku' => 'ORB-GL-SHP', 'cost' => 24.00, 'price' => 53.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[0]->id, 'stock' => 45],
            ['name' => 'Oribe Gold Lust Nourishing Hair Oil', 'sku' => 'ORB-GL-OIL', 'cost' => 28.00, 'price' => 66.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[0]->id, 'stock' => 30],
            ['name' => 'Kerastase Chronologiste Masque Intense', 'sku' => 'KRS-CHR-MSK', 'cost' => 35.00, 'price' => 78.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[1]->id, 'stock' => 25],
            ['name' => 'Kerastase Elixir Ultime L\'Huile Originale', 'sku' => 'KRS-ELX-OIL', 'cost' => 25.00, 'price' => 58.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[1]->id, 'stock' => 38],
            ['name' => 'Valmont Prime Renewing Pack Masque', 'sku' => 'VLM-PRM-MSK', 'cost' => 110.00, 'price' => 260.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[2]->id, 'stock' => 12],
            ['name' => 'Valmont Hydra3 Regenetic Serum', 'sku' => 'VLM-HYD-SRM', 'cost' => 135.00, 'price' => 310.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[2]->id, 'stock' => 8],
            ['name' => 'Biologique Recherche Lotion P50 1970', 'sku' => 'BR-P50-1970', 'cost' => 45.00, 'price' => 112.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[3]->id, 'stock' => 50],
            ['name' => 'Biologique Recherche Creme Masque Vernix', 'sku' => 'BR-CR-VERNIX', 'cost' => 90.00, 'price' => 235.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[3]->id, 'stock' => 15],
            ['name' => 'Davines OI All In One Milk Treatment', 'sku' => 'DVN-OI-MILK', 'cost' => 16.00, 'price' => 38.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[4]->id, 'stock' => 60],
            ['name' => 'Davines The Renaissance Circle Hair Mask', 'sku' => 'DVN-REN-MSK', 'cost' => 6.00, 'price' => 14.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[4]->id, 'stock' => 75],
            ['name' => 'Joanna Vargas Daily Serum with Vitamin F', 'sku' => 'JV-DLY-SRM', 'cost' => 40.00, 'price' => 85.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[5]->id, 'stock' => 20],
            ['name' => 'Joanna Vargas Magic Glow Wand Device', 'sku' => 'JV-WND-DEV', 'cost' => 120.00, 'price' => 285.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[5]->id, 'stock' => 5], // low stock alert
            ['name' => 'Olaplex No. 3 Hair Perfector', 'sku' => 'OLX-NO3-PRF', 'cost' => 14.00, 'price' => 30.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[6]->id, 'stock' => 80],
            ['name' => 'Olaplex No. 7 Bonding Oil Elixir', 'sku' => 'OLX-NO7-OIL', 'cost' => 14.00, 'price' => 30.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[6]->id, 'stock' => 45],
            ['name' => 'CND SolarOil Cuticle Conditioning Care', 'sku' => 'CND-SOL-OIL', 'cost' => 7.00, 'price' => 16.50, 'cat' => $prodCatNail->id, 'sup' => $suppliers[7]->id, 'stock' => 90],
            ['name' => 'CND Scentsations Citrus & Green Tea Lotion', 'sku' => 'CND-SCN-CIT', 'cost' => 8.00, 'price' => 18.00, 'cat' => $prodCatNail->id, 'sup' => $suppliers[7]->id, 'stock' => 40],
            ['name' => 'Moroccanoil Treatment Original Oil', 'sku' => 'MRC-TRT-ORG', 'cost' => 21.00, 'price' => 48.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[8]->id, 'stock' => 55],
            ['name' => 'Moroccanoil Intense Hydrating Mask', 'sku' => 'MRC-HYD-MSK', 'cost' => 19.00, 'price' => 44.00, 'cat' => $prodCatHair->id, 'sup' => $suppliers[8]->id, 'stock' => 35],
            ['name' => 'Aromatherapy Associates Deep Relax Bath Oil', 'sku' => 'AA-DP-RLX-OIL', 'cost' => 32.00, 'price' => 75.00, 'cat' => $prodCatSpa->id, 'sup' => $suppliers[9]->id, 'stock' => 28],
            ['name' => 'Aromatherapy Associates De-Stress Muscle Gel', 'sku' => 'AA-DST-MSC-GL', 'cost' => 20.00, 'price' => 46.00, 'cat' => $prodCatSpa->id, 'sup' => $suppliers[9]->id, 'stock' => 32],
            ['name' => 'Aromatherapy Associates Rose Pure Body Butter', 'sku' => 'AA-RSE-BDY-BTR', 'cost' => 24.00, 'price' => 54.00, 'cat' => $prodCatSpa->id, 'sup' => $suppliers[9]->id, 'stock' => 22],
            ['name' => 'Glow & Grace Organic Silk Sleeping Mask', 'sku' => 'GG-SLK-MSK', 'cost' => 12.00, 'price' => 35.00, 'cat' => $prodCatSpa->id, 'sup' => $suppliers[9]->id, 'stock' => 65],
            ['name' => 'Glow & Grace Rose Quartz Sculpting Gua Sha', 'sku' => 'GG-RQ-GUASHA', 'cost' => 9.00, 'price' => 28.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[5]->id, 'stock' => 40],
            ['name' => 'Glow & Grace Signature Amber Botanical Candle', 'sku' => 'GG-AMB-CNDL', 'cost' => 15.00, 'price' => 42.00, 'cat' => $prodCatSpa->id, 'sup' => $suppliers[9]->id, 'stock' => 50],
            ['name' => 'Glow & Grace Ultra Velvet Lip Elixir', 'sku' => 'GG-LIP-ELX', 'cost' => 8.00, 'price' => 22.00, 'cat' => $prodCatSkin->id, 'sup' => $suppliers[5]->id, 'stock' => 80],
        ];

        $productModels = [];
        foreach ($productsData as $prod) {
            $productModels[] = Product::create([
                'tenant_id' => $tenant->id,
                'category_id' => $prod['cat'],
                'supplier_id' => $prod['sup'],
                'name' => $prod['name'],
                'slug' => Str::slug($prod['name']),
                'sku' => $prod['sku'],
                'barcode' => '8' . rand(10000000000, 99999999999),
                'brand' => explode(' ', $prod['name'])[0],
                'description' => "Authentic luxury beauty product: {$prod['name']}.",
                'purchase_price' => $prod['cost'],
                'selling_price' => $prod['price'],
                'tax_percent' => 8.5,
                'stock_quantity' => $prod['stock'],
                'min_stock_alert' => 10,
                'is_active' => true,
            ]);
        }

        // ==========================================
        // 11. SAMPLE POS SALES & INVOICES
        // ==========================================
        for ($s = 1; $s <= 25; $s++) {
            $saleCust = $customerModels[$s % count($customerModels)];
            $saleProd = $productModels[$s % count($productModels)];
            $saleSvc = $serviceModels[$s % count($serviceModels)];
            $staff = $staffModels[$s % count($staffModels)];

            $sub = $saleProd->selling_price + $saleSvc->price;
            $tax = round($sub * 0.085, 2);
            $total = $sub + $tax;

            $sale = Sale::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $flagshipBranch->id,
                'customer_id' => $saleCust->id,
                'staff_id' => $staff->id,
                'cash_register_id' => $cashRegister->id,
                'sale_number' => 'POS-202611-' . str_pad((string)$s, 4, '0', STR_PAD_LEFT),
                'subtotal' => $sub,
                'discount_amount' => 0.0,
                'tax_amount' => $tax,
                'tip_amount' => ($s % 2 === 0) ? 20.00 : 10.00,
                'total_amount' => $total + (($s % 2 === 0) ? 20.00 : 10.00),
                'paid_amount' => $total + (($s % 2 === 0) ? 20.00 : 10.00),
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'item_type' => 'service',
                'item_id' => $saleSvc->id,
                'item_name' => $saleSvc->name,
                'quantity' => 1,
                'unit_price' => $saleSvc->price,
                'total_amount' => $saleSvc->price,
                'staff_id' => $staff->id,
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'item_type' => 'product',
                'item_id' => $saleProd->id,
                'item_name' => $saleProd->name,
                'quantity' => 1,
                'unit_price' => $saleProd->selling_price,
                'total_amount' => $saleProd->selling_price,
                'staff_id' => $staff->id,
            ]);

            Payment::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $flagshipBranch->id,
                'payable_type' => Sale::class,
                'payable_id' => $sale->id,
                'amount' => $sale->total_amount,
                'currency' => 'USD',
                'method' => ($s % 2 === 0) ? 'card' : 'cash',
                'transaction_reference' => 'TXN-POS-' . strtoupper(Str::random(6)),
                'status' => 'completed',
            ]);

            Invoice::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $flagshipBranch->id,
                'customer_id' => $saleCust->id,
                'sale_id' => $sale->id,
                'invoice_number' => 'INV-POS-' . str_pad((string)$s, 4, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays($s)->toDateString(),
                'due_date' => Carbon::now()->subDays($s)->toDateString(),
                'subtotal' => $sale->subtotal,
                'tax_amount' => $sale->tax_amount,
                'total_amount' => $sale->total_amount,
                'paid_amount' => $sale->paid_amount,
                'status' => 'paid',
            ]);
        }

        // ==========================================
        // 12. GIFT CARDS & COUPONS
        // ==========================================
        GiftCard::create([
            'tenant_id' => $tenant->id,
            'code' => 'GLOW-LUXURY-250',
            'initial_balance' => 250.00,
            'current_balance' => 250.00,
            'purchaser_name' => 'Lord Archibald Sterling',
            'recipient_name' => 'Victoria Sterling',
            'recipient_email' => 'customer@example.test',
            'expires_at' => Carbon::now()->addYear()->toDateString(),
            'is_active' => true,
        ]);

        GiftCard::create([
            'tenant_id' => $tenant->id,
            'code' => 'SPADAY-GIFT-100',
            'initial_balance' => 100.00,
            'current_balance' => 45.00,
            'purchaser_name' => 'Beatrice Sinclair',
            'recipient_name' => 'Gwendolyn Pierce',
            'recipient_email' => 'gwendolyn@client.test',
            'expires_at' => Carbon::now()->addMonths(6)->toDateString(),
            'is_active' => true,
        ]);

        Coupon::create([
            'tenant_id' => $tenant->id,
            'code' => 'WELCOME15',
            'type' => 'percentage',
            'value' => 15.0,
            'min_spend' => 75.00,
            'max_discount' => 50.00,
            'usage_limit' => 500,
            'used_count' => 84,
            'expires_at' => Carbon::now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        Coupon::create([
            'tenant_id' => $tenant->id,
            'code' => 'VIPRETREAT',
            'type' => 'fixed',
            'value' => 30.00,
            'min_spend' => 150.00,
            'usage_limit' => 100,
            'used_count' => 19,
            'expires_at' => Carbon::now()->addMonths(2)->toDateString(),
            'is_active' => true,
        ]);

        // ==========================================
        // 13. REVIEWS & TESTIMONIALS
        // ==========================================
        Review::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $demoCustomer->id,
            'service_id' => $serviceModels[8]->id, // Diamond Hydra-Infusion
            'staff_id' => $staffModels[3]->id, // Aria
            'rating' => 5,
            'comment' => 'The Diamond Hydra-Infusion facial is truly sublime! Aria is an absolute artist with skin. My complexion has never glowed like this. The private suite and chilled champagne made the experience pure heaven.',
            'is_approved' => true,
            'is_public' => true,
        ]);

        Review::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerModels[1]->id,
            'service_id' => $serviceModels[4]->id, // Balayage
            'staff_id' => $staffModels[0]->id, // Julian
            'rating' => 5,
            'comment' => 'Julian is the uncontested master of Parisian Balayage. The dimension and softness he achieved on my hair exceeded every expectation. Glow & Grace is in a league of its own.',
            'is_approved' => true,
            'is_public' => true,
        ]);

        Review::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customerModels[2]->id,
            'service_id' => $serviceModels[13]->id, // Volcanic Stone
            'staff_id' => $staffModels[1]->id, // Maya
            'rating' => 5,
            'comment' => 'Maya\'s Heated Basalt Volcanic Stone massage melted away weeks of tension. The aromatic botanical oils and tranquil ocean soundtrack at the Malibu branch are transcendent.',
            'is_approved' => true,
            'is_public' => true,
        ]);

        // ==========================================
        // 14. MARKETING CAMPAIGNS
        // ==========================================
        Campaign::create([
            'tenant_id' => $tenant->id,
            'name' => 'Autumn Gold Luxury Rejuvenation Broadcast',
            'channel' => 'email',
            'subject' => '✨ An Exclusive Invitation: Autumn Gold Rejuvenation Rituals',
            'content' => 'Dear Valued Guest, as seasons shift, immerse yourself in our signature 24K Gold Cellular Anti-Aging Facial and botanical hair glazes.',
            'target_segment' => 'vip',
            'status' => 'sent',
            'sent_at' => Carbon::now()->subDays(5),
            'recipients_count' => 45,
            'stats' => ['sent' => 45, 'delivered' => 45, 'opened' => 38, 'clicked' => 19],
        ]);

        // ==========================================
        // 15. WEBSITE PAGES & CONTENT CMS
        // ==========================================
        $homePage = WebsitePage::create([
            'tenant_id' => $tenant->id,
            'title' => 'Home — Luxury Salon & Spa Sanctuary',
            'slug' => 'home',
            'template' => 'home',
            'meta_title' => 'Glow & Grace | Luxury Salon & Spa Sanctuary in Beverly Hills & Malibu',
            'meta_description' => 'Experience bespoke hair couture, restorative clinical facials, holistic massages, and VIP wellness memberships.',
            'is_published' => true,
        ]);

        WebsiteSection::create([
            'tenant_id' => $tenant->id,
            'page_id' => $homePage->id,
            'section_key' => 'hero',
            'title' => 'Where Timeless Elegance Meets Holistic Wellness',
            'subtitle' => 'Award-winning artisan salon, luxury day spa, and private wellness retreat in Beverly Hills & Malibu.',
            'content' => 'Step into an oasis of understated luxury. Our internationally celebrated stylists, certified aestheticians, and holistic therapists craft personalized treatments to elevate your beauty and restore your inner serenity.',
            'button_text' => 'Book Your Experience',
            'button_url' => '/booking',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
    }
}
