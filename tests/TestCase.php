<?php

namespace Tests;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function buyer() { return \App\Models\User::where('email', 'siswa@smkgo.id')->firstOrFail(); }

    protected function staff() { return \App\Models\User::where('email', 'staff@bu-rina.id')->firstOrFail(); }

    protected function owner() { return \App\Models\User::where('email', 'owner@bu-rina.id')->firstOrFail(); }

    protected function admin() { return \App\Models\User::where('email', 'admin@smkgo.id')->firstOrFail(); }

    protected function tenantRina() { return \App\Models\Tenant::where('slug', 'kantin-bu-rina')->firstOrFail(); }

    protected function tenantKopsis() { return \App\Models\Tenant::where('slug', 'koperasi-siswa')->firstOrFail(); }
}
