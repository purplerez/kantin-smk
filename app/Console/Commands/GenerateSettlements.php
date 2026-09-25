<?php

namespace App\Console\Commands;

use App\Services\SettlementService;
use Illuminate\Console\Command;

class GenerateSettlements extends Command
{
    protected $signature = 'kantin:generate-settlements {date? : Tanggal YYYY-MM-DD (default: hari ini)}';

    protected $description = 'Hitung & simpan settlement harian per tenant';

    public function handle(SettlementService $service): int
    {
        $date = $this->argument('date') ?: now()->toDateString();
        $count = $service->generateForDate($date);

        $this->info("Settlement dibuat untuk {$count} tenant pada {$date}.");

        return self::SUCCESS;
    }
}
