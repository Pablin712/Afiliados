<?php

namespace Database\Seeders;

use App\Models\Action;
use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Program;
use App\Models\Profit;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserBank;
use App\Services\DailyFinancialStatsService;
use App\Services\ProfitDistributionService;
use App\Services\ProfitPayoutService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComprehensiveTestScenarioSeeder extends Seeder
{
    private const TOTAL_USERS = 50;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::role('admin')->firstOrFail();
        $admin->sponsor_id = $admin->id;
        $admin->approved_at = $admin->approved_at ?? now();
        $admin->save();

        $membershipTypes = MembershipType::query()
            ->whereIn('name', ['free', 'customer', 'beginner', 'explorer', 'professional', 'elite'])
            ->get()
            ->keyBy(fn (MembershipType $type) => strtolower((string) $type->name));

        foreach (['free', 'customer', 'beginner', 'explorer', 'professional', 'elite'] as $requiredType) {
            if (! $membershipTypes->has($requiredType)) {
                throw new \RuntimeException("Membership type '{$requiredType}' is required before seeding test scenario.");
            }
        }

        $program = Program::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first() ?? Program::query()->orderBy('id')->firstOrFail();

        $this->cleanScenarioData($admin);

        $usersBySlot = $this->seedUsers($admin);
        $this->seedUserBanks($usersBySlot);

        $approvedPayments = $this->seedPaymentsAndMemberships(
            $usersBySlot,
            $admin,
            $program,
            $membershipTypes->get('free')
        );

        $this->applyMembershipTierRules($membershipTypes);
        $this->seedProfits($approvedPayments, $membershipTypes->get('customer'), $admin);
        $this->seedActions($usersBySlot, $admin);
        $this->seedDailyStats();

        $this->command?->info('Comprehensive test scenario seeded.');
        $this->command?->line('- 50 users with role user and sponsor tree');
        $this->command?->line('- Pending, rejected and approved payments');
        $this->command?->line('- Memberships with free/active/pending states and tier upgrades');
        $this->command?->line('- Pending and paid profits with linked expense transactions');
        $this->command?->line('- Daily financial stats for the last 30 days');
    }

    private function cleanScenarioData(User $admin): void
    {
        $nonAdminIds = User::query()
            ->where('id', '!=', $admin->id)
            ->pluck('id');

        Action::query()->delete();
        DB::table('daily_financial_stats')->delete();
        Profit::query()->delete();
        Payment::query()->delete();
        Transaction::query()->delete();

        if ($nonAdminIds->isNotEmpty()) {
            UserBank::query()->whereIn('user_id', $nonAdminIds)->delete();
            Membership::query()->whereIn('user_id', $nonAdminIds)->delete();

            DB::table('model_has_roles')
                ->where('model_type', User::class)
                ->whereIn('model_id', $nonAdminIds)
                ->delete();

            DB::table('model_has_permissions')
                ->where('model_type', User::class)
                ->whereIn('model_id', $nonAdminIds)
                ->delete();

            User::query()
                ->whereIn('id', $nonAdminIds)
                ->orderByDesc('id')
                ->get()
                ->each(fn (User $user) => $user->delete());
        }

        $adminBanks = Bank::query()->get();
        foreach ($adminBanks as $bank) {
            $bank->amount = 0;
            $bank->save();
        }

        $admin->commission_balance = 0;
        $admin->save();
    }

    /**
     * @return array<int, User>
     */
    private function seedUsers(User $admin): array
    {
        $usersBySlot = [];

        for ($slot = 1; $slot <= self::TOTAL_USERS; $slot++) {
            $sponsorSlot = $this->resolveSponsorSlot($slot);
            $sponsorId = $sponsorSlot === 0
                ? $admin->id
                : ($usersBySlot[$sponsorSlot]->id ?? $admin->id);

            $user = User::query()->create([
                'name' => sprintf('Usuario Demo %02d', $slot),
                'email' => sprintf('demo.user%02d@afiliados.test', $slot),
                'identification' => sprintf('DEMO-%05d', $slot),
                'password' => 'User12345*',
                'sponsor_id' => $sponsorId,
                'commission_balance' => 0,
                'approved_at' => now()->subDays(70 - ($slot % 40)),
                'created_at' => now()->subDays(80 - ($slot % 50)),
                'updated_at' => now()->subDays(40 - ($slot % 20)),
            ]);

            $user->affiliate_code = User::buildAffiliateCode($user->name, $user->id);
            $user->save();
            $user->assignRole('user');

            $usersBySlot[$slot] = $user;
        }

        return $usersBySlot;
    }

    private function seedUserBanks(array $usersBySlot): void
    {
        $bankNames = ['Pichincha', 'Guayaquil', 'Produbanco', 'Binance', 'PayPal'];
        $types = ['savings', 'checking', 'wallet', 'mobile_payment'];

        foreach ($usersBySlot as $slot => $user) {
            UserBank::query()->create([
                'user_id' => $user->id,
                'bank_name' => $bankNames[$slot % count($bankNames)],
                'owner' => $user->name,
                'identification' => sprintf('UB-%05d', $slot),
                'number' => sprintf('09%08d', 10000000 + $slot),
                'type' => $types[$slot % count($types)],
                'is_default' => true,
                'detail' => 'Cuenta principal para recibir utilidades.',
                'created_at' => now()->subDays(50 - ($slot % 25)),
                'updated_at' => now()->subDays(20 - ($slot % 10)),
            ]);

            if ($slot % 5 === 0) {
                UserBank::query()->create([
                    'user_id' => $user->id,
                    'bank_name' => 'Banco Secundario',
                    'owner' => $user->name,
                    'identification' => sprintf('UB2-%05d', $slot),
                    'number' => sprintf('22%08d', 20000000 + $slot),
                    'type' => 'checking',
                    'is_default' => false,
                    'detail' => 'Cuenta secundaria de pruebas.',
                    'created_at' => now()->subDays(35 - ($slot % 15)),
                    'updated_at' => now()->subDays(10 - ($slot % 8)),
                ]);
            }
        }
    }

    /**
     * @return Collection<int, Payment>
     */
    private function seedPaymentsAndMemberships(array $usersBySlot, User $admin, Program $program, MembershipType $freeType): Collection
    {
        $approvedPayments = collect();
        $adminBanks = Bank::query()->orderBy('id')->get();

        if ($adminBanks->isEmpty()) {
            throw new \RuntimeException('At least one admin bank account is required before seeding payments.');
        }

        $pendingSlots = [47, 50];
        $rejectedSlots = [48];
        $freeSlots = [49];

        foreach ($usersBySlot as $slot => $user) {
            $createdAt = now()->subDays(65 - ($slot % 35));

            if (in_array($slot, $pendingSlots, true)) {
                $pendingPayment = $this->createPaymentWithTransaction(
                    user: $user,
                    program: $program,
                    bank: $adminBanks->first(),
                    state: 'pending',
                    amount: (float) $program->first_payment_cost,
                    createdAt: $createdAt,
                    reviewedBy: null,
                    reviewedAt: null,
                    keepZeroTransaction: true
                );

                Membership::query()->create([
                    'user_id' => $user->id,
                    'membership_type_id' => $freeType->id,
                    'status' => 'pending_payment',
                    'started_at' => null,
                    'expires_at' => null,
                    'last_payment_id' => $pendingPayment->id,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                continue;
            }

            if (in_array($slot, $rejectedSlots, true)) {
                $this->createPaymentWithTransaction(
                    user: $user,
                    program: $program,
                    bank: $adminBanks->first(),
                    state: 'rejected',
                    amount: (float) $program->first_payment_cost,
                    createdAt: $createdAt,
                    reviewedBy: $admin,
                    reviewedAt: (clone $createdAt)->addHours(2),
                    keepZeroTransaction: true
                );

                Membership::query()->create([
                    'user_id' => $user->id,
                    'membership_type_id' => $freeType->id,
                    'status' => 'free',
                    'started_at' => $createdAt,
                    'expires_at' => null,
                    'last_payment_id' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                continue;
            }

            if (in_array($slot, $freeSlots, true)) {
                Membership::query()->create([
                    'user_id' => $user->id,
                    'membership_type_id' => $freeType->id,
                    'status' => 'free',
                    'started_at' => $createdAt,
                    'expires_at' => null,
                    'last_payment_id' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                continue;
            }

            $primaryBank = $adminBanks->get(($slot - 1) % $adminBanks->count());
            $approvedPayment = $this->createPaymentWithTransaction(
                user: $user,
                program: $program,
                bank: $primaryBank,
                state: 'approved',
                amount: (float) $program->first_payment_cost,
                createdAt: $createdAt,
                reviewedBy: $admin,
                reviewedAt: (clone $createdAt)->addHours(3),
                keepZeroTransaction: false
            );

            $lastApprovedPayment = $approvedPayment;

            if ($slot % 6 === 0) {
                $renewalAt = now()->subDays(20 - ($slot % 10));
                $renewalPayment = $this->createPaymentWithTransaction(
                    user: $user,
                    program: $program,
                    bank: $primaryBank,
                    state: 'approved',
                    amount: (float) $program->renewal_cost,
                    createdAt: $renewalAt,
                    reviewedBy: $admin,
                    reviewedAt: (clone $renewalAt)->addHours(2),
                    keepZeroTransaction: false
                );

                $approvedPayments->push($renewalPayment);
                $lastApprovedPayment = $renewalPayment;
            }

            $approvedPayments->push($approvedPayment);

            Membership::query()->create([
                'user_id' => $user->id,
                'membership_type_id' => $program->membership_type_id,
                'status' => 'active',
                'started_at' => $lastApprovedPayment->reviewed_at,
                'expires_at' => Carbon::parse((string) $lastApprovedPayment->reviewed_at)->addMonths((int) $program->duration_months),
                'last_payment_id' => $lastApprovedPayment->id,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
        }

        return $approvedPayments;
    }

    private function createPaymentWithTransaction(
        User $user,
        Program $program,
        Bank $bank,
        string $state,
        float $amount,
        Carbon $createdAt,
        ?User $reviewedBy,
        ?Carbon $reviewedAt,
        bool $keepZeroTransaction
    ): Payment {
        $amountPrevious = (float) $bank->amount;
        $movementAmount = $keepZeroTransaction ? 0.0 : $amount;
        $amountNow = $amountPrevious + $movementAmount;

        $transaction = Transaction::query()->create([
            'bank_id' => $bank->id,
            'type' => 'income',
            'amount_previous' => $amountPrevious,
            'amount' => $movementAmount,
            'amount_now' => $amountNow,
            'detail' => $state === 'approved'
                ? sprintf('Ingreso aprobado de %s', $user->name)
                : sprintf('Pago %s de %s', $state, $user->name),
            'is_annulled' => false,
            'created_at' => $createdAt,
        ]);

        if (! $keepZeroTransaction) {
            $bank->amount = $amountNow;
            $bank->save();
        }

        return Payment::query()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'transaction_id' => $transaction->id,
            'number' => sprintf('TRX-DEMO-%d-%d', $user->id, random_int(1000, 9999)),
            'photo' => 'payment-receipts/demo-receipt.png',
            'amount' => $amount,
            'state' => $state,
            'reviewed_by' => $reviewedBy?->id,
            'reviewed_at' => $reviewedAt,
            'created_at' => $createdAt,
            'updated_at' => $reviewedAt ?? $createdAt,
        ]);
    }

    private function applyMembershipTierRules(Collection $membershipTypes): void
    {
        $directActiveAffiliates = DB::table('users as child')
            ->join('memberships as m', 'm.user_id', '=', 'child.id')
            ->join('membership_types as mt', 'mt.id', '=', 'm.membership_type_id')
            ->where('m.status', 'active')
            ->whereRaw('LOWER(mt.name) <> ?', ['free'])
            ->groupBy('child.sponsor_id')
            ->select('child.sponsor_id', DB::raw('COUNT(*) as total'))
            ->pluck('total', 'child.sponsor_id');

        $activeMemberships = Membership::query()
            ->with('membershipType')
            ->where('status', 'active')
            ->get();

        foreach ($activeMemberships as $membership) {
            $userId = (int) $membership->user_id;
            $activeAffiliates = (int) ($directActiveAffiliates[$userId] ?? 0);

            $targetTypeName = match (true) {
                $activeAffiliates >= 30 => 'elite',
                $activeAffiliates >= 20 => 'professional',
                $activeAffiliates >= 10 => 'explorer',
                $activeAffiliates >= 3 => 'beginner',
                default => 'customer',
            };

            $targetType = $membershipTypes->get($targetTypeName);
            if (! $targetType instanceof MembershipType) {
                continue;
            }

            if ($membership->membership_type_id !== $targetType->id) {
                $membership->membership_type_id = $targetType->id;
                $membership->save();
            }
        }
    }

    private function seedProfits(Collection $approvedPayments, MembershipType $customerType, User $admin): void
    {
        /** @var ProfitDistributionService $distributionService */
        $distributionService = app(ProfitDistributionService::class);

        foreach ($approvedPayments as $payment) {
            $distributionService->distributeForApprovedPayment($payment, $customerType);
        }

        $richestBank = Bank::query()->orderByDesc('amount')->first();
        if (! $richestBank instanceof Bank) {
            return;
        }

        /** @var ProfitPayoutService $payoutService */
        $payoutService = app(ProfitPayoutService::class);

        $pendingProfits = Profit::query()
            ->where('state', 'pending')
            ->orderBy('id')
            ->take(25)
            ->get();

        foreach ($pendingProfits as $profit) {
            try {
                $payoutService->markAsPaid(
                    $profit,
                    $richestBank->id,
                    $admin->id,
                    sprintf('Pago de utilidad de prueba para profit #%d', $profit->id)
                );
            } catch (\Throwable) {
                break;
            }
        }
    }

    private function seedActions(array $usersBySlot, User $admin): void
    {
        $modules = ['users', 'memberships', 'payments', 'profits', 'transactions', 'actions'];
        $events = ['view_index', 'view_show', 'create', 'approve_payment', 'update'];
        $methods = ['GET', 'POST', 'PATCH'];

        for ($i = 1; $i <= 120; $i++) {
            $slot = (($i - 1) % self::TOTAL_USERS) + 1;
            $actor = $i % 6 === 0 ? $admin : $usersBySlot[$slot];
            $module = $modules[$i % count($modules)];
            $event = $events[$i % count($events)];
            $method = $methods[$i % count($methods)];

            Action::query()->create([
                'user_id' => $actor->id,
                'module' => $module,
                'action' => $event,
                'method' => $method,
                'route' => sprintf('admin.%s.index', $module),
                'url' => sprintf('/admin/%s', $module),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'SeederTestAgent/1.0',
                'payload' => ['source' => 'ComprehensiveTestScenarioSeeder', 'index' => $i],
                'old_values' => null,
                'new_values' => ['status' => 'ok'],
                'created_at' => now()->subDays(30 - ($i % 30))->subMinutes($i),
            ]);
        }
    }

    private function seedDailyStats(): void
    {
        /** @var DailyFinancialStatsService $statsService */
        $statsService = app(DailyFinancialStatsService::class);

        if (! $statsService->hasStatsTable()) {
            return;
        }

        for ($day = 29; $day >= 0; $day--) {
            $statsService->registerForDate(now()->subDays($day));
        }
    }

    private function resolveSponsorSlot(int $slot): int
    {
        if ($slot === 1 || $slot === 32 || $slot === 43 || $slot === 47) {
            return 0;
        }

        if ($slot >= 2 && $slot <= 31) {
            return 1;
        }

        if ($slot >= 33 && $slot <= 42) {
            return 32;
        }

        if ($slot >= 44 && $slot <= 46) {
            return 43;
        }

        return match ($slot) {
            48 => 44,
            49 => 45,
            50 => 47,
            default => 1,
        };
    }
}
