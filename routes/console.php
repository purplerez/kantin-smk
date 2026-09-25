<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset ketersediaan menu setiap pagi (tenant menandai ulang "tersedia hari ini")
Artisan::command('kantin:reset-availability', function () {
    $count = \App\Models\Product::withoutGlobalScopes()->update(['is_available_today' => true]);
    $this->info("Ketersediaan {$count} menu direset.");
})->purpose('Reset flag is_available_today untuk semua produk');

// ===== Jadwal (dipakai saat deploy shared-hosting via `php artisan schedule:run` di cron) =====
// Di preview, endpoint /cron/* dipanggil oleh scheduler platform (.emergent/crons.yml).
Schedule::command('kantin:auto-cancel-unpaid')->everyFifteenMinutes();
Schedule::command('kantin:generate-settlements')->dailyAt('23:30');
Schedule::command('kantin:reset-availability')->dailyAt('06:00');
