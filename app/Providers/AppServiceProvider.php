<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProductPolicy;
use App\Policies\TenantPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);

        Gate::define('manage-platform', fn (User $u) => $u->isAdmin());
        Gate::define('manage-tenant', fn (User $u) => $u->isTenantAdmin() || $u->isAdmin());
        Gate::define('fulfill-orders', fn (User $u) => $u->isTenantRole() || $u->isAdmin());

        Number::useLocale('id');

        // Rate-limit login PER email+IP (bukan per-IP saja) supaya satu SMK di belakang
        // satu IP NAT tidak saling mengunci saat jam istirahat.
        RateLimiter::for('login', function (Request $request) {
            $key = Str::lower((string) $request->input('email')).'|'.$request->ip();

            return [Limit::perMinute(10)->by($key)];
        });

        // @rupiah(15000) => Rp15.000
        Blade::directive('rupiah', fn ($expr) => "<?php echo 'Rp'.number_format((int) ($expr), 0, ',', '.'); ?>");

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
