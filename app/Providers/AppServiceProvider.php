<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

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

        Gate::define('manage-platform', fn (User $u) => $u->isAdmin());
        Gate::define('manage-tenant', fn (User $u) => $u->isTenantAdmin() || $u->isAdmin());
        Gate::define('fulfill-orders', fn (User $u) => $u->isTenantRole() || $u->isAdmin());

        Number::useLocale('id');

        // @rupiah(15000) => Rp15.000
        Blade::directive('rupiah', fn ($expr) => "<?php echo 'Rp'.number_format((int) ($expr), 0, ',', '.'); ?>");

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
