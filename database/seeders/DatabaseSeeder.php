<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Platform admin (dari .env, idempoten) =====
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@smkgo.id')],
            ['name' => env('ADMIN_NAME', 'Platform Admin'), 'password' => env('ADMIN_PASSWORD', 'admin123'), 'role' => Role::Admin, 'is_active' => true],
        );

        // ===== Kategori =====
        $categories = collect(['Makanan', 'Minuman', 'Jajanan', 'Paket Hemat'])
            ->mapWithKeys(fn ($name, $i) => [$name => Category::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'sort_order' => $i])]);

        // ===== Tenant + produk =====
        $tenants = [
            [
                'name' => 'Kantin Bu Rina', 'description' => 'Masakan rumahan hangat setiap istirahat.',
                'image_url' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=800&q=80',
                'bank_name' => 'BCA', 'bank_account' => '1234567890', 'bank_holder' => 'Rina Susanti',
                'products' => [
                    ['Nasi Goreng Spesial', 'Makanan', 15000, 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?auto=format&fit=crop&w=600&q=80'],
                    ['Ayam Geprek Sambal Ijo', 'Makanan', 18000, 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?auto=format&fit=crop&w=600&q=80'],
                    ['Soto Ayam Bening', 'Makanan', 14000, 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=600&q=80'],
                    ['Es Jeruk Segar', 'Minuman', 6000, 'https://images.unsplash.com/photo-1613478223719-2ab802602423?auto=format&fit=crop&w=600&q=80'],
                ],
                'staff' => [
                    ['Bu Rina', 'owner@bu-rina.id', 'owner123', Role::TenantAdmin],
                    ['Dimas — Kasir', 'staff@bu-rina.id', 'staff123', Role::TenantStaf],
                ],
            ],
            [
                'name' => 'Koperasi Siswa', 'description' => 'Minuman dingin, roti, dan kebutuhan harian siswa.',
                'image_url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=800&q=80',
                'bank_name' => 'Mandiri', 'bank_account' => '9876543210', 'bank_holder' => 'Koperasi Siswa SMK',
                'products' => [
                    ['Es Teh Manis Jumbo', 'Minuman', 5000, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80'],
                    ['Paket Roti & Susu', 'Paket Hemat', 12000, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80', false],
                    ['Air Mineral 600ml', 'Minuman', 4000, 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?auto=format&fit=crop&w=600&q=80'],
                ],
                'staff' => [
                    ['Pak Budi', 'owner@kopsis.id', 'owner123', Role::TenantAdmin],
                ],
            ],
            [
                'name' => 'Jajan Corner', 'description' => 'Camilan kekinian, pedas, dan gurih.',
                'image_url' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=800&q=80',
                'products' => [
                    ['Tahu Walik Crispy', 'Jajanan', 10000, 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&w=600&q=80'],
                    ['Mie Level Kantin', 'Makanan', 13000, 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?auto=format&fit=crop&w=600&q=80'],
                    ['Cireng Bumbu Rujak', 'Jajanan', 8000, 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=600&q=80'],
                ],
                'staff' => [
                    ['Kak Sari', 'owner@jajancorner.id', 'owner123', Role::TenantAdmin],
                ],
            ],
        ];

        foreach ($tenants as $data) {
            $tenant = Tenant::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'], 'description' => $data['description'], 'image_url' => $data['image_url'],
                    'status' => Tenant::STATUS_ACTIVE, 'is_open' => true,
                    'bank_name' => $data['bank_name'] ?? null, 'bank_account' => $data['bank_account'] ?? null, 'bank_holder' => $data['bank_holder'] ?? null,
                ],
            );

            foreach ($data['products'] as $row) {
                [$name, $cat, $price, $img] = $row;
                Product::withoutGlobalScopes()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $name],
                    ['category_id' => $categories[$cat]->id, 'price' => $price, 'image_url' => $img, 'is_available_today' => $row[4] ?? true],
                );
            }

            foreach ($data['staff'] as [$name, $email, $password, $role]) {
                User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $password, 'role' => $role, 'tenant_id' => $tenant->id, 'is_active' => true]);
            }
        }

        // ===== Pembeli demo =====
        User::updateOrCreate(['email' => 'siswa@smkgo.id'], ['name' => 'Nadia Pratama', 'identifier' => '2024001', 'password' => 'buyer123', 'role' => Role::User, 'is_active' => true]);
        User::updateOrCreate(['email' => 'guru@smkgo.id'], ['name' => 'Pak Hendra', 'identifier' => '19870512', 'password' => 'guru123', 'role' => Role::User, 'is_active' => true]);
    }
}
