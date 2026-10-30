<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jam operasional per hari per tenant
        Schema::create('tenant_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->comment('0=Minggu … 6=Sabtu (Carbon::dayOfWeek)');
            $table->boolean('is_open')->default(true)->comment('false = libur seharian');
            $table->time('open_at')->default('07:00:00');
            $table->time('close_at')->default('15:00:00');
            $table->timestamps();

            $table->unique(['tenant_id', 'day_of_week']);
        });

        // Kolom scheduled_for pada orders: jam buka berikutnya saat PO dibuat
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('scheduled_for')->nullable()->after('cancel_reason')
                ->comment('Pre-order: jam buka toko berikutnya saat pesanan dibuat');
        });

        // Untuk MySQL production: tambah nilai pre_order pada kolom enum status
        // Di SQLite (test) enum disimpan sebagai string — tidak butuh ALTER.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','preparing','ready','completed','cancelled','pre_order') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('scheduled_for');
        });

        Schema::dropIfExists('tenant_hours');
    }
};

