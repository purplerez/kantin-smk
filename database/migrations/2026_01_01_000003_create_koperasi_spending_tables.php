<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->boolean('is_koperasi')->default(false)->after('is_open')->index();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('source', ['app', 'counter'])->default('app')->after('status');
            $table->foreignId('entered_by')->nullable()->after('buyer_id')->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable()->after('completed_at');

            $table->index(['buyer_id', 'tenant_id', 'payment_status', 'paid_at'], 'orders_buyer_tenant_payment_paid_idx');
        });

        Schema::create('spending_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['position_id', 'tenant_id', 'effective_from'], 'targets_pos_tenant_effective_idx');
        });

        Schema::create('spending_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7)->comment('Format YYYY-MM');
            $table->integer('amount')->comment('Nominal signed integer (bisa positif/negatif)');
            $table->string('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'tenant_id', 'period'], 'adj_user_tenant_period_idx');
        });

        Schema::create('spending_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('period', 7)->comment('Format YYYY-MM');
            $table->timestamp('closed_at');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'period']);
        });

        Schema::create('spending_closing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('closing_id')->constrained('spending_closings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('target_amount')->default(0);
            $table->integer('total_amount')->default(0);
            $table->boolean('is_met')->default(false);
            $table->timestamps();

            $table->unique(['closing_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spending_closing_items');
        Schema::dropIfExists('spending_closings');
        Schema::dropIfExists('spending_adjustments');
        Schema::dropIfExists('spending_targets');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_buyer_tenant_payment_paid_idx');
            $table->dropForeign(['entered_by']);
            $table->dropColumn(['source', 'entered_by', 'paid_at']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('is_koperasi');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });

        Schema::dropIfExists('positions');
    }
};

