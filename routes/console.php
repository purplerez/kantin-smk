<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset ketersediaan menu setiap pagi (tenant menandai ulang "tersedia hari ini")
Artisan::command('kantin:reset-availability', function () {
    $count = \App\Models\Product::withoutGlobalScopes()->update(['is_available_today' => true]);
    $this->info("Ketersediaan {$count} menu direset.");
})->purpose('Reset flag is_available_today untuk semua produk');
