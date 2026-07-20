<?php

namespace App\Console\Commands;

use App\Services\MembershipExpiryService;
use Illuminate\Console\Command;

class DowngradeExpiredMembershipsCommand extends Command
{
    protected $signature = 'memberships:downgrade-expired';

    protected $description = 'Downgrade active memberships past their expiry date to free and remove from groups, unless they qualify for a free renewal';

    public function __construct(private readonly MembershipExpiryService $membershipExpiryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $result = $this->membershipExpiryService->processExpired();

        if ($result['processed'] === 0) {
            $this->info('No expired memberships found.');

            return self::SUCCESS;
        }

        $this->info("Downgraded {$result['downgraded']} expired membership(s) to free. Free renewals granted: {$result['free_renewals']}.");

        return self::SUCCESS;
    }
}
